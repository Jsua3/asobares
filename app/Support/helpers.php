<?php

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

if (! function_exists('ajuste')) {
    /**
     * Lee un texto institucional editable desde el panel.
     *
     * Existe para que ninguna vista lleve contenido quemado (RNF-09):
     * en Blade se escribe `{{ ajuste('hero_titulo') }}`.
     */
    function ajuste(string $clave, mixed $porDefecto = ''): mixed
    {
        return Setting::valor($clave, $porDefecto);
    }
}

if (! function_exists('pesos')) {
    /** Formatea un monto en pesos colombianos: $1.250.000 */
    function pesos(float|int|string|null $monto): string
    {
        return '$'.number_format((float) $monto, 0, ',', '.');
    }
}

if (! function_exists('ordenarEnEspanol')) {
    /**
     * Ordena alfabéticamente de verdad, no por bytes.
     *
     * `ORDER BY nombre` en SQLite usa colación BINARIA: 'Z' es 0x5A y 'Á' es
     * 0xC3 0x81, así que «Zorba» sale antes que «Ámbar». En un sitio en
     * español eso se lee como desorden --que es exactamente la queja que
     * originó OBS3-06-- y encima cambia entre motores: MySQL con
     * `utf8mb4_unicode_ci` sí ordena bien, así que el defecto aparecería en
     * desarrollo y no en producción, o al revés.
     *
     * Se usa sobre colecciones ya acotadas (la portada trae seis). Para una
     * lista paginada no sirve: ahí el orden lo tiene que dar la base.
     *
     * @template TClave of array-key
     * @template TValor
     *
     * @param  Collection<TClave, TValor>  $elementos
     * @return Collection<TClave, TValor>
     */
    function ordenarEnEspanol(Collection $elementos, string $campo = 'nombre'): Collection
    {
        // Sin `intl` no hay comparación con locale. Se degrada al orden que
        // trae la colección en vez de reventar: un orden imperfecto es peor
        // que uno perfecto y mejor que un error 500.
        if (! class_exists(Collator::class)) {
            return $elementos;
        }

        $comparador = new Collator('es_CO');

        return $elementos
            ->sort(fn ($a, $b): int => $comparador->compare(
                (string) data_get($a, $campo),
                (string) data_get($b, $campo)
            ))
            ->values();
    }
}

if (! function_exists('enlaceSeguro')) {
    /**
     * Deja pasar sólo destinos http(s).
     *
     * La validación del formulario es la primera barrera, pero los ajustes
     * también se cargan de semillas y de la base: si alguna vez entra por ahí
     * un `javascript:` o un `data:`, el enlace del pie lo ejecutaría en el
     * origen del sitio. Aquí se descarta y el enlace simplemente no se pinta.
     */
    function enlaceSeguro(?string $url): ?string
    {
        $limpio = trim((string) $url);

        if ($limpio === '') {
            return null;
        }

        return str_starts_with(strtolower($limpio), 'https://')
            || str_starts_with(strtolower($limpio), 'http://')
            ? $limpio
            : null;
    }
}

if (! function_exists('enlaceWhatsapp')) {
    /** Arma un enlace de WhatsApp con mensaje precargado. */
    function enlaceWhatsapp(?string $numero, string $mensaje = ''): ?string
    {
        if (blank($numero)) {
            return null;
        }

        $limpio = preg_replace('/\D/', '', $numero) ?? '';

        if ($limpio === '') {
            return null;
        }

        // Números colombianos de 10 dígitos: se les antepone el indicativo.
        if (strlen($limpio) === 10) {
            $limpio = '57'.$limpio;
        }

        return 'https://wa.me/'.$limpio.($mensaje !== '' ? '?text='.rawurlencode($mensaje) : '');
    }
}

if (! function_exists('esImagenDeRelleno')) {
    /**
     * Las portadas del demo las dibuja `GeneradorImagen` con un PNG
     * transparente cuyo nombre es el md5 de la semilla. No son fotografía:
     * si se pintan en la portada tapan el banco editorial y la Home se lee
     * como maqueta. Una foto subida por Filament nace con ULID, no con md5.
     */
    function esImagenDeRelleno(?string $ruta, ?string $disco = null): bool
    {
        if (! filled($ruta)) {
            return true;
        }

        if (preg_match('/^[a-f0-9]{32}\.png$/i', basename($ruta)) === 1) {
            return true;
        }

        $disco ??= config('almacenamiento.publico', 'public');

        return ! Storage::disk($disco)->exists($ruta);
    }
}

if (! function_exists('urlDeFotoDeLaHome')) {
    /**
     * Cadena visual de la portada: foto real → asset editorial → nada
     * (el llamador pinta el fallback gráfico).
     */
    function urlDeFotoDeLaHome(?string $rutaAlmacenada, ?string $editorial = null, ?string $disco = null): ?string
    {
        $disco ??= config('almacenamiento.publico', 'public');

        if (filled($rutaAlmacenada) && ! esImagenDeRelleno($rutaAlmacenada, $disco)) {
            return Storage::disk($disco)->url($rutaAlmacenada);
        }

        if (filled($editorial) && is_file(public_path($editorial))) {
            return asset($editorial);
        }

        return null;
    }
}

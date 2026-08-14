<?php

use App\Models\Setting;

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

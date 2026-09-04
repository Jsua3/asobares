<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * La franja «El gremio en cifras» de la portada (D-25, Acta 05).
 *
 * Cuatro ranuras que la oficina teclea en «Ajustes del sitio» cada quince
 * días con el archivo de la contadora: un número y qué significa. Se pintan
 * solo las que tengan número, y la franja entera desaparece cuando ninguna lo
 * tiene: el sitio nace sin cifras y no presume nada, igual que el directorio.
 * El sistema no lee el archivo de la contadora; es la fuente de la que la
 * oficina copia los números (la lectura automática es Fase II).
 */
final class CifrasDelGremio
{
    public const int RANURAS = 4;

    /** El grupo de ajustes que `SettingSeeder` crea y no vuelve a pisar. */
    public const string GRUPO = 'gremio';

    public const string CLAVE_TITULO = 'portada_gremio_titulo';

    public static function clave(int $ranura): string
    {
        return "gremio_cifra_{$ranura}";
    }

    public static function claveDetalle(int $ranura): string
    {
        return self::clave($ranura).'_detalle';
    }

    /** @return list<string> */
    public static function claves(): array
    {
        $claves = [];

        foreach (range(1, self::RANURAS) as $ranura) {
            $claves[] = self::clave($ranura);
            $claves[] = self::claveDetalle($ranura);
        }

        return $claves;
    }

    /**
     * Las ranuras con número, en su orden.
     *
     * @return Collection<int, array{valor: string, texto: string}>
     */
    public static function vigentes(): Collection
    {
        return collect(range(1, self::RANURAS))
            ->map(fn (int $ranura): array => [
                'valor' => trim((string) ajuste(self::clave($ranura))),
                'texto' => trim((string) ajuste(self::claveDetalle($ranura))),
            ])
            ->filter(fn (array $cifra): bool => $cifra['valor'] !== '')
            ->values();
    }

    /**
     * La fecha de la última cifra que cambió, o `null` si no hay ninguna.
     *
     * Sale de `updated_at`, y por eso «Ajustes del sitio» escribe solo lo que
     * cambia: una actualización masiva sellaría la fecha de todas las claves
     * en cada guardado y esta diría la del último guardado de cualquier cosa.
     */
    public static function actualizadasEl(): ?CarbonImmutable
    {
        $fecha = Setting::query()
            ->whereIn('clave', self::claves())
            ->where('valor', '<>', '')
            ->max('updated_at');

        return $fecha === null ? null : CarbonImmutable::parse($fecha);
    }
}

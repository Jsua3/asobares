<?php

namespace App\Support;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Municipio;
use Illuminate\Support\Collection;

/**
 * «A todos o nada» (OBS3-05).
 *
 * En la revisión del 28 de agosto se propuso darle un espacio a las alcaldías
 * junto a los demás aliados institucionales, y el directivo lo paró en seco:
 *
 *   --«tenemos un espacio para las alcaldías» (R21 03:35)
 *   --«es para no abrir susceptibilidades... no le toca nombrarlos a todos,
 *      porque uno no se nombre» (R21 03:41-03:44)
 *   --«a todos o nada» (R21 03:47)
 *
 * No es una preferencia estética: es política. Nombrar la alcaldía de un
 * municipio y no la del vecino le cuesta al gremio una relación que necesita
 * para lo que de verdad hace, que es sentarse con las instituciones.
 *
 * El §27.5 lo fija como regla de contenido y el acta pide «documentarla para
 * que nadie la rompa después». Documentarla no basta: quien la rompa lo hará
 * sin leerla, cargando una alcaldía un martes por la tarde. Por eso la regla
 * se aplica al pintar --si falta una, no sale ninguna-- y así el juego parcial
 * es literalmente irrepresentable en el sitio.
 *
 * Deja de ser una promesa y pasa a ser una propiedad.
 */
class ReglaDeAlcaldias
{
    /**
     * Los municipios cubiertos que aún no tienen su alcaldía entre los
     * aliados visibles. Vacío significa que la regla se cumple.
     *
     * @param  Collection<int, Aliado>  $visibles
     * @return Collection<int, Municipio>
     */
    public function faltantes(Collection $visibles): Collection
    {
        $cubiertos = $this->municipiosConAlcaldia($visibles);

        return Municipio::query()
            ->orderBy('nombre')
            ->get()
            ->reject(fn (Municipio $municipio): bool => $cubiertos->contains($municipio->getKey()));
    }

    /**
     * Ninguna alcaldía cargada también cumple: «nada» es la mitad válida de
     * «a todos o nada», y es el estado en que nace el sitio.
     *
     * @param  Collection<int, Aliado>  $visibles
     */
    public function seCumple(Collection $visibles): bool
    {
        $conAlcaldia = $this->municipiosConAlcaldia($visibles);

        return $conAlcaldia->isEmpty() || $this->faltantes($visibles)->isEmpty();
    }

    /**
     * Los aliados que de verdad pueden salir al sitio: si el juego de
     * alcaldías está incompleto, se caen todas y se queda el resto.
     *
     * @param  Collection<int, Aliado>  $visibles
     * @return Collection<int, Aliado>
     */
    public function filtrar(Collection $visibles): Collection
    {
        if ($this->seCumple($visibles)) {
            return $visibles;
        }

        return $visibles->reject(fn (Aliado $aliado): bool => $this->esAlcaldia($aliado))->values();
    }

    /**
     * Una alcaldía es un aliado institucional atado a un municipio. Los dos
     * requisitos importan: un patrocinador comercial con sede en Salento no
     * es la Alcaldía de Salento, y atarlo no debe activar la regla.
     */
    public function esAlcaldia(Aliado $aliado): bool
    {
        return $aliado->municipio_id !== null
            && $aliado->tipo === TipoAliado::Institucional;
    }

    /**
     * @param  Collection<int, Aliado>  $visibles
     * @return Collection<int, int>
     */
    private function municipiosConAlcaldia(Collection $visibles): Collection
    {
        return $visibles
            ->filter(fn (Aliado $aliado): bool => $this->esAlcaldia($aliado))
            ->pluck('municipio_id')
            ->unique()
            ->values();
    }
}

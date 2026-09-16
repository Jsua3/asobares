<?php

namespace App\Support;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Municipio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * «A todos o nada»: o salen entre los aliados las alcaldías de todos los
 * municipios, o no sale ninguna.
 *
 * No es una preferencia estética: es política. Nombrar la alcaldía de un
 * municipio y no la del vecino le cuesta al gremio una relación que necesita
 * para lo que de verdad hace, que es sentarse con las instituciones.
 *
 * Documentar la regla no basta: quien la rompa lo hará sin leerla, cargando
 * una alcaldía un martes por la tarde. Por eso se aplica al pintar --si falta
 * una, no sale ninguna-- y el juego parcial es irrepresentable en el sitio.
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
     * La regla aplicada a los aliados que hoy salen al sitio. La usa quien
     * pinta a un aliado fuera de la portada, como el organizador de un evento.
     */
    public function seCumpleEnElSitio(): bool
    {
        return $this->seCumple(Aliado::visible()->get());
    }

    /**
     * `esAlcaldia()` escrita como consulta, para acotar aliados en la base.
     * Tiene que decir lo mismo que la versión en memoria de abajo.
     *
     * @param  Builder<Aliado>  $aliados
     * @return Builder<Aliado>
     */
    public function sinAlcaldias(Builder $aliados): Builder
    {
        return $aliados->where(function (Builder $noAlcaldias): void {
            $noAlcaldias
                ->whereNull('municipio_id')
                ->orWhere('tipo', '!=', TipoAliado::Institucional->value);
        });
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

<?php

namespace App\Models;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use Database\Factories\AspiranteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Banco de talento del gremio: bartender, chef, mesero, administrador...
 *
 * Es distinto de una postulación. Aquí la persona deja su perfil sin apuntar
 * a ninguna vacante, para los cargos escasos que el gremio conecta a mano.
 */
class Aspirante extends Model
{
    /** @use HasFactory<AspiranteFactory> */
    use HasFactory;

    protected $table = 'aspirantes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'categoria_cargo' => CargoDelSector::class,
            'estado' => EstadoDeGestion::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
            'aprobado_el' => 'datetime',
        ];
    }

    public function estaAprobado(): bool
    {
        return $this->aprobado_el !== null;
    }

    /**
     * Lo que el establecimiento afiliado puede ver: aprobado por la secretaría
     * y no descartado. Las dos condiciones van juntas a propósito, porque son
     * la misma pregunta desde dos lados y separarlas invita a que una consulta
     * nueva se acuerde solo de una.
     *
     * @param  Builder<Aspirante>  $consulta
     * @return Builder<Aspirante>
     */
    public function scopeVisibleParaAfiliados(Builder $consulta): Builder
    {
        return $consulta
            ->whereNotNull('aprobado_el')
            ->where('estado', '!=', EstadoDeGestion::Descartado);
    }
}

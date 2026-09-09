<?php

namespace App\Models;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use Database\Factories\AspiranteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

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

    use LogsActivity;

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
     * RF-39. Aprobar un perfil **entrega el nombre, el teléfono y el correo de
     * una persona a todos los establecimientos afiliados**; retirarlo se los
     * quita. Son las dos decisiones más sensibles del panel en materia de datos
     * personales y hasta el 9 de septiembre de 2026 no dejaban rastro ninguno:
     * `aprobado_el` guardaba cuándo, nunca quién, y retirar ponía esa columna en
     * nulo, borrando la única huella.
     *
     * Se registra **solo `aprobado_el`** y no el resto de columnas: el nombre, el
     * teléfono, el correo y la experiencia son los datos personales que este
     * módulo existe para custodiar, y copiarlos a la tabla de actividad --que no
     * tiene purga-- los haría sobrevivir a la depuración de `bolsas:depurar`. La
     * bitácora anota la decisión, no el expediente.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['aprobado_el'])
            ->logOnlyDirty()
            // Sin esto, cada edición del perfil desde el panel --un cambio de
            // estado de gestión, una nota-- dejaría una entrada vacía en la
            // bitácora que dice «actualizó» sin haber cambiado la visibilidad.
            ->dontLogEmptyChanges()
            ->useLogName('aspirante')
            ->setDescriptionForEvent(fn (string $evento): string => $this->estaAprobado()
                ? "Perfil de {$this->nombre}: entra al banco de talento y queda visible para los afiliados"
                : "Perfil de {$this->nombre}: sale del banco de talento y deja de verse");
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

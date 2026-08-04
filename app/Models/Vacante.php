<?php

namespace App\Models;

use App\Enums\CargoDelSector;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Models\Concerns\EsPublicable;
use Database\Factories\VacanteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Bolsa de empleo del sector: la publica el establecimiento asociado y la
 * aprueba la secretaría. Nadie del gremio edita una vacante ajena.
 */
class Vacante extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<VacanteFactory> */
    use HasFactory;

    protected $table = 'vacantes';

    protected $guarded = ['id'];

    /**
     * Bandera transitoria, nunca persistida: la fijan `cerrar()` y
     * `reabrir()` en `MisVacantesController` para que el guardado no se
     * confunda con una edición de contenido.
     *
     * `FlujoDeAprobacionObserver` degrada a `pendiente_aprobacion` cualquier
     * guardado de un registro publicado hecho por quien no puede publicar,
     * sin distinguir qué cambió. Cerrar y reabrir no son eso: son un cambio
     * de ciclo de vida que no pasa por aprobación (ver `VacantePolicy::cerrar()`).
     */
    public bool $saltaFlujoDeAprobacion = false;

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoVacante::class,
            'categoria_cargo' => CargoDelSector::class,
            'fecha_limite' => 'date',
            'cerrada_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Asociado, $this> */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    /** @return HasMany<Postulacion, $this> */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /**
     * Sigue en pie: ni cerrada a mano ni pasada de fecha.
     *
     * Se resuelve en la consulta y no con un cron, igual que la vigencia de
     * los proveedores: una vacante de una noche desaparece sola al día
     * siguiente aunque nadie entre al sistema.
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query
            ->whereNull('cerrada_at')
            ->where(function (Builder $q): void {
                $q->whereNull('fecha_limite')->orWhereDate('fecha_limite', '>=', now()->toDateString());
            });
    }

    public function estaCerrada(): bool
    {
        return $this->cerrada_at !== null;
    }

    public function estaVencida(): bool
    {
        return $this->fecha_limite !== null && $this->fecha_limite->lt(now()->startOfDay());
    }

    public function estaVigente(): bool
    {
        return ! $this->estaCerrada() && ! $this->estaVencida();
    }

    /** Solo una vacante viva y aprobada recibe gente. */
    public function aceptaPostulaciones(): bool
    {
        return $this->estaPublicado() && $this->estaVigente();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cargo', 'estado', 'asociado_id', 'cerrada_at'])
            ->logOnlyDirty()
            ->useLogName('vacante')
            ->setDescriptionForEvent(fn (string $evento): string => "Vacante {$this->cargo}: {$evento}");
    }
}

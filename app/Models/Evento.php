<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use App\Models\Concerns\EsPublicable;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Solo eventos del gremio (ExpoBar, Congreso Nacional, capacitaciones propias).
 * Nunca eventos de bares individuales.
 */
class Evento extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<EventoFactory> */
    use HasFactory;

    protected $table = 'eventos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoEvento::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'precio' => 'decimal:2',
            'permite_inscripcion' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<Inscripcion, $this> */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function scopeProximo(Builder $query): Builder
    {
        return $query->where('fecha_inicio', '>=', now())->orderBy('fecha_inicio');
    }

    public function scopePasado(Builder $query): Builder
    {
        return $query->where('fecha_inicio', '<', now())->orderByDesc('fecha_inicio');
    }

    public function esGratuito(): bool
    {
        return (float) $this->precio <= 0;
    }

    public function esFuturo(): bool
    {
        return $this->fecha_inicio->isFuture();
    }

    /** El registro lo maneja la Nacional: el botón lleva a su plataforma. */
    public function delegaRegistroExterno(): bool
    {
        return filled($this->enlace_externo);
    }

    public function cuposDisponibles(): ?int
    {
        if ($this->cupos === null) {
            return null;
        }

        return max(0, $this->cupos - $this->inscripciones()->count());
    }

    public function admiteInscripciones(): bool
    {
        if (! $this->permite_inscripcion || $this->delegaRegistroExterno() || ! $this->esFuturo()) {
            return false;
        }

        return $this->cuposDisponibles() === null || $this->cuposDisponibles() > 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'fecha_inicio', 'precio'])
            ->logOnlyDirty()
            ->useLogName('evento')
            ->setDescriptionForEvent(fn (string $evento): string => "Evento {$this->titulo}: {$evento}");
    }
}

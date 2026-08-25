<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use App\Models\Concerns\EsPublicable;
use Carbon\CarbonInterface;
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

    /**
     * Un evento EN CURSO sigue siendo próximo: lo que decide es cuándo
     * TERMINA, no cuándo empezó. Mirando sólo `fecha_inicio`, el Congreso
     * Nacional —tres días, `EventoSeeder`— se mudaba a «Pasados» el minuto uno
     * de su segundo día, con dos días todavía por delante, y la visitante que
     * quería inscribirse tenía que ir a buscarlo al archivo.
     *
     * `fecha_fin` es nullable, así que el evento de un solo momento se trata
     * como un rango degenerado vía COALESCE, que hablan igual SQLite —el motor
     * de este proyecto—, MySQL y Postgres.
     */
    public function scopeProximo(Builder $query): Builder
    {
        return $query
            ->whereRaw('COALESCE(fecha_fin, fecha_inicio) >= ?', [now()])
            ->orderBy('fecha_inicio');
    }

    /**
     * Simétrico del anterior, y tiene que moverse con él: si sólo se corrigiera
     * `proximo()`, un evento en curso saldría en las DOS pestañas y los dos
     * contadores sumarían más que el total de eventos publicados.
     */
    public function scopePasado(Builder $query): Builder
    {
        return $query
            ->whereRaw('COALESCE(fecha_fin, fecha_inicio) < ?', [now()])
            ->orderByDesc('fecha_inicio');
    }

    /**
     * Eventos que TOCAN la ventana, no sólo los que arrancan dentro de ella.
     *
     * Es lo que necesita una casilla de calendario: el Congreso Nacional dura
     * tres días y tiene que salir en las tres, no sólo en la del arranque. Se
     * solapan dos intervalos con la regla clásica —arranca antes de que la
     * ventana acabe Y termina después de que empiece—, y por eso `whereBetween`
     * sobre `fecha_inicio` no vale: dejaría fuera el evento que empezó el mes
     * pasado y sigue corriendo.
     *
     * Aprovecha el índice `['estado','fecha_inicio']` de la migración por sus
     * dos primeras columnas.
     */
    public function scopeEnRango(Builder $query, CarbonInterface $desde, CarbonInterface $hasta): Builder
    {
        return $query
            ->where('fecha_inicio', '<=', $hasta)
            ->whereRaw('COALESCE(fecha_fin, fecha_inicio) >= ?', [$desde])
            ->orderBy('fecha_inicio');
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

        /*
         * El `loadCount('inscripciones')` del controlador no servía de nada:
         * este método re-consultaba siempre. Medido sobre una petición real a
         * `/eventos/{slug}`: SEIS `select count(*) from inscripciones`, porque
         * la ficha encadena `cuposDisponibles()` y `admiteInscripciones()`
         * desde tres puntos distintos y el segundo llama al primero dos veces.
         *
         * Ninguna de las seis daba error ni tardaba: es el modo de fallo que
         * sólo se ve contando consultas.
         */
        $inscritos = $this->inscripciones_count ?? $this->inscripciones()->count();

        return max(0, $this->cupos - $inscritos);
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

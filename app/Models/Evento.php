<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Models\Concerns\EsPublicable;
use App\Support\ReglaDeAlcaldias;
use Carbon\CarbonInterface;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Eventos del gremio y de aliados institucionales o comerciales.
 */
class Evento extends Model
{
    use EsPublicable, LogsActivity;

    public const ORGANIZADOR_ASOBARES = 'ASOBARES Capítulo Quindío';

    /** @use HasFactory<EventoFactory> */
    use HasFactory;

    protected $table = 'eventos';

    protected $guarded = ['id'];

    private bool $altaComunitariaValidada = false;

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoEvento::class,
            'origen' => OrigenEvento::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'precio' => 'decimal:2',
            'permite_inscripcion' => 'boolean',
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Evento $evento): void {
            $evento->origen ??= OrigenEvento::Asobares;

            $origen = $evento->origen instanceof OrigenEvento
                ? $evento->origen
                : OrigenEvento::from((string) $evento->origen);

            if ($evento->fecha_fin !== null && $evento->fecha_inicio !== null && $evento->fecha_fin->lessThan($evento->fecha_inicio)) {
                throw ValidationException::withMessages([
                    'fecha_fin' => 'El evento no puede terminar antes de empezar.',
                ]);
            }

            if ($origen === OrigenEvento::Asobares) {
                $evento->aliado_id = null;

                return;
            }

            if ($origen === OrigenEvento::Comunidad) {
                $evento->aliado_id = null;
                $evento->permite_inscripcion = false;
                $evento->cupos = null;
                $evento->precio = 0;

                return;
            }

            if (blank($evento->aliado_id)) {
                throw ValidationException::withMessages([
                    'aliado_id' => 'Selecciona el aliado que organiza este evento.',
                ]);
            }

            // El gremio no inscribe ni cobra a nombre de un aliado: el cobro
            // entraría a la cuenta de Bold del gremio y los datos de quien se
            // inscribe quedarían bajo su responsabilidad. La inscripción de un
            // evento de aliado va por su enlace externo.
            $evento->permite_inscripcion = false;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Lo que el público puede ver de la agenda: el listado, el calendario, la
     * ficha, la portada y el sitemap preguntan aquí y en ningún otro sitio.
     *
     * Publicado no basta cuando organiza un aliado, porque su nombre y su web
     * salen en la ficha, en el riel y en el JSON-LD. El aliado tiene que poder
     * salir al sitio él mismo —aprobado y activo, como en la portada— y
     * respetar «a todos o nada» de las alcaldías (`ReglaDeAlcaldias`). Un
     * aliado nace apagado en el panel: sin esta compuerta su evento lo
     * publicaría antes que él. Y un evento de aliado cuyo aliado se borró
     * tampoco sale, porque se leería como organizado por ASOBARES.
     */
    public function scopeVisibleAlPublico(Builder $query): Builder
    {
        $regla = app(ReglaDeAlcaldias::class);
        $alcaldiasFuera = ! $regla->seCumpleEnElSitio();

        return $query
            ->publicado()
            ->where(function (Builder $eventos) use ($regla, $alcaldiasFuera): void {
                $eventos
                    ->whereNull('origen')
                    ->orWhere('origen', '!=', OrigenEvento::Aliado->value)
                    ->orWhereHas('aliado', function (Builder $aliado) use ($regla, $alcaldiasFuera): void {
                        $aliado->publicado()->where('activo', true);

                        if ($alcaldiasFuera) {
                            $regla->sinAlcaldias($aliado);
                        }
                    });
            });
    }

    /** Los que organiza el gremio. La portada habla con su voz y solo pinta estos. */
    public function scopeDelGremio(Builder $query): Builder
    {
        return $query->where(function (Builder $eventos): void {
            $eventos->whereNull('origen')->orWhere('origen', OrigenEvento::Asobares->value);
        });
    }

    public function esVisibleAlPublico(): bool
    {
        return $this->exists
            && static::query()->visibleAlPublico()->whereKey($this->getKey())->exists();
    }

    /** @return HasMany<Inscripcion, $this> */
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /**
     * @return BelongsTo<Aliado, $this>
     */
    public function aliado(): BelongsTo
    {
        return $this->belongsTo(Aliado::class);
    }

    /**
     * @return BelongsTo<Municipio, $this>
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /** Con coordenadas se puede pintar el punto en el mapa. */
    public function tieneUbicacion(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    /**
     * El enlace para abrir el lugar en un mapa: el que se escribió a mano o,
     * sin él, OpenStreetMap con las coordenadas. Nulo si no hay ninguno.
     */
    public function urlDelMapa(): ?string
    {
        if (filled($this->mapa_url)) {
            return $this->mapa_url;
        }

        if (! $this->tieneUbicacion()) {
            return null;
        }

        return "https://www.openstreetmap.org/?mlat={$this->lat}&mlon={$this->lng}#map=17/{$this->lat}/{$this->lng}";
    }

    /**
     * Un evento EN CURSO sigue siendo próximo: lo que decide es cuándo
     * TERMINA, no cuándo empezó. Mirando sólo `fecha_inicio`, un evento de
     * varios días pasaría a «Pasados» en su segundo día, con días todavía por
     * delante, y quien quiere inscribirse tendría que buscarlo en el archivo.
     *
     * `fecha_fin` es nullable, así que el evento de un solo momento se trata
     * como un rango degenerado vía COALESCE, que se escribe igual en SQLite,
     * MySQL y PostgreSQL.
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

    public function esDeAliado(): bool
    {
        return $this->origenPublico() === OrigenEvento::Aliado;
    }

    public function esDeLaComunidad(): bool
    {
        return $this->origenPublico() === OrigenEvento::Comunidad;
    }

    public function marcarAltaComunitariaValidada(): void
    {
        $this->altaComunitariaValidada = true;
    }

    public function esAltaComunitariaValidada(): bool
    {
        return ! $this->exists && $this->esDeLaComunidad() && $this->altaComunitariaValidada;
    }

    /**
     * Los eventos anteriores a la columna `origen` pueden llegar en null.
     * En lectura se interpretan como ASOBARES; no como aliado.
     */
    public function origenPublico(): OrigenEvento
    {
        return $this->origen ?? OrigenEvento::Asobares;
    }

    public function organizadorVisible(): ?string
    {
        if ($this->esDeLaComunidad()) {
            return null;
        }

        if ($this->esDeAliado() && $this->aliado !== null) {
            return $this->aliado->nombre;
        }

        return self::ORGANIZADOR_ASOBARES;
    }

    /**
     * @return array<string, string>|null
     */
    public function organizadorJsonLd(): ?array
    {
        if ($this->esDeLaComunidad()) {
            return null;
        }

        $organizador = [
            '@type' => 'Organization',
            'name' => $this->organizadorVisible(),
        ];

        if ($this->esDeAliado()) {
            if (filled($this->aliado?->url)) {
                $organizador['url'] = $this->aliado->url;
            }

            return $organizador;
        }

        $organizador['url'] = route('inicio');

        return $organizador;
    }

    public function cuposDisponibles(): ?int
    {
        if ($this->cupos === null) {
            return null;
        }

        /*
         * Usa el `inscripciones_count` que carga el controlador con
         * `loadCount()` antes de consultar: la ficha encadena
         * `cuposDisponibles()` y `admiteInscripciones()` desde varios puntos
         * y el segundo llama al primero dos veces, así que sin este atajo
         * cada llamada repetiría el `count(*)`. Es un fallo que no da error
         * ni tarda: solo se ve contando consultas.
         */
        $inscritos = $this->inscripciones_count ?? $this->inscripciones()->count();

        return max(0, $this->cupos - $inscritos);
    }

    public function admiteInscripciones(): bool
    {
        // `esDeAliado()` además del guardado: una fila anterior a la regla
        // puede traer `permite_inscripcion` encendido.
        if (! $this->permite_inscripcion || $this->esDeAliado() || $this->esDeLaComunidad() || $this->delegaRegistroExterno() || ! $this->esFuturo()) {
            return false;
        }

        return $this->cuposDisponibles() === null || $this->cuposDisponibles() > 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'fecha_inicio', 'precio', 'origen', 'aliado_id'])
            ->logOnlyDirty()
            ->useLogName('evento')
            ->setDescriptionForEvent(fn (string $evento): string => "Evento {$this->titulo}: {$evento}");
    }
}

<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\AsociadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Asociado extends Model implements HasMedia
{
    use EsPublicable, InteractsWithMedia, LogsActivity;

    /** @use HasFactory<AsociadoFactory> */
    use HasFactory;

    protected $table = 'asociados';

    protected $guarded = ['id'];

    /**
     * Campos internos del gremio. Nunca se exponen en el sitio público:
     * el propietario del establecimiento decide qué información suya se publica.
     *
     * @var list<string>
     */
    public const array CAMPOS_INTERNOS = [
        'representante',
        // NIT o cedula del titular. En la base real del gremio 24 de 41 filas
        // traen cedula de persona natural, no NIT de empresa: es un dato de
        // identificacion y no se publica jamas.
        'documento',
        'correo_interno',
        'telefono_interno',
        'fecha_afiliacion',
        'autorizacion_datos_at',
        'autorizacion_datos_origen',
        'notas_internas',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'destacado' => 'boolean',
            'lat' => 'float',
            'lng' => 'float',
            'fecha_afiliacion' => 'date',
            'autorizacion_datos_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * El buscador por nombre del directorio público, sin distinguir mayúsculas.
     *
     * `whereLike(caseSensitive: false)` y NO `where(…, 'like', …)`. El `LIKE`
     * de SQLite es insensible a mayúsculas para ASCII y el de PostgreSQL es
     * sensible: con la forma antigua, buscar «bar merlín» dejaba de encontrar
     * «Bar Merlín» el día del despliegue, en silencio y sin ningún error.
     * Medido contra PostgreSQL 17 sobre los diez establecimientos sembrados,
     * `like '%bar%'` devolvía 4 filas donde `ilike '%bar%'` devuelve 10.
     *
     * Lo resuelve la gramática del propio Laravel —emite `ilike` en Postgres y
     * `like` en SQLite—, así que no hay que mantener un `match` por driver ni
     * arriesgarse a mandarle a SQLite un operador que no existe.
     *
     * Vive aquí y no en el controlador para que la guardia pueda afirmar la
     * SQL generada por los dos motores: la suite corre sobre SQLite, donde el
     * defecto NO se reproduce, y una prueba de comportamiento sola pasaría
     * igual de verde con el código roto.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeBuscarPorNombre(Builder $query, string $texto): Builder
    {
        return $query->whereLike('nombre', '%'.$texto.'%', caseSensitive: false);
    }

    /** @return BelongsTo<Categoria, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /** @return HasOne<Cartera, $this> */
    public function cartera(): HasOne
    {
        return $this->hasOne(Cartera::class);
    }

    /** @return HasMany<Vacante, $this> */
    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class);
    }

    /** @return HasMany<Transaccion, $this> */
    public function transacciones(): HasMany
    {
        return $this->hasMany(Transaccion::class);
    }

    /** @return HasMany<User, $this> */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * La propiedad que decide si una foto de la galería sale al sitio.
     *
     * OBS3-13. El directivo puso la condición al enterarse de que el
     * propietario subiría fotos: «lo tienen que aprobar ellos, no sea que
     * pongan imágenes... exóticas» (R23 00:45-01:05). Vive en las propiedades
     * de medialibrary y no en una columna porque el sujeto de la aprobación
     * es cada archivo, no la ficha.
     */
    public const string FOTO_APROBADA = 'aprobada';

    /** Motivo escrito cuando la secretaría devuelve una foto. */
    public const string FOTO_MOTIVO = 'motivo_rechazo';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('galeria')
            ->useDisk(config('almacenamiento.publico'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Lo único que puede ver un visitante.
     *
     * El valor por defecto es `false` a propósito: una foto sin marcar es una
     * foto sin aprobar. Si el defecto fuera `true`, cualquier ruta que
     * olvidara sellar la propiedad publicaría material sin moderar, que es
     * exactamente lo que la regla existe para impedir.
     *
     * @return Collection<int, Media>
     */
    public function fotosAprobadas(): Collection
    {
        return $this->getMedia('galeria')
            ->filter(fn ($media): bool => (bool) $media->getCustomProperty(self::FOTO_APROBADA, false))
            ->values();
    }

    /** Las que esperan a la secretaría. Solo las ve el dueño y el panel. */
    public function fotosPendientes(): Collection
    {
        return $this->getMedia('galeria')
            ->filter(fn ($media): bool => ! (bool) $media->getCustomProperty(self::FOTO_APROBADA, false))
            ->values();
    }

    /**
     * Todo se sirve en webp (RNF-02). `nonQueued` porque el demo corre con
     * QUEUE_CONNECTION=sync y las semillas deben quedar listas al instante.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 300)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('grande')
            ->fit(Fit::Max, 1200, 900)
            ->format('webp')
            ->nonQueued();
    }

    /**
     * Vista pública de la ficha: excluye explícitamente los campos internos.
     *
     * @return array<string, mixed>
     */
    public function datosPublicos(): array
    {
        return collect($this->attributesToArray())
            ->except(self::CAMPOS_INTERNOS)
            ->all();
    }

    /**
     * ¿Hay evidencia de que el titular autorizó el tratamiento de sus datos?
     *
     * No basta con que alguien lo recuerde: la Ley 1581 pregunta cuándo y con
     * qué soporte. Mientras esto sea falso, la ficha no debería publicarse.
     */
    public function tieneAutorizacionDeDatos(): bool
    {
        return $this->autorizacion_datos_at !== null;
    }

    public function tieneUbicacion(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'destacado', 'municipio_id', 'categoria_id'])
            ->logOnlyDirty()
            ->useLogName('asociado')
            ->setDescriptionForEvent(fn (string $evento): string => "Asociado {$this->nombre}: {$evento}");
    }
}

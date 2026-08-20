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
        'correo_interno',
        'telefono_interno',
        'fecha_afiliacion',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('galeria')
            ->useDisk(config('almacenamiento.publico'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
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

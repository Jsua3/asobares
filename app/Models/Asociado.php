<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Asociado extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\AsociadoFactory> */
    use HasFactory;

    use EsPublicable, InteractsWithMedia, LogsActivity;

    protected $table = 'asociados';

    protected $guarded = ['id'];

    /**
     * Campos internos del gremio. Nunca se exponen en el sitio pÃºblico:
     * el propietario del establecimiento decide quÃ© informaciÃ³n suya se publica.
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
            ->useDisk('public')
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
     * Vista pÃºblica de la ficha: excluye explÃ­citamente los campos internos.
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

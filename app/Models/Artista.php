<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Models\Concerns\EsPublicable;
use App\Support\VideoDeYoutube;
use Database\Factories\ArtistaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Categoría aparte del empleo: "el DJ es artista, el mesero es empleo".
 */
class Artista extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<ArtistaFactory> */
    use HasFactory;

    protected $table = 'artistas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoArtista::class,
            'tarifa_desde' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * ID del video guardado. Nunca se embebe la URL cruda: el iframe se
     * arma solo con este ID validado.
     */
    public function youtubeId(): ?string
    {
        return VideoDeYoutube::id($this->video_url);
    }

    public function tieneVideo(): bool
    {
        return $this->youtubeId() !== null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'tipo', 'genero_musical'])
            ->logOnlyDirty()
            ->useLogName('artista')
            ->setDescriptionForEvent(fn (string $evento): string => "Artista {$this->nombre}: {$evento}");
    }
}

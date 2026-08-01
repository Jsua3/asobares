<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Models\Concerns\EsPublicable;
use Database\Factories\ArtistaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * CategorÃ­a aparte del empleo: "el DJ es artista, el mesero es empleo".
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
     * Extrae el ID de YouTube de la URL guardada. Nunca se embebe la URL
     * cruda: solo se arma el iframe con el ID validado.
     */
    public function youtubeId(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        $patrones = [
            '#^https?://(?:www\.)?youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#',
            '#^https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{11})#',
            '#^https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9_-]{11})#',
            '#^https?://youtu\.be/([A-Za-z0-9_-]{11})#',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $this->video_url, $coincidencias) === 1) {
                return $coincidencias[1];
            }
        }

        return null;
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

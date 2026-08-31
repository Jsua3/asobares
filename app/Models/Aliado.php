<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoAliado;
use App\Models\Concerns\EsPublicable;
use Database\Factories\AliadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Aliado extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<AliadoFactory> */
    use HasFactory;

    protected $table = 'aliados';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoAliado::class,
            'activo' => 'boolean',
        ];
    }

    /** Para salir al carrusel hace falta estar aprobado Y activo. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->publicado()->where('activo', true)->orderBy('orden');
    }

    /**
     * Solo lo llevan las alcaldias: es lo que las ata a su municipio y lo que
     * permite aplicar la regla de OBS3-05 sin adivinar por el nombre.
     *
     * @return BelongsTo<Municipio, $this>
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Institucional: respalda al gremio. Comercial: le vende a sus afiliados.
     * La portada los pinta en dos bandas distintas (OBS3-04).
     */
    public function esInstitucional(): bool
    {
        return $this->tipo === TipoAliado::Institucional;
    }

    /** El detalle del convenio es contenido privado de asociados. */
    public function tieneConvenioPrivado(): bool
    {
        return filled($this->detalle_convenio);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'tipo', 'municipio_id', 'estado', 'activo', 'orden'])
            ->logOnlyDirty()
            ->useLogName('aliado')
            ->setDescriptionForEvent(fn (string $evento): string => "Aliado {$this->nombre}: {$evento}");
    }
}

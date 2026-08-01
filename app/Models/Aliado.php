<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\AliadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            'activo' => 'boolean',
        ];
    }

    /** Para salir al carrusel hace falta estar aprobado Y activo. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->publicado()->where('activo', true)->orderBy('orden');
    }

    /** El detalle del convenio es contenido privado de asociados. */
    public function tieneConvenioPrivado(): bool
    {
        return filled($this->detalle_convenio);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'activo', 'orden'])
            ->logOnlyDirty()
            ->useLogName('aliado')
            ->setDescriptionForEvent(fn (string $evento): string => "Aliado {$this->nombre}: {$evento}");
    }
}

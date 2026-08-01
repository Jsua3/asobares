<?php

namespace App\Models;

use Database\Factories\AliadoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Aliado extends Model
{
    /** @use HasFactory<AliadoFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'aliados';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    /** El detalle del convenio es contenido privado de asociados. */
    public function tieneConvenioPrivado(): bool
    {
        return filled($this->detalle_convenio);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'activo', 'orden'])
            ->logOnlyDirty()
            ->useLogName('aliado')
            ->setDescriptionForEvent(fn (string $evento): string => "Aliado {$this->nombre}: {$evento}");
    }
}

<?php

namespace App\Models;

use Database\Factories\MunicipioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Municipio extends Model
{
    /** @use HasFactory<MunicipioFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'municipios';

    protected $fillable = ['nombre', 'slug', 'activo', 'orden'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<Asociado, $this> */
    public function asociados(): HasMany
    {
        return $this->hasMany(Asociado::class);
    }

    /** @return HasMany<RequisitoApertura, $this> */
    public function requisitos(): HasMany
    {
        return $this->hasMany(RequisitoApertura::class)->orderBy('orden');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'slug', 'activo', 'orden'])
            ->logOnlyDirty()
            ->useLogName('municipio')
            ->setDescriptionForEvent(fn (string $evento): string => "Municipio {$this->nombre}: {$evento}");
    }
}

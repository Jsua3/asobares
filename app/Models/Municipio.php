<?php

namespace App\Models;

use Database\Factories\MunicipioFactory;
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

    protected $fillable = ['nombre', 'slug'];

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

    /** @return HasMany<Artista, $this> */
    public function artistas(): HasMany
    {
        return $this->hasMany(Artista::class);
    }

    /** @return HasMany<Proveedor, $this> */
    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'slug'])
            ->logOnlyDirty()
            ->useLogName('municipio')
            ->setDescriptionForEvent(fn (string $evento): string => "Municipio {$this->nombre}: {$evento}");
    }
}

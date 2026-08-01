<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'categorias';

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'slug'])
            ->logOnlyDirty()
            ->useLogName('categoria')
            ->setDescriptionForEvent(fn (string $evento): string => "Categoría {$this->nombre}: {$evento}");
    }
}

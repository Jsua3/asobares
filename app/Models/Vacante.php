<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Models\Concerns\EsPublicable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * Bolsa de empleo del sector: solo los establecimientos asociados publican.
 */
class Vacante extends Model
{
    /** @use HasFactory<\Database\Factories\VacanteFactory> */
    use HasFactory;

    use EsPublicable, LogsActivity;

    protected $table = 'vacantes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'tipo' => TipoVacante::class,
        ];
    }

    /** @return BelongsTo<Asociado, $this> */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    /** @return HasMany<Aspirante, $this> */
    public function aspirantes(): HasMany
    {
        return $this->hasMany(Aspirante::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cargo', 'estado', 'asociado_id'])
            ->logOnlyDirty()
            ->useLogName('vacante')
            ->setDescriptionForEvent(fn (string $evento): string => "Vacante {$this->cargo}: {$evento}");
    }
}

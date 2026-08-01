<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\RequisitoAperturaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Producto insignia del sitio: la guía normativa, que difiere por municipio.
 */
class RequisitoApertura extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<RequisitoAperturaFactory> */
    use HasFactory;

    protected $table = 'requisitos_apertura';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'checklist' => 'array',
            'costo_aproximado' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tieneAdjunto(): bool
    {
        return filled($this->adjunto);
    }

    public function tieneCosto(): bool
    {
        return $this->costo_aproximado !== null && (float) $this->costo_aproximado > 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['entidad', 'estado', 'municipio_id', 'costo_aproximado'])
            ->logOnlyDirty()
            ->useLogName('requisito')
            ->setDescriptionForEvent(fn (string $evento): string => "Requisito {$this->entidad}: {$evento}");
    }
}

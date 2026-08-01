<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * Producto insignia del sitio: la guÃ­a normativa, que difiere por municipio.
 */
class RequisitoApertura extends Model
{
    /** @use HasFactory<\Database\Factories\RequisitoAperturaFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'requisitos_apertura';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
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
            ->logOnly(['entidad', 'municipio_id', 'costo_aproximado'])
            ->logOnlyDirty()
            ->useLogName('requisito')
            ->setDescriptionForEvent(fn (string $evento): string => "Requisito {$this->entidad}: {$evento}");
    }
}

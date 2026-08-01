<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "La gente no paga porque no sabe cuÃ¡nto debe, entonces todo el mundo llama
 * a Natalia." El asociado consulta su estado de cuenta en /mi-cuenta.
 */
class Cartera extends Model
{
    /** @use HasFactory<\Database\Factories\CarteraFactory> */
    use HasFactory;

    protected $table = 'carteras';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'saldo_pendiente' => 'decimal:2',
            'ultimo_pago_at' => 'date',
            'actualizado_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Asociado, $this> */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    public function scopeEnMora(Builder $query): Builder
    {
        return $query->where('meses_mora', '>', 0);
    }

    public function estaAlDia(): bool
    {
        return $this->meses_mora === 0 && (float) $this->saldo_pendiente <= 0;
    }

    /** Deja la cartera saldada tras un pago aprobado. */
    public function marcarAlDia(): void
    {
        $this->forceFill([
            'saldo_pendiente' => 0,
            'meses_mora' => 0,
            'ultimo_pago_at' => now()->toDateString(),
            'actualizado_at' => now(),
        ])->save();
    }
}

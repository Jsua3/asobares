<?php

namespace App\Models;

use Database\Factories\CarteraFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * El estado de cuenta de cada asociado. Quien no sabe cuánto debe no paga y
 * termina llamando a la oficina, así que el asociado lo consulta él mismo en
 * /mi-cuenta.
 */
class Cartera extends Model
{
    /** @use HasFactory<CarteraFactory> */
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

    /**
     * Aplica un pago sobre el saldo. Un abono parcial reduce la deuda; solo
     * cuando la cubre entera la cartera queda al día: un abono de $50.000 no
     * puede borrar una deuda de $500.000.
     */
    public function abonar(float $monto): void
    {
        $restante = round(max(0.0, (float) $this->saldo_pendiente - $monto), 2);

        if ($restante <= 0.0) {
            $this->marcarAlDia();

            return;
        }

        $mensualidad = max(1, (int) config('pagos.mensualidad'));

        $this->forceFill([
            'saldo_pendiente' => $restante,
            // Se recalcula contra lo que sigue debiendo, y nunca por encima de
            // los meses que ya tenía: un pago no puede empeorar la mora.
            'meses_mora' => min($this->meses_mora, max(1, (int) ceil($restante / $mensualidad))),
            'ultimo_pago_at' => now()->toDateString(),
            'actualizado_at' => now(),
        ])->save();
    }

    /** Deja la cartera saldada tras un pago que cubre toda la deuda. */
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

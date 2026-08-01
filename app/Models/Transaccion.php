<?php

namespace App\Models;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use Database\Factories\TransaccionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaccion extends Model
{
    /** @use HasFactory<TransaccionFactory> */
    use HasFactory;

    protected $table = 'transacciones';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'concepto' => ConceptoTransaccion::class,
            'estado' => EstadoTransaccion::class,
            'metodo' => MetodoPago::class,
            'monto' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    /** @return BelongsTo<Inscripcion, $this> */
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    /** @return BelongsTo<Asociado, $this> */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    public function scopeAprobada(Builder $query): Builder
    {
        return $query->where('estado', EstadoTransaccion::Aprobada);
    }

    public function estaAprobada(): bool
    {
        return $this->estado === EstadoTransaccion::Aprobada;
    }

    /** Referencia legible y Ãºnica: ASO-2026-A1B2C3. */
    public static function generarReferencia(): string
    {
        do {
            $referencia = sprintf('ASO-%s-%s', now()->year, strtoupper(bin2hex(random_bytes(3))));
        } while (static::where('referencia', $referencia)->exists());

        return $referencia;
    }
}

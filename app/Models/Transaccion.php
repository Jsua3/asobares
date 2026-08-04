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
use Illuminate\Support\Facades\URL;

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

    /**
     * Referencia legible y única: ASO-2026-A1B2C3D4E5F60718.
     *
     * Con 3 bytes eran 16,7 millones de combinaciones, adivinables por fuerza
     * bruta en horas. La referencia viaja por correo y por la pasarela, así
     * que no es un secreto, pero tampoco puede ser enumerable.
     */
    public static function generarReferencia(): string
    {
        do {
            $referencia = sprintf('ASO-%s-%s', now()->year, strtoupper(bin2hex(random_bytes(8))));
        } while (static::where('referencia', $referencia)->exists());

        return $referencia;
    }

    /**
     * URL de retorno tras el pago: firmada y con caducidad.
     *
     * Es la dirección que se le entrega a Bold como `callback_url` y a la que
     * vuelve quien paga. Muestra el detalle del cobro, así que la referencia
     * por sí sola no puede abrirla.
     */
    public function urlDeEstado(): string
    {
        return URL::temporarySignedRoute(
            'pago.estado',
            now()->addHours(2),
            ['transaccion' => $this->referencia],
        );
    }
}

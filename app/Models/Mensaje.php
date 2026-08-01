<?php

namespace App\Models;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use Database\Factories\MensajeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mensaje extends Model
{
    /** @use HasFactory<MensajeFactory> */
    use HasFactory;

    protected $table = 'mensajes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMensaje::class,
            'estado' => EstadoMensaje::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
            'respondido_at' => 'datetime',
        ];
    }

    public function esPqr(): bool
    {
        return $this->tipo === TipoMensaje::Pqr;
    }

    /**
     * Consecutivo anual de PQR: PQR-2026-0001, PQR-2026-0002...
     *
     * Se bloquea la tabla durante el cálculo para que dos envíos simultáneos
     * no reciban el mismo radicado.
     */
    public static function generarRadicado(?int $anio = null): string
    {
        $anio ??= now()->year;
        $prefijo = "PQR-{$anio}-";

        return DB::transaction(function () use ($prefijo): string {
            $ultimo = static::query()
                ->where('radicado', 'like', "{$prefijo}%")
                ->lockForUpdate()
                ->orderByDesc('radicado')
                ->value('radicado');

            $consecutivo = $ultimo === null ? 1 : ((int) substr($ultimo, strlen($prefijo))) + 1;

            return $prefijo.str_pad((string) $consecutivo, 4, '0', STR_PAD_LEFT);
        });
    }
}

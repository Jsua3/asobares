<?php

namespace App\Models;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    /** @use HasFactory<\Database\Factories\MensajeFactory> */
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
}

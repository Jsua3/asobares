<?php

namespace App\Models;

use App\Enums\EstadoInscripcion;
use Database\Factories\InscripcionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    /** @use HasFactory<InscripcionFactory> */
    use HasFactory;

    protected $table = 'inscripciones';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoInscripcion::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Evento, $this> */
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    /** @return BelongsTo<Transaccion, $this> */
    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class);
    }

    public function estaConfirmada(): bool
    {
        return $this->estado === EstadoInscripcion::Confirmada;
    }
}

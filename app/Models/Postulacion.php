<?php

namespace App\Models;

use App\Enums\EstadoDeGestion;
use Database\Factories\PostulacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alguien que se postuló a una vacante concreta desde el sitio público.
 *
 * No requiere cuenta: la fricción de registrarse espantaría a la mitad de
 * los candidatos de un turno de una noche.
 */
class Postulacion extends Model
{
    /** @use HasFactory<PostulacionFactory> */
    use HasFactory;

    protected $table = 'postulaciones';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoDeGestion::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Vacante, $this> */
    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Quien busca empleo en el sector: bartender, chef, mesero, administrador...
 */
class Aspirante extends Model
{
    /** @use HasFactory<\Database\Factories\AspiranteFactory> */
    use HasFactory;

    protected $table = 'aspirantes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
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

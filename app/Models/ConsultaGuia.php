<?php

namespace App\Models;

use Database\Factories\ConsultaGuiaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Conteo anónimo de consultas a la guía de apertura, por municipio.
 *
 * Es el insumo del mapa de calor del observatorio: «en qué municipios la
 * gente quiere abrir un negocio» es la señal más útil que la página insignia
 * puede darle al gremio para hablar con una alcaldía.
 *
 * ⚠️ No guarda IP, agente de usuario ni sesión, a propósito: así es un
 * agregado y no un dato personal, y queda fuera del alcance de la Ley 1581.
 * `ConsultasDeGuiaTest::test_la_tabla_no_guarda_ningun_dato_personal` lo
 * vigila.
 */
class ConsultaGuia extends Model
{
    /** @use HasFactory<ConsultaGuiaFactory> */
    use HasFactory;

    protected $table = 'consultas_guia';

    protected $fillable = ['municipio_id', 'requisito_apertura_id'];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(RequisitoApertura::class, 'requisito_apertura_id');
    }

    /** Punto único de escritura: el controlador no arma el modelo a mano. */
    public static function registrar(int $municipioId, ?int $requisitoId = null): void
    {
        static::create([
            'municipio_id' => $municipioId,
            'requisito_apertura_id' => $requisitoId,
        ]);
    }
}

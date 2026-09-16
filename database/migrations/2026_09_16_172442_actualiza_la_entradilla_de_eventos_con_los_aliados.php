<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * La entradilla sembrada de /eventos decía «Solo eventos del gremio», y la
 * agenda ya publica eventos de aliados con su organizador a la vista. La fila
 * sembrada tapa el texto por defecto de la vista, así que corregir la vista no
 * basta: hay que corregir la fila.
 *
 * Solo se toca si sigue diciendo lo que sembró el seeder. Si la oficina la
 * reescribió desde el panel, su texto manda.
 */
return new class extends Migration
{
    private const string ANTERIOR = 'Solo eventos del gremio: ferias, foros y formación para los establecimientos del Quindío.';

    private const string NUEVA = 'Eventos, capacitaciones y experiencias del gremio y sus aliados para el sector gastronómico y de entretenimiento del Quindío.';

    public function up(): void
    {
        // Por el modelo y no por `DB::table`: el `saved` de Setting borra la
        // caché de ajustes, y sin eso el sitio seguiría sirviendo la anterior.
        Setting::query()
            ->where('clave', 'eventos_intro')
            ->where('valor', self::ANTERIOR)
            ->first()
            ?->update(['valor' => self::NUEVA]);
    }

    public function down(): void
    {
        Setting::query()
            ->where('clave', 'eventos_intro')
            ->where('valor', self::NUEVA)
            ->first()
            ?->update(['valor' => self::ANTERIOR]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ata un aliado a su municipio, que es lo que permite aplicar la regla dura
 * de las alcaldías (OBS3-05).
 *
 * La alternativa era reconocer una alcaldía por el nombre, con una expresión
 * tipo `/^Alcaldía de /`. No sirve: el día que alguien escriba «Alcaldía
 * Municipal de Armenia» o «Armenia — Alcaldía», la regla deja de aplicarse
 * sin que nadie se entere, que es exactamente el modo de fallo que la regla
 * existe para impedir. Con clave foránea, o el aliado está atado a su
 * municipio o no lo está, y eso no admite interpretación.
 *
 * Nulo para todos los demás: una licorera no es de ningún municipio en el
 * sentido que importa aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aliados', function (Blueprint $table) {
            $table->foreignId('municipio_id')
                ->nullable()
                ->after('tipo')
                ->constrained('municipios')
                // Si se borra el municipio, el aliado deja de representarlo
                // pero no desaparece: borrarlo en cascada se llevaría por
                // delante un registro que alguien cargó a mano.
                ->nullOnDelete();

            $table->index(['municipio_id', 'estado', 'activo'], 'aliados_por_municipio_index');
        });
    }

    public function down(): void
    {
        Schema::table('aliados', function (Blueprint $table) {
            $table->dropIndex('aliados_por_municipio_index');
            $table->dropConstrainedForeignId('municipio_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dónde se hace un evento, para mostrarlo en un mapa (bloque C, aprobado por
 * Natalia el 18 sep). El mismo contrato que la ficha de un asociado:
 * dirección, municipio, coordenadas y enlace de mapa, todo opcional.
 * `lugar` sigue siendo el nombre del sitio. Solo agrega columnas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('direccion', 180)->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->nullOnDelete();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('mapa_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropColumn(['direccion', 'lat', 'lng', 'mapa_url']);
        });
    }
};

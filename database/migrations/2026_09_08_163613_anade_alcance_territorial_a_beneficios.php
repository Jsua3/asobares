<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alcance territorial de un beneficio (Acta 07, A-01).
 *
 * Un solo módulo y no tres: lo que distingue un beneficio de ASOBARES Colombia
 * de uno del capítulo o de uno municipal es una columna, no una tabla aparte.
 *
 * `alcance` nace **nullable y sin valor por defecto**, y eso es la decisión, no
 * un descuido. Los cinco beneficios que ya existen salen del catálogo oficial
 * «BENEFICIOS AFILIADOS», y ese documento no dice de quién es cada uno:
 * ponerles un alcance de oficio sería publicar una afirmación que nadie hizo.
 * Sin clasificar, el sitio no les pinta etiqueta territorial --como la franja de
 * cifras, que vacía no se muestra-- y los clasifica el gremio desde el panel.
 *
 * El municipio va por clave foránea y no por texto libre, igual que en aliados
 * desde el 31 de agosto: `municipios` ya es el eje de asociados, requisitos,
 * artistas, proveedores y aliados, y el filtro público valida contra ella. Un
 * «Circasia» escrito a mano no cruza con nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficios', function (Blueprint $table) {
            $table->string('alcance')->nullable()->after('descripcion');

            $table->foreignId('municipio_id')
                ->nullable()
                ->after('alcance')
                ->constrained('municipios')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // La pregunta que hace el sitio: los de este alcance, en su orden.
            $table->index(['alcance', 'orden'], 'beneficios_por_alcance_index');
        });
    }

    public function down(): void
    {
        Schema::table('beneficios', function (Blueprint $table) {
            $table->dropIndex('beneficios_por_alcance_index');
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropColumn('alcance');
        });
    }
};

<?php

use App\Enums\TipoAliado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dos niveles de aliado (OBS3-04). La revisión del 28 de agosto pidió que las
 * instituciones se vieran aparte de las marcas con convenio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aliados', function (Blueprint $table) {
            // `comercial` por defecto porque es lo que eran todos los
            // registros hasta hoy: la tabla nació sin distinguir. Marcar
            // institucional es una decisión que alguien toma, ficha a ficha.
            $table->string('tipo')
                ->default(TipoAliado::Comercial->value)
                ->after('nombre');
        });

        // El índice de `scopeVisible` ordenaba por `estado, activo, orden`.
        // Ahora la portada agrupa además por tipo, así que el índice tiene
        // que cubrirlo o la consulta de la portada ordena en memoria.
        Schema::table('aliados', function (Blueprint $table) {
            $table->index(['estado', 'activo', 'tipo', 'orden'], 'aliados_visibles_por_tipo_index');
        });

        // La Cámara de Comercio ya estaba sembrada y es institucional: es la
        // sede de la oficina del capítulo. Se corrige aquí y no en el
        // sembrador para que las bases ya creadas no se queden con el dato
        // mal, que es justo lo que el panel enseñaría el día de la demo.
        DB::table('aliados')
            ->where('nombre', 'like', '%Cámara de Comercio%')
            ->update(['tipo' => TipoAliado::Institucional->value]);
    }

    public function down(): void
    {
        Schema::table('aliados', function (Blueprint $table) {
            $table->dropIndex('aliados_visibles_por_tipo_index');
            $table->dropColumn('tipo');
        });
    }
};

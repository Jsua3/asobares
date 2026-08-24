<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La guía normativa gana procedencia y caducidad (RF-60).
 *
 * Hasta ahora la tabla decía qué pide cada entidad y cuánto cuesta, pero no
 * cuándo se comprobó ni contra qué. Los 18 registros que existen los escribió
 * el sembrador durante la construcción del demo, y un lector no tiene forma de
 * distinguirlos de los que sí vienen del documento oficial de la Alcaldía.
 *
 * Las tres columnas NO son la misma cosa, y por eso se comportan distinto:
 *
 * - `verificado_el` y `verificado_con` son **informativos**. Nunca despublican
 *   nada. Alimentan un filtro de trabajo en el panel y una marca honesta en el
 *   sitio: quien no tenga fecha lo dice en su cara.
 * - `vigente_hasta` sí **saca la ficha del sitio** al vencer, y existe sólo
 *   para lo que de verdad caduca: un decreto transitorio, una restricción por
 *   temporada. Vacío significa permanente, que es el caso normal.
 *
 * `date` y no `timestamp`: verificar es un acto con día, no con instante — la
 * Alcaldía y la Cámara fechan por día. Contrasta a propósito con
 * `autorizacion_datos_at`, que sí es `timestamp` porque la Ley 1581 pregunta
 * el momento exacto del consentimiento. Dos preguntas distintas, dos tipos.
 *
 * El índice va sólo en `vigente_hasta`: entra en la consulta pública en cada
 * visita a la guía y en cada generación del sitemap. `verificado_el` sólo se
 * filtra desde el panel, sobre decenas de filas.
 *
 * Sin relleno retroactivo, y esa ausencia es la decisión: inventarle una fecha
 * plausible a contenido que nadie verificó destruiría el mecanismo el mismo
 * día que se construye.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitos_apertura', function (Blueprint $table): void {
            $table->date('verificado_el')->nullable()->after('estado');
            $table->string('verificado_con')->nullable()->after('verificado_el');
            $table->date('vigente_hasta')->nullable()->after('verificado_con');

            $table->index('vigente_hasta');
        });
    }

    public function down(): void
    {
        Schema::table('requisitos_apertura', function (Blueprint $table): void {
            $table->dropIndex(['vigente_hasta']);
            $table->dropColumn(['verificado_el', 'verificado_con', 'vigente_hasta']);
        });
    }
};

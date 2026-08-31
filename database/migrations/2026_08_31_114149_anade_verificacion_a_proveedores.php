<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha y procedencia de la última verificación de un proveedor (OBS3-12).
 *
 * Réplica del patrón de RF-60, que ya funciona en la guía normativa. La queja
 * de la revisión del 28 de agosto fue de datos muertos: proveedores que «ya no
 * existen, ya no contestan» (R22 04:19), y el pedido, «que sí respondan, y que
 * la información esté actualizada» (R22 04:13-04:15).
 *
 * `visible_hasta` ya existía y NO sirve para esto: modela la monetización
 * --hasta cuándo pagó el proveedor por estar en la base-- que es una pregunta
 * comercial. Que alguien haya pagado no dice que su teléfono siga sonando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // `date` y no `datetime`: quien verifica anota el día, no la hora.
            $table->date('verificado_el')->nullable()->after('visible_hasta');

            // Con quién se confirmó. En la guía es el documento oficial; aquí
            // es la persona o el canal --«hablé con Marta, 3xx...»--, que es
            // lo que permite volver a preguntar sin empezar de cero.
            $table->string('verificado_con')->nullable()->after('verificado_el');

            // Índice para la pila de trabajo de la oficina: «qué me falta por
            // verificar» ordenado por lo más viejo.
            $table->index(['estado', 'verificado_el'], 'proveedores_por_verificacion_index');
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropIndex('proveedores_por_verificacion_index');
            $table->dropColumn(['verificado_el', 'verificado_con']);
        });
    }
};

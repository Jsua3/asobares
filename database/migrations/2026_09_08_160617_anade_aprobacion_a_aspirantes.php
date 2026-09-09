<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Puerta de aprobación del banco de talento.
 *
 * Hasta hoy, quien dejaba su perfil en /empleo quedaba visible al instante para
 * todos los establecimientos afiliados: el directorio solo escondía a los
 * `descartado`. Eran datos personales de un tercero publicados a un público
 * cerrado sin que nadie los mirara.
 *
 * No se resuelve con `EstadoDeGestion` porque ese enum es SEGUIMIENTO y lo
 * comparten las postulaciones, donde una aprobación no significa nada: la
 * postulación va derecha al establecimiento dueño de la vacante. Se resuelve
 * con una columna propia, que es el mismo patrón de `verificado_el` en
 * proveedores.
 *
 * `timestamp` y no `date` --al revés que en proveedores-- porque aquí importa
 * el instante en que alguien lo aprobó y no el día en que lo comprobó: esta
 * marca no caduca, solo se pone o se quita.
 *
 * Nace en null para TODOS, incluidos los perfiles que ya estaban: los siete
 * registrados aceptaron con una versión anterior de la política (D-27), así que
 * dejarlos entrar aprobados de oficio sería justo lo contrario de lo que esta
 * columna existe para arreglar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspirantes', function (Blueprint $table) {
            $table->timestamp('aprobado_el')->nullable()->after('estado');

            // La pila de trabajo de la secretaría: qué falta por aprobar,
            // descontando lo ya descartado.
            $table->index(['estado', 'aprobado_el'], 'aspirantes_por_aprobacion_index');
        });
    }

    public function down(): void
    {
        Schema::table('aspirantes', function (Blueprint $table) {
            $table->dropIndex('aspirantes_por_aprobacion_index');
            $table->dropColumn('aprobado_el');
        });
    }
};

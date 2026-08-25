<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La base real del gremio trae tres cosas que el esquema no sabía guardar.
 *
 * Hasta ahora los asociados eran datos de demostración inventados por el
 * sembrador, así que el esquema solo tenía lo que el sembrador producía. El
 * 20 de agosto llegó la base de verdad —«Base de Datos 2025», 41
 * establecimientos— y trae columnas que no tienen dónde caer:
 *
 * - **NIT o cédula.** Es el identificador con el que la oficina factura y
 *   concilia la cartera. En 24 de las 41 filas NO es un NIT de empresa: es la
 *   cédula del propietario. Por eso nace como campo **interno** y entra en
 *   `Asociado::CAMPOS_INTERNOS`, no como un dato más de la ficha.
 * - **Género musical** y **servicios ofrecidos.** Son públicos y son, de
 *   hecho, por lo que alguien busca un sitio: «dónde hay salsa en Armenia».
 *
 * Y una que sí existía pero mal medida: `horario` era `string`, y 19 de las 41
 * filas traen el horario en varias líneas («Lunes a Viernes de 2pm a 12am /
 * Sábado…»). Pasa a `text`.
 *
 * Finalmente, dos columnas de evidencia. La Ley 1581 no pregunta si el titular
 * autorizó: pregunta **cuándo y con qué soporte**. Las seis tablas de
 * formularios públicos ya guardan esa evidencia desde el 15 de agosto; los
 * asociados no la tenían porque a un asociado lo carga la oficina, no él
 * mismo. Sin estas dos columnas, publicar la base real sería tratar datos de
 * identificación sin poder demostrar de dónde salió el permiso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asociados', function (Blueprint $table): void {
            // Público: es lo que hace buscable el directorio.
            $table->text('genero_musical')->nullable()->after('horario');
            $table->text('servicios')->nullable()->after('genero_musical');

            // Interno: identificación del titular. Nunca sale al sitio.
            $table->string('documento', 40)->nullable()->after('representante');

            // Evidencia del consentimiento (Ley 1581/2012, art. 9).
            $table->timestamp('autorizacion_datos_at')->nullable()->after('fecha_afiliacion');
            $table->string('autorizacion_datos_origen')->nullable()->after('autorizacion_datos_at');
        });

        // Cambio de tipo aparte: en SQLite `change()` reconstruye la tabla, y
        // mezclarlo con los `add` de arriba deja el orden de columnas a merced
        // del motor.
        Schema::table('asociados', function (Blueprint $table): void {
            $table->text('horario')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('asociados', function (Blueprint $table): void {
            $table->dropColumn([
                'genero_musical',
                'servicios',
                'documento',
                'autorizacion_datos_at',
                'autorizacion_datos_origen',
            ]);
        });

        Schema::table('asociados', function (Blueprint $table): void {
            $table->string('horario')->nullable()->change();
        });
    }
};

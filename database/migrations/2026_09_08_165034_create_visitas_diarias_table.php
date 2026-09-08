<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Analítica del sitio (Acta 07, A-02, con la contrapropuesta aceptada).
 *
 * Mismo criterio que `consultas_guia`: no se guarda IP, ni navegador, ni
 * sesión. Así es un agregado y no un dato personal, y queda fuera del alcance
 * de la Ley 1581 --que es por lo que esa tabla tampoco necesita purga--.
 *
 * Y una vuelta más: no es una fila por visita sino **un contador por ruta y
 * día**. Dos consecuencias buscadas. La tabla crece con el calendario y no con
 * el tráfico --88 rutas por 365 días es el techo del año, unas 32.000 filas en
 * el peor caso--, y desaparece la última traza que quedaba, que era la hora
 * exacta de cada visita: con una fila por visita, la secuencia de horas de un
 * día flojo puede reconstruir el paso de una persona por el sitio.
 *
 * `ruta` es el NOMBRE de la ruta y no la URL, a propósito: una URL trae la
 * cadena de consulta, y ahí puede venir cualquier cosa que alguien pegue en la
 * barra del navegador. El nombre es un conjunto acotado que decide el código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_diarias', function (Blueprint $table): void {
            $table->id();
            $table->string('ruta');
            $table->date('dia');
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();

            // Es la clave del contador: sin esto, dos peticiones simultáneas
            // sobre una ruta nueva crean dos filas para el mismo día y la
            // suma se parte en dos.
            $table->unique(['ruta', 'dia'], 'visitas_diarias_ruta_dia_unique');

            // La consulta del tablero: la ventana de los últimos treinta días.
            $table->index('dia', 'visitas_diarias_dia_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_diarias');
    }
};

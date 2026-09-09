<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flujo de entradas al sitio (Acta 08, A-03).
 *
 * `total` cuenta páginas servidas: quien abre cuatro fichas cuenta cuatro. Eso
 * responde «qué se mira y cuánto», y no responde lo que pidió la dirección, que
 * es cuánta gente entra. `entradas` cuenta **llegadas**: la primera página de
 * una visita, reconocida porque su `Referer` no es nuestro propio dominio.
 *
 * Va como columna de la misma fila y no como tabla aparte porque es el mismo
 * cubo --ruta y día-- visto con otra pregunta: separarlo obligaría a cuadrar dos
 * contadores que siempre se escriben juntos.
 *
 * ⚠️ Se sigue sin guardar nada de quien visita. El `Referer` se MIRA para
 * decidir y no se escribe, exactamente el trato que ya recibe el navegador para
 * descartar rastreadores: una URL de procedencia puede traer términos de
 * búsqueda, identificadores de campaña o el perfil desde el que se hizo clic, y
 * guardarla sacaría esta tabla de ser un agregado anónimo.
 *
 * `entradas` NO es «visitantes únicos»: dos visitas de la misma persona en dos
 * días cuentan dos. Contar personas exige IP, cookie o sesión, que es lo que el
 * Acta 07 descartó y lo que sigue necesitando la política de tratamiento
 * publicada (D-19).
 *
 * Las filas anteriores al 9 de septiembre de 2026 se quedan con `entradas` en 0
 * porque ese dato no se recogió; la gráfica arranca desde aquí y no miente
 * repartiendo hacia atrás lo que nadie midió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas_diarias', function (Blueprint $table): void {
            $table->unsignedInteger('entradas')->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('visitas_diarias', function (Blueprint $table): void {
            $table->dropColumn('entradas');
        });
    }
};

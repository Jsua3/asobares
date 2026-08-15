<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las seis tablas que guardan consentimiento de formularios públicos.
     *
     * Evidencia del consentimiento (G12, Ley 1581): un booleano y una fecha
     * no responden lo que la SIC pregunta ante un reclamo del titular —
     * desde dónde se autorizó, con qué navegador y qué versión de la
     * política estaba publicada al aceptar.
     *
     * @var list<string>
     */
    private const array TABLAS = [
        'postulaciones',
        'aspirantes',
        'artistas',
        'proveedores',
        'inscripciones',
        'mensajes',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->string('consentimiento_ip', 45)->nullable()->after('consentimiento_at');
                $table->string('consentimiento_agente')->nullable()->after('consentimiento_ip');
                $table->string('consentimiento_politica', 100)->nullable()->after('consentimiento_agente');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn(['consentimiento_ip', 'consentimiento_agente', 'consentimiento_politica']);
            });
        }
    }
};

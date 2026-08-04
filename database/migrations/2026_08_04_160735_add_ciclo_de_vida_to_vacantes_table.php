<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacantes', function (Blueprint $table): void {
            // Obligatoria para el empleo momentáneo: es la fecha del turno.
            $table->date('fecha_limite')->nullable()->after('franja_horaria');
            // Cierre manual del asociado: «ya contraté». No pasa por aprobación.
            $table->timestamp('cerrada_at')->nullable()->after('fecha_limite');
            // Lo escribe la secretaría al devolver; el asociado lo lee en su cuenta.
            $table->text('motivo_devolucion')->nullable()->after('estado');
            $table->string('categoria_cargo')->default('otros')->after('cargo');

            $table->index(['estado', 'categoria_cargo']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::table('vacantes', function (Blueprint $table): void {
            $table->dropIndex(['estado', 'categoria_cargo']);
            $table->dropIndex(['fecha_limite']);
            $table->dropColumn(['fecha_limite', 'cerrada_at', 'motivo_devolucion', 'categoria_cargo']);
        });
    }
};

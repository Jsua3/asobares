<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_afiliacion', function (Blueprint $table) {
            $table->foreignId('asociado_id')->nullable()->after('gestion_notas')
                ->constrained('asociados')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('asociado_id')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->after('user_id')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('aprobado_at')->nullable()->after('aprobado_por');
            $table->foreignId('rechazado_por')->nullable()->after('aprobado_at')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('rechazado_at')->nullable()->after('rechazado_por');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_afiliacion', function (Blueprint $table) {
            $table->dropForeign(['asociado_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['aprobado_por']);
            $table->dropForeign(['rechazado_por']);
            $table->dropColumn([
                'asociado_id',
                'user_id',
                'aprobado_por',
                'aprobado_at',
                'rechazado_por',
                'rechazado_at',
            ]);
        });
    }
};

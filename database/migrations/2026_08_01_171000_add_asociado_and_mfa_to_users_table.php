<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // El rol `asociado` enlaza con su establecimiento para /mi-cuenta.
            $table->foreignId('asociado_id')->nullable()->after('id')
                ->constrained('asociados')->cascadeOnUpdate()->nullOnDelete();

            // MFA del nucleo de Filament 4 (app de autenticacion + codigos de recuperacion).
            $table->text('app_authentication_secret')->nullable();
            $table->json('app_authentication_recovery_codes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['asociado_id']);
            $table->dropColumn(['asociado_id', 'app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};

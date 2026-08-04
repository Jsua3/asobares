<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las fichas de artista y proveedor pasan a entrar por solicitud pública, así
 * que capturan consentimiento como cualquier otro formulario del sitio.
 *
 * `user_id` queda preparado —y sin usar— para la fase en que estas dos bolsas
 * tengan cuenta propia y autogestión.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artistas', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('correo')->nullable()->after('whatsapp');
            $table->boolean('acepta_datos')->default(false)->after('estado');
            $table->timestamp('consentimiento_at')->nullable()->after('acepta_datos');
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->boolean('acepta_datos')->default(false)->after('estado');
            $table->timestamp('consentimiento_at')->nullable()->after('acepta_datos');
        });
    }

    public function down(): void
    {
        Schema::table('artistas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['correo', 'acepta_datos', 'consentimiento_at']);
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['acepta_datos', 'consentimiento_at']);
        });
    }
};

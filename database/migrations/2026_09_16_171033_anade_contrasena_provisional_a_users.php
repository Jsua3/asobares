<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La contraseña que puso alguien distinto del titular: la genérica de la
     * importación de la base del gremio o la que la oficina escribe en el
     * panel. Solo el titular la apaga, desde /mi-cuenta/seguridad.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('contrasena_provisional')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('contrasena_provisional');
        });
    }
};

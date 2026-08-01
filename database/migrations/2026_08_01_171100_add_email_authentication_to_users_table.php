<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Segundo factor por codigo al correo, alternativa a la app de
            // autenticacion para quien no quiera instalar una (RF-40).
            $table->boolean('has_email_authentication')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_email_authentication');
        });
    }
};

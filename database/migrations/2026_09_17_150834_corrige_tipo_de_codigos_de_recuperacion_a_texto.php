<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `User::$casts` cifra este campo entero con `encrypted:array`: lo que
     * llega a la base es un blob cifrado, no JSON de verdad. La migración
     * original lo declaró `json()`, y Postgres valida ese blob como JSON y
     * lo rechaza (`22P02: invalid input syntax for type json`) apenas alguien
     * intenta registrar la app de autenticación. SQLite no valida el tipo,
     * así que el defecto es invisible en local. `app_authentication_secret`
     * ya es `text()` y no tiene este problema.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('app_authentication_recovery_codes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('app_authentication_recovery_codes')->nullable()->change();
        });
    }
};

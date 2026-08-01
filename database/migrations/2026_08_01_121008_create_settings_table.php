<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Todo texto institucional del sitio vive aqui: ninguna vista lleva
        // contenido quemado (RNF-09).
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            /** string | text | numero | booleano | json */
            $table->string('tipo')->default('string');
            $table->string('grupo')->default('general');
            $table->string('etiqueta')->nullable();
            $table->timestamps();

            $table->index('grupo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

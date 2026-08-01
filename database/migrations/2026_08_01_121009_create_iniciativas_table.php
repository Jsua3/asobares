<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Las iniciativas que el gremio tiene en marcha. Cambian de estado con
        // el tiempo, asi que viven en la base y no en una vista.
        Schema::create('iniciativas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('resumen');
            $table->text('descripcion')->nullable();
            $table->string('estado_iniciativa')->default('formulacion');
            /** Linea del plan de accion: seguridad, cultura o sostenibilidad. */
            $table->string('linea')->nullable();
            $table->string('lugar')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'orden']);
            $table->index('estado_iniciativa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iniciativas');
    }
};

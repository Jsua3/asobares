<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->string('tipo')->default('evento');
            $table->text('descripcion')->nullable();
            $table->string('lugar')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('imagen')->nullable();
            $table->unsignedInteger('cupos')->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->boolean('permite_inscripcion')->default(true);
            // Eventos de la Nacional: el boton lleva a su propio registro.
            $table->string('enlace_externo')->nullable();
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'fecha_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};

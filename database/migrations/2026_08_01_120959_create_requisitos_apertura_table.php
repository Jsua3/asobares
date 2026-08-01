<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitos_apertura', function (Blueprint $table) {
            $table->id();
            // La normatividad difiere por municipio: es el eje de la guia.
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('entidad');
            $table->text('descripcion')->nullable();
            /** Lista de items del tramite: ["Formulario RUES diligenciado", ...] */
            $table->json('checklist')->nullable();
            $table->string('enlace_externo')->nullable();
            // Formato oficial descargable (PDF de la entidad).
            $table->string('adjunto')->nullable();
            $table->string('adjunto_nombre')->nullable();
            $table->decimal('costo_aproximado', 12, 2)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['municipio_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos_apertura');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            // Solo los establecimientos asociados publican vacantes.
            $table->foreignId('asociado_id')->constrained('asociados')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('cargo');
            $table->string('tipo')->default('tiempo_completo');
            $table->text('descripcion')->nullable();
            $table->string('franja_horaria')->nullable();
            $table->string('whatsapp_contacto', 30)->nullable();
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'cargo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};

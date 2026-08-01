<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Boletin de baja frecuencia (~mensual), con datos que envia la Nacional.
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('extracto')->nullable();
            $table->longText('contenido')->nullable();
            $table->string('imagen')->nullable();
            $table->string('categoria')->default('noticia');
            $table->timestamp('publicado_at')->nullable();
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'publicado_at']);
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};

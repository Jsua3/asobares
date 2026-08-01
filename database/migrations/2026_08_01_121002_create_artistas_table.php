<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categoria separada del empleo: "el DJ es artista, el mesero es empleo".
        Schema::create('artistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('tipo')->default('dj');
            $table->string('genero_musical')->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('tarifa_desde', 12, 2)->nullable();
            // Solo URL de YouTube; se embebe unicamente el ID extraido.
            $table->string('video_url')->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->cascadeOnUpdate()->nullOnDelete();
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artistas');
    }
};

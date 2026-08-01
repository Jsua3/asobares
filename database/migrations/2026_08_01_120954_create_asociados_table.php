<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asociados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnUpdate()->restrictOnDelete();

            // --- Campos publicos: el propietario del establecimiento decide que se muestra ---
            $table->text('descripcion')->nullable();
            $table->string('direccion')->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->string('tripadvisor_url')->nullable();
            $table->string('horario')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            // Portada: ruta simple. La galería vive en medialibrary (colección `galeria`).
            $table->string('foto_portada')->nullable();
            $table->boolean('destacado')->default(false);
            $table->string('estado')->default('borrador');

            // --- Campos internos: NUNCA se exponen en el sitio publico ---
            $table->string('representante')->nullable();
            $table->string('correo_interno')->nullable();
            $table->string('telefono_interno', 30)->nullable();
            $table->date('fecha_afiliacion')->nullable();
            $table->text('notas_internas')->nullable();

            $table->timestamps();

            $table->index(['estado', 'destacado']);
            $table->index(['municipio_id', 'categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asociados');
    }
};

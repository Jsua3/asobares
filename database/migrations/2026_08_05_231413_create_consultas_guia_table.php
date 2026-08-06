<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultas_guia', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipio_id')->constrained()->cascadeOnDelete();
            // Se llena solo al descargar un formato; visitar la guía no lo trae.
            $table->foreignId('requisito_apertura_id')->nullable()->constrained('requisitos_apertura')->nullOnDelete();
            $table->timestamps();

            // El observatorio agrupa por municipio y por mes.
            $table->index(['municipio_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas_guia');
    }
};

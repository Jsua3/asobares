<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->default('contacto');
            $table->string('nombre');
            $table->string('correo');
            $table->string('telefono', 30)->nullable();
            $table->text('mensaje');

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            // Consecutivo tipo PQR-2026-0001, solo para mensajes de tipo PQR.
            $table->string('radicado')->nullable()->unique();
            $table->string('estado')->default('nuevo');
            $table->text('nota_respuesta')->nullable();
            $table->timestamp('respondido_at')->nullable();

            $table->timestamps();

            $table->index(['tipo', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};

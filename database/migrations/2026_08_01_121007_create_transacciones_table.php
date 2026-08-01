<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->string('referencia')->unique();
            $table->string('concepto');
            $table->foreignId('inscripcion_id')->nullable()->constrained('inscripciones')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('asociado_id')->nullable()->constrained('asociados')->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('COP');
            $table->string('estado')->default('pendiente');
            $table->string('metodo')->default('pse');
            /** Respuesta cruda de la pasarela, para auditoria. */
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['estado', 'concepto']);
            $table->index('created_at');
        });

        // Cierra el ciclo inscripciones <-> transacciones.
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->foreign('transaccion_id')->references('id')->on('transacciones')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropForeign(['transaccion_id']);
        });

        Schema::dropIfExists('transacciones');
    }
};

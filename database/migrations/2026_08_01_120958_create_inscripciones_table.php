<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('correo');
            $table->string('telefono', 30)->nullable();
            $table->string('establecimiento')->nullable();

            // Habeas data (Ley 1581 de 2012): sin autorizacion no se guarda el registro.
            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            $table->string('estado')->default('registrada');
            // La FK se agrega tras crear transacciones (dependencia circular).
            $table->unsignedBigInteger('transaccion_id')->nullable()->index();

            $table->timestamps();

            $table->index(['evento_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('correo');
            $table->string('telefono', 30)->nullable();
            $table->text('experiencia')->nullable();

            $table->string('estado')->default('nuevo');

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            $table->timestamps();

            // Reenviar el formulario no puede llenar la bandeja del asociado
            // con la misma persona repetida.
            $table->unique(['vacante_id', 'correo']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};

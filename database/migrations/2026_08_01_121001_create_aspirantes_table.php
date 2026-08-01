<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirantes', function (Blueprint $table) {
            $table->id();
            // Nulo cuando la persona solo deja su perfil, sin postularse a una vacante concreta.
            $table->foreignId('vacante_id')->nullable()->constrained('vacantes')->cascadeOnUpdate()->nullOnDelete();
            $table->string('nombre');
            $table->string('correo');
            $table->string('telefono', 30)->nullable();
            $table->string('cargo_interes');
            $table->text('experiencia')->nullable();

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            $table->timestamps();

            $table->index('cargo_interes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspirantes');
    }
};

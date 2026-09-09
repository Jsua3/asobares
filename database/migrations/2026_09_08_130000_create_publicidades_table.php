<?php

use App\Enums\EstadoPublicidad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicidades', function (Blueprint $table): void {
            $table->id();
            $table->string('anunciante');
            $table->string('nombre_comercial')->nullable();
            $table->string('contacto');
            $table->string('email');
            $table->string('telefono', 30);
            $table->string('imagen')->nullable();
            $table->string('url_destino')->nullable();
            $table->string('ubicacion');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->decimal('valor', 12, 2);
            $table->string('estado')->default(EstadoPublicidad::Borrador->value);
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('aprobado_at')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['estado', 'ubicacion', 'fecha_inicio', 'fecha_fin'], 'publicidades_visibles_index');
            $table->index('aprobado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicidades');
    }
};

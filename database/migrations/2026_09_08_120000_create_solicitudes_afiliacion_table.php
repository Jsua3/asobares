<?php

use App\Enums\EstadoSolicitudAfiliacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_afiliacion', function (Blueprint $table) {
            $table->id();

            $table->string('solicitante_nombre', 120);
            $table->string('solicitante_identificacion', 40);
            $table->string('solicitante_telefono', 30);
            $table->string('solicitante_correo', 180);
            $table->string('solicitante_cargo', 80);

            $table->string('establecimiento_nombre', 160);
            $table->string('razon_social', 180);
            $table->string('nit', 40);
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('direccion', 255);
            $table->string('establecimiento_telefono', 30);
            $table->string('establecimiento_correo', 180);
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('descripcion');

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();
            $table->string('consentimiento_ip', 45)->nullable();
            $table->string('consentimiento_agente', 255)->nullable();
            $table->string('consentimiento_politica', 120)->nullable();

            $table->string('estado', 40)->default(EstadoSolicitudAfiliacion::Pendiente->value)->index();
            $table->timestamp('visita_programada_at')->nullable();
            $table->timestamp('resuelto_at')->nullable();
            $table->text('gestion_notas')->nullable();

            $table->timestamps();

            $table->index(['municipio_id', 'estado']);
            $table->index(['categoria_id', 'estado']);
            $table->index('solicitante_correo');
            $table->index('nit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_afiliacion');
    }
};

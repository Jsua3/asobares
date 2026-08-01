<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aliados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->text('descripcion')->nullable();
            // Contenido privado: solo visible para asociados con sesion iniciada.
            $table->text('detalle_convenio')->nullable();
            $table->unsignedInteger('orden')->default(0);
            // `estado` = aprobado por la direccion. `activo` = se muestra hoy
            // en el carrusel. Para salir al sitio hacen falta los dos.
            $table->string('estado')->default('borrador');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['estado', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aliados');
    }
};

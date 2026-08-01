<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('categoria_proveedor')->default('otros');
            $table->text('descripcion')->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('correo')->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->cascadeOnUpdate()->nullOnDelete();
            // Monetizacion futura: solo se listan los proveedores con vigencia al dia.
            $table->date('visible_hasta')->nullable();
            $table->string('estado')->default('borrador');
            $table->timestamps();

            $table->index(['estado', 'categoria_proveedor']);
            $table->index('visible_hasta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};

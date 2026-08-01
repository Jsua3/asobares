<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estado de cuenta del afiliado. Se importa por CSV desde el panel
        // (archivo de la contadora); el asociado solo lo consulta en /mi-cuenta.
        Schema::create('carteras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asociado_id')->unique()->constrained('asociados')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->unsignedInteger('meses_mora')->default(0);
            $table->date('ultimo_pago_at')->nullable();
            $table->timestamp('actualizado_at')->nullable();
            $table->timestamps();

            $table->index('meses_mora');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carteras');
    }
};

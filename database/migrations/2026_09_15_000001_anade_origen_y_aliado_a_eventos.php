<?php

use App\Enums\OrigenEvento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('origen')->default(OrigenEvento::Asobares->value);
            $table->foreignId('aliado_id')
                ->nullable()
                ->constrained('aliados')
                ->nullOnDelete();

            $table->index(['origen', 'aliado_id'], 'eventos_origen_aliado_index');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex('eventos_origen_aliado_index');
            $table->dropConstrainedForeignId('aliado_id');
            $table->dropColumn('origen');
        });
    }
};

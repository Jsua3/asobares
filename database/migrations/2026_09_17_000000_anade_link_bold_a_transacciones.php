<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->string('bold_payment_link')->nullable();
        });

        DB::table('transacciones')->whereNotNull('payload')->orderBy('id')->chunkById(100, function ($transacciones): void {
            foreach ($transacciones as $transaccion) {
                $payload = json_decode($transaccion->payload, true);
                $link = is_array($payload) ? ($payload['bold_payment_link'] ?? null) : null;

                if (is_string($link) && str_starts_with($link, 'LNK_')) {
                    DB::table('transacciones')->where('id', $transaccion->id)->update(['bold_payment_link' => $link]);
                }
            }
        });

        Schema::table('transacciones', function (Blueprint $table) {
            $table->unique('bold_payment_link');
        });
    }

    public function down(): void
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropUnique(['bold_payment_link']);
            $table->dropColumn('bold_payment_link');
        });
    }
};

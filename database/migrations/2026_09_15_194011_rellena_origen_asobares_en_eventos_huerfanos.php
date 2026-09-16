<?php

use App\Enums\OrigenEvento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('eventos', 'origen')) {
            return;
        }

        DB::table('eventos')
            ->where(function ($consulta): void {
                $consulta->whereNull('origen')->orWhere('origen', '');
            })
            ->update(['origen' => OrigenEvento::Asobares->value]);
    }

    public function down(): void
    {
        // No se revierte: los registros sin origen eran datos incompletos.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('municipios', function (Blueprint $table): void {
            $table->boolean('activo')->default(true)->after('slug');
            $table->unsignedInteger('orden')->default(0)->after('activo');

            $table->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table): void {
            $table->dropIndex(['activo', 'orden']);
            $table->dropColumn(['activo', 'orden']);
        });
    }
};

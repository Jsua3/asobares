<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El aspirante deja de ser «postulación sin vacante» y pasa a ser el banco de
 * talento del gremio: una persona, un registro, un correo.
 *
 * Se reconstruye la tabla en vez de alterarla porque SQLite no deja soltar una
 * columna atada a una clave foránea, y `vacante_id` lo está.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirantes_nuevo', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('correo')->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('cargo_interes');
            $table->string('categoria_cargo')->default('otros');
            $table->text('experiencia')->nullable();
            $table->string('estado')->default('nuevo');

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            $table->timestamps();

            $table->index('categoria_cargo');
        });

        // De cada correo repetido sobrevive el registro más reciente.
        $vistos = [];

        DB::table('aspirantes')->orderByDesc('id')->get()->each(function (object $fila) use (&$vistos): void {
            if (isset($vistos[$fila->correo])) {
                return;
            }

            $vistos[$fila->correo] = true;

            DB::table('aspirantes_nuevo')->insert([
                'id' => $fila->id,
                'nombre' => $fila->nombre,
                'correo' => $fila->correo,
                'telefono' => $fila->telefono,
                'cargo_interes' => $fila->cargo_interes,
                'categoria_cargo' => 'otros',
                'experiencia' => $fila->experiencia,
                'estado' => 'nuevo',
                'acepta_datos' => $fila->acepta_datos,
                'consentimiento_at' => $fila->consentimiento_at,
                'created_at' => $fila->created_at,
                'updated_at' => $fila->updated_at,
            ]);
        });

        Schema::drop('aspirantes');
        Schema::rename('aspirantes_nuevo', 'aspirantes');
    }

    public function down(): void
    {
        Schema::table('aspirantes', function (Blueprint $table): void {
            $table->dropUnique(['correo']);
            $table->dropIndex(['categoria_cargo']);
            $table->dropColumn(['categoria_cargo', 'estado']);
        });
    }
};

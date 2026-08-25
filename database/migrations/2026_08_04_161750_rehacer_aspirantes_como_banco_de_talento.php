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
            $table->string('correo')->unique('aspirantes_correo_unique');
            $table->string('telefono', 30)->nullable();
            $table->string('cargo_interes');
            $table->string('categoria_cargo')->default('otros');
            $table->text('experiencia')->nullable();
            $table->string('estado')->default('nuevo');

            $table->boolean('acepta_datos')->default(false);
            $table->timestamp('consentimiento_at')->nullable();

            $table->timestamps();

            $table->index('categoria_cargo', 'aspirantes_categoria_cargo_index');
        });

        // De cada correo repetido sobrevive el registro más reciente.
        $vistos = [];
        $copiados = 0;

        DB::table('aspirantes')->orderByDesc('id')->get()->each(function (object $fila) use (&$vistos, &$copiados): void {
            if (isset($vistos[$fila->correo])) {
                return;
            }

            $vistos[$fila->correo] = true;
            $copiados++;

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

        // Insertar `id` explícito NO adelanta la secuencia de identidad en
        // PostgreSQL: el rowid de SQLite se deriva de MAX(id) y se acomoda
        // solo, pero la secuencia de Postgres se queda donde estaba y el
        // primer aspirante nuevo choca con clave duplicada en la primaria.
        //
        // Sobre base vacía —el `migrate` de un despliegue limpio— el bucle de
        // arriba no inserta nada y aquí no hay nada que hacer; por eso se mira
        // `$copiados` y no se llama a `setval` a ciegas: con la tabla vacía
        // dejaría la secuencia en 1 «ya usado» y el primer aspirante saldría
        // con id 2. Muerde de verdad el día que se importe el volcado de
        // `docs/ingenieria/base-de-datos/` y se corran las migraciones encima.
        //
        // Va aquí, antes del `rename`: después, `pg_get_serial_sequence` ya no
        // encontraría la tabla `aspirantes_nuevo` y la migración fallaría en
        // Postgres pasando en SQLite, que es el peor sitio donde descubrirlo.
        if ($copiados > 0 && DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('aspirantes_nuevo', 'id'), "
                .'(SELECT MAX(id) FROM aspirantes_nuevo))'
            );
        }

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

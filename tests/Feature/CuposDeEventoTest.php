<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * G4. El cupo se comprobaba con un conteo y la inscripción se insertaba
 * después, en dos pasos sueltos: dos peticiones simultáneas leían el mismo
 * conteo y las dos insertaban.
 *
 * Una carrera de verdad no se reproduce en PHPUnit —las pruebas corren en un
 * solo proceso y sobre SQLite, donde `lockForUpdate` no compila a nada—, así
 * que lo que se verifica aquí es la propiedad que cierra la carrera: que la
 * comprobación y la inserción ocurren dentro de la misma transacción.
 */
class CuposDeEventoTest extends TestCase
{
    use RefreshDatabase;

    private function evento(int $cupos): Evento
    {
        return Evento::create([
            'titulo' => 'Taller con aforo',
            'slug' => 'taller-con-aforo',
            'descripcion' => 'Aforo limitado.',
            'fecha_inicio' => now()->addDays(15),
            'precio' => 0,
            'cupos' => $cupos,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }

    /** @return array<string, string> */
    private function datos(string $correo): array
    {
        return [
            'nombre' => 'Persona Interesada',
            'correo' => $correo,
            'telefono' => '3145520987',
            'acepta_datos' => '1',
        ];
    }

    public function test_la_comprobacion_del_cupo_y_la_inscripcion_ocurren_en_la_misma_transaccion(): void
    {
        $evento = $this->evento(1);
        $nivelAlInsertar = null;

        // `RefreshDatabase` ya envuelve cada prueba en una transacción, así
        // que comparar contra cero no probaría nada: la referencia es el nivel
        // de fuera, y el INSERT tiene que ocurrir por debajo.
        $nivelDeLaPrueba = DB::transactionLevel();

        Inscripcion::creating(function () use (&$nivelAlInsertar): void {
            $nivelAlInsertar = DB::transactionLevel();
        });

        $this->post(route('eventos.inscribir', $evento), $this->datos('primera@ejemplo.test'));

        $this->assertNotNull($nivelAlInsertar, 'La inscripción no llegó a crearse.');
        $this->assertGreaterThan(
            $nivelDeLaPrueba,
            $nivelAlInsertar,
            'Sin transacción propia, el conteo de cupos y el INSERT quedan expuestos a una carrera.'
        );
    }

    public function test_no_se_pasa_del_aforo(): void
    {
        $evento = $this->evento(1);

        $this->post(route('eventos.inscribir', $evento), $this->datos('primera@ejemplo.test'));
        $this->post(route('eventos.inscribir', $evento), $this->datos('segunda@ejemplo.test'));

        $this->assertSame(1, $evento->inscripciones()->count(), 'El aforo era de uno.');
    }

    public function test_el_segundo_aspirante_recibe_el_aviso_de_cupos_agotados(): void
    {
        $evento = $this->evento(1);

        $this->post(route('eventos.inscribir', $evento), $this->datos('primera@ejemplo.test'));

        $this->from(route('eventos.show', $evento))
            ->post(route('eventos.inscribir', $evento), $this->datos('segunda@ejemplo.test'))
            ->assertSessionHas('error');
    }

    public function test_un_evento_sin_aforo_declarado_no_se_limita(): void
    {
        $evento = $this->evento(0);
        $evento->update(['cupos' => null]);

        $this->post(route('eventos.inscribir', $evento), $this->datos('primera@ejemplo.test'));
        $this->post(route('eventos.inscribir', $evento), $this->datos('segunda@ejemplo.test'));

        $this->assertSame(2, $evento->inscripciones()->count());
    }
}

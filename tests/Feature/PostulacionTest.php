<?php

namespace Tests\Feature;

use App\Enums\EstadoDeGestion;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_postulacion_queda_ligada_a_su_vacante(): void
    {
        $vacante = Vacante::factory()->publicado()->create();
        $postulacion = Postulacion::factory()->for($vacante)->create();

        $this->assertTrue($vacante->postulaciones->contains($postulacion));
        $this->assertSame($vacante->id, $postulacion->vacante->id);
        $this->assertSame(EstadoDeGestion::Nuevo, $postulacion->estado);
    }

    public function test_la_misma_persona_no_se_postula_dos_veces_a_la_misma_vacante(): void
    {
        $vacante = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($vacante)->create(['correo' => 'duvan@ejemplo.test']);

        $this->expectException(QueryException::class);

        Postulacion::factory()->for($vacante)->create(['correo' => 'duvan@ejemplo.test']);
    }

    public function test_la_misma_persona_si_se_postula_a_vacantes_distintas(): void
    {
        Postulacion::factory()->for(Vacante::factory()->publicado())->create(['correo' => 'duvan@ejemplo.test']);
        Postulacion::factory()->for(Vacante::factory()->publicado())->create(['correo' => 'duvan@ejemplo.test']);

        $this->assertSame(2, Postulacion::where('correo', 'duvan@ejemplo.test')->count());
    }

    public function test_borrar_la_vacante_se_lleva_sus_postulaciones(): void
    {
        $vacante = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($vacante)->count(3)->create();

        $vacante->delete();

        $this->assertSame(0, Postulacion::count());
    }
}

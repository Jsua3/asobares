<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Models\Aspirante;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BancoDeTalentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_aspirante_ya_no_cuelga_de_una_vacante(): void
    {
        $this->assertFalse(
            Schema::hasColumn('aspirantes', 'vacante_id'),
            'La relación con vacantes vive ahora en la tabla de postulaciones.'
        );
    }

    public function test_el_correo_del_aspirante_es_unico(): void
    {
        Aspirante::factory()->create(['correo' => 'duvan@ejemplo.test']);

        $this->expectException(QueryException::class);

        Aspirante::factory()->create(['correo' => 'duvan@ejemplo.test']);
    }

    public function test_el_aspirante_arranca_como_nuevo_y_guarda_su_categoria(): void
    {
        $aspirante = Aspirante::factory()->create(['categoria_cargo' => CargoDelSector::Cocina]);

        $this->assertSame(EstadoDeGestion::Nuevo, $aspirante->estado);
        $this->assertSame(CargoDelSector::Cocina, $aspirante->fresh()->categoria_cargo);
    }
}

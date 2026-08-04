<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CicloDeVidaDeVacanteTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_vacante_publicada_y_sin_fecha_limite_esta_vigente(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $this->assertTrue($vacante->estaVigente());
        $this->assertTrue($vacante->aceptaPostulaciones());
        $this->assertTrue(Vacante::publicado()->vigente()->whereKey($vacante->id)->exists());
    }

    public function test_una_vacante_cerrada_sale_del_muro(): void
    {
        $vacante = Vacante::factory()->publicado()->cerrada()->create();

        $this->assertTrue($vacante->estaCerrada());
        $this->assertFalse($vacante->estaVigente());
        $this->assertFalse($vacante->aceptaPostulaciones());
        $this->assertFalse(Vacante::publicado()->vigente()->whereKey($vacante->id)->exists());
    }

    public function test_una_vacante_vencida_sale_del_muro_sin_que_nadie_la_toque(): void
    {
        $vacante = Vacante::factory()->publicado()->vencida()->create();

        $this->assertTrue($vacante->estaVencida());
        $this->assertFalse($vacante->estaVigente());
        $this->assertFalse(Vacante::publicado()->vigente()->whereKey($vacante->id)->exists());
    }

    public function test_la_fecha_limite_de_hoy_todavia_cuenta_como_vigente(): void
    {
        $vacante = Vacante::factory()->publicado()->create(['fecha_limite' => now()->toDateString()]);

        $this->assertTrue($vacante->estaVigente());
        $this->assertTrue(Vacante::publicado()->vigente()->whereKey($vacante->id)->exists());
    }

    public function test_una_vacante_pendiente_de_aprobacion_no_acepta_postulaciones(): void
    {
        $vacante = Vacante::factory()->pendiente()->create();

        $this->assertFalse($vacante->aceptaPostulaciones());
    }

    public function test_la_categoria_de_cargo_se_castea_al_enum(): void
    {
        $vacante = Vacante::factory()->create(['categoria_cargo' => CargoDelSector::Barra]);

        $this->assertSame(CargoDelSector::Barra, $vacante->fresh()->categoria_cargo);
    }
}

<?php

namespace Tests\Unit;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Enums\TipoVacante;
use PHPUnit\Framework\TestCase;

class EnumsDeBolsaTest extends TestCase
{
    public function test_el_cargo_del_sector_cubre_las_areas_de_un_establecimiento(): void
    {
        $valores = array_column(CargoDelSector::cases(), 'value');

        $this->assertSame(
            ['administracion', 'cocina', 'barra', 'servicio', 'seguridad', 'aseo', 'otros'],
            $valores
        );
        $this->assertSame('Barra', CargoDelSector::Barra->getLabel());
    }

    public function test_el_estado_de_gestion_arranca_en_nuevo(): void
    {
        $this->assertSame('nuevo', EstadoDeGestion::Nuevo->value);
        $this->assertSame('Contactado', EstadoDeGestion::Contactado->getLabel());
        $this->assertSame('danger', EstadoDeGestion::Descartado->getColor());
    }

    public function test_solo_el_empleo_momentaneo_exige_fecha_limite(): void
    {
        $this->assertTrue(TipoVacante::Momentaneo->exigeFechaLimite());
        $this->assertFalse(TipoVacante::TiempoCompleto->exigeFechaLimite());
        $this->assertFalse(TipoVacante::PorTurnos->exigeFechaLimite());
    }
}

<?php

namespace Tests\Feature;

use App\Models\RequisitoApertura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * RF-60: normativa vigente y decretos transitorios por municipio.
 *
 * La guía es el producto insignia y es información que alguien usa para
 * decidir si abre un negocio. Sin procedencia ni fecha, un trámite que
 * inventó el sembrador se lee igual que uno verificado contra la Alcaldía.
 */
class VigenciaDeLaGuiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_tabla_gana_las_tres_columnas_de_procedencia(): void
    {
        $columnas = Schema::getColumnListing('requisitos_apertura');

        $this->assertContains('verificado_el', $columnas);
        $this->assertContains('verificado_con', $columnas);
        $this->assertContains('vigente_hasta', $columnas);
    }

    public function test_las_dos_fechas_llegan_al_modelo_como_fechas_y_no_como_texto(): void
    {
        $requisito = RequisitoApertura::factory()->create([
            'verificado_el' => '2026-08-20',
            'vigente_hasta' => '2026-11-30',
        ]);

        $requisito->refresh();

        $this->assertInstanceOf(Carbon::class, $requisito->verificado_el);
        $this->assertInstanceOf(Carbon::class, $requisito->vigente_hasta);
        $this->assertSame('2026-08-20', $requisito->verificado_el->toDateString());
    }

    public function test_los_tres_campos_nacen_vacios_porque_nadie_ha_verificado_nada(): void
    {
        $requisito = RequisitoApertura::factory()->create();

        $this->assertNull($requisito->verificado_el);
        $this->assertNull($requisito->verificado_con);
        $this->assertNull($requisito->vigente_hasta);
    }

    public function test_la_bitacora_registra_quien_cambia_una_fecha_de_verificacion(): void
    {
        // Declarar «verifiqué esto contra la Alcaldía» es una afirmación de
        // autoridad sobre información legal: tiene que quedar rastro.
        $registrados = (new RequisitoApertura)->getActivitylogOptions()->logAttributes;

        $this->assertContains('verificado_el', $registrados);
        $this->assertContains('verificado_con', $registrados);
        $this->assertContains('vigente_hasta', $registrados);
    }

    public function test_los_estados_de_factory_producen_lo_que_prometen(): void
    {
        $this->assertNotNull(RequisitoApertura::factory()->verificado()->create()->verificado_el);
        $this->assertTrue(RequisitoApertura::factory()->caducado()->create()->vigente_hasta->isPast());
        $this->assertTrue(RequisitoApertura::factory()->transitorio()->create()->vigente_hasta->isFuture());
    }
}

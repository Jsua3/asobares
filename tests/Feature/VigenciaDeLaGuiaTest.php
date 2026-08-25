<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
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

    public function test_esta_verificado_solo_si_hay_fecha(): void
    {
        $this->assertFalse(RequisitoApertura::factory()->create()->estaVerificado());
        $this->assertTrue(RequisitoApertura::factory()->verificado()->create()->estaVerificado());
    }

    /**
     * El borde es estricto y por eso tiene tres casos y no dos: «más de un
     * año» y «un año o más» son reglas distintas y se leen igual en prosa.
     */
    public function test_el_umbral_de_revision_cuenta_desde_el_dia_siguiente_al_ano(): void
    {
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $reciente = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->addDay()->toDateString())->create();
        $justoEnElBorde = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->toDateString())->create();
        $pasado = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->subDay()->toDateString())->create();

        $this->assertFalse($reciente->necesitaRevision(), 'Once meses no es revisión pendiente.');
        $this->assertFalse($justoEnElBorde->necesitaRevision(), 'A los doce meses exactos todavía sirve.');
        $this->assertTrue($pasado->necesitaRevision(), 'Al día siguiente del año, sí.');
    }

    public function test_lo_que_nadie_verifico_nunca_tambien_necesita_revision(): void
    {
        // No son el mismo estado para el lector, pero son la misma pila de
        // trabajo para la oficina, y el filtro del panel ataca esa pila.
        $this->assertTrue(RequisitoApertura::factory()->create()->necesitaRevision());
    }

    public function test_es_transitorio_solo_lo_que_tiene_fecha_de_vencimiento(): void
    {
        $this->assertFalse(RequisitoApertura::factory()->create()->esTransitorio());
        $this->assertTrue(RequisitoApertura::factory()->transitorio()->create()->esTransitorio());
    }

    /** «Vigente hasta el 30 de noviembre» incluye el 30. */
    public function test_un_decreto_vive_hasta_el_final_de_su_ultimo_dia(): void
    {
        $permanente = RequisitoApertura::factory()->create();
        $hoyEsSuUltimoDia = RequisitoApertura::factory()->transitorio(now()->toDateString())->create();
        $vencioAyer = RequisitoApertura::factory()->caducado()->create();

        $this->assertFalse($permanente->haCaducado(), 'Sin fecha no caduca nunca.');
        $this->assertFalse($hoyEsSuUltimoDia->haCaducado(), 'El último día todavía cuenta.');
        $this->assertTrue($vencioAyer->haCaducado());
    }

    public function test_el_scope_deja_pasar_lo_permanente_y_lo_que_aun_no_vence(): void
    {
        $permanente = RequisitoApertura::factory()->create();
        $futuro = RequisitoApertura::factory()->transitorio()->create();
        $hoy = RequisitoApertura::factory()->transitorio(now()->toDateString())->create();
        $vencido = RequisitoApertura::factory()->caducado()->create();

        $vigentes = RequisitoApertura::vigente()->pluck('id');

        $this->assertContains($permanente->id, $vigentes);
        $this->assertContains($futuro->id, $vigentes);
        $this->assertContains($hoy->id, $vigentes, 'El último día todavía cuenta.');
        $this->assertNotContains($vencido->id, $vigentes);
    }

    /**
     * Fija el comportamiento observable: componer `publicado()->vigente()`
     * nunca devuelve borradores, tengan o no fecha de vencimiento.
     */
    public function test_el_scope_no_anula_el_publicado_que_lo_precede(): void
    {
        $borradorPermanente = RequisitoApertura::factory()->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);

        $ids = RequisitoApertura::publicado()->vigente()->pluck('id');

        $this->assertNotContains(
            $borradorPermanente->id,
            $ids,
            'Un borrador sin fecha de vencimiento NO puede colarse por el orWhere.'
        );
    }

    /**
     * `test_el_scope_no_anula_el_publicado_que_lo_precede` no puede
     * detectar si alguien le quita el grupo al `orWhere`: Eloquent ya aísla
     * las condiciones de un scope local en su propio grupo, con o sin el
     * cierre manual (`Builder::callScope()` -> `addNewWheresWithinGroup()`).
     * Por eso esta prueba afirma la SQL emitida en vez del resultado: es la
     * única forma de que el grupo del `orWhere` tenga una prueba que de
     * verdad se rompa si el bloque se copia fuera del scope y pierde el
     * cierre.
     */
    public function test_la_sql_del_scope_mantiene_el_grupo_del_orwhere(): void
    {
        $sql = RequisitoApertura::publicado()->vigente()->toSql();

        $this->assertStringContainsString(
            'and ("vigente_hasta" is null or "vigente_hasta" >= ?)',
            $sql,
            'El orWhere tiene que salir agrupado entre parentesis: suelto, anularia el estado = publicado que lo precede.'
        );
    }
}

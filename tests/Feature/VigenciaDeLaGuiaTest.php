<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
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

    protected function setUp(): void
    {
        parent::setUp();

        // Varias pruebas de borde llaman now() más de una vez (para fechar
        // el registro y luego para calcular el texto que se espera ver).
        // Sin fijar el reloj, una corrida que cruce medianoche entre esas
        // llamadas movería el borde bajo los pies de la prueba. Laravel
        // restaura el reloj real solo al terminar cada prueba.
        Carbon::setTestNow(Carbon::create(2026, 8, 24, 12, 0, 0, 'America/Bogota'));
    }

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

    /** La factory base no rellena procedencia: es la fábrica, no la decisión de no rellenar retroactivamente. */
    public function test_la_factory_base_deja_en_null_los_tres_campos_de_procedencia(): void
    {
        $requisito = RequisitoApertura::factory()->create();

        $this->assertNull($requisito->verificado_el);
        $this->assertNull($requisito->verificado_con);
        $this->assertNull($requisito->vigente_hasta);
    }

    /**
     * Declarar «verifiqué esto contra la Alcaldía» es una afirmación de
     * autoridad sobre información legal: tiene que quedar rastro de quién lo
     * dijo y qué cambió. Se ejerce un `update()` real y se lee la fila de
     * `Activity` que quedó — no basta con leer `getActivitylogOptions()` en
     * el propio modelo, porque ese método sigue existiendo y devolviendo lo
     * mismo aunque alguien borre `LogsActivity` del `use` de la clase, y
     * entonces ninguna fila se escribiría de verdad.
     */
    public function test_la_bitacora_registra_quien_cambia_una_fecha_de_verificacion(): void
    {
        $secretaria = User::factory()->create();
        $this->actingAs($secretaria);

        $requisito = RequisitoApertura::factory()->create();

        $requisito->update(['verificado_el' => '2026-08-20']);

        $registro = Activity::forSubject($requisito)->causedBy($secretaria)->latest('id')->first();

        $this->assertNotNull(
            $registro,
            'La actualización de verificado_el debió quedar en la bitácora con esta persona como causante.'
        );
        $this->assertArrayHasKey(
            'verificado_el',
            $registro->attribute_changes->get('attributes', []),
            'La bitácora registró algo, pero no el atributo que en realidad cambió.'
        );
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

        // `NoOverflow` como el modelo: con la resta corriente, los días 29, 30
        // y 31 el borde que construye la prueba y el que aplica
        // `necesitaRevision()` caen en días distintos y los tres casos dejan
        // de medir lo que dicen. Ver `VentanaDeMesesTest`.
        $reciente = RequisitoApertura::factory()
            ->verificado(now()->subMonthsNoOverflow($meses)->addDay()->toDateString())->create();
        $justoEnElBorde = RequisitoApertura::factory()
            ->verificado(now()->subMonthsNoOverflow($meses)->toDateString())->create();
        $pasado = RequisitoApertura::factory()
            ->verificado(now()->subMonthsNoOverflow($meses)->subDay()->toDateString())->create();

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
     * El borrador con `vigente_hasta` futuro es el único dato capaz de
     * delatar la fuga: por semántica NULL, un borrador permanente (sin
     * fecha) jamás pasaría "vigente_hasta >= hoy" aunque el `orWhere`
     * saliera suelto, porque `NULL >= fecha` no es verdadero en SQL.
     *
     * Las dos negaciones por sí solas las cumpliría también un conjunto
     * vacío —una mutación que rompiera el scope hacia el otro lado, por
     * ejemplo cambiando el `orWhere` por un `where` y dejando la condición
     * imposible, dejaría `$ids` vacío y ambas pasarían igual—. El control
     * positivo es lo que obliga a que el scope de verdad esté dejando pasar
     * algo.
     */
    public function test_el_scope_no_anula_el_publicado_que_lo_precede(): void
    {
        $borradorTransitorio = RequisitoApertura::factory()->transitorio()->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);
        $borradorPermanente = RequisitoApertura::factory()->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);
        $publicadoVigente = RequisitoApertura::factory()->publicado()->create();

        $ids = RequisitoApertura::publicado()->vigente()->pluck('id');

        $this->assertContains(
            $publicadoVigente->id,
            $ids,
            'Control positivo: sin él, las dos negaciones de abajo las cumpliría también un conjunto vacío.'
        );
        $this->assertNotContains(
            $borradorTransitorio->id,
            $ids,
            'Un borrador con vigente_hasta futuro NO puede colarse por el orWhere.'
        );
        $this->assertNotContains($borradorPermanente->id, $ids);
    }

    /**
     * Esta prueba no puede detectar una regresión de `scopeVigente()`:
     * Eloquent reagrupa los scopes locales en su propio paréntesis, con o
     * sin el cierre que el código fuente escribe alrededor del `orWhere`, así
     * que `publicado()->vigente()` sale agrupado de cualquier forma.
     *
     * Lo que sí demuestra es el peligro fuera de un scope: la misma lógica,
     * escrita en línea sin ese cierre —como quedaría si alguien la copia a
     * un controlador, un `whereRaw` o tras un `toBase()`—, SÍ deja colar un
     * borrador con `vigente_hasta` futuro, porque ahí el `orWhere` sí deja
     * de depender del `estado`.
     */
    public function test_el_orwhere_suelto_fuera_de_un_scope_si_anula_el_publicado(): void
    {
        $borradorTransitorio = RequisitoApertura::factory()->transitorio()->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);

        $porScope = RequisitoApertura::publicado()->vigente()->pluck('id');

        $enLineaSinCierre = RequisitoApertura::publicado()
            ->whereNull('vigente_hasta')
            ->orWhere('vigente_hasta', '>=', now()->toDateString())
            ->pluck('id');

        $this->assertNotContains(
            $borradorTransitorio->id,
            $porScope,
            'Por scope, Eloquent aisla el orWhere y el borrador no se cuela.'
        );
        $this->assertContains(
            $borradorTransitorio->id,
            $enLineaSinCierre,
            'La misma logica sin el cierre y fuera del scope SI deja colar el borrador: el peligro es real.'
        );
    }

    private function requisitoPublicado(Municipio $municipio, array $atributos = []): RequisitoApertura
    {
        return RequisitoApertura::factory()->publicado()->create(
            array_merge(['municipio_id' => $municipio->id], $atributos)
        );
    }

    public function test_un_decreto_vencido_no_aparece_en_la_guia(): void
    {
        $municipio = Municipio::factory()->create();
        $vivo = $this->requisitoPublicado($municipio, ['entidad' => 'Cuerpo de Bomberos']);
        $vencido = $this->requisitoPublicado($municipio, [
            'entidad' => 'Decreto de restricción horaria',
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee($vivo->entidad)
            ->assertDontSee($vencido->entidad);
    }

    public function test_un_municipio_cuya_guia_entera_vencio_no_sale_en_el_selector(): void
    {
        $vivo = Municipio::factory()->create(['nombre' => 'Armenia']);
        $apagado = Municipio::factory()->create(['nombre' => 'Pijao']);

        $this->requisitoPublicado($vivo);
        $this->requisitoPublicado($apagado, ['vigente_hasta' => now()->subDay()->toDateString()]);

        $respuesta = $this->get(route('guia.index'))->assertSuccessful();

        $respuesta->assertSee('Armenia');
        $respuesta->assertDontSee(route('guia.index', ['municipio' => $apagado->slug]), escape: false);
    }

    /**
     * La URL `?municipio=X` de un municipio apagado no desaparece —sigue
     * respondiendo 200 con «Todavía no hay guía publicada»— pero deja de ser
     * indexable. Mismo patrón que ya usa el calendario de eventos para un
     * mes sin datos: navegable, no indexado.
     */
    public function test_la_guia_de_un_municipio_apagado_no_se_indexa(): void
    {
        $vivo = Municipio::factory()->create();
        $apagado = Municipio::factory()->create();

        $this->requisitoPublicado($vivo);
        $this->requisitoPublicado($apagado, ['vigente_hasta' => now()->subDay()->toDateString()]);

        $this->get(route('guia.index', ['municipio' => $vivo->slug]))
            ->assertSuccessful()
            ->assertDontSee('noindex', escape: false);

        $this->get(route('guia.index', ['municipio' => $apagado->slug]))
            ->assertSuccessful()
            ->assertSee('Todavía no hay guía publicada')
            ->assertSee('name="robots" content="noindex, follow"', escape: false);
    }

    /**
     * La puerta que importa. Comprobar la caducidad sólo en la vista dejaría
     * el PDF del decreto vencido descargable por URL directa — el mismo
     * agujero que el §8.3 del runbook encontró en la política del bucket.
     */
    public function test_el_formato_de_un_decreto_vencido_no_se_puede_descargar(): void
    {
        Storage::fake(config('almacenamiento.privado'));
        Storage::disk(config('almacenamiento.privado'))->put('formatos/decreto.pdf', '%PDF-1.4');

        $municipio = Municipio::factory()->create();
        $vencido = $this->requisitoPublicado($municipio, [
            'adjunto' => 'formatos/decreto.pdf',
            'adjunto_nombre' => 'Decreto de restricción horaria',
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('guia.formato', $vencido))->assertNotFound();
    }

    public function test_el_formato_de_un_tramite_vigente_si_se_descarga(): void
    {
        // Contraprueba: sin ella, una descarga rota pasaría la prueba anterior.
        Storage::fake(config('almacenamiento.privado'));
        Storage::disk(config('almacenamiento.privado'))->put('formatos/bomberos.pdf', '%PDF-1.4');

        $municipio = Municipio::factory()->create();
        $vivo = $this->requisitoPublicado($municipio, [
            'adjunto' => 'formatos/bomberos.pdf',
            'adjunto_nombre' => 'Solicitud de visita',
        ]);

        $this->get(route('guia.formato', $vivo))->assertSuccessful();
    }

    public function test_el_sitemap_no_anuncia_un_municipio_cuya_guia_vencio(): void
    {
        $vivo = Municipio::factory()->create();
        $apagado = Municipio::factory()->create();

        $this->requisitoPublicado($vivo);
        $this->requisitoPublicado($apagado, ['vigente_hasta' => now()->subDay()->toDateString()]);

        $respuesta = $this->get('/sitemap.xml')->assertSuccessful();

        $respuesta->assertSee(route('guia.index', ['municipio' => $vivo->slug]), escape: false);
        $respuesta->assertDontSee(route('guia.index', ['municipio' => $apagado->slug]), escape: false);
    }

    public function test_un_tramite_verificado_ensena_su_fecha_y_su_fuente(): void
    {
        $municipio = Municipio::factory()->create();
        $this->requisitoPublicado($municipio, [
            'verificado_el' => '2026-08-20',
            'verificado_con' => 'Documento oficial de la Alcaldía de Armenia',
        ]);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Verificado el 20 de agosto de 2026')
            ->assertSee('Documento oficial de la Alcaldía de Armenia');
    }

    public function test_un_tramite_sin_fechar_lo_dice_en_su_cara(): void
    {
        $municipio = Municipio::factory()->create();
        $this->requisitoPublicado($municipio);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Sin verificar contra la fuente oficial')
            ->assertDontSee('Verificado el');
    }

    /**
     * `vigente_hasta` sale de `now()` (con el reloj fijo en el setUp) y no de
     * una fecha escrita a mano: una fecha bomba como '2026-11-30' pasa hoy
     * pero deja de pasar el día que el scope `vigente()` la excluya por
     * vencida — y fallaría por eso, no porque el código se rompiera.
     */
    public function test_un_decreto_transitorio_anuncia_hasta_cuando_vale(): void
    {
        $municipio = Municipio::factory()->create();
        $vigenteHasta = now()->addMonths(3);
        $this->requisitoPublicado($municipio, ['vigente_hasta' => $vigenteHasta->toDateString()]);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Vigente hasta el '.$vigenteHasta->translatedFormat('d \d\e F \d\e Y'));
    }

    /**
     * Contraprueba. No es que una vista en blanco fuera a colarse: las tres
     * pruebas anteriores usan assertSee sobre texto que sólo existe si el
     * trámite se renderiza, así que ya caen solas ante eso.
     *
     * Lo que esta prueba sí atrapa, confirmado forzando el bug a mano, es la
     * insignia «Vigente hasta» filtrándose a un trámite que no es
     * transitorio —por ejemplo si la condición esTransitorio() quedara mal
     * escrita, invertida o desaparecida—. Sin esta prueba, un visitante
     * vería una fecha de caducidad falsa en un trámite permanente.
     */
    public function test_un_tramite_permanente_no_anuncia_ninguna_caducidad(): void
    {
        $municipio = Municipio::factory()->create();
        $requisito = $this->requisitoPublicado($municipio, ['entidad' => 'Cámara de Comercio']);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee($requisito->entidad)
            ->assertDontSee('Vigente hasta');
    }
}

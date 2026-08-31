<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * La bolsa de proveedores dice cuándo se comprobó cada contacto (OBS3-12).
 *
 * La queja de la revisión del 28 de agosto fue de datos muertos: proveedores
 * que «ya no existen, ya no contestan» (R22 04:19), y el pedido, «que sí
 * respondan, y que la información esté actualizada» (R22 04:13-04:15).
 *
 * Réplica del patrón de RF-60, que ya funciona en la guía normativa. Con una
 * diferencia deliberada: seis meses en vez de doce. Un trámite de apertura
 * cambia al ritmo de los acuerdos municipales; un proveedor cambia de número,
 * de dueño o de oficio cuando le va mal un semestre.
 *
 * `visible_hasta` NO servía para esto y por eso hubo columna nueva: modela la
 * monetización --hasta cuándo pagó por estar en la base--, que es una pregunta
 * comercial. Que alguien haya pagado no dice que su teléfono siga sonando.
 */
class VerificacionDeProveedoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_proveedor_nuevo_nace_sin_verificar(): void
    {
        $proveedor = Proveedor::factory()->create();

        $this->assertFalse($proveedor->estaVerificado());
        $this->assertTrue($proveedor->necesitaRevision(), 'Lo que nadie comprobó nunca es trabajo pendiente.');
    }

    public function test_un_proveedor_recien_verificado_no_necesita_revision(): void
    {
        $proveedor = Proveedor::factory()->verificado()->create();

        $this->assertTrue($proveedor->estaVerificado());
        $this->assertFalse($proveedor->necesitaRevision());
    }

    public function test_una_verificacion_vencida_vuelve_a_ser_trabajo_pendiente(): void
    {
        $proveedor = Proveedor::factory()->verificacionVencida()->create();

        $this->assertTrue($proveedor->estaVerificado(), 'Sigue verificado: lo que caducó es la tranquilidad.');
        $this->assertTrue($proveedor->necesitaRevision());
    }

    /**
     * El borde estricto, y en los cuatro días que desbordan.
     *
     * Es la lección del §28 aplicada por adelantado: `subMonths()` un 31 de
     * agosto no da el 28 de febrero, da el 3 de marzo, y con eso el corte se
     * adelanta hasta dos días y marca como caducada una ficha que todavía
     * está dentro del plazo. Fijar la fecha es lo único que hace que esta
     * prueba signifique lo mismo cualquier día del mes.
     *
     * @return list<array{string}>
     */
    public static function diasQueDesbordan(): array
    {
        return [
            '31 de agosto' => ['2026-08-31'],
            '30 de agosto' => ['2026-08-30'],
            '31 de marzo' => ['2026-03-31'],
            '29 de febrero bisiesto' => ['2028-02-29'],
        ];
    }

    #[DataProvider('diasQueDesbordan')]
    public function test_a_los_seis_meses_exactos_todavia_sirve(string $hoy): void
    {
        Carbon::setTestNow(Carbon::parse($hoy));

        $justoEnElBorde = Proveedor::factory()->create([
            'verificado_el' => now()->subMonthsNoOverflow(Proveedor::MESES_HASTA_REVISION)->toDateString(),
        ]);

        $unDiaDespues = Proveedor::factory()->create([
            'verificado_el' => now()->subMonthsNoOverflow(Proveedor::MESES_HASTA_REVISION)->subDay()->toDateString(),
        ]);

        $this->assertFalse(
            $justoEnElBorde->necesitaRevision(),
            "El {$hoy}, a los seis meses exactos la verificación todavía vale."
        );
        $this->assertTrue(
            $unDiaDespues->necesitaRevision(),
            "El {$hoy}, un día más allá del plazo ya no vale."
        );

        Carbon::setTestNow();
    }

    /**
     * Lo que ve quien busca proveedor, que es de lo que iba la queja. Los tres
     * estados se distinguen en la ficha: un contacto sin fecha no vale más que
     * uno viejo --vale menos, porque el lector no sabe cuál de los dos tiene--.
     */
    public function test_la_ficha_publica_distingue_los_tres_estados(): void
    {
        $this->seed(DatabaseSeeder::class);
        Proveedor::query()->delete();

        Proveedor::factory()->publicado()->verificado()->create(['nombre' => 'Hielo Al Dia', 'slug' => 'hielo-al-dia']);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee(ajuste('proveedores_verificado'), escape: false)
            ->assertDontSee(ajuste('proveedores_sin_verificar'), escape: false);

        Proveedor::query()->delete();
        Proveedor::factory()->publicado()->create(['nombre' => 'Hielo Sin Comprobar', 'slug' => 'hielo-sin-comprobar', 'verificado_el' => null]);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee(ajuste('proveedores_sin_verificar'), escape: false)
            ->assertDontSee(ajuste('proveedores_verificado'), escape: false);

        Proveedor::query()->delete();
        Proveedor::factory()->publicado()->verificacionVencida()->create(['nombre' => 'Hielo Viejo', 'slug' => 'hielo-viejo']);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee(ajuste('proveedores_verificacion_vieja'), escape: false);
    }

    /**
     * La pila de trabajo de la oficina, ordenada: lo que nadie comprobó nunca
     * va primero, porque es peor que comprobarlo tarde.
     */
    public function test_el_scope_lista_lo_pendiente_con_lo_nunca_verificado_primero(): void
    {
        $nunca = Proveedor::factory()->create(['nombre' => 'Nunca', 'verificado_el' => null]);
        $viejo = Proveedor::factory()->verificacionVencida()->create(['nombre' => 'Viejo']);
        $alDia = Proveedor::factory()->verificado()->create(['nombre' => 'Al día']);

        $pendientes = Proveedor::query()->necesitaRevision()->pluck('id')->all();

        $this->assertSame([$nunca->id, $viejo->id], $pendientes);
        $this->assertNotContains($alDia->id, $pendientes);
    }

    /**
     * Haber pagado no es haber respondido. Son dos preguntas distintas y
     * confundirlas es lo que produjo la queja: `visible_hasta` estaba y no
     * evitó nada.
     */
    public function test_estar_vigente_no_es_estar_verificado(): void
    {
        $pagoPeroNoResponde = Proveedor::factory()->create([
            'visible_hasta' => now()->addYear()->toDateString(),
            'verificado_el' => null,
        ]);

        $this->assertTrue($pagoPeroNoResponde->estaVigente());
        $this->assertFalse($pagoPeroNoResponde->estaVerificado());
        $this->assertTrue($pagoPeroNoResponde->necesitaRevision());
    }
}

<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Filament\Resources\RequisitoAperturas\Pages\EditRequisitoApertura;
use App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La oficina necesita saber qué trámites lleva sin revisar. La fecha sola no
 * basta: hace falta poder listar la pila de trabajo.
 */
class VigenciaEnElPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_registra_la_verificacion_y_quita_la_vigencia_transitoria_desde_el_formulario(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $requisito = RequisitoApertura::factory()->publicado()->transitorio()->create([
            'municipio_id' => Municipio::factory(),
        ]);

        // El punto de partida importa: si el registro ya naciera con
        // `vigente_hasta` en null, la aserción final no demostraría nada.
        $this->assertNotNull($requisito->vigente_hasta);

        Livewire::test(EditRequisitoApertura::class, ['record' => $requisito->getRouteKey()])
            ->fillForm([
                'verificado_el' => '2026-08-20',
                'verificado_con' => 'Documento oficial de la Alcaldía de Armenia',
                'vigente_hasta' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $requisito->refresh();

        $this->assertSame('2026-08-20', $requisito->verificado_el->toDateString());
        $this->assertSame('Documento oficial de la Alcaldía de Armenia', $requisito->verificado_con);
        $this->assertNull(
            $requisito->vigente_hasta,
            'Vacío significa permanente: el campo tiene que poder pasar de fecha a nulo, y por eso el registro nace transitorio.'
        );
    }

    public function test_la_tabla_muestra_el_nombre_del_municipio_y_no_su_numero(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create(['nombre' => 'Circasia']);
        RequisitoApertura::factory()->publicado()->create(['municipio_id' => $municipio->id]);

        // El recurso tiene slug propio: `/admin/requisitos`, no el que se
        // deduciría del nombre de la clase. Se usa la ruta con nombre.
        $this->get(route('filament.admin.resources.requisitos.index'))
            ->assertSuccessful()
            ->assertSee('Circasia');
    }

    public function test_el_filtro_lista_lo_rancio_y_lo_que_nadie_verifico(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $alDia = RequisitoApertura::factory()->publicado()->verificado()
            ->create(['municipio_id' => $municipio->id]);
        $rancio = RequisitoApertura::factory()->publicado()
            ->verificado(now()->subMonths($meses)->subDay()->toDateString())
            ->create(['municipio_id' => $municipio->id]);
        $sinVerificar = RequisitoApertura::factory()->publicado()
            ->create(['municipio_id' => $municipio->id]);

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('necesita_revision')
            ->assertCanSeeTableRecords([$rancio, $sinVerificar])
            ->assertCanNotSeeTableRecords([$alDia]);
    }

    public function test_el_filtro_de_caducados_solo_trae_lo_que_ya_vencio(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();

        $permanente = RequisitoApertura::factory()->publicado()
            ->create(['municipio_id' => $municipio->id]);
        $vigente = RequisitoApertura::factory()->publicado()->transitorio()
            ->create(['municipio_id' => $municipio->id]);
        $vencido = RequisitoApertura::factory()->publicado()->caducado()
            ->create(['municipio_id' => $municipio->id]);

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('caducados')
            ->assertCanSeeTableRecords([$vencido])
            ->assertCanNotSeeTableRecords([$permanente, $vigente]);
    }

    /**
     * Declarar «verifiqué esto contra la Alcaldía» es una afirmación de
     * autoridad sobre información legal. No hace falta un permiso nuevo: el
     * FlujoDeAprobacionObserver ya devuelve a pendiente cualquier edición de
     * la secretaría sobre algo publicado. Lo que faltaba es que esa protección
     * dejara de ser incidental — una guarda que nadie comprueba se rompe el
     * día que alguien añade un atajo.
     */
    public function test_la_secretaria_que_feche_un_publicado_lo_devuelve_a_pendiente(): void
    {
        // ⚠️ El requisito se crea ANTES de `actingAs`, y el orden es la prueba.
        // Con la secretaría ya autenticada, el propio observer degradaría el
        // registro al crearlo, y el test pasaría por la razón equivocada:
        // estaría midiendo una ficha que nunca llegó a estar publicada.
        $requisito = RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => Municipio::factory(),
        ]);

        $this->assertSame(EstadoPublicacion::Publicado, $requisito->fresh()->estado);

        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Livewire::test(EditRequisitoApertura::class, ['record' => $requisito->getRouteKey()])
            ->fillForm([
                'verificado_el' => now()->toDateString(),
                'verificado_con' => 'Lo confirmé por teléfono',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            EstadoPublicacion::PendienteAprobacion,
            $requisito->fresh()->estado,
            'Fechar un requisito publicado es una decisión de publicación: vuelve a la cola.'
        );
    }

    /**
     * El filtro repite en SQL la regla que el modelo expresa en PHP. Si una de
     * las dos se mueve sin la otra, la lista de trabajo miente. Esta prueba es
     * la costura.
     */
    public function test_el_filtro_y_el_predicado_coinciden_en_el_borde_exacto(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $justoEnElBorde = RequisitoApertura::factory()->publicado()
            ->verificado(now()->subMonths($meses)->toDateString())
            ->create(['municipio_id' => $municipio->id]);

        $this->assertFalse($justoEnElBorde->necesitaRevision());

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('necesita_revision')
            ->assertCanNotSeeTableRecords([$justoEnElBorde]);
    }
}

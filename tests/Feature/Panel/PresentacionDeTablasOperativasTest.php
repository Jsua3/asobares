<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use App\Filament\Resources\Aliados\Pages\ListAliados;
use App\Filament\Resources\Artistas\Pages\ListArtistas;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Filament\Resources\Aspirantes\Pages\ListAspirantes;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Filament\Resources\Iniciativas\Pages\ListIniciativas;
use App\Filament\Resources\Mensajes\Pages\ListMensajes;
use App\Filament\Resources\Noticias\Pages\ListNoticias;
use App\Filament\Resources\Proveedors\Pages\ListProveedors;
use App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas;
use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\RequisitoApertura;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\ActionGroup;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Las cuatro bandejas anchas deben leerse como la de empleo: lo esencial
 * primero, lo largo en el detalle, y las acciones en un menú.
 */
class PresentacionDeTablasOperativasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /**
     * @return list<string>
     */
    private function nombresDeAcciones(Testable $componente): array
    {
        $acciones = $componente->instance()->getTable()->getRecordActions();

        $this->assertCount(1, $acciones);
        $this->assertInstanceOf(ActionGroup::class, $acciones[0]);

        return array_keys($acciones[0]->getFlatActions());
    }

    private function assertPatronOperativo(Testable $componente): void
    {
        $this->assertSame(['class' => 'asb-operativo'], $componente->instance()->getExtraBodyAttributes());
    }

    public function test_artistas_prioriza_identidad_clasificacion_estado_y_fecha(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Montenegro']);
        $artista = Artista::factory()->create([
            'nombre' => 'DJ Cumbre',
            'genero_musical' => 'Salsa',
            'whatsapp' => '3101112233',
            'municipio_id' => $municipio->id,
        ]);

        $lista = Livewire::test(ListArtistas::class);

        $this->assertPatronOperativo($lista);

        $lista->assertTableColumnExists('nombre')
            ->assertTableColumnExists('tipo')
            ->assertTableColumnExists('estado')
            ->assertTableColumnExists('created_at')
            ->assertTableColumnDoesNotExist('slug')
            ->assertTableColumnDoesNotExist('video_url')
            ->assertTableColumnDoesNotExist('instagram_url')
            ->assertTableColumnDoesNotExist('foto')
            ->assertTableColumnDoesNotExist('whatsapp')
            ->assertTableColumnDoesNotExist('tarifa_desde')
            ->assertTableColumnHasDescription('nombre', 'Salsa · Montenegro', $artista)
            ->assertTableFilterExists('estado')
            ->searchTable('3101112233')
            ->assertCanSeeTableRecords([$artista]);

        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));
    }

    public function test_proveedores_deja_la_vigencia_existente_y_esconde_el_contacto_largo(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Calarcá']);
        $proveedor = Proveedor::factory()->create([
            'nombre' => 'Hielo del Quindío',
            'correo' => 'hielo@proveedor.test',
            'municipio_id' => $municipio->id,
            'visible_hasta' => now()->addMonth()->toDateString(),
        ]);

        $lista = Livewire::test(ListProveedors::class);

        $this->assertPatronOperativo($lista);

        $lista->assertTableColumnExists('nombre')
            ->assertTableColumnExists('categoria_proveedor')
            ->assertTableColumnExists('estado')
            ->assertTableColumnExists('visible_hasta')
            ->assertTableColumnDoesNotExist('slug')
            ->assertTableColumnDoesNotExist('whatsapp')
            ->assertTableColumnDoesNotExist('correo')
            ->assertTableColumnHasDescription('nombre', 'Calarcá', $proveedor)
            ->assertTableFilterExists('estado')
            ->searchTable('hielo@proveedor.test')
            ->assertCanSeeTableRecords([$proveedor]);

        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));
    }

    public function test_mensajes_se_leen_como_bandeja_y_conservan_ver_a_la_vista(): void
    {
        $mensaje = Mensaje::create([
            'tipo' => TipoMensaje::Pqr,
            'nombre' => 'Carlos Muñoz',
            'correo' => 'carlos@ejemplo.test',
            'mensaje' => 'No me ha llegado el carné de afiliado y necesito el detalle completo del trámite.',
            'radicado' => 'PQR-2026-0099',
            'estado' => EstadoMensaje::Nuevo,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ]);

        $lista = Livewire::test(ListMensajes::class);

        $this->assertPatronOperativo($lista);

        $lista->assertTableColumnExists('nombre')
            ->assertTableColumnExists('tipo')
            ->assertTableColumnExists('estado')
            ->assertTableColumnExists('created_at')
            ->assertTableColumnExists('mensaje')
            ->assertTableColumnDoesNotExist('radicado')
            ->assertTableColumnHasDescription('nombre', 'PQR-2026-0099 · carlos@ejemplo.test', $mensaje)
            ->assertTableFilterExists('tipo')
            ->assertTableFilterExists('estado')
            ->assertTableActionExists('view')
            ->searchTable('PQR-2026-0099')
            ->assertCanSeeTableRecords([$mensaje]);

        $acciones = $lista->instance()->getTable()->getRecordActions();
        $this->assertSame('view', $acciones[0]->getName());
        $this->assertInstanceOf(ActionGroup::class, $acciones[1]);
        $this->assertSame(['responder'], array_keys($acciones[1]->getFlatActions()));

        $lista->callAction(TestAction::make('responder')->table($mensaje), data: [
            'nota_respuesta' => 'Se verificó el pago y se despachó el carné.',
        ])->assertHasNoErrors();

        $this->assertSame(EstadoMensaje::Respondido, $mensaje->fresh()->estado);
    }

    /**
     * Cierre de la regresión de ancho horizontal (Eventos, Boletín, Guía
     * normativa, Iniciativas, Aspirantes y Asociados): las seis bandejas
     * quedaban con columnas sueltas y acciones sin agrupar. Solo se verifica
     * el contrato que ya exige este archivo —`asb-operativo` + un único
     * `ActionGroup`—, igual que las cuatro bandejas de arriba.
     */
    public function test_eventos_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $evento = Evento::factory()->create();

        $lista = Livewire::test(ListEventos::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$evento]);
    }

    public function test_boletin_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $noticia = Noticia::factory()->create();

        $lista = Livewire::test(ListNoticias::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$noticia]);
    }

    public function test_guia_normativa_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $requisito = RequisitoApertura::factory()->create();

        $lista = Livewire::test(ListRequisitoAperturas::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$requisito]);
    }

    public function test_iniciativas_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $iniciativa = Iniciativa::factory()->create();

        $lista = Livewire::test(ListIniciativas::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$iniciativa]);
    }

    public function test_aspirantes_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $aspirante = Aspirante::factory()->create();

        $lista = Livewire::test(ListAspirantes::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['edit', 'aprobar', 'retirar'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$aspirante]);
    }

    /**
     * Asociados ya tenía `asb-operativo` desde `bff282c` (solo en la página de
     * listado); lo que faltaba era agrupar las acciones de la tabla, que es
     * lo que esta prueba cierra.
     */
    public function test_asociados_agrupa_sus_acciones_y_usa_el_patron_operativo(): void
    {
        $asociado = Asociado::factory()->create();

        $lista = Livewire::test(ListAsociados::class);

        $this->assertPatronOperativo($lista);
        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));

        $lista->assertCanSeeTableRecords([$asociado]);
    }

    public function test_aliados_resumen_el_convenio_sin_mostrar_url_ni_archivo_de_logo(): void
    {
        $aliado = Aliado::factory()->create([
            'nombre' => 'Licorera del Convenio',
            'detalle_convenio' => '15 % de descuento en pedidos superiores a quinientos mil pesos, con entrega en Armenia.',
            'url' => 'https://licorera.example/convenio-extenso',
            'logo' => 'aliados/logo-largo.png',
        ]);

        $lista = Livewire::test(ListAliados::class);

        $this->assertPatronOperativo($lista);

        $lista->assertTableColumnExists('nombre')
            ->assertTableColumnExists('tipo')
            ->assertTableColumnExists('detalle_convenio')
            ->assertTableColumnExists('estado')
            ->assertTableColumnExists('activo')
            ->assertTableColumnDoesNotExist('logo')
            ->assertTableColumnDoesNotExist('url')
            ->assertTableColumnDoesNotExist('orden')
            ->assertTableFilterExists('estado')
            ->searchTable('Licorera del Convenio')
            ->assertCanSeeTableRecords([$aliado]);

        $this->assertSame(['aprobar', 'devolver', 'edit'], $this->nombresDeAcciones($lista));
    }
}

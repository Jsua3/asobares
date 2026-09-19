<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Aliados\AliadoResource;
use App\Filament\Resources\Artistas\ArtistaResource;
use App\Filament\Resources\Asociados\AsociadoResource;
use App\Filament\Resources\Eventos\EventoResource;
use App\Filament\Resources\Iniciativas\IniciativaResource;
use App\Filament\Resources\Noticias\NoticiaResource;
use App\Filament\Resources\Proveedors\ProveedorResource;
use App\Filament\Resources\SolicitudAfiliacions\SolicitudAfiliacionResource;
use App\Filament\Resources\Vacantes\VacanteResource;
use App\Models\Asociado;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Filament\Livewire\GlobalSearch;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\PanelCompletoTest;
use Tests\TestCase;

/**
 * Moverse por el panel sin la flecha del navegador.
 *
 * El logo lleva al sitio público; cada formulario de crear o editar tiene un
 * botón para volver a su listado —las migas de pan de Filament se ocultan en
 * el teléfono—; un buscador general encuentra asociados, eventos, artistas y
 * demás respetando los permisos, y la campana se cierra al ir al aviso.
 */
class NavegacionDelPanelTest extends TestCase
{
    use RefreshDatabase;

    /** Los recursos que el buscador general recorre. */
    private const array RECURSOS_DEL_BUSCADOR = [
        AliadoResource::class,
        ArtistaResource::class,
        AsociadoResource::class,
        EventoResource::class,
        IniciativaResource::class,
        NoticiaResource::class,
        ProveedorResource::class,
        SolicitudAfiliacionResource::class,
        VacanteResource::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function direccion(): User
    {
        return User::role(User::ROL_SUPER_ADMIN)->firstOrFail();
    }

    private function secretaria(): User
    {
        return User::role(User::ROL_SUBADMIN)->firstOrFail();
    }

    private function buscar(string $texto): Testable
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return Livewire::test(GlobalSearch::class)->set('search', $texto);
    }

    /**
     * El logo es la puerta al sitio público, y se abre con una carga normal:
     * la navegación SPA del panel no sirve para una página que no es del panel.
     */
    public function test_el_logo_lleva_a_la_portada_publica_sin_navegacion_spa(): void
    {
        $portada = route('inicio');

        $this->actingAs($this->direccion())
            ->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('<a href="'.$portada.'">', escape: false)
            ->assertDontSee('href="'.$portada.'" wire:navigate', escape: false);
    }

    #[DataProviderExternal(PanelCompletoTest::class, 'recursosConCreacion')]
    public function test_crear_tiene_un_boton_para_volver_al_listado(string $recurso): void
    {
        $contenido = $this->actingAs($this->direccion())
            ->get($recurso::getUrl('create'))
            ->assertOk()
            ->getContent();

        $this->assertBotonDeVolver($contenido, $recurso);
    }

    #[DataProviderExternal(PanelCompletoTest::class, 'recursosConEdicion')]
    public function test_editar_tiene_un_boton_para_volver_al_listado(string $recurso): void
    {
        $modelo = $recurso::getModel();
        $registro = $modelo::query()->first() ?? $modelo::factory()->create();

        $contenido = $this->actingAs($this->direccion())
            ->get($recurso::getUrl('edit', ['record' => $registro]))
            ->assertOk()
            ->getContent();

        $this->assertBotonDeVolver($contenido, $recurso);
    }

    /** En el listado no hay a dónde volver. */
    public function test_el_listado_no_tiene_boton_de_volver(): void
    {
        $this->actingAs($this->direccion())
            ->get(AsociadoResource::getUrl('index'))
            ->assertOk()
            ->assertDontSee('Volver a');
    }

    private function assertBotonDeVolver(string $contenido, string $recurso): void
    {
        $listado = preg_quote(e($recurso::getUrl('index')), '#');
        $etiqueta = preg_quote(e('Volver a '.$recurso::getPluralModelLabel()), '#');

        // El enlace con esa etiqueta, no cualquier enlace al listado: las
        // migas de pan también llevan ahí.
        $this->assertMatchesRegularExpression(
            "#<a[^>]*href=\"{$listado}\"[^>]*>(?:(?!</a>).)*{$etiqueta}#s",
            $contenido,
            class_basename($recurso).' no tiene el botón «Volver a '.$recurso::getPluralModelLabel().'».'
        );
    }

    public function test_el_buscador_recorre_los_recursos_acordados(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $buscables = collect(Filament::getPanel('admin')->getResources())
            ->filter(fn (string $recurso): bool => $recurso::canGloballySearch())
            ->sort()
            ->values()
            ->all();

        $esperados = self::RECURSOS_DEL_BUSCADOR;
        sort($esperados);

        $this->assertSame($esperados, $buscables);
    }

    public function test_el_buscador_encuentra_un_asociado_por_su_nombre_y_lleva_a_su_ficha(): void
    {
        $this->actingAs($this->direccion());
        $asociado = Asociado::factory()->create(['nombre' => 'Almendros del Parque']);

        $this->buscar('Almendros del')
            ->assertSee('Almendros del Parque')
            ->assertSee(AsociadoResource::getUrl('edit', ['record' => $asociado]));
    }

    /**
     * La bolsa de empleo no tiene página de edición: la vacante lleva al
     * listado ya filtrado por su cargo.
     */
    public function test_una_vacante_lleva_al_listado_filtrado_por_su_cargo(): void
    {
        $this->actingAs($this->direccion());
        Vacante::factory()->create(['cargo' => 'Bartender de coctelería']);

        $this->buscar('Bartender de cocte')
            ->assertSee('Bartender de coctelería')
            ->assertSee(VacanteResource::getUrl('index', ['search' => 'Bartender de coctelería']));
    }

    /**
     * Buscar no abre lo que el rol no puede ver: sin permiso para ver
     * asociados, la secretaría no los encuentra por el buscador.
     */
    public function test_el_buscador_respeta_los_permisos_del_rol(): void
    {
        Asociado::factory()->create(['nombre' => 'Almendros del Parque']);
        $secretaria = $this->secretaria();
        $this->actingAs($secretaria);

        $this->buscar('Almendros del')->assertSee('Almendros del Parque');

        $secretaria->roles->first()->revokePermissionTo('ver_asociado');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($secretaria->fresh());

        $this->buscar('Almendros del')->assertDontSee('Almendros del Parque');
    }

    /**
     * La campana vive en una zona de la barra que Filament conserva entre
     * páginas (`x-persist`): al abrir un aviso, la página cambiaba y el panel
     * de avisos seguía abierto encima. Y un clic en el título o el texto del
     * aviso no hacía nada: solo navegaba su botón. Las dos cosas se midieron
     * con clics reales en el navegador; aquí se vigila que el script siga
     * cargado y cableado.
     */
    public function test_abrir_un_aviso_lleva_a_su_destino_y_cierra_la_campana(): void
    {
        $this->actingAs($this->direccion())
            ->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('panel-campana', escape: false);

        $script = file_get_contents(resource_path('js/panel-campana.js'));

        $this->assertMatchesRegularExpression(
            "#addEventListener\(\s*'livewire:navigate'[\s\S]*?'close-modal'[\s\S]*?id:\s*'database-notifications'#",
            $script,
            'panel-campana.js tiene que cerrar el panel de avisos al navegar.'
        );
        $this->assertMatchesRegularExpression(
            "#closest\('\.fi-no-database \.fi-no-notification'\)[\s\S]*?querySelector\('a\[href\]'\)\?\.click\(\)#",
            $script,
            'panel-campana.js tiene que delegar el clic de la tarjeta del aviso en su enlace.'
        );
    }
}

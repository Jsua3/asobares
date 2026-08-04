<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Recorre TODO el panel, no solo los listados.
 *
 * PanelAdminTest solo abría las páginas índice, y por eso no vio que el
 * login estaba roto. Esta prueba abre además cada formulario de creación,
 * cada formulario de edición con un registro real, las páginas sueltas y
 * el perfil, que es donde se configura el segundo factor.
 *
 * Es un barrido de humo: no verifica reglas de negocio, verifica que nada
 * reviente al renderizarse.
 */
class PanelCompletoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function direccion(): User
    {
        return User::role(User::ROL_SUPER_ADMIN)->firstOrFail();
    }

    /**
     * Todos los recursos que el panel tiene registrados, descubiertos en
     * caliente: si mañana se agrega uno, entra solo a la prueba.
     *
     * @return list<array{0: class-string<resource>}>
     */
    public static function recursosDelPanel(): array
    {
        return array_map(
            fn (string $recurso): array => [$recurso],
            self::recursosRegistrados()
        );
    }

    /**
     * Recursos que se pueden crear/editar desde el panel como dirección.
     * Vacante se excluye: solo los asociados la crean, el gremio solo modera.
     *
     * @return list<array{0: class-string<resource>}>
     */
    public static function recursosCreablesYEditables(): array
    {
        return array_filter(
            self::recursosDelPanel(),
            fn (array $recurso) => $recurso[0] !== 'App\\Filament\\Resources\\Vacantes\\VacanteResource'
        );
    }

    /**
     * Se anclan las rutas al propio archivo de prueba, no al directorio de
     * trabajo, y la clase se arma con los dos últimos segmentos: así funciona
     * igual en Windows que en Linux.
     *
     * @return list<class-string<resource>>
     */
    private static function recursosRegistrados(): array
    {
        $directorio = dirname(__DIR__, 2).'/app/Filament/Resources';
        $recursos = [];

        foreach (glob($directorio.'/*/*Resource.php') ?: [] as $archivo) {
            $normalizado = str_replace('\\', '/', $archivo);
            $carpeta = basename(dirname($normalizado));
            $clase = basename($normalizado, '.php');

            $recursos[] = "App\\Filament\\Resources\\{$carpeta}\\{$clase}";
        }

        sort($recursos);

        return $recursos;
    }

    #[DataProvider('recursosDelPanel')]
    public function test_el_listado_de_cada_recurso_carga(string $recurso): void
    {
        $this->actingAs($this->direccion())
            ->get($recurso::getUrl('index'))
            ->assertSuccessful();
    }

    #[DataProvider('recursosCreablesYEditables')]
    public function test_el_formulario_de_creacion_de_cada_recurso_carga(string $recurso): void
    {
        if (! $recurso::hasPage('create')) {
            $this->markTestSkipped(class_basename($recurso).' no se crea desde el panel.');
        }

        $this->actingAs($this->direccion())
            ->get($recurso::getUrl('create'))
            ->assertSuccessful();
    }

    #[DataProvider('recursosCreablesYEditables')]
    public function test_el_formulario_de_edicion_de_cada_recurso_carga_con_un_registro_real(string $recurso): void
    {
        if (! $recurso::hasPage('edit')) {
            $this->markTestSkipped(class_basename($recurso).' no se edita desde el panel.');
        }

        $registro = $recurso::getModel()::query()->first();

        if ($registro === null) {
            $this->markTestSkipped('Las semillas no crearon ningún '.class_basename($recurso).'.');
        }

        $this->actingAs($this->direccion())
            ->get($recurso::getUrl('edit', ['record' => $registro]))
            ->assertSuccessful();
    }

    /** @return list<array{0: string}> */
    public static function paginasSueltas(): array
    {
        return [
            ['/admin'],              // escritorio con sus widgets
            ['/admin/ajustes'],
            ['/admin/bitacora'],
        ];
    }

    #[DataProvider('paginasSueltas')]
    public function test_las_paginas_sueltas_cargan(string $ruta): void
    {
        $this->actingAs($this->direccion())->get($ruta)->assertSuccessful();
    }

    public function test_el_perfil_carga(): void
    {
        // Aquí vive la configuración del segundo factor: es la página con más
        // superficie de contratos de MFA y la que menos se visita.
        $url = Filament::getPanel('admin')->getProfileUrl();

        $this->assertNotNull($url, 'El panel debería exponer la página de perfil.');

        $this->actingAs($this->direccion())->get($url)->assertSuccessful();
    }

    public function test_todos_los_widgets_del_escritorio_renderizan(): void
    {
        $this->actingAs($this->direccion());

        foreach (Filament::getPanel('admin')->getWidgets() as $widget) {
            Livewire::test($widget)
                ->assertOk();
        }
    }

    public function test_la_secretaria_puede_abrir_los_formularios_que_le_corresponden(): void
    {
        $secretaria = User::role(User::ROL_SUBADMIN)->firstOrFail();

        foreach (self::recursosRegistrados() as $recurso) {
            if (! $recurso::hasPage('create')) {
                continue;
            }

            $respuesta = $this->actingAs($secretaria)->get($recurso::getUrl('create'));

            // O puede crear, o la policy se lo niega limpiamente. Lo que no
            // puede pasar es un 500.
            $this->assertContains(
                $respuesta->status(),
                [200, 403],
                class_basename($recurso).' respondió '.$respuesta->status().' al abrir el formulario de creación.'
            );
        }
    }

    public function test_no_quedan_paginas_del_panel_sin_registrar(): void
    {
        // Cada Page en app/Filament/Pages debe estar accesible: si alguna se
        // queda sin ruta o sin permiso, es un menú que no lleva a ninguna parte.
        $paginas = array_filter(
            Filament::getPanel('admin')->getPages(),
            fn (string $pagina): bool => str_starts_with($pagina, 'App\\Filament\\Pages\\')
        );

        foreach ($paginas as $pagina) {
            /** @var class-string<Page> $pagina */
            $this->actingAs($this->direccion())
                ->get($pagina::getUrl())
                ->assertSuccessful();
        }
    }
}

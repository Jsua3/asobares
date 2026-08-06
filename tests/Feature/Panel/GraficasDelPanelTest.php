<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Las gráficas del panel dejaron de pintar sus etiquetas en un gris fijo.
 */
class GraficasDelPanelTest extends TestCase
{
    /**
     * `Panel` no tiene `getAssets()` en Filament 4.12: los assets quedan en
     * la propiedad protegida de `HasAssets` y solo se vuelcan al registro
     * real cuando se llama a `registerAssets()`, que los empuja al
     * `AssetManager` que hay detrás de la fachada `FilamentAsset`. Por eso
     * se construye el panel, se fuerza ese volcado y se lee la fachada en
     * vez de una `getAssets()` que no existe.
     */
    public function test_el_plugin_de_graficas_esta_registrado_en_el_panel(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());
        $panel->registerAssets();

        $ids = array_map(
            fn ($asset): string => $asset->getId(),
            FilamentAsset::getScripts(['app'])
        );

        $this->assertContains('panel-graficas', $ids);
    }

    /**
     * Un clon recien hecho, sin `npm run build`, tiene que poder ejecutar
     * artisan. Antes de la guarda, `php artisan view:clear` lanzaba
     * ViteManifestNotFoundException, que es justo el comando que hay que
     * correr ANTES de compilar.
     *
     * Se esconden manifiesto y `public/hot` a la vez: si el entorno de la
     * prueba tuviera `npm run dev` corriendo, `public/hot` por si solo
     * bastaria para que `Vite::asset()` no lance nada, y la prueba no
     * estaria comprobando el punto muerto real (clon sin nada compilado).
     */
    public function test_definir_el_panel_no_estalla_sin_nada_compilado(): void
    {
        $manifiesto = public_path('build/manifest.json');
        $copiaManifiesto = $manifiesto.'.prueba';
        $existiaManifiesto = file_exists($manifiesto);

        $servidorDeDesarrollo = public_path('hot');
        $copiaServidor = $servidorDeDesarrollo.'.prueba';
        $existiaServidor = file_exists($servidorDeDesarrollo);

        if ($existiaManifiesto) {
            rename($manifiesto, $copiaManifiesto);
        }

        if ($existiaServidor) {
            rename($servidorDeDesarrollo, $copiaServidor);
        }

        try {
            $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());
            $this->assertInstanceOf(Panel::class, $panel);
        } finally {
            if ($existiaManifiesto) {
                rename($copiaManifiesto, $manifiesto);
            }

            if ($existiaServidor) {
                rename($copiaServidor, $servidorDeDesarrollo);
            }
        }
    }

    /**
     * Un manifiesto que existe pero no conoce esta entrada es el otro
     * camino hacia el mismo punto muerto: alguien que compilo en otra rama
     * (main, por ejemplo) hace checkout de esta y corre cualquier `artisan`
     * antes de `npm run build`. Esta prueba es la razon de que la guarda
     * real sea un `catch (ViteException)` y no un `file_exists()`: el
     * archivo existe, así que `file_exists()` por si solo no distingue
     * este caso de uno sano.
     *
     * Se esconde tambien `public/hot`: si existiera, `Vite::asset()`
     * usaria el servidor de desarrollo y ni siquiera leeria el
     * manifiesto, y la prueba no estaria ejercitando el camino real.
     */
    public function test_definir_el_panel_no_estalla_con_manifiesto_desactualizado(): void
    {
        $manifiesto = public_path('build/manifest.json');
        $copiaManifiesto = $manifiesto.'.prueba';
        $existiaManifiesto = file_exists($manifiesto);

        $servidorDeDesarrollo = public_path('hot');
        $copiaServidor = $servidorDeDesarrollo.'.prueba';
        $existiaServidor = file_exists($servidorDeDesarrollo);

        if ($existiaManifiesto) {
            rename($manifiesto, $copiaManifiesto);
        }

        if ($existiaServidor) {
            rename($servidorDeDesarrollo, $copiaServidor);
        }

        File::ensureDirectoryExists(dirname($manifiesto));

        // Manifiesto valido, pero de una rama que no conoce panel-graficas.js.
        File::put($manifiesto, json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
            ],
        ]));

        try {
            $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());
            $this->assertInstanceOf(Panel::class, $panel);
        } finally {
            File::delete($manifiesto);

            if ($existiaManifiesto) {
                rename($copiaManifiesto, $manifiesto);
            }

            if ($existiaServidor) {
                rename($copiaServidor, $servidorDeDesarrollo);
            }
        }
    }

    /**
     * Se afirma sobre el texto del archivo porque el proyecto no tiene banco
     * de pruebas de JavaScript (no hay vitest ni jest en `package.json`), y
     * montarlo para un plugin de 40 líneas no se paga. La verificación real
     * de que el repintado funciona es el Paso 7 de esta tarea, a ojo en el
     * navegador y en los dos temas.
     */
    public function test_el_plugin_lee_los_tokens_y_repinta_al_cambiar_de_tema(): void
    {
        $js = File::get(resource_path('js/panel-graficas.js'));

        // Lee del tema activo en vez de llevar colores dentro.
        $this->assertStringContainsString("leerToken('--asb-tinta')", $js);
        $this->assertStringContainsString("leerToken('--asb-linea')", $js);

        // Chart.js no redibuja solo porque cambie la clase de <html>.
        $this->assertStringContainsString('MutationObserver', $js);
        $this->assertStringContainsString("attributeFilter: ['class']", $js);

        // Lleva su propio registro de instancias en vez de un global de Chart.
        $this->assertStringContainsString('start(grafica)', $js);
        $this->assertStringContainsString('stop(grafica)', $js);
    }

    /**
     * Los widgets no deben escribir colores de texto a mano. El relleno de
     * marca sí se admite: como relleno funciona en los dos temas.
     */
    public function test_los_widgets_no_cablean_colores_de_ejes_ni_rejilla(): void
    {
        $hallazgos = [];

        foreach (File::allFiles(app_path('Filament/Widgets')) as $archivo) {
            $contenido = $archivo->getContents();

            foreach (['borderColor' => '#', 'color' => '#'] as $clave => $_) {
                if (preg_match("/'{$clave}'\s*=>\s*'#/", $contenido) === 1) {
                    $hallazgos[] = $archivo->getFilename()." cablea '{$clave}'";
                }
            }
        }

        $this->assertSame([], $hallazgos, implode("\n", $hallazgos));
    }
}

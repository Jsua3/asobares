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

<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Foundation\Vite;
use Illuminate\Foundation\ViteException;
use Illuminate\Foundation\ViteManifestNotFoundException;
use Illuminate\Support\Facades\Facade;
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
     * No se toca el manifiesto en disco: `Illuminate\Foundation\Vite`
     * cachea el manifiesto ya leido en una propiedad estatica de clase
     * (`Vite::$manifests`), y esa cache sobrevive dentro de la misma
     * peticion de prueba. El panel admin real ya evaluo `Vite::asset()`
     * durante el arranque de consola de esta misma prueba (`Panel::register()`
     * llama a `registerAssets()` porque `php artisan test` corre en
     * consola), así que para cuando el cuerpo de la prueba se ejecuta el
     * manifiesto real ya esta en esa cache estatica: sobrescribir el
     * archivo en disco no la invalida, y la prueba no ejercitaria nada.
     * En su lugar se sustituye la instancia de Vite en el contenedor por
     * una que lanza sin tocar el sistema de archivos.
     */
    public function test_definir_el_panel_no_estalla_sin_nada_compilado(): void
    {
        $this->fingirQueViteLanzaAlPedirAsset(
            fn () => throw new ViteManifestNotFoundException('manifiesto de prueba: ausente')
        );

        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertInstanceOf(Panel::class, $panel);
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
     * Mismo motivo que la prueba anterior para no tocar el disco: la
     * cache estatica de `Vite::$manifests` haria inutil sobrescribir el
     * manifiesto real.
     */
    public function test_definir_el_panel_no_estalla_con_manifiesto_desactualizado(): void
    {
        $this->fingirQueViteLanzaAlPedirAsset(
            fn () => throw new ViteException('Unable to locate file in Vite manifest: resources/js/panel-graficas.js.')
        );

        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertInstanceOf(Panel::class, $panel);
    }

    /**
     * Sustituye la instancia real de `Vite` en el contenedor por una que
     * lanza en cuanto se le pide un asset, sin tocar el sistema de
     * archivos ni el manifiesto real.
     *
     * `Illuminate\Foundation\Vite::class` es el accessor real de la
     * fachada (`vendor/laravel/framework/src/Illuminate/Support/Facades/Vite.php`,
     * `getFacadeAccessor()`), así que esa es la clave que hay que
     * sustituir en el contenedor. Y como la fachada cachea la instancia ya
     * resuelta en una propiedad estatica propia (`Facade::$resolvedInstance`),
     * hace falta limpiar tambien ese cache: si no, la fachada sigue
     * devolviendo el Vite real que el arranque de consola de esta misma
     * prueba ya resolvio antes de que el cuerpo de la prueba se ejecute.
     */
    private function fingirQueViteLanzaAlPedirAsset(\Closure $lanzar): void
    {
        Facade::clearResolvedInstance(Vite::class);

        $this->app->instance(Vite::class, new class($lanzar) extends Vite
        {
            public function __construct(private \Closure $lanzar) {}

            public function asset($asset, $buildDirectory = null)
            {
                ($this->lanzar)();
            }
        });
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

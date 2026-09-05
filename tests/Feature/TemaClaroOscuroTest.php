<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El sitio público se puede ver en claro o en oscuro, y el control vive en una
 * barra lateral fija que acompaña el scroll.
 */
class TemaClaroOscuroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function crearUsuario(string $rol, ?Asociado $asociado = null): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado?->id]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /**
     * `app.css` declara `--font-sans: 'Poppins', ...` pero nada enlazaba el
     *
     * @font-face compilado por `bunny('Poppins', ...)` en vite.config.js:
     * medido en navegador, Poppins resolvía por fallback y no se
     * renderizaba (el mismo defecto que tenía el panel, ver
     * TemaDelPanelTest). El layout público debe traer `Vite::fonts()`.
     */
    public function test_el_sitio_publico_enlaza_la_hoja_de_fuentes_real(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('@font-face', false)
            ->assertSee('font-family: "Poppins"', false);
    }

    // --- El tema se aplica antes del primer pintado ---

    public function test_el_layout_resuelve_el_tema_en_el_head_antes_de_pintar(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            // Lee la preferencia y la aplica sobre <html> de forma síncrona.
            ->assertSee("localStorage.getItem('theme')", false)
            ->assertSee("classList.toggle('dark'", false)
            // Sin este fallback, quien nunca eligió no seguiría a su sistema.
            ->assertSee('prefers-color-scheme: dark', false)
            // La barra del navegador móvil también cambia de color.
            ->assertSee('theme-color', false);
    }

    /**
     * Chromium congela toda propiedad con `transition` cuya valor venga de una
     * custom property cuando esa property cambia. Sin la mordaza, cambiar de
     * tema en vivo dejaba los enlaces de la navbar y los bordes de las
     * tarjetas con el color del tema anterior hasta recargar la página.
     */
    public function test_el_cambio_en_vivo_apaga_las_transiciones_para_no_congelar_colores(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('transition:none !important', false)
            ->assertSee('requestAnimationFrame', false);
    }

    /**
     * Hasta el 3 sep 2026 esta prueba prohibía «Sistema» a propósito (OBS3-03:
     * el sitio arranca en el tema del dispositivo, y el selector solo ofrecía
     * forzar uno). Sua decidió ese día que el popover de la barra de
     * escritorio ofrezca las tres: el arranque sigue siendo el del
     * dispositivo; lo que cambia es que se puede VOLVER a él tras forzar uno.
     * Anotado en encargo.md §13.
     *
     * Las cadenas '>Claro<' y '>Oscuro<' las emiten dos controles a la vez:
     * la barra lateral (móvil) y el popover (escritorio). '>Sistema<' solo el
     * popover.
     */
    public function test_el_selector_ofrece_claro_oscuro_y_sistema(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('Apariencia del sitio', false)
            ->assertSee('>Claro<', false)
            ->assertSee('>Oscuro<', false)
            ->assertSee('>Sistema<', false)
            ->assertSee("\$store.tema.elegir('light')", false)
            ->assertSee("\$store.tema.elegir('dark')", false)
            ->assertSee("\$store.tema.elegir('system')", false);
    }

    /**
     * Desde el 3 sep 2026 el tema de escritorio vive en la barra de
     * navegación (popover-tema) y la barra lateral se queda solo en móvil.
     */
    public function test_el_control_de_tema_vive_en_la_barra_lateral_en_movil_y_en_la_navbar_en_escritorio(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('tema-lateral fixed', false)
            ->assertSee('sm:top-1/2', false)
            ->assertSee('lg:hidden', false)
            ->assertSee('id="popover-tema"', false);
    }

    // --- Quién ve qué en el desplegable de cuenta ---

    public function test_el_visitante_anonimo_no_ve_desplegable_de_configuracion_en_la_navbar(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('Apariencia del sitio', false)
            ->assertDontSee('Configuración del sitio', false)
            ->assertDontSee('menu-cuenta', false)
            ->assertDontSee('Cerrar sesión')
            ->assertDontSee('Ir al panel del gremio');
    }

    public function test_el_asociado_ve_su_establecimiento_y_el_atajo_a_mi_cuenta(): void
    {
        $asociado = Asociado::query()->firstOrFail();
        $usuario = $this->crearUsuario(User::ROL_ASOCIADO, $asociado);

        $respuesta = $this->actingAs($usuario)->get('/contacto');

        $respuesta->assertOk()
            ->assertSee($usuario->name)
            ->assertSee($asociado->nombre)
            ->assertSee('Cerrar sesión')
            // El dueño de establecimiento no entra al panel.
            ->assertDontSee('Ir al panel del gremio');
    }

    public function test_la_secretaria_ve_el_atajo_al_panel_y_no_el_de_mi_cuenta(): void
    {
        $usuario = $this->crearUsuario(User::ROL_SUBADMIN);

        $respuesta = $this->actingAs($usuario)->get('/contacto');

        $respuesta->assertOk()
            ->assertSee($usuario->name)
            ->assertSee('Secretaría del gremio')
            ->assertSee('Ir al panel del gremio')
            ->assertSee('Cerrar sesión')
            ->assertDontSee('Mis vacantes');
    }

    public function test_la_direccion_tambien_tiene_el_desplegable_en_el_sitio_publico(): void
    {
        $usuario = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        $respuesta = $this->actingAs($usuario)->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('Dirección del gremio')
            ->assertSee('Ir al panel del gremio');
    }

    /**
     * El paginador de Laravel venía cableado en grises (2,63:1 sobre el fondo
     * oscuro) y, sin carpeta `lang/`, escupía las claves «pagination.previous»
     * y «pagination.next» y un «Showing … results» en inglés.
     */
    public function test_el_paginador_esta_en_espanol_y_sigue_el_tema(): void
    {
        /*
         * `Paginator::$defaultView` es estático y vive en todo el proceso.
         * Livewire lo reapunta a su propia vista al renderizar una tabla del
         * panel (vendor/livewire/.../SupportPagination.php) y no siempre lo
         * restaura, así que si antes corrió una prueba de /admin este test
         * mediría la vista de Livewire. En producción no ocurre —cada petición
         * arranca limpia—, pero aquí hay que devolverlo a su sitio.
         */
        Paginator::useTailwind();

        // El directorio pagina de 12 en 12. No se confía en cuántos deja
        // publicados el seeder: se garantiza que haya más de una página.
        Asociado::factory()->count(15)->publicado()->create();

        $publicados = Asociado::query()->where('estado', EstadoPublicacion::Publicado)->count();
        $this->assertGreaterThan(12, $publicados, 'Hacen falta más de 12 publicados para que haya paginación.');

        $respuesta = $this->get('/directorio');

        $respuesta->assertOk()
            ->assertSee('Paginación', false)
            ->assertSee('Página siguiente', false)
            ->assertSee('Mostrando')
            // Las claves crudas y el inglés no deben aparecer nunca.
            ->assertDontSee('pagination.previous')
            ->assertDontSee('pagination.next')
            ->assertDontSee('Showing')
            ->assertDontSee('results');
    }

    // --- La guardia que sostiene el refactor ---

    /**
     * @return array<string, string> patrón prohibido => por qué
     */
    public static function clasesProhibidas(): array
    {
        return [
            '/(?:bg|text|border|divide|from|to|via|ring|placeholder|fill|stroke|decoration)-noche-\d+/' => 'la rampa noche es del tema oscuro; usa fondo/superficie/tinta/suave/tenue/apagado',
            '/(?:border|divide|ring)-white\/[\[\.\d]/' => 'los bordes blancos translúcidos son invisibles en claro; usa linea o linea-fuerte',
            '/\bbg-white\b/' => 'el blanco fijo no es una superficie; usa superficie',
            '/\b(?:bg|text|border)-black\b/' => 'el negro fijo no es un fondo; usa fondo o fuerte',
            '/hover:text-white\b/' => 'aclarar hacia el blanco solo funciona en oscuro; usa hover:text-fuerte',
            '/\b(?:bg|text|border)-(?:emerald|amber)-(?:950|400|300|200|100)\b/' => 'los estados asumían fondo negro; usa exito/aviso y sus variantes',
            '/\bbg-marca-950\b/' => 'el panel rojo oscuro no existe en claro; usa bg-marca-panel',
            '/\btext-marca-(?:100|300|400)\b/' => 'el rojo claro no alcanza contraste sobre fondo claro; usa acento o acento-fuerte',
            // Esta es la que se habria comido al paginador de Laravel, que
            // venia cableado en grises y daba 2.63:1 sobre el fondo oscuro.
            '/\b(?:bg|text|border|divide|ring)-(?:gray|slate|zinc|neutral|stone)-\d+/' => 'los grises de fábrica de Tailwind no siguen el tema; usa los tokens',
        ];
    }

    /**
     * El modo claro se rompe en silencio: una clase cableada suelta no lanza
     * ningún error, solo deja texto negro sobre negro que nadie ve hasta que
     * un afiliado se queja. Esta prueba es lo único que lo impide.
     */
    public function test_ninguna_vista_publica_conserva_clases_de_tema_cableadas(): void
    {
        $directorios = [
            resource_path('views/publico'),
            resource_path('views/components/publico'),
            resource_path('views/components/layouts'),
            resource_path('views/errors'),
            // Las vistas publicadas de paquetes también pintan en el sitio.
            resource_path('views/vendor'),
        ];

        $hallazgos = [];

        foreach ($directorios as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                $contenido = $archivo->getContents();
                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                foreach (self::clasesProhibidas() as $patron => $motivo) {
                    if (preg_match_all($patron, $contenido, $coincidencias) > 0) {
                        $hallazgos[] = sprintf(
                            '%s → %s (%s)',
                            $ruta,
                            implode(', ', array_unique($coincidencias[0])),
                            $motivo
                        );
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Quedan clases de tema cableadas:\n".implode("\n", $hallazgos));
    }

    /**
     * El panel se sumó al refactor bicromático, así que la guardia tiene que
     * cubrirlo: sus vistas se rompen en silencio igual que las del sitio.
     */
    public function test_ninguna_vista_del_panel_conserva_clases_de_tema_cableadas(): void
    {
        $directorios = array_filter([
            resource_path('views/components/panel'),
            resource_path('views/filament'),
        ], File::isDirectory(...));

        $this->assertNotEmpty($directorios, 'No hay vistas de panel que vigilar.');

        $hallazgos = [];

        foreach ($directorios as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                $contenido = $archivo->getContents();
                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                foreach (self::clasesProhibidas() as $patron => $motivo) {
                    if (preg_match_all($patron, $contenido, $coincidencias) > 0) {
                        $hallazgos[] = sprintf(
                            '%s → %s (%s)',
                            $ruta,
                            implode(', ', array_unique($coincidencias[0])),
                            $motivo
                        );
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Quedan clases de tema cableadas en el panel:\n".implode("\n", $hallazgos));
    }
}

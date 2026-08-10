<?php

namespace Tests\Feature\Panel;

use App\Enums\CargoDelSector;
use App\Filament\Pages\InformeDelObservatorio;
use App\Filament\Pages\Observatorio;
use App\Filament\Widgets\Observatorio\CoberturaDeProveedores;
use App\Filament\Widgets\Observatorio\ComposicionDelSector;
use App\Filament\Widgets\Observatorio\DemandaLaboralPorArea;
use App\Filament\Widgets\Observatorio\OfertaContraDemanda;
use App\Filament\Widgets\Observatorio\PresenciaPorMunicipio;
use App\Filament\Widgets\Observatorio\SaludFinanciera;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\User;
use App\Models\Vacante;
use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El observatorio es el argumento que la dirección lleva a una alcaldía.
 */
class ObservatorioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get(Observatorio::getUrl())
            ->assertOk()
            ->assertSee('Observatorio del gremio');
    }

    /**
     * La frontera negativa, que es la que prueba algo: el observatorio lleva
     * salud financiera, y la secretaría no ve dinero en ninguna otra pantalla.
     */
    public function test_la_secretaria_no_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->assertFalse(Observatorio::canAccess());
        $this->get(Observatorio::getUrl())->assertForbidden();
    }

    public function test_la_banda_de_kpis_usa_el_componente_del_panel_y_rotula_su_n(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $respuesta = $this->get(Observatorio::getUrl());

        $respuesta->assertOk()
            // El componente KPI rinde este enlace cuando recibe `url`.
            ->assertSee('Ver detalle')
            // Y el principio #5 del spec: ninguna cifra sin su n.
            ->assertSee('n = ', false);
    }

    public function test_las_tres_visualizaciones_solidas_dibujan_y_rotulan_su_muestra(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        foreach ([
            PresenciaPorMunicipio::class,
            ComposicionDelSector::class,
            SaludFinanciera::class,
        ] as $widget) {
            Livewire::test($widget)->assertSee('n = ');
        }
    }

    public function test_los_widgets_del_observatorio_dejan_hueco_al_plugin_de_tema(): void
    {
        foreach (File::allFiles(app_path('Filament/Widgets/Observatorio')) as $archivo) {
            // `GraficaDelObservatorio` es la base abstracta de la que heredan
            // las seis: no declara `getOptions()` (cada gráfica trae la suya),
            // así que no le aplica esta regla.
            if ($archivo->getFilenameWithoutExtension() === 'GraficaDelObservatorio') {
                continue;
            }

            $contenido = $archivo->getContents();

            $this->assertStringContainsString(
                "'ticks'",
                $contenido,
                $archivo->getFilename().' debe declarar ticks aunque venga vacio: el plugin de tema solo escribe donde ya hay clave.'
            );
            $this->assertStringContainsString("'grid'", $contenido, $archivo->getFilename().' debe declarar grid.');
        }
    }

    /**
     * La prueba de arriba solo mira si las cadenas `'ticks'` y `'grid'`
     * aparecen en algún lugar del archivo, no que cada eje declarado las
     * tenga las dos. Un eje al que le falte una de las dos sigue en el gris
     * de fábrica y esta prueba pasaba igual. Se inspecciona la estructura
     * real que devuelve `getOptions()`, eje por eje.
     */
    public function test_cada_eje_de_los_widgets_del_observatorio_declara_ticks_y_grid(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        foreach ([
            PresenciaPorMunicipio::class,
            ComposicionDelSector::class,
            SaludFinanciera::class,
        ] as $widget) {
            $instancia = Livewire::test($widget)->instance();

            $metodo = new \ReflectionMethod($widget, 'getOptions');
            $opciones = $metodo->invoke($instancia);

            $ejes = $opciones['scales'] ?? [];
            $this->assertNotEmpty($ejes, "{$widget} no declara ninguna escala en getOptions().");

            foreach ($ejes as $nombreEje => $eje) {
                $this->assertArrayHasKey(
                    'ticks',
                    $eje,
                    "{$widget}: el eje \"{$nombreEje}\" no declara ticks, así que el plugin de tema no tiene dónde escribir."
                );
                $this->assertArrayHasKey(
                    'grid',
                    $eje,
                    "{$widget}: el eje \"{$nombreEje}\" no declara grid, así que el plugin de tema no tiene dónde escribir."
                );
            }
        }
    }

    /**
     * `<x-filament-panels::page>` ya invoca `{{ $this->footerWidgets }}` por
     * dentro (vendor/filament/filament/resources/views/components/page/index.blade.php).
     * Si la vista de la página lo vuelve a invocar a mano, cada widget monta
     * dos instancias Livewire: el doble de consultas, y un directivo vería
     * cada gráfica repetida.
     */
    public function test_cada_widget_del_observatorio_se_monta_una_sola_vez(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $html = $this->get(Observatorio::getUrl())->getContent();

        foreach ([
            'app.filament.widgets.observatorio.presencia-por-municipio',
            'app.filament.widgets.observatorio.composicion-del-sector',
            'app.filament.widgets.observatorio.salud-financiera',
            'app.filament.widgets.observatorio.cobertura-de-proveedores',
            'app.filament.widgets.observatorio.demanda-laboral-por-area',
            'app.filament.widgets.observatorio.oferta-contra-demanda',
        ] as $nombreLivewire) {
            $this->assertSame(
                1,
                substr_count($html, $nombreLivewire),
                "{$nombreLivewire} aparece ".substr_count($html, $nombreLivewire).' veces en el HTML: se está montando más de una instancia.'
            );
        }
    }

    /**
     * Con la semilla de hoy la oferta contra demanda no llega al umbral, así
     * que la pantalla tiene que decirlo en vez de dibujar barras sobre n=17.
     */
    public function test_una_visualizacion_sin_muestra_no_dibuja_y_lo_explica(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(OfertaContraDemanda::class)
            ->assertSee('Aún sin muestra suficiente')
            ->assertSee('n = ');
    }

    /**
     * Y cuando el dato llegue, dibuja. Si esta prueba se cae, es que el
     * estado vacío se quedó pegado y el observatorio nunca enseñará empleo.
     */
    public function test_la_misma_visualizacion_dibuja_en_cuanto_hay_muestra(): void
    {
        // `publicado()` es un estado real de ambas factories, verificado.
        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->count(35)->publicado()->for($asociado)->create([
            'categoria_cargo' => CargoDelSector::Barra,
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(OfertaContraDemanda::class)
            ->assertDontSee('Aún sin muestra suficiente');
    }

    /**
     * El informe es el objeto que la dirección deja sobre la mesa en una
     * alcaldía: marca, fecha, y ninguna cifra sin su n.
     */
    public function test_el_informe_lleva_marca_fecha_y_el_n_de_cada_serie(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get(InformeDelObservatorio::getUrl())
            ->assertOk()
            ->assertSee('ASOBARES')
            ->assertSee(now()->format('d/m/Y'))
            ->assertSee('n = ', false)
            // El descargo es obligatorio: hay series que no alcanzan muestra.
            ->assertSee('muestra');
    }

    public function test_el_informe_es_igual_de_exclusivo_que_el_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->get(InformeDelObservatorio::getUrl())->assertForbidden();
    }

    public function test_el_tema_esconde_el_cromo_del_panel_al_imprimir(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString('@media print', $tema);
    }

    /**
     * `test_el_informe_lleva_marca_fecha_y_el_n_de_cada_serie` solo mira si
     * la palabra «muestra» aparece en algún lugar de la página, y esa
     * palabra vive también en el título fijo «Descargo sobre el tamaño de
     * muestra», que sale siempre, tenga los datos que tenga el informe. Si
     * alguien borra el aviso por serie (el párrafo junto a cada tabla) esa
     * prueba sigue en verde, y es justo ese aviso el que impide que un
     * funcionario confunda `n = 10` con una cifra sólida.
     *
     * Esta prueba afirma la estructura, no la presencia de una palabra: la
     * serie sin muestra suficiente lleva el aviso pegado a su propia
     * sección, y la serie con muestra suficiente no lo lleva en la suya.
     * Para eso hace falta un caso de cada tipo en el mismo render:
     * `coberturaDeProveedores()` ya no alcanza con la semilla por defecto
     * (ver el docblock de `CoberturaDeProveedores`, n = 10), y se empuja
     * `ofertaContraDemanda()` por encima del umbral con aspirantes reales de
     * una sola categoría — mismo mecanismo que
     * `test_la_misma_visualizacion_dibuja_en_cuanto_hay_muestra`.
     *
     * `extraerSeccionDeSerie()` acota cada sección por su propio
     * `data-serie` hasta su propio `</section>` — no por el título de la
     * SIGUIENTE serie, como hacía la primera versión de esta prueba. Con
     * títulos como delimitador, un revisor demostró que renombrar el título
     * de otra serie (sin tocar el aviso de nadie) podía desplazar el "hasta"
     * varias secciones más abajo: el recorte se tragaba media página, el
     * aviso seguía cayendo dentro por pura casualidad, y la prueba pasaba
     * sin haber comprobado nada. `data-serie` no es texto visible que pueda
     * reaparecer en el descargo del pie, así que no tiene ese problema; y el
     * `assertLessThan` de abajo es la segunda red: si algún día el recorte
     * volviera a inflarse por cualquier otra vía, el tamaño lo delata aunque
     * el contenido, por casualidad, siguiera pasando.
     */
    public function test_el_aviso_de_muestra_insuficiente_va_pegado_a_su_serie_y_no_a_la_que_si_alcanza(): void
    {
        $metricas = app(MetricasDelObservatorio::class);

        // Precondición explícita: si el seeder cambia y la cobertura de
        // proveedores llega a 30, esta prueba tiene que avisarlo en vez de
        // volverse un falso positivo silencioso.
        $this->assertLessThan(
            SerieDelObservatorio::MUESTRA_MINIMA,
            $metricas->coberturaDeProveedores()->n,
            'Esta prueba necesita que la cobertura de proveedores no alcance muestra con la semilla por defecto.'
        );

        Aspirante::factory()->count(35)->create(['categoria_cargo' => CargoDelSector::Barra]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $html = $this->get(InformeDelObservatorio::getUrl())
            ->assertOk()
            ->getContent();

        $avisoPorSerie = 'todavía no alcanza muestra suficiente';

        $seccionSinMuestra = $this->extraerSeccionDeSerie($html, 'cobertura-de-proveedores');
        // El título pinta el título real que trae la sección: si alguna vez
        // vuelve a divergir de `series()`, esta línea lo delata en vez de
        // dejar que `extraerSeccionDeSerie()` encuentre la sección correcta
        // pero con contenido de otra.
        $this->assertStringContainsString('Cobertura de proveedores', $seccionSinMuestra);
        $this->assertStringContainsString(
            $avisoPorSerie,
            $seccionSinMuestra,
            'La sección de "Cobertura de proveedores" (sin muestra suficiente) debería llevar el aviso pegado a su tabla.'
        );
        $this->assertLessThan(
            8000,
            strlen($seccionSinMuestra),
            'La sección parece haberse tragado más página de la que le corresponde.'
        );

        $seccionConMuestra = $this->extraerSeccionDeSerie($html, 'oferta-contra-demanda');
        $this->assertStringContainsString('Oferta contra demanda', $seccionConMuestra);
        $this->assertStringNotContainsString(
            $avisoPorSerie,
            $seccionConMuestra,
            'La sección de "Oferta contra demanda" ya alcanza muestra suficiente y no debería llevar el aviso.'
        );
        $this->assertLessThan(
            8000,
            strlen($seccionConMuestra),
            'La sección parece haberse tragado más página de la que le corresponde.'
        );
    }

    /**
     * Recorta el `<section data-serie="{clave}">...</section>` completo de
     * una serie del informe. Ver el docblock de la prueba de arriba para el
     * porqué de anclar en `data-serie` y no en el título de otra sección.
     */
    private function extraerSeccionDeSerie(string $html, string $clave): string
    {
        $inicio = strpos($html, 'data-serie="'.$clave.'"');
        $this->assertNotFalse($inicio, "No se encontró la sección con data-serie=\"{$clave}\" en el HTML.");

        $fin = strpos($html, '</section>', $inicio);
        $this->assertNotFalse($fin, "La sección \"{$clave}\" no cierra con </section>.");

        return substr($html, $inicio, $fin - $inicio);
    }

    /**
     * Las tres flacas también respetan el hueco del plugin de tema: cada
     * eje declarado en `getOptions()` trae `ticks` y `grid`, igual que las
     * tres sólidas.
     */
    public function test_cada_eje_de_las_tres_visualizaciones_flacas_declara_ticks_y_grid(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        foreach ([
            CoberturaDeProveedores::class,
            DemandaLaboralPorArea::class,
            OfertaContraDemanda::class,
        ] as $widget) {
            $instancia = Livewire::test($widget)->instance();

            $metodo = new \ReflectionMethod($widget, 'getOptions');
            $opciones = $metodo->invoke($instancia);

            $ejes = $opciones['scales'] ?? [];
            $this->assertNotEmpty($ejes, "{$widget} no declara ninguna escala en getOptions().");

            foreach ($ejes as $nombreEje => $eje) {
                $this->assertArrayHasKey(
                    'ticks',
                    $eje,
                    "{$widget}: el eje \"{$nombreEje}\" no declara ticks, así que el plugin de tema no tiene dónde escribir."
                );
                $this->assertArrayHasKey(
                    'grid',
                    $eje,
                    "{$widget}: el eje \"{$nombreEje}\" no declara grid, así que el plugin de tema no tiene dónde escribir."
                );
            }
        }
    }
}

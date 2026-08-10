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

    /**
     * WCAG 2.1 §1.4.11 pide 3:1 para los objetos gráficos que hacen falta
     * para entender el contenido. Una barra de una gráfica lo es: si no se
     * distingue del fondo, el dato que carga no llega.
     */
    private const float CONTRASTE_MINIMO = 3.0;

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

    /**
     * Reemplaza a `test_las_tres_visualizaciones_solidas_dibujan_y_rotulan_su_muestra`,
     * la prueba que dejó pasar el Crítico 1: decía «dibujan» en su nombre y
     * solo comprobaba `assertSee('n = ')` sobre una lista de tres gráficas
     * escrita a mano, sin mirar canvas ni estado vacío. Esa lista incluía
     * `ComposicionDelSector` con n = 24 (bajo el umbral) y la daba por
     * «sólida» igual.
     *
     * Esta versión afirma lo que promete —canvas visible cuando la muestra
     * alcanza, estado vacío cuando no— para las SEIS gráficas, y deriva
     * cuáles deberían dibujar del umbral en vivo: lee la serie real de cada
     * widget por reflexión (`serie()`, protegido) y le pregunta
     * `hayMuestraSuficiente()`, en vez de repetir una lista de nombres que
     * alguien tendría que recordar mover el día que una muestra cruce el
     * umbral en cualquier dirección.
     *
     * El wrapper del canvas de `chart-widget.blade.php` (vendor/filament)
     * siempre imprime el `<canvas>` en el HTML, esté vacío o no: solo lo
     * envuelve en `style="display: none"` cuando `isEmpty()` es cierto. Por
     * eso la señal de "dibuja" no es la presencia del tag sino la AUSENCIA
     * de ese estilo, junto con la ausencia del texto del estado vacío.
     */
    public function test_cada_grafica_dibuja_o_calla_segun_si_su_muestra_alcanza_el_umbral_vigente(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $paginaObservatorio = Livewire::test(Observatorio::class)->instance();
        $widgets = (new \ReflectionMethod(Observatorio::class, 'getFooterWidgets'))->invoke($paginaObservatorio);

        $this->assertNotEmpty($widgets, 'Observatorio::getFooterWidgets() no debería estar vacío.');

        foreach ($widgets as $claseWidget) {
            $prueba = Livewire::test($claseWidget);

            $serie = (new \ReflectionMethod($claseWidget, 'serie'))->invoke($prueba->instance());

            $prueba->assertSee('n = ');

            if ($serie->hayMuestraSuficiente()) {
                $prueba
                    ->assertDontSeeHtml('style="display: none"')
                    ->assertDontSee('Aún sin muestra suficiente');
            } else {
                $prueba
                    ->assertSeeHtml('style="display: none"')
                    ->assertSee('Aún sin muestra suficiente');
            }
        }
    }

    /**
     * `InformeDelObservatorio::series()` lee el `que` de cada serie desde el
     * `que()` estático del widget correspondiente en vez de repetir la frase
     * a mano: antes vivía escrita dos veces (aquí y en el widget flaco) sin
     * ninguna prueba que las atara.
     *
     * Lo que esta prueba demuestra es DIVERGENCIA: que las dos frases no se
     * separen. No detecta que alguien vuelva a escribir a mano en el informe
     * la misma cadena que ya devuelve el widget —eso pasa en verde, se
     * comprobó— y no puede detectarlo comparando valores. La duplicación
     * idéntica es un problema de lectura del código; la divergencia es el que
     * llega al lector del informe con dos frases distintas para el mismo
     * dato, y es el que se vigila aquí.
     */
    public function test_el_que_del_informe_es_el_mismo_que_el_de_su_widget(): void
    {
        $mapa = [
            'presencia-por-municipio' => PresenciaPorMunicipio::class,
            'composicion-del-sector' => ComposicionDelSector::class,
            'salud-financiera' => SaludFinanciera::class,
            'cobertura-de-proveedores' => CoberturaDeProveedores::class,
            'demanda-laboral-por-area' => DemandaLaboralPorArea::class,
            'oferta-contra-demanda' => OfertaContraDemanda::class,
        ];

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $informe = Livewire::test(InformeDelObservatorio::class)->instance();

        foreach ($informe->series() as $item) {
            $claseWidget = $mapa[$item['clave']] ?? null;
            $this->assertNotNull($claseWidget, "Clave sin mapear en la prueba: {$item['clave']}");

            $this->assertSame(
                $claseWidget::que(),
                $item['que'],
                "El 'que' de \"{$item['clave']}\" en el informe debería venir de {$claseWidget}::que()."
            );
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
     *
     * Empuja los DOS lados —demanda (vacantes) y oferta (aspirantes)— por
     * encima del umbral, no solo uno: desde el arreglo de
     * `SerieDelObservatorio::hayMuestraSuficiente()` (ver su docblock),
     * empujar un solo conjunto ya no basta para que la serie completa se
     * declare con muestra suficiente. Es justo el fallo que ese arreglo
     * cierra, así que esta prueba tiene que demostrar el caso real: los dos
     * conjuntos con muestra propia, no uno prestándole la suya al otro.
     */
    public function test_la_misma_visualizacion_dibuja_en_cuanto_hay_muestra(): void
    {
        // `publicado()` es un estado real de ambas factories, verificado.
        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->count(35)->publicado()->for($asociado)->create([
            'categoria_cargo' => CargoDelSector::Barra,
        ]);
        Aspirante::factory()->count(35)->create(['categoria_cargo' => CargoDelSector::Barra]);

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
     * `ofertaContraDemanda()` por encima del umbral con vacantes Y
     * aspirantes reales de una sola categoría —los dos lados, no solo
     * uno: desde el arreglo de `SerieDelObservatorio::hayMuestraSuficiente()`
     * empujar un solo conjunto ya no basta— mismo mecanismo que
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

        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->count(35)->publicado()->for($asociado)->create([
            'categoria_cargo' => CargoDelSector::Barra,
        ]);
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

    /**
     * Centinela del ancla de vendor.
     *
     * Varias pruebas deciden «dibuja / no dibuja» mirando si aparece
     * `style="display: none"`, que lo emite `chart-widget.blade.php` de
     * Filament, no este proyecto. Si una versión futura pasara a una clase,
     * los `assertDontSeeHtml` se volverían pases permanentes —verdes para
     * siempre, sin vigilar nada— y nadie se enteraría.
     *
     * Esto no arregla la fragilidad: la hace ruidosa. Si esta prueba se cae
     * tras actualizar Filament, hay que cambiar el ancla en las pruebas que
     * la usan, no borrar ésta.
     */
    public function test_el_ancla_de_vendor_que_distingue_dibujar_de_no_dibujar_sigue_existiendo(): void
    {
        $plantilla = base_path('vendor/filament/widgets/resources/views/chart-widget.blade.php');

        $this->assertFileExists($plantilla, 'Filament movió la plantilla del ChartWidget: las pruebas que miran su HTML necesitan otra ancla.');

        $this->assertStringContainsString(
            'style="display: none"',
            File::get($plantilla),
            'Filament dejó de emitir `style="display: none"` para ocultar el lienzo. Los `assertDontSeeHtml` que dependen de esa cadena ya no vigilan nada: hay que actualizarlos.'
        );
    }

    /**
     * El estado vacío se contradecía en pantalla: «hoy hay n = 762 registros
     * y hacen falta al menos 30». Quien lo lee tiene razón en desconfiar —762
     * es mayor que 30— y este módulo existe justo para aguantar esa pregunta.
     * El total no era lo que fallaba; fallaba una de las tres señales.
     */
    public function test_el_estado_vacio_nombra_la_senal_que_no_llega_y_no_el_total(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $serie = app(MetricasDelObservatorio::class)->presenciaPorMunicipio();

        // Precondición de la prueba: es la serie que cruza tres señales y hoy
        // no alcanza. Si algún día alcanzara, esto avisa en vez de dejar la
        // prueba afirmando sobre un estado vacío que ya no se dibuja.
        $this->assertFalse(
            $serie->hayMuestraSuficiente(),
            'Presencia por municipio ya alcanza muestra: esta prueba necesita una serie que NO alcance para poder mirar su estado vacío.'
        );

        Livewire::test(PresenciaPorMunicipio::class)
            ->assertSee($serie->rotuloDeLaMuestraQueDecide())
            ->assertSee('hacen falta al menos '.SerieDelObservatorio::MUESTRA_MINIMA);
    }

    /**
     * Una barra tiene que verse sobre la superficie donde se dibuja, y este
     * panel es bicromático: son DOS superficies, no una.
     *
     * La versión anterior de esta prueba prohibía una sola cadena —el Ambient
     * White con el que «Otros» quedaba invisible en claro— y por eso no
     * vigilaba nada más: repintar «Otros» de blanco puro, que es peor que el
     * bug original, la dejaba en verde. Mientras tanto Wine, Pub Grey y Pub
     * Black seguían por debajo de 3:1 sobre el fondo oscuro, y Pub Black era
     * literalmente el color de ese fondo.
     *
     * Mide contraste WCAG de verdad, sobre las SIETE ranuras y contra la
     * superficie de cada tema, leyendo `tokens.css` en vez de copiar la
     * paleta aquí: si la paleta se mueve, la prueba se mueve con ella.
     */
    public function test_los_colores_de_las_series_se_ven_en_su_tema(): void
    {
        $invisibles = [];

        foreach ($this->paletaDeSeriesPorTema() as $tema => $paleta) {
            foreach ($paleta['colores'] as $ranura => $color) {
                $contraste = $this->contraste($color, $paleta['superficie']);

                if ($contraste < self::CONTRASTE_MINIMO) {
                    $invisibles[] = sprintf(
                        '--asb-serie-%d (%s) da %.2f:1 sobre la superficie del tema %s (%s).',
                        $ranura,
                        $color,
                        $contraste,
                        $tema,
                        $paleta['superficie'],
                    );
                }
            }
        }

        // Se acumulan todas antes de fallar: quien rompa la paleta merece ver
        // de una vez cada serie que se pierde, no descubrirlas de una en una.
        $this->assertSame([], $invisibles, sprintf(
            "Hay series que no se distinguen de su fondo (mínimo %s:1):\n%s",
            self::CONTRASTE_MINIMO,
            implode("\n", $invisibles),
        ));
    }

    /**
     * Ninguna gráfica de varias series puede cablear sus colores a mano, y
     * cada una declara ranuras distintas entre sí.
     *
     * Recorre TODAS las gráficas del observatorio, no una: `PresenciaPorMunicipio`
     * y `DemandaLaboralPorArea` cablearon cada una por su lado la misma
     * paleta pensada para fondo claro, y arreglar solo la segunda habría
     * dejado a Wine invisible en la primera. Una gráfica de una sola serie no
     * entra: su relleno es el acento de marca, que sí funciona en los dos
     * temas.
     */
    public function test_ninguna_grafica_de_varias_series_cablea_sus_colores(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $cableadas = [];
        $repetidas = [];

        foreach ($this->conjuntosDeCadaGrafica() as $grafica => $conjuntos) {
            if (count($conjuntos) <= 1) {
                continue;
            }

            $ranuras = collect($conjuntos)->pluck('asobaresSerie', 'label');

            foreach ($ranuras->filter(fn (mixed $ranura): bool => $ranura === null)->keys() as $sinRanura) {
                $cableadas[] = "{$grafica} → «{$sinRanura}»";
            }

            foreach ($ranuras->duplicates() as $duplicada) {
                $repetidas[] = "{$grafica} → ranura {$duplicada}";
            }
        }

        $this->assertSame([], $cableadas, "Hay series sin ranura declarada: `panel-graficas.js` no las repinta y se quedan con el color del tema claro sobre fondo oscuro.\n".implode("\n", $cableadas));
        $this->assertSame([], $repetidas, "Hay series que comparten ranura y se dibujarían del mismo color:\n".implode("\n", $repetidas));
    }

    /**
     * El color de reserva que manda el servidor y el token del tema claro son
     * el mismo valor escrito en dos sitios, y esa es justo la forma en la que
     * este módulo ya se equivocó una vez con la frase de `que()`. Los ata.
     */
    public function test_el_color_de_reserva_no_diverge_del_token_del_tema_claro(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $claro = $this->paletaDeSeriesPorTema()['claro']['colores'];

        foreach ($this->conjuntosDeCadaGrafica() as $grafica => $conjuntos) {
            foreach ($conjuntos as $conjunto) {
                if (! isset($conjunto['asobaresSerie'])) {
                    continue;
                }

                $this->assertArrayHasKey(
                    $conjunto['asobaresSerie'],
                    $claro,
                    "{$grafica} pide la ranura {$conjunto['asobaresSerie']}, que no existe en tokens.css."
                );

                $this->assertSame(
                    $claro[$conjunto['asobaresSerie']],
                    strtolower($conjunto['backgroundColor']),
                    sprintf(
                        'La reserva de «%s» en %s se separó de --asb-serie-%d: hasta que el plugin de tema pinte, la barra saldría de un color que ya no está en la paleta.',
                        $conjunto['label'],
                        $grafica,
                        $conjunto['asobaresSerie'],
                    )
                );
            }
        }
    }

    /**
     * Los conjuntos de datos de cada gráfica del observatorio, por nombre
     * corto de clase. Sale de `getFooterWidgets()` y no de una lista escrita
     * a mano: una gráfica nueva entra sola en estas pruebas.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function conjuntosDeCadaGrafica(): array
    {
        $pagina = new Observatorio;
        $widgets = (new \ReflectionMethod(Observatorio::class, 'getFooterWidgets'))->invoke($pagina);

        $this->assertNotEmpty($widgets, 'Sin widgets no hay nada que vigilar y estas pruebas pasarían en vacío.');

        $conjuntos = [];
        foreach ($widgets as $widget) {
            $instancia = Livewire::test($widget)->instance();
            $datos = (new \ReflectionMethod($widget, 'getData'))->invoke($instancia);

            $conjuntos[class_basename($widget)] = $datos['datasets'] ?? [];
        }

        return $conjuntos;
    }

    /**
     * La paleta efectiva de cada tema con la superficie sobre la que se
     * dibuja, leídas de `tokens.css`: `:root` para el claro, `.dark` para el
     * oscuro. Una ranura que `.dark` no redefine hereda la de `:root`, que es
     * exactamente lo que hace el navegador en cascada.
     *
     * @return array<string, array{superficie: string, colores: array<int, string>}>
     */
    private function paletaDeSeriesPorTema(): array
    {
        [$raiz, $oscuro] = explode('.dark {', File::get(resource_path('css/tokens.css')), 2);

        $coloresClaros = $this->coloresDeSerieEn($raiz);

        $this->assertCount(
            count(CargoDelSector::cases()),
            $coloresClaros,
            'tokens.css debe declarar una ranura `--asb-serie-N` por área en `:root`; si falta alguna, esta prueba estaría midiendo menos colores de los que se dibujan.'
        );

        return [
            'claro' => [
                'superficie' => $this->superficieDe($raiz),
                'colores' => $coloresClaros,
            ],
            'oscuro' => [
                'superficie' => $this->superficieDe($oscuro),
                'colores' => $this->coloresDeSerieEn($oscuro) + $coloresClaros,
            ],
        ];
    }

    /** @return array<int, string> */
    private function coloresDeSerieEn(string $bloque): array
    {
        preg_match_all('/--asb-serie-(\d+):\s*(#[0-9a-fA-F]{6});/', $bloque, $coincidencias, PREG_SET_ORDER);

        $colores = [];
        foreach ($coincidencias as [, $ranura, $color]) {
            $colores[(int) $ranura] = strtolower($color);
        }

        return $colores;
    }

    private function superficieDe(string $bloque): string
    {
        $encontrado = preg_match('/--asb-superficie:\s*(#[0-9a-fA-F]{6});/', $bloque, $coincidencias);

        $this->assertSame(1, $encontrado, 'No se pudo leer `--asb-superficie` de tokens.css: la prueba se quedaría sin referencia contra la que medir.');

        return strtolower($coincidencias[1]);
    }

    /** Razón de contraste WCAG 2.1 entre dos colores hexadecimales. */
    private function contraste(string $unColor, string $otroColor): float
    {
        $uno = $this->luminanciaRelativa($unColor);
        $otro = $this->luminanciaRelativa($otroColor);

        return (max($uno, $otro) + 0.05) / (min($uno, $otro) + 0.05);
    }

    /** Luminancia relativa WCAG 2.1, la que entra en la razón de contraste. */
    private function luminanciaRelativa(string $hex): float
    {
        $canales = array_map(
            static function (string $par): float {
                $canal = hexdec($par) / 255;

                return $canal <= 0.03928 ? $canal / 12.92 : (($canal + 0.055) / 1.055) ** 2.4;
            },
            str_split(ltrim($hex, '#'), 2)
        );

        return 0.2126 * $canales[0] + 0.7152 * $canales[1] + 0.0722 * $canales[2];
    }
}

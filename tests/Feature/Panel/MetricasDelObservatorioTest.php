<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\ConsultaGuia;
use App\Models\Municipio;
use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El observatorio existe para llevar cifras a una alcaldía. Una serie que no
 * sabe cuántos datos la sostienen no sirve para eso.
 */
class MetricasDelObservatorioTest extends TestCase
{
    use RefreshDatabase;

    private function serie(int $n): SerieDelObservatorio
    {
        return new SerieDelObservatorio(
            etiquetas: ['Armenia', 'Calarcá'],
            series: ['Asociados' => [$n - 1, 1]],
            n: $n,
            unidad: 'asociados',
        );
    }

    public function test_una_serie_por_debajo_del_umbral_no_tiene_muestra_suficiente(): void
    {
        $this->assertFalse($this->serie(29)->hayMuestraSuficiente());
        $this->assertTrue($this->serie(30)->hayMuestraSuficiente());
    }

    public function test_el_rotulo_de_muestra_dice_cuantos_y_de_que(): void
    {
        $this->assertSame('n = 42 asociados', $this->serie(42)->rotuloDeMuestra());
    }

    public function test_una_serie_sin_datos_se_reconoce_vacia(): void
    {
        $vacia = new SerieDelObservatorio(etiquetas: [], series: [], n: 0, unidad: 'vacantes');

        $this->assertTrue($vacia->estaVacia());
        $this->assertFalse($this->serie(30)->estaVacia());
    }

    /**
     * La relacion entre etiquetas y n es un OR: si cualquiera de los dos falta,
     * la serie esta vacia. Sin esta prueba, un cambio futuro de `||` a `&&`
     * pasaria desapercibido: el observatorio dibujaria un grafico de nada.
     * Este caso asinmetrico fija que etiquetas presentes pero n=0 sigue siendo vacia.
     */
    public function test_con_etiquetas_pero_sin_muestra_esta_vacia(): void
    {
        $sinMuestra = new SerieDelObservatorio(
            etiquetas: ['Armenia', 'Calarca'],
            series: ['Asociados' => [0, 0]],
            n: 0,
            unidad: 'asociados',
        );

        $this->assertTrue($sinMuestra->estaVacia());
    }

    /**
     * La relacion entre etiquetas y n es un OR: si cualquiera de los dos falta,
     * la serie esta vacia. Sin esta prueba, un cambio futuro de `||` a `&&`
     * pasaria desapercibido: el observatorio dibujaria un grafico de nada.
     * Este caso asinmetrico fija que sin etiquetas pero n>0 sigue siendo vacia.
     */
    public function test_sin_etiquetas_pero_con_muestra_esta_vacia(): void
    {
        $sinEtiquetas = new SerieDelObservatorio(
            etiquetas: [],
            series: ['Asociados' => []],
            n: 42,
            unidad: 'asociados',
        );

        $this->assertTrue($sinEtiquetas->estaVacia());
    }

    /**
     * El umbral vive en un solo sitio: si el componente KPI y el observatorio
     * usaran números distintos, la misma cifra sería «muestra pequeña» en una
     * tarjeta y suficiente en la gráfica de al lado.
     *
     * Se prueba por comportamiento y no buscando el número en el archivo:
     * después del paso 4 ese archivo ya no contiene el literal, sino la
     * referencia a la constante. Una aserción sobre el texto fallaría justo
     * después del arreglo que pretende verificar.
     */
    public function test_la_tarjeta_kpi_marca_muestra_pequena_con_el_mismo_umbral(): void
    {
        $limite = SerieDelObservatorio::MUESTRA_MINIMA;

        $justoDebajo = Blade::render(
            '<x-panel.kpi etiqueta="Mora" valor="18 %" :n="$n" />',
            ['n' => $limite - 1]
        );
        $justoEncima = Blade::render(
            '<x-panel.kpi etiqueta="Mora" valor="18 %" :n="$n" />',
            ['n' => $limite]
        );

        $this->assertStringContainsString('muestra pequeña', $justoDebajo);
        $this->assertStringNotContainsString('muestra pequeña', $justoEncima);
    }

    public function test_la_presencia_por_municipio_cuenta_las_tres_senales(): void
    {
        $armenia = Municipio::factory()->create(['nombre' => 'Armenia']);
        $salento = Municipio::factory()->create(['nombre' => 'Salento']);

        Asociado::factory()->count(3)->publicado()->for($armenia)->create();
        Asociado::factory()->publicado()->for($salento)->create();
        ConsultaGuia::factory()->count(5)->for($armenia)->create();

        $serie = app(MetricasDelObservatorio::class)->presenciaPorMunicipio();

        $indiceArmenia = array_search('Armenia', $serie->etiquetas, true);
        $this->assertNotFalse($indiceArmenia);
        $this->assertSame(3, $serie->series['Asociados'][$indiceArmenia]);
        $this->assertSame(5, $serie->series['Consultas de la guía'][$indiceArmenia]);
    }

    /** Un asociado en borrador no es presencia del gremio. */
    public function test_la_presencia_ignora_lo_no_publicado(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Filandia']);
        Asociado::factory()->publicado()->for($municipio)->create();
        Asociado::factory()->for($municipio)->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);

        $serie = app(MetricasDelObservatorio::class)->presenciaPorMunicipio();
        $indice = array_search('Filandia', $serie->etiquetas, true);

        $this->assertSame(1, $serie->series['Asociados'][$indice]);
    }

    public function test_las_seis_metricas_agregan_en_una_sola_consulta_por_serie(): void
    {
        $this->seed(DatabaseSeeder::class);

        $metricas = app(MetricasDelObservatorio::class);

        /*
         * El listener se registra UNA vez, fuera del bucle. `DB::listen` no se
         * retira nunca, y `foreach` no crea scope: registrar uno por iteración
         * deja k listeners incrementando la misma variable por referencia, así
         * que la medida sería k × consultas reales y desde la quinta vuelta el
         * test sería insatisfacible para cualquier implementación.
         */
        $consultas = 0;
        DB::listen(function () use (&$consultas): void {
            $consultas++;
        });

        foreach (['presenciaPorMunicipio', 'composicionDelSector', 'saludFinanciera',
            'coberturaDeProveedores', 'demandaLaboralPorArea', 'ofertaContraDemanda'] as $metodo) {
            $consultas = 0;

            app(MetricasDelObservatorio::class)->{$metodo}();

            $this->assertLessThanOrEqual(
                4,
                $consultas,
                "{$metodo} dispara {$consultas} consultas: se está agregando en memoria."
            );
        }

        $this->assertNotNull($metricas);
    }

    public function test_las_metricas_se_memoizan_dentro_de_la_misma_instancia(): void
    {
        $this->seed(DatabaseSeeder::class);

        $metricas = app(MetricasDelObservatorio::class);
        $metricas->saludFinanciera();

        $consultas = 0;
        DB::listen(function () use (&$consultas): void {
            $consultas++;
        });

        $metricas->saludFinanciera();

        $this->assertSame(0, $consultas, 'La segunda llamada debe usar la memoizacion.');
    }

    /**
     * Con la semilla de hoy las dos métricas de empleo no llegan al umbral, y
     * eso es lo que la interfaz tiene que poder decir. Si algún día la semilla
     * crece, esta prueba cambia de sentido: afirma la regla, no el número.
     */
    public function test_las_metricas_declaran_si_tienen_muestra_suficiente(): void
    {
        $this->seed(DatabaseSeeder::class);

        $metricas = app(MetricasDelObservatorio::class);

        $this->assertTrue(
            $metricas->presenciaPorMunicipio()->hayMuestraSuficiente(),
            'Las consultas de la guia sí tienen volumen.'
        );
        $this->assertSame(
            $metricas->ofertaContraDemanda()->n < SerieDelObservatorio::MUESTRA_MINIMA,
            ! $metricas->ofertaContraDemanda()->hayMuestraSuficiente()
        );
    }
}

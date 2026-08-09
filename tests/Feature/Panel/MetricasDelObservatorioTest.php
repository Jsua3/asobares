<?php

namespace Tests\Feature\Panel;

use App\Panel\SerieDelObservatorio;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * El observatorio existe para llevar cifras a una alcaldía. Una serie que no
 * sabe cuántos datos la sostienen no sirve para eso.
 */
class MetricasDelObservatorioTest extends TestCase
{
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
}

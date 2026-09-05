<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La barra pública de escritorio en tres estados (spec:
 * docs/ingenieria/navbar-tres-estados-diseno.md). Cada prueba nombra en su
 * docblock la rotura que la pone roja; se hizo antes de darla por buena.
 */
class NavbarTresEstadosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rotura: borrar `--asb-caida-modulo: 0px` del bloque de movimiento reducido.
     */
    public function test_el_rebote_es_un_token_que_el_movimiento_reducido_anula(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('--duracion-rebote: 520ms', $tokens);
        $this->assertStringContainsString('--asb-separacion-modulos: 0.75rem', $tokens);
        $this->assertStringContainsString('--asb-caida-modulo: 6px', $tokens);
        $this->assertStringContainsString('--asb-escala-popover: 0.92', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-popover: -6px', $tokens);
        $this->assertStringContainsString('--asb-escala-isotipo: 0.9', $tokens);

        $reducido = strstr($tokens, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($reducido, 'tokens.css ya no tiene el bloque de movimiento reducido');

        $this->assertStringContainsString('--asb-caida-modulo: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-popover: 1', $reducido);
        $this->assertStringContainsString('--asb-desplazamiento-popover: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-isotipo: 1', $reducido);
        $this->assertStringContainsString('--ease-rebote-suave: var(--ease-cajon)', $reducido);
        $this->assertStringContainsString('--ease-rebote-vivo: var(--ease-cajon)', $reducido);

        // La separación es layout, no movimiento: sobrevive.
        $this->assertStringNotContainsString('--asb-separacion-modulos: 0', $reducido);
    }

    /**
     * El respaldo para navegadores sin linear() no puede ser una segunda
     * declaración: var() es inválido en tiempo de cómputo y la propiedad
     * caería a `ease`, no a la declaración anterior. Por eso el token nace
     * como cubic-bezier y solo dentro de @supports pasa a linear().
     *
     * Rotura: borrar la declaración con cubic-bezier de fuera del @supports.
     */
    public function test_el_rebote_lleva_respaldo_por_supports(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $soporte = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))');
        $this->assertNotFalse($soporte, 'falta el bloque @supports de linear()');

        $antes = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))', true);

        foreach (['--ease-rebote-suave', '--ease-rebote-vivo'] as $curva) {
            $this->assertStringContainsString("{$curva}: cubic-bezier(0.32, 0.72, 0, 1)", $antes, "{$curva} sin respaldo cubic-bezier");
            $this->assertStringContainsString("{$curva}: linear(0, ", $soporte, "{$curva} sin linear() dentro del @supports");
        }
    }

    /**
     * Rotura: en `leer()` volver a `['light', 'dark'].includes(guardado)`.
     */
    public function test_el_store_de_tema_acepta_sistema_y_distingue_lo_resuelto(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("resuelto: 'light'", $js);
        $this->assertStringContainsString("['light', 'dark', 'system'].includes(guardado)", $js);
        $this->assertStringContainsString('this.resuelto = this.resolver(', $js);
        $this->assertStringContainsString("matchMedia('(prefers-color-scheme: dark)').addEventListener('change'", $js);
    }

    /**
     * Rotura: cambiar `data-pais="co"` por `data-pais="es"`.
     */
    public function test_las_banderas_son_colombia_y_estados_unidos(): void
    {
        $bandera = File::get(resource_path('views/components/publico/bandera.blade.php'));

        $this->assertStringContainsString('data-pais="co"', $bandera);
        $this->assertStringContainsString('data-pais="us"', $bandera);
        $this->assertStringNotContainsString('data-pais="es"', $bandera);
        $this->assertStringNotContainsString('data-pais="gb"', $bandera);

        $colombia = Blade::render('<x-publico.bandera pais="co" />');
        $this->assertStringContainsString('<svg', $colombia);
        $this->assertStringContainsString('aria-hidden="true"', $colombia);
        $this->assertStringContainsString('#FCD116', $colombia);
    }

    /**
     * Rotura: quitar el atributo `media` de la precarga del isotipo.
     */
    public function test_el_isotipo_existe_se_pinta_doble_y_se_precarga_solo_en_escritorio(): void
    {
        $this->assertFileExists(public_path('img/monograma-asobares.png'));

        $doble = Blade::render('<x-publico.logo doble alto="h-8" />');
        $this->assertStringContainsString('logo-doble__completo', $doble);
        $this->assertStringContainsString('logo-doble__isotipo', $doble);
        $this->assertStringContainsString('img/logo-asobares.png', $doble);
        $this->assertStringContainsString('img/monograma-asobares.png', $doble);
        $this->assertSame(1, substr_count($doble, 'alt="ASOBARES Capítulo Quindío"'), 'la marca se anuncia una sola vez');
        $this->assertStringContainsString('alt=""', $doble);
        $this->assertStringContainsString('width="156" height="108"', $doble);

        $simple = Blade::render('<x-publico.logo alto="h-8" />');
        $this->assertStringNotContainsString('logo-doble', $simple, 'sin `doble` el componente rinde lo de siempre');

        $this->get('/contacto')
            ->assertOk()
            ->assertSee('rel="preload" as="image" href="http://localhost:8000/img/monograma-asobares.png" media="(min-width: 64rem)"', false);
    }

    /**
     * Rotura: poner `computer-desktop` en el botón, o quitar la fila Sistema.
     */
    public function test_el_control_de_tema_muestra_sol_o_luna_y_ofrece_sistema_en_el_popover(): void
    {
        $html = Blade::render('<x-publico.control-tema />');

        [$boton, $popover] = explode('id="popover-tema"', $html, 2);

        $this->assertStringContainsString('aria-label="Apariencia del sitio"', $boton);
        $this->assertStringContainsString('aria-controls="popover-tema"', $boton);
        $this->assertStringContainsString("x-show=\"\$store.tema.resuelto === 'light'\"", $boton);
        $this->assertStringContainsString("x-show=\"\$store.tema.resuelto === 'dark'\"", $boton);
        $this->assertStringNotContainsString('computer-desktop', $boton, 'el botón nunca muestra el monitor');
        $this->assertStringNotContainsString('M9 17.25v1.007', $boton, 'ni el path del monitor');

        $this->assertStringContainsString('>Claro<', $popover);
        $this->assertStringContainsString('>Oscuro<', $popover);
        $this->assertStringContainsString('>Sistema<', $popover);
        $this->assertStringContainsString("\$store.tema.elegir('light')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('dark')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('system')", $popover);
        $this->assertSame(3, substr_count($popover, 'x-bind:aria-pressed='), 'las tres filas marcan la activa');

        // Lo que las guardias globales exigen a todo desplegable de la barra.
        $this->assertStringContainsString('transicion-desplegable', $html);
        $this->assertStringContainsString('fila-pulsable', $html);
        $this->assertStringContainsString('ease-rebote-vivo duration-(--duracion-rebote)', $html);
        $this->assertStringContainsString('scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)', $html);
    }
}

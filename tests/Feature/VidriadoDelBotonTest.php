<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * Al pulsar, el botón de marca se vidria: el relleno se retira y deja ver lo
 * que hay detrás, desenfocado y con la saturación subida.
 *
 * Lo pidió la dirección al ver la maqueta de movimiento --«que al presionarlo
 * le de efecto transparente»-- y sustituye al brillo especular que se había
 * propuesto primero.
 *
 * Aquí se vigilan las tres cosas que se pagan caras:
 *
 * 1. **El contraste del rótulo sobre el cristal**, que se vuelve a MEDIR, y
 *    que es lo que fija el tinte de cada tema. Son dos recetas opuestas: en
 *    oscuro el cristal es el rojo de marca adelgazado, porque la página de
 *    detrás es negra y el blanco mejora; en claro ningún alfa del rojo
 *    sostiene texto blanco, así que el tinte oscurece hasta marca-950.
 * 2. **Que el relleno no vuelva a una utilidad `bg-*`**, que pisaría al
 *    portador y dejaría el vidriado sin efecto.
 * 3. **Que no se use `color-mix()` con un `calc()` dentro**, que es como se
 *    escribió la primera versión: compilaba, y Lightning CSS la plegaba en el
 *    archivo servido dejando el rótulo con la tinta del estado pulsado
 *    siempre. El fuente estaba bien y el sitio, mal.
 */
class VidriadoDelBotonTest extends TestCase
{
    use MideContraste;

    private const float MINIMO_AA = 4.5;

    private const string BLANCO = '#ffffff';

    private function app(): string
    {
        return File::get(resource_path('css/app.css'));
    }

    private function tokens(): string
    {
        return File::get(resource_path('css/tokens.css'));
    }

    // ── Lo que de verdad importa: que el rótulo se lea ────────────────────

    /**
     * En tema oscuro el cristal es el rojo de marca al 42 % sobre una página
     * negra, así que el rótulo blanco no solo aguanta: mejora respecto al
     * botón sólido (4,83:1).
     */
    public function test_el_rotulo_se_lee_sobre_el_cristal_del_tema_oscuro(): void
    {
        [$color, $alfa] = $this->cristal('.dark');

        foreach (['#0b090a' => 'la página', '#121011' => 'una superficie'] as $fondo => $donde) {
            $compuesto = $this->componer($color, $alfa, $fondo);
            $razon = $this->contraste($compuesto, self::BLANCO);

            $this->assertGreaterThanOrEqual(
                self::MINIMO_AA,
                $razon,
                sprintf('El rótulo blanco sobre el cristal da %.2f:1 contra %s del tema oscuro.', $razon, $donde)
            );
        }
    }

    /**
     * En claro es al revés y por eso hay dos recetas: el tinte OSCURECE. Con
     * el rojo de marca adelgazado, el rótulo blanco cae a 1,97:1 y no hay alfa
     * que lo salve, porque ni el rojo sólido llega holgado.
     */
    public function test_el_rotulo_se_lee_sobre_el_cristal_del_tema_claro(): void
    {
        [$color, $alfa] = $this->cristal(':root');

        foreach (['#ffffff' => 'una tarjeta', '#f5f3f4' => 'la página'] as $fondo => $donde) {
            $compuesto = $this->componer($color, $alfa, $fondo);
            $razon = $this->contraste($compuesto, self::BLANCO);

            $this->assertGreaterThanOrEqual(
                self::MINIMO_AA,
                $razon,
                sprintf('El rótulo blanco sobre el cristal da %.2f:1 contra %s del tema claro.', $razon, $donde)
            );
        }
    }

    /**
     * Y que siga siendo CRISTAL. Subir el alfa hasta casi opaco arreglaría el
     * contraste y mataría el efecto: por encima del 85 % ya no se ve nada
     * detrás y esto vuelve a ser un botón que se oscurece al pulsarlo.
     */
    public function test_el_cristal_sigue_dejando_pasar_la_pagina(): void
    {
        foreach ([':root', '.dark'] as $tema) {
            [, $alfa] = $this->cristal($tema);

            $this->assertLessThanOrEqual(
                0.85,
                $alfa,
                "El cristal de `{$tema}` está al {$alfa} y ya no deja ver lo de detrás: deja de ser cristal."
            );
        }
    }

    // ── Las dos trampas ───────────────────────────────────────────────────

    /**
     * El relleno vive en el portador. Una utilidad `bg-*` de `@layer
     * utilities` gana siempre a `@layer components` en Tailwind 4 y dejaría el
     * botón con un fondo opaco fijo: el vidriado no se vería nunca.
     */
    public function test_el_relleno_no_vuelve_a_una_utilidad(): void
    {
        $primaria = Blade::render('<x-publico.boton>Enviar</x-publico.boton>');

        $this->assertStringContainsString('cta-vivo', $primaria);
        $this->assertDoesNotMatchRegularExpression(
            '/class="[^"]*\bbg-(accion|marca-\d00)\b/',
            $primaria,
            'El relleno volvió al marcado como utilidad y pisará al portador.'
        );

        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));
        $this->assertDoesNotMatchRegularExpression(
            '/cta-vivo[^"]*\bbg-accion\b/',
            $navbar,
            '«Afíliate» volvió a llevar el relleno como utilidad.'
        );

        // Y el portador sí lo declara, sobre la capa que se retira.
        $this->assertStringContainsString('background-color: var(--asb-accion);', $this->reglaDelCristal());
    }

    /**
     * La trampa que costó una compilación entera: `color-mix()` con un
     * `calc()` de porcentaje se pliega al minificar y el archivo servido queda
     * con el primer color a secas. Se prohíbe en todo el bloque del botón.
     */
    public function test_el_vidriado_no_usa_color_mix_con_calc(): void
    {
        $bloque = $this->bloqueDelBoton();

        $this->assertStringNotContainsString(
            'color-mix(',
            $bloque,
            'Lightning CSS pliega `color-mix()` con `calc()` dentro: el CSS servido no dirá lo que dice el fuente.'
        );

        // Ni la propiedad registrada que la acompañaba. Se prohíbe el USO
        // --la declaración y la lectura-- y no la palabra: el comentario del
        // portador cuenta por qué se descartó, y esa explicación tiene que
        // poder quedarse escrita.
        $this->assertStringNotContainsString('var(--vidriado)', $bloque);
        $this->assertStringNotContainsString('@property --vidriado {', $this->app());
    }

    // ── El resto del contrato ─────────────────────────────────────────────

    /**
     * La ida más rápida que la vuelta. Es lo que separa un líquido de un
     * parpadeo: con las dos duraciones iguales el botón destella.
     */
    public function test_el_cristal_se_retira_mas_rapido_de_lo_que_vuelve(): void
    {
        $tokens = $this->tokens();

        preg_match('/--duracion-vidriado-ida:\s*(\d+)ms/', $tokens, $ida);
        preg_match('/--duracion-vidriado-vuelta:\s*(\d+)ms/', $tokens, $vuelta);

        $this->assertNotEmpty($ida, 'Falta `--duracion-vidriado-ida`.');
        $this->assertNotEmpty($vuelta, 'Falta `--duracion-vidriado-vuelta`.');

        $this->assertLessThan(
            (int) $vuelta[1],
            (int) $ida[1],
            'El color tiene que huir más deprisa de lo que vuelve, o el botón parpadea en vez de fluir.'
        );
    }

    /**
     * La capa del relleno va DETRÁS del rótulo y dentro del botón. Sin
     * `isolation: isolate`, el `z-index: -1` la manda al contexto de
     * apilamiento de la página y la capa desaparece bajo el fondo del sitio.
     */
    public function test_la_capa_del_relleno_queda_dentro_del_boton(): void
    {
        $portador = $this->reglaDe('.cta-vivo');
        $capa = $this->reglaDelCristal();

        $this->assertStringContainsString('isolation: isolate;', $portador);
        $this->assertStringContainsString('z-index: -1;', $capa);
    }

    /**
     * Quien pide menos transparencia se queda sin cristal, y con DOS tokens:
     * el tinte pasa a ser el mismo relleno --así no se retira nada-- y el
     * desenfoque muere. Ni una regla del botón se toca.
     */
    public function test_la_transparencia_reducida_apaga_el_cristal(): void
    {
        $bloque = $this->bloqueDe($this->tokens(), '@media (prefers-reduced-transparency: reduce)');

        $this->assertStringContainsString('--asb-vidrio-boton: var(--asb-accion);', $bloque);
        $this->assertStringContainsString('--asb-desenfoque-vidriado: 0px;', $bloque);
    }

    // ── Utilidades ────────────────────────────────────────────────────────

    /**
     * El color y el alfa del cristal de un tema, leídos de `tokens.css`.
     *
     * @return array{0: string, 1: float}
     */
    private function cristal(string $tema): array
    {
        $bloque = $this->bloqueDelTema($tema);

        $encontrado = preg_match(
            '/--asb-vidrio-boton:\s*rgb\(\s*(\d+)\s+(\d+)\s+(\d+)\s*\/\s*([\d.]+)\s*\)/',
            $bloque,
            $partes
        );

        $this->assertSame(1, $encontrado, "No se pudo leer `--asb-vidrio-boton` de `{$tema}`.");

        $hex = sprintf('#%02x%02x%02x', (int) $partes[1], (int) $partes[2], (int) $partes[3]);

        return [$hex, (float) $partes[4]];
    }

    private function bloqueDelTema(string $tema): string
    {
        $css = $this->tokens();
        [$claro, $oscuro] = explode('.dark {', $css, 2);

        return $tema === ':root' ? $claro : explode('@media', $oscuro, 2)[0];
    }

    /** El bloque entero de reglas del botón, para las prohibiciones. */
    private function bloqueDelBoton(): string
    {
        $css = $this->app();
        $inicio = strpos($css, '.cta-vivo {');
        $this->assertNotFalse($inicio, '`.cta-vivo` ya no existe en app.css.');

        $fin = strpos($css, '.video-marquesina', $inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }

    private function reglaDelCristal(): string
    {
        return $this->reglaDe('.cta-vivo::before');
    }

    private function reglaDe(string $selector): string
    {
        $css = $this->app();
        $inicio = strpos($css, $selector.' {');

        $this->assertNotFalse($inicio, "No existe la regla `{$selector}`.");

        $fin = strpos($css, '}', $inicio);

        return substr($css, $inicio, $fin - $inicio);
    }

    private function bloqueDe(string $css, string $marca): string
    {
        $inicio = strpos($css, $marca);
        $this->assertNotFalse($inicio, "No existe el bloque `{$marca}`.");

        $profundidad = 0;
        $desde = strpos($css, '{', $inicio);

        for ($i = $desde; $i < strlen($css); $i++) {
            $profundidad += (int) ($css[$i] === '{') - (int) ($css[$i] === '}');

            if ($profundidad === 0) {
                return substr($css, $desde, $i - $desde + 1);
            }
        }

        $this->fail("No se pudo delimitar `{$marca}`.");
    }
}

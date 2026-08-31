<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El velo del hero con imagen de fondo (OBS3-02).
 *
 * El directivo pidió fondo con vida --«el banner que va moviéndose o el video,
 * algo que le genere vida», R21 05:22-- y en la misma frase puso el límite:
 * «no sea que afecte la visibilidad de las letras» (R21 05:35). El §27.8 lo
 * convierte en prohibición: no se mete imagen de fondo en el hero sin
 * comprobar el contraste en los dos temas.
 *
 * Comprobarlo una vez no sirve, porque la imagen la elige otra persona más
 * tarde y puede ser cualquiera. Por eso lo que se prueba aquí no es «esta foto
 * contrasta», sino que el velo garantiza el contraste contra la imagen más
 * hostil que exista: blanco puro bajo el tema oscuro, negro puro bajo el
 * claro. Si alguien baja la opacidad del velo, esto se pone rojo.
 */
class VeloDelHeroTest extends TestCase
{
    /** Lo que exige WCAG 2.1 AA para texto normal. */
    private const float AA_TEXTO_NORMAL = 4.5;

    /**
     * La imagen más hostil para cada tema: la que más se acerca al color del
     * texto. En oscuro el texto es claro, así que lo peor es una foto blanca;
     * en claro es al revés.
     *
     * @var array<string, string>
     */
    private const array PEOR_IMAGEN = [
        'claro' => '#000000',
        'oscuro' => '#ffffff',
    ];

    /**
     * La prueba que de verdad protege. Recalcula el contraste desde el CSS
     * real --no desde constantes copiadas aquí-- así que también cae si
     * alguien cambia un token de color y no se acuerda del hero.
     */
    public function test_el_velo_garantiza_contraste_aa_con_la_imagen_mas_hostil(): void
    {
        $css = File::get(resource_path('css/tokens.css'));

        $claro = $this->bloque($css, ':root');
        $oscuro = $this->bloque($css, '.dark');

        // `--asb-velo-hero` se declara una sola vez, en `:root`. El tema
        // oscuro lo hereda: no se duplica porque el COLOR del velo ya cambia
        // solo, al ser `--asb-fondo`. Si algún día se declara aparte, esta
        // línea lo recoge sin tocar la prueba.
        $alfaBase = (float) $this->declaracion($claro, '--asb-velo-hero');
        $this->assertGreaterThan(0, $alfaBase, 'No se encontró `--asb-velo-hero` en `:root`.');

        $temas = [
            'claro' => [$claro, $alfaBase],
            'oscuro' => [$oscuro, (float) $this->declaracion($oscuro, '--asb-velo-hero', (string) $alfaBase)],
        ];

        foreach ($temas as $tema => [$bloque, $alfa]) {
            $velo = $this->declaracion($bloque, '--asb-fondo');

            // `--asb-suave` es el texto más flojo que pinta el hero: es el
            // color del subtítulo. Si él pasa, el titular pasa de sobra.
            $texto = $this->declaracion($bloque, '--asb-suave');

            $fondoCompuesto = $this->componer($velo, $alfa, self::PEOR_IMAGEN[$tema]);
            $contraste = $this->contraste($texto, $fondoCompuesto);

            $this->assertGreaterThanOrEqual(
                self::AA_TEXTO_NORMAL,
                $contraste,
                sprintf(
                    'Tema %s: con el velo al %.2f, el texto %s sobre la imagen %s queda en %.2f:1, '
                    .'por debajo del %.1f:1 que exige AA. Sube `--asb-velo-hero`.',
                    $tema, $alfa, $texto, self::PEOR_IMAGEN[$tema], $contraste, self::AA_TEXTO_NORMAL
                )
            );
        }
    }

    /**
     * Sin velo el peor caso es texto invisible. Esta prueba fija ese hecho
     * para que nadie lo descubra en la demo: si mañana alguien piensa que el
     * velo sobra, aquí está el número que dice que no.
     */
    public function test_sin_velo_el_peor_caso_seria_ilegible(): void
    {
        $css = File::get(resource_path('css/tokens.css'));

        foreach (['claro' => ':root', 'oscuro' => '.dark'] as $tema => $selector) {
            $bloque = $this->bloque($css, $selector);
            $contraste = $this->contraste(
                $this->declaracion($bloque, '--asb-suave'),
                self::PEOR_IMAGEN[$tema]
            );

            $this->assertLessThan(
                self::AA_TEXTO_NORMAL,
                $contraste,
                "Tema {$tema}: si esto deja de ser cierto, la premisa del velo cambió y hay que revisarla."
            );
        }
    }

    /**
     * El velo y la imagen viven en la misma clase a propósito: no se puede
     * pintar el fondo sin arrastrar el velo detrás. Si alguien los separa
     * --por ejemplo moviendo el `::after` a una clase aparte que haya que
     * recordar poner-- vuelve el riesgo que el §27.8 prohíbe.
     */
    public function test_el_velo_es_inseparable_de_la_capa_de_medio(): void
    {
        $css = File::get(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.hero-medio::after\s*\{[^}]*opacity:\s*var\(--asb-velo-hero\)/s',
            $css,
            'El velo del hero debe colgar de `.hero-medio::after` y leer su opacidad del token.'
        );

        $this->assertMatchesRegularExpression(
            '/\.hero-medio::after\s*\{[^}]*background-color:\s*var\(--asb-fondo\)/s',
            $css,
            'El velo se pinta con el fondo del tema, para que se invierta solo entre claro y oscuro.'
        );
    }

    /** Las otras doce páginas que usan el hero no pueden cambiar. */
    public function test_el_hero_sin_medio_rinde_lo_de_siempre(): void
    {
        $html = Blade::render('<x-publico.hero titulo="Un título" subtitulo="Un subtítulo" />');

        $this->assertStringNotContainsString('hero-con-medio', $html);
        $this->assertStringNotContainsString('hero-medio', $html);
        $this->assertStringContainsString('class="resplandor-marca border-b border-linea"', $html);
        $this->assertStringContainsString('Un título', $html);
    }

    /** Con medio: capa, clase de apilado, y decoración oculta al lector. */
    public function test_el_hero_con_medio_pinta_la_capa_y_la_oculta_a_los_lectores(): void
    {
        $html = Blade::render(
            '<x-publico.hero titulo="Un título">'
            .'<x-slot:medio><img src="/img/prueba.webp" alt=""></x-slot:medio>'
            .'</x-publico.hero>'
        );

        $this->assertStringContainsString('hero-con-medio', $html);
        $this->assertStringContainsString('/img/prueba.webp', $html);
        $this->assertMatchesRegularExpression(
            '/<div class="hero-medio" aria-hidden="true">/',
            $html,
            'La capa de fondo es decoración: va con `aria-hidden` para que no la anuncie un lector de pantalla.'
        );
    }

    // --- utilidades ---------------------------------------------------

    /** Cuerpo de un bloque CSS de primer nivel, que aquí no anidan llaves. */
    private function bloque(string $css, string $selector): string
    {
        $encontrado = preg_match(
            '/^'.preg_quote($selector, '/').'\s*\{(.*?)^\}/ms',
            $css,
            $coincidencias
        );

        $this->assertSame(1, $encontrado, "No se encontró el bloque `{$selector}` en el CSS.");

        return $coincidencias[1];
    }

    private function declaracion(string $bloque, string $propiedad, ?string $porDefecto = null): string
    {
        if (preg_match('/'.preg_quote($propiedad, '/').'\s*:\s*([^;]+);/', $bloque, $coincidencias) === 1) {
            return trim(preg_replace('/\s*\/\*.*?\*\//s', '', $coincidencias[1]));
        }

        $this->assertNotNull($porDefecto, "No se encontró `{$propiedad}` y no hay valor heredado que usar.");

        return $porDefecto;
    }

    /** Composición alfa normal, que es como el navegador pinta el velo. */
    private function componer(string $velo, float $alfa, string $imagen): string
    {
        $v = $this->canales($velo);
        $i = $this->canales($imagen);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($alfa * $v[0] + (1 - $alfa) * $i[0]),
            (int) round($alfa * $v[1] + (1 - $alfa) * $i[1]),
            (int) round($alfa * $v[2] + (1 - $alfa) * $i[2])
        );
    }

    private function contraste(string $a, string $b): float
    {
        $luminancias = [$this->luminancia($a), $this->luminancia($b)];
        rsort($luminancias);

        return ($luminancias[0] + 0.05) / ($luminancias[1] + 0.05);
    }

    /** Luminancia relativa de WCAG 2.1. */
    private function luminancia(string $hex): float
    {
        $lineal = array_map(static function (int $canal): float {
            $proporcion = $canal / 255;

            return $proporcion <= 0.03928
                ? $proporcion / 12.92
                : (($proporcion + 0.055) / 1.055) ** 2.4;
        }, $this->canales($hex));

        return 0.2126 * $lineal[0] + 0.7152 * $lineal[1] + 0.0722 * $lineal[2];
    }

    /** @return array{int, int, int} */
    private function canales(string $hex): array
    {
        $limpio = ltrim(trim($hex), '#');

        $this->assertSame(6, strlen($limpio), "El color `{$hex}` no está en formato #rrggbb.");

        return [
            (int) hexdec(substr($limpio, 0, 2)),
            (int) hexdec(substr($limpio, 2, 2)),
            (int) hexdec(substr($limpio, 4, 2)),
        ];
    }
}

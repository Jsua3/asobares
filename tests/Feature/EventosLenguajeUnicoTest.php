<?php

namespace Tests\Feature;

use App\Models\Evento;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ExpressionErrorException;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * Eventos habla el idioma del resto del sitio: conserva el ritmo editorial
 * --bandas, folios, filetes, tipografía-- y pone la superficie y el movimiento
 * de la casa en todo lo que se pulsa.
 *
 * Nada de lo que se vigila aquí falla a la vista:
 *
 *   - Una superficie pulsable con las esquinas rectas o por debajo de la escala.
 *   - Una superficie sin `:active`, que en táctil no responde a nada.
 *   - Algo que se pulsa en una página de Eventos y no acusa el toque, o una
 *     caja que la hoja dibuja sobre un enlace sin que nadie la vigile.
 *   - Un `:hover` fuera de la puerta de puntero fino, que en el teléfono se
 *     queda pegado tras el toque, o escrito como utilidad en la vista.
 *   - Un enlace de texto al que la hoja le pisa el portador `.enlace-accion`.
 *   - Un movimiento con valores a mano, que el movimiento reducido no anula; o
 *     un bloque de movimiento reducido que apaga también el color.
 *   - Un riel que se queda con el gesto vertical y con el pellizco.
 *   - Un relleno rojo con rótulo claro que en oscuro baja del 4,5:1, o un
 *     acento que no sostiene texto sobre su propio tinte.
 *   - Un anillo de foco que la pista del riel recorta.
 *
 * `eventos-editorial.css` va sin capa, así que gana a las utilidades de las
 * vistas y a los portadores de `app.css` sin mirar la especificidad: lo que
 * declara es lo que se ve. Por eso la hoja se lee como árbol --contexto,
 * selector y declaraciones-- y no como cadenas sueltas, que casarían igual con
 * un comentario, con la regla de otro selector o con la misma regla fuera de
 * su media.
 */
class EventosLenguajeUnicoTest extends TestCase
{
    use MideContraste;
    use RefreshDatabase;

    private const string PUERTA = '@media (hover: hover) and (pointer: fine)';

    private const string REDUCIDO = '@media (prefers-reduced-motion: reduce)';

    /** Lo que cuelga de la raíz de las páginas de Eventos, sin la cabecera ni el pie del sitio. */
    private const string DENTRO_DE_EVENTOS = '//*[contains(concat(" ", normalize-space(@class), " "), " eventos-editorial ")]';

    /** RNF-12 para texto normal: los rótulos de Eventos miden de 11 a 14 px. */
    private const float MINIMO_TEXTO = 4.5;

    /** WCAG 2.1 §1.4.11 para el indicador de foco, como en `FocoVisibleTest`. */
    private const float MINIMO_ANILLO = 3.0;

    /** Escala de la casa: celdas y tarjetas 0.75rem; piezas grandes 1rem; píldoras 999px. */
    private const float RADIO_MINIMO_REM = 0.75;

    /**
     * Las superficies pulsables de Eventos y el encogimiento de su `:active`.
     *
     * Una tarjeta encoge un 1,5 % y un control de 44 px un 6 %: el mismo
     * porcentaje sobre una caja pequeña casi no se ve. Los dos salen de tokens
     * que `tokens.css` pone a 1 bajo movimiento reducido.
     *
     * @return array<string, array{string, string}>
     */
    public static function superficies(): array
    {
        return [
            'ficha de evento' => ['.eventos-editorial-ficha__enlace', '--asb-encogimiento-tarjeta'],
            'mandos del riel' => ['.eventos-editorial-riel__mando', '--asb-encogimiento-control'],
            'segmentos del conmutador' => ['.eventos-editorial-conmutador a', '--asb-encogimiento-control'],
            'píldora del calendario' => ['.eventos-editorial-calendario a', '--asb-encogimiento-tarjeta'],
            'evento de la agenda de móvil' => ['.eventos-editorial-agenda__evento', '--asb-encogimiento-tarjeta'],
        ];
    }

    /**
     * Los enlaces de texto de Eventos.
     *
     * No son superficies: no tienen caja que redondear ni que encoger. El
     * fundido de color y la atenuación al pulsar los pone `.enlace-accion`
     * desde el marcado; la hoja pone el hover tras la puerta de puntero fino y
     * el anillo de foco, y nada más.
     *
     * @return array<string, array{string}>
     */
    public static function enlacesDeTexto(): array
    {
        return [
            'vuelta desde la ficha' => ['.eventos-editorial-retorno'],
            'vuelta a los próximos desde el mes vacío' => ['.eventos-editorial-proximos'],
        ];
    }

    // --- A. La superficie ---

    #[DataProvider('superficies')]
    public function test_ninguna_superficie_pulsable_es_recta(string $selector): void
    {
        $radio = $this->declaracionesDe($selector)['border-radius'] ?? null;

        $this->assertNotNull($radio, "{$selector} no declara radio: es una caja pulsable con las esquinas rectas.");

        foreach (preg_split('/\s+/', $radio) as $esquina) {
            $this->assertGreaterThanOrEqual(
                $this->aPixeles(self::RADIO_MINIMO_REM.'rem'),
                $this->aPixeles($esquina),
                "{$selector} redondea {$radio}, por debajo de la escala de la casa (".self::RADIO_MINIMO_REM.'rem).'
            );
        }
    }

    /**
     * Las dos listas de arriba se cierran solas desde la hoja: si aparece otro
     * elemento que responde al puntero o al dedo, tiene que entrar en una de
     * ellas y cumplir lo mismo que los demás.
     */
    public function test_toda_superficie_que_responde_esta_vigilada(): void
    {
        $vigiladas = array_merge(array_column(self::superficies(), 0), array_column(self::enlacesDeTexto(), 0));
        $sueltas = [];

        foreach ($this->reglas($this->hoja()) as $regla) {
            foreach ($regla['selectores'] as $selector) {
                if (preg_match('/^(.+?)(?::not\([^)]*\))*:(?:hover|active)(?:\s|$)/', $selector, $partes)
                    && ! in_array($partes[1], $vigiladas, true)) {
                    $sueltas[] = $selector;
                }
            }
        }

        $this->assertSame([], array_values(array_unique($sueltas)), "Responden al puntero o al dedo sin estar en superficies() ni en enlacesDeTexto():\n".implode("\n", $sueltas));
    }

    /**
     * Y se cierran también desde las páginas servidas, que es donde estaba el
     * defecto: una ficha sin ningún estado no deja rastro en la hoja que la
     * prueba de arriba pueda ver.
     *
     * Todo enlace y todo botón de las páginas de Eventos acusa el toque: o lo
     * vigila una de las dos listas, o lleva un portador de acuse de `app.css`
     * --los botones de la casa, el paginador, el enlace de la política de
     * datos--. Todo lo que esta hoja dibuja con fondo, borde o sombra está en
     * superficies(), porque es lo que tiene que redondear y encoger. Y ninguna
     * caja pintada envuelve sola a una superficie: ese marco se lee como parte
     * de lo que se pulsa, y su relleno no responde al dedo.
     */
    public function test_todo_lo_que_se_pulsa_en_eventos_acusa_el_toque(): void
    {
        $superficies = array_column(self::superficies(), 0);
        $vigilados = array_merge($superficies, array_column(self::enlacesDeTexto(), 0));
        $portadores = MovimientoTest::portadoresDeAcuse();
        $pintores = $this->selectoresQuePintanCaja();

        $this->assertContains('.eventos-editorial-ficha__enlace', $pintores, 'La lectura de la hoja ya no encuentra la caja de la ficha: esta guardia no ve lo que pinta.');

        $revisados = 0;
        $sinVigilar = [];
        $marcos = [];
        $mudos = [];

        foreach ($this->paginasDeEventos() as $pagina => $arbol) {
            $deSuperficie = $this->nodosQueCasan($arbol, $superficies);
            $vigiladosAqui = $this->nodosQueCasan($arbol, $vigilados);
            $pintados = $this->nodosQueCasan($arbol, $pintores);

            foreach ($arbol->query(self::DENTRO_DE_EVENTOS.'//*[self::a[@href] or self::button]') as $elemento) {
                $revisados++;
                $ruta = $elemento->getNodePath();
                $nombre = sprintf('%s → «%s»', $pagina, $elemento->getAttribute('aria-label') ?: Str::limit(trim((string) preg_replace('/\s+/', ' ', $elemento->textContent)), 40));

                if (isset($pintados[$ruta]) && ! isset($deSuperficie[$ruta])) {
                    $sinVigilar[] = "{$nombre}: lo dibuja «{$pintados[$ruta]}»";
                }

                $padre = $elemento->parentNode;

                if (isset($deSuperficie[$ruta], $pintados[$padre->getNodePath()]) && $padre->childElementCount === 1) {
                    $marcos[] = "{$nombre}: «{$pintados[$padre->getNodePath()]}» lo enmarca";
                }

                if (! isset($vigiladosAqui[$ruta]) && array_intersect(preg_split('/\s+/', trim($elemento->getAttribute('class'))), $portadores) === []) {
                    $mudos[] = $nombre;
                }
            }
        }

        $this->assertGreaterThanOrEqual(15, $revisados, 'Las páginas de Eventos pintaron menos enlaces de los que tienen: esta guardia no está mirando las páginas.');
        $this->assertSame([], $sinVigilar, "La hoja dibuja una caja pulsable que no está en superficies():\n".implode("\n", $sinVigilar));
        $this->assertSame([], $marcos, "Una caja pintada envuelve sola a una superficie pulsable: su relleno parece pulsable y no responde:\n".implode("\n", $marcos));
        $this->assertSame([], $mudos, "No acusan el toque --ni los vigila la hoja ni llevan portador de acuse--:\n".implode("\n", $mudos));
    }

    // --- B. Los tres estados ---

    /**
     * Puntero, dedo y teclado, cada uno donde le toca.
     *
     * El `:hover` va dentro de la puerta. El `:active` va FUERA de toda media:
     * en táctil es el único acuse que existe. Baja al instante --0 ms-- y sube
     * con el reloj de la regla base. El foco dibuja un trazo medible.
     */
    #[DataProvider('superficies')]
    public function test_cada_superficie_responde_al_puntero_al_dedo_y_al_teclado(string $selector, string $encogimiento): void
    {
        $hover = $this->reglasDeEstado($selector, 'hover');

        $this->assertNotEmpty($hover, "{$selector} no declara :hover: con ratón no dice que se puede pulsar.");

        foreach ($hover as $regla) {
            $this->assertStringContainsString(self::PUERTA, $regla['contexto'], "El :hover de {$selector} está fuera de la puerta de puntero fino: en el teléfono se queda pegado tras el toque.");
        }

        $pulsado = $this->reglasDeEstado($selector, 'active');

        $this->assertCount(1, $pulsado, "{$selector} tiene que declarar un solo :active: sin él no acusa el dedo en táctil.");
        $this->assertSame('', $pulsado[0]['contexto'], "El :active de {$selector} vive dentro de «{$pulsado[0]['contexto']}»: en táctil tiene que aplicarse siempre.");

        $acuse = $this->declaraciones($pulsado[0]['cuerpo']);

        $this->assertSame("scale(var({$encogimiento}))", $acuse['transform'] ?? null, "El acuse de {$selector} tiene que encoger con {$encogimiento}: un literal no lo anula el movimiento reducido.");
        $this->assertSame('0ms', $acuse['transition-duration'] ?? null, "El :active de {$selector} tiene que bajar al instante: con retardo, el dedo se levanta antes de verlo.");

        $this->assertAnilloDeFocoMedible($selector);
    }

    /**
     * Un enlace de texto responde con puntero fino y con teclado, y deja el
     * dedo a su portador.
     *
     * Esta hoja va sin capa: cualquier `transition`, `opacity` o movimiento que
     * declare sobre el enlace --en reposo o en un estado-- sustituye entero al
     * de `.enlace-accion`, y la atenuación al pulsar muere sin ningún error.
     * Tampoco encoge: `scale()` sobre texto lo saca de la rejilla de píxeles.
     *
     * Lo que cambia en la flecha al pasar el puntero funde con el reloj del
     * portador, `.enlace-accion svg`, y el color del hover sostiene texto sobre
     * el fondo y la superficie editoriales en los dos temas.
     */
    #[DataProvider('enlacesDeTexto')]
    public function test_cada_enlace_de_texto_responde_sin_pisar_a_su_portador(string $selector): void
    {
        $hover = $this->reglasDeEstado($selector, 'hover');

        $this->assertNotEmpty($hover, "{$selector} no declara :hover: con ratón no dice que se puede pulsar.");

        $propio = null;

        foreach ($hover as $regla) {
            $this->assertStringContainsString(self::PUERTA, $regla['contexto'], "El :hover de {$selector} está fuera de la puerta de puntero fino: en el teléfono se queda pegado tras el toque.");

            foreach ($regla['selectores'] as $estado) {
                if (! str_starts_with($estado, $selector.':hover')) {
                    continue;
                }

                $descendiente = trim(substr($estado, strlen($selector.':hover')));

                if ($descendiente === '') {
                    $propio = $this->declaraciones($regla['cuerpo'])['color'] ?? $propio;

                    continue;
                }

                $this->assertSame('svg', $descendiente, "«{$estado}» mueve algo que no es la flecha: su reloj no lo pone ningún portador.");
                $this->assertMatchesRegularExpression(
                    '/\.enlace-accion svg\s*\{[^}]*transition:\s*translate var\(--duracion-[\w-]+\) var\(--ease-[\w-]+\)/',
                    File::get(resource_path('css/app.css')),
                    "La flecha de {$selector} se mueve al pasar el puntero y `.enlace-accion svg` ya no la funde."
                );
            }
        }

        $this->assertNotNull($propio, "El :hover de {$selector} no cambia el color del enlace: sin puntero fino no queda otra señal.");

        foreach (['claro', 'oscuro'] as $tema) {
            foreach (['--evt-bg', '--evt-bg-alta', '--evt-surface'] as $fondo) {
                $razon = $this->contraste($this->resolverColor($propio, $tema), $this->hexEditorial($fondo, $tema));

                $this->assertGreaterThanOrEqual(self::MINIMO_TEXTO, $razon, sprintf('El hover de %s da %.2f:1 sobre %s en %s.', $selector, $razon, $fondo, $tema));
            }
        }

        $this->assertSame([], $this->reglasDeEstado($selector, 'active'), "{$selector} declara su propio :active: el acuse de un enlace de texto es la atenuación de `.enlace-accion`, y esta hoja se la pisa.");

        $sobreElEnlace = '/^'.preg_quote($selector, '/').'(?::[\w-]+(?:\([^)]*\))?|\[[^\]]*\])*$/';

        foreach ($this->reglas($this->hoja()) as $regla) {
            if (preg_grep($sobreElEnlace, $regla['selectores']) === []) {
                continue;
            }

            foreach (array_keys($this->declaraciones($regla['cuerpo'])) as $propiedad) {
                $this->assertDoesNotMatchRegularExpression(
                    '/^(?:transition(?:-[\w-]+)?|opacity|transform|scale|translate|rotate)$/',
                    $propiedad,
                    implode(', ', $regla['selectores'])." declara `{$propiedad}` y pisa entero a `.enlace-accion`."
                );
            }
        }

        $this->assertAnilloDeFocoMedible($selector);
    }

    /**
     * Cada cambio de estado funde con el reloj de la casa: el color a
     * `--duracion-boton` con `--ease-color`, el acuse a `--duracion-instante`
     * con `--ease-out`.
     *
     * La transición se declara entera en la regla base porque dos atajos
     * `transition` sobre el mismo elemento no se suman: gana el último. Una
     * propiedad que cambia al pasar el puntero y no está en la lista salta de
     * golpe. Lo que cambia en un descendiente --la foto, la flecha-- funde en
     * la regla de ese descendiente.
     */
    #[DataProvider('superficies')]
    public function test_cada_superficie_funde_con_el_reloj_de_la_casa(string $selector): void
    {
        $reloj = $this->relojDe($selector);

        $this->assertNotSame([], $reloj, "{$selector} no declara transición: sus estados saltan de golpe.");
        $this->assertSame('var(--duracion-instante) var(--ease-out)', $reloj['transform'] ?? null, "El acuse de {$selector} tiene que subir en --duracion-instante con --ease-out.");

        foreach ($this->reglasDeEstado($selector, 'hover') as $regla) {
            foreach ($regla['selectores'] as $estado) {
                $descendiente = trim((string) preg_replace('/^.*?:hover/', '', $estado));

                foreach (array_keys($this->declaraciones($regla['cuerpo'])) as $propiedad) {
                    if ($descendiente === '') {
                        $this->assertSame('var(--duracion-boton) var(--ease-color)', $reloj[$propiedad] ?? null, "{$selector} cambia `{$propiedad}` al pasar el puntero sin fundirlo en --duracion-boton con --ease-color.");

                        continue;
                    }

                    $this->assertMatchesRegularExpression('/^var\(--duracion-[\w-]+\) var\(--ease-[\w-]+\)$/', $this->relojDe($descendiente)[$propiedad] ?? '', "«{$descendiente}» cambia `{$propiedad}` al pasar el puntero por {$selector} sin transición propia.");
                }
            }
        }
    }

    public function test_la_hoja_no_escribe_relojes_a_mano(): void
    {
        $sueltos = [];

        foreach ($this->reglas($this->hoja()) as $regla) {
            $selector = implode(', ', $regla['selectores']);

            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if ($propiedad === 'transition') {
                    foreach ($this->transiciones($valor) as [$animada, $duracion, $curva]) {
                        if (! preg_match('/^var\(--duracion-[\w-]+\)$/', $duracion) || ! preg_match('/^var\(--ease-[\w-]+\)$/', $curva)) {
                            $sueltos[] = "{$selector} → {$animada} {$duracion} {$curva}";
                        }
                    }
                }

                if ($propiedad === 'transition-duration' && ! ($valor === '0ms' && str_ends_with($selector, ':active'))) {
                    $sueltos[] = "{$selector} → transition-duration: {$valor}";
                }
            }
        }

        $this->assertSame([], $sueltos, "Relojes fuera de los tokens (el único literal admitido es el 0 ms de un :active):\n".implode("\n", $sueltos));
    }

    // --- C. Movimiento reducido ---

    /**
     * Bajo movimiento reducido se apaga lo que SE MUEVE y nada más. El color,
     * el borde, la opacidad y el subrayado no son movimiento: quitarlos deja
     * sin señal de hover a quien pidió menos movimiento.
     *
     * La geometría no se anula en esta hoja sino en `tokens.css`, que pone a
     * 1 o a 0 los tokens de encogimiento, zoom y avance. Por eso todo lo que se
     * mueve al pasar o al pulsar tiene que salir de uno de esos tokens.
     */
    public function test_el_movimiento_reducido_apaga_lo_que_se_mueve_y_deja_el_color(): void
    {
        $reglas = $this->reglas($this->hoja());
        $reducidas = array_filter($reglas, fn (array $regla): bool => str_contains($regla['contexto'], self::REDUCIDO));

        $this->assertNotEmpty($reducidas, 'La hoja no tiene bloque de movimiento reducido.');

        foreach ($reducidas as $regla) {
            foreach (array_keys($this->declaraciones($regla['cuerpo'])) as $propiedad) {
                $this->assertDoesNotMatchRegularExpression(
                    '/^(?:color|background(?:-color)?|border(?:-[\w-]+)?-color|box-shadow|opacity|outline(?:-[\w-]+)?|text-decoration(?:-[\w-]+)?|transition(?:-[\w-]+)?)$/',
                    $propiedad,
                    'El movimiento reducido de '.implode(', ', $regla['selectores'])." quita `{$propiedad}`, que no es movimiento."
                );
            }
        }

        $anulados = $this->tokensAnuladosPorMovimientoReducido();
        $movimientos = 0;

        foreach ($reglas as $regla) {
            if (! preg_match('/:(?:hover|active)(?:\s|$|,)/', implode(',', $regla['selectores']).',')) {
                continue;
            }

            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if (! in_array($propiedad, ['transform', 'scale', 'translate', 'rotate'], true)) {
                    continue;
                }

                $movimientos++;
                $selector = implode(', ', $regla['selectores']);

                /*
                 * Un token multiplicado sigue quieto solo si el token se anula a
                 * cero: `calc(var(--avance) * -1)` invierte el sentido y vale 0
                 * bajo movimiento reducido, pero una escala que se anula a 1 y
                 * se multiplica ya no es 1.
                 */
                $multiplicado = '/calc\(var\((--[\w-]+)\) \* -?\d*\.?\d+\)/';

                preg_match_all($multiplicado, $valor, $factores);

                foreach ($factores[1] as $token) {
                    $this->assertMatchesRegularExpression('/^(?:0|0px|0%)$/', $anulados[$token] ?? '', "{$selector} multiplica {$token}, que bajo movimiento reducido no vale cero: el producto se sigue moviendo.");
                }

                /* Fuera los tokens y los ceros, que no mueven nada: lo que quede con una cifra es un literal. */
                $resto = (string) preg_replace([$multiplicado, '/var\(--[\w-]+\)/', '/(?<![\w.-])0(?:px|rem|em|%)?(?![\w.])/'], '', $valor);

                $this->assertDoesNotMatchRegularExpression('/\d/', $resto, "{$selector} mueve `{$propiedad}: {$valor}` con un literal: el movimiento reducido no puede anularlo.");

                preg_match_all('/var\((--[\w-]+)\)/', $valor, $tokens);

                $this->assertNotEmpty($tokens[1], "{$selector} mueve `{$propiedad}` sin token.");

                foreach ($tokens[1] as $token) {
                    $this->assertArrayHasKey($token, $anulados, "{$selector} se mueve con {$token}, que tokens.css no anula bajo movimiento reducido.");
                    $this->assertMatchesRegularExpression('/^(?:1|0|0px|0%)$/', $anulados[$token], "{$token} no se anula del todo bajo movimiento reducido: vale {$anulados[$token]}.");
                }
            }
        }

        $this->assertGreaterThanOrEqual(count(self::superficies()) + 3, $movimientos, 'La hoja declara menos movimientos de los que tiene: un acuse por superficie, la foto y la flecha de la ficha, y la flecha de la vuelta.');
    }

    // --- D. El riel ---

    /**
     * En el teléfono la ficha cubre casi todo el ancho y buena parte del alto,
     * justo donde cae el dedo. Con `touch-action: pan-x` un deslizamiento
     * vertical que empieza sobre ella no mueve la página y el pellizco no
     * amplía. Tampoco puede robarlo un manejador de la vista.
     */
    public function test_el_riel_deja_pasar_el_gesto_vertical_y_el_pellizco(): void
    {
        $declaradas = 0;

        foreach ($this->reglas($this->hoja()) as $regla) {
            if (! in_array('.eventos-editorial-riel__pista', $regla['selectores'], true)) {
                continue;
            }

            $valor = $this->declaraciones($regla['cuerpo'])['touch-action'] ?? null;

            if ($valor === null) {
                continue;
            }

            $declaradas++;
            $gestos = preg_split('/\s+/', $valor);

            if (array_intersect($gestos, ['auto', 'manipulation']) !== []) {
                continue;
            }

            $this->assertContains('pan-x', $gestos, "La pista dejó de desplazarse en horizontal: touch-action: {$valor}.");
            $this->assertContains('pan-y', $gestos, "La pista se queda el gesto vertical: touch-action: {$valor}.");
            $this->assertContains('pinch-zoom', $gestos, "La pista se queda el pellizco: touch-action: {$valor}.");
        }

        $this->assertSame(1, $declaradas, 'La pista declara su touch-action una sola vez, en la regla base.');

        $vista = File::get(resource_path('views/publico/eventos/index.blade.php'));

        $this->assertDoesNotMatchRegularExpression('/(?:x-on:|@)(?:touchstart|touchmove|wheel|pointermove)\b/', $vista, 'Un manejador de gestos en la vista de Eventos puede robar el desplazamiento que la hoja deja pasar.');
    }

    /**
     * El anillo de la ficha enfocada sale 4 px de su borde, y la pista del
     * riel recorta todo lo que sale de su caja. Cada ficha encajada toca el
     * borde de arriba y el de la izquierda de la pista, así que sin sitio el
     * anillo pierde esos dos lados.
     *
     * El sitio lo da un sangrado: relleno para el anillo y el mismo margen en
     * negativo para que ninguna ficha se mueva. Tiene cuatro piezas más, y
     * sin cualquiera de ellas vuelve el defecto o se corre el riel:
     *
     *   - El riel no recorta en horizontal, o corta el sangrado.
     *   - El riel es contexto de bloque, o el margen negativo se funde con el
     *     suyo y el riel sube.
     *   - El encaje empieza donde empieza el relleno, o cada ficha encajada
     *     queda corrida.
     *   - Los mandos suman ese `scroll-padding` a su origen, o con el encaje
     *     apagado por movimiento reducido avanzan a una posición corrida.
     */
    public function test_el_anillo_de_foco_de_la_ficha_cabe_dentro_del_riel(): void
    {
        $foco = $this->declaraciones($this->reglasDeEstado('.eventos-editorial-ficha__enlace', 'focus-visible')[0]['cuerpo']);
        $anillo = $this->aPixeles(strtok($foco['outline'], ' ')) + $this->aPixeles($foco['outline-offset']);

        $this->assertGreaterThan(0, $anillo);

        $pista = $this->declaracionesDe('.eventos-editorial-riel__pista');

        foreach (['padding-top', 'padding-left', 'padding-right', 'padding-bottom'] as $lado) {
            $this->assertArrayHasKey($lado, $pista, "La pista no declara {$lado}: el anillo de la ficha no tiene sitio por ahí.");
            $this->assertGreaterThanOrEqual($anillo, $this->aPixeles($pista[$lado]), "El {$lado} de la pista ({$pista[$lado]}) no cabe el anillo de {$anillo} px.");
        }

        foreach ($this->reglas($this->hoja()) as $regla) {
            if (! in_array('.eventos-editorial-riel__pista', $regla['selectores'], true) || $regla['contexto'] === '') {
                continue;
            }

            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if (str_starts_with($propiedad, 'padding')) {
                    $this->assertGreaterThanOrEqual($anillo, $this->aPixeles($valor), "En «{$regla['contexto']}» la pista baja {$propiedad} a {$valor}, donde no cabe el anillo.");
                }
            }
        }

        $this->assertSame('-'.$pista['padding-top'], $pista['margin-top'] ?? null, 'El relleno de arriba de la pista no se devuelve con margen negativo: las fichas bajan.');
        $this->assertSame('-'.$pista['padding-left'], $pista['margin-left'] ?? null, 'El relleno izquierdo de la pista no se devuelve con margen negativo: las fichas se corren.');
        $this->assertSame($pista['padding-left'], strtok($pista['scroll-padding-inline'] ?? '', ' '), 'El encaje de la pista no empieza donde empieza su relleno: cada ficha encajada queda corrida.');

        $riel = $this->declaracionesDe('.eventos-editorial-riel');

        foreach (['overflow', 'overflow-x'] as $propiedad) {
            $recorte = $riel[$propiedad] ?? 'visible';

            $this->assertNotContains($recorte, ['clip', 'hidden', 'auto', 'scroll'], "El riel recorta en horizontal ({$propiedad}: {$recorte}) y corta el sangrado del anillo.");
        }

        $this->assertSame('flow-root', $riel['display'] ?? null, 'Sin contexto de bloque propio el margen negativo de la pista se funde con el del riel, y el riel sube.');

        $this->assertMatchesRegularExpression(
            '/const origen = pista\.getBoundingClientRect\(\)\.left \+ \(parseFloat\(getComputedStyle\(pista\)\.scrollPaddingLeft\) \|\| 0\);/',
            File::get(resource_path('views/publico/eventos/index.blade.php')),
            'avanzar() no suma el scroll-padding de la pista a su origen: con el encaje apagado, los mandos dejan cada ficha corrida.'
        );
    }

    // --- E. Contraste ---

    /**
     * Un relleno rojo con rótulo claro encima no puede ser el acento editorial,
     * que en oscuro es un rojo para TEXTO: blanco sobre él baja de 4,5:1. Se
     * resuelve el token del relleno en los dos temas, como lo resuelve el
     * navegador, y se mide contra el rótulo real --el de la hoja y el que pinta
     * el componente--.
     */
    public function test_los_rellenos_con_rotulo_claro_sostienen_aa_en_los_dos_temas(): void
    {
        $conmutador = $this->arbol(Blade::render('<x-publico.conmutador-eventos activo="proximos" :total-proximos="2" :total-pasados="1" />'));
        $activos = $conmutador->query('//a[@aria-current="true"]');

        $this->assertSame(1, $activos->length, 'El conmutador no marca un segmento activo.');
        $this->assertMatchesRegularExpression('/(?:^|\s)text-white(?:\s|$)/', $activos->item(0)->getAttribute('class'), 'El rótulo del segmento activo ya no es blanco: el contraste de abajo mide otra cosa.');

        $fecha = $this->declaracionesDe('.eventos-editorial-ficha__fecha');
        $hora = $this->declaracionesDe('.eventos-editorial-ficha__hora')['color'] ?? '';

        $this->assertSame(1, preg_match('/^rgb\((\d+) (\d+) (\d+) \/ ([\d.]+)\)$/', $hora, $canalesDeHora), 'La hora de la ficha ya no es un rgb con alfa: esta prueba no sabe medirla.');

        $casos = [
            'segmento activo' => [$this->declaracionesDe('.eventos-editorial-conmutador a[aria-current="true"]')['background-color'] ?? '', ['rótulo' => '#ffffff']],
            'columna de fecha' => [$fecha['background'] ?? '', ['día y mes' => $fecha['color'] ?? '', 'hora' => null]],
        ];

        foreach (['claro', 'oscuro'] as $tema) {
            foreach ($casos as $nombre => [$relleno, $rotulos]) {
                $fondo = $this->resolverColor($relleno, $tema);

                foreach ($rotulos as $cual => $rotulo) {
                    $texto = $rotulo ?? $this->componer(
                        sprintf('#%02x%02x%02x', $canalesDeHora[1], $canalesDeHora[2], $canalesDeHora[3]),
                        (float) $canalesDeHora[4],
                        $fondo
                    );
                    $razon = $this->contraste($texto, $fondo);

                    $this->assertGreaterThanOrEqual(self::MINIMO_TEXTO, $razon, sprintf('%s en %s: %s (%s) sobre %s (%s) da %.2f:1.', ucfirst($nombre), $tema, $cual, $texto, $relleno, $fondo, $razon));
                }
            }
        }
    }

    /**
     * El acento editorial es un rojo para texto y tiene que sostenerlo donde
     * se pinta: sobre la ficha, sobre la ficha realizada y sobre su propio
     * tinte en las dos, que es el chip del tipo. La píldora del calendario
     * lleva su propio rótulo y se mide sobre su tinte en una celda del mes y
     * en un día colgante.
     */
    public function test_el_texto_rojo_sostiene_aa_sobre_sus_superficies_y_tintes(): void
    {
        $pildora = $this->declaracionesDe('.eventos-editorial-calendario a');

        $this->assertSame('var(--evt-acento-suave)', $pildora['background'] ?? null, 'La píldora ya no se tiñe con el tinte editorial: esta prueba mide otra cosa.');

        $casos = [
            'acento editorial' => ['var(--evt-acento)', ['--evt-surface', '--evt-surface-alta']],
            'rótulo de la píldora' => [$pildora['color'] ?? '', ['--evt-surface', '--evt-bg-alta']],
        ];

        foreach (['claro', 'oscuro'] as $tema) {
            $this->assertSame(1, preg_match('/^rgb\((\d+) (\d+) (\d+) \/ ([\d.]+)\)$/', $this->valorEditorial('--evt-acento-suave', $tema), $tinte), "El tinte editorial de {$tema} ya no es un rgb con alfa.");
            $colorDelTinte = sprintf('#%02x%02x%02x', $tinte[1], $tinte[2], $tinte[3]);

            foreach ($casos as $nombre => [$valor, $superficies]) {
                $texto = $this->resolverColor($valor, $tema);

                foreach ($superficies as $superficie) {
                    $liso = $this->hexEditorial($superficie, $tema);

                    foreach (['liso' => $liso, 'teñido' => $this->componer($colorDelTinte, (float) $tinte[4], $liso)] as $capa => $fondo) {
                        $razon = $this->contraste($texto, $fondo);

                        $this->assertGreaterThanOrEqual(self::MINIMO_TEXTO, $razon, sprintf('%s en %s: %s sobre %s %s (%s) da %.2f:1.', ucfirst($nombre), $tema, $texto, $superficie, $capa, $fondo, $razon));
                    }
                }
            }
        }
    }

    // --- F. Las vistas ---

    /**
     * Lo que dicta la hoja convive con el marcado que lo pinta.
     *
     * Tailwind 4 compila `hover:` detrás de `@media (hover: hover)`, que no es
     * la puerta de puntero fino, y en lo que dicta la hoja es además letra
     * muerta o una segunda mano: la hoja va sin capa y su `color` la pisa.
     *
     * Un enlace de texto tiene que llevar `.enlace-accion`, que es su acuse. Y
     * una superficie que lo lleva conserva la opacidad en la transición que la
     * hoja le declara, porque esa lista sustituye entera a la del portador: sin
     * ella la atenuación vuelve de golpe al soltar.
     *
     * Se leen las páginas servidas y no las plantillas, porque una lista de
     * clases se escribe en `class`, en `@class` o en una variable. Cada
     * selector vigilado tiene que aparecer en alguna, o esto no mira nada.
     */
    public function test_lo_que_dicta_la_hoja_convive_con_el_marcado(): void
    {
        $enlaces = array_column(self::enlacesDeTexto(), 0);
        $vigilados = array_merge(array_column(self::superficies(), 0), $enlaces);
        $vistos = array_fill_keys($vigilados, 0);

        foreach ($this->paginasDeEventos() as $pagina => $arbol) {
            foreach ($vigilados as $selector) {
                foreach ($arbol->query($this->aXPath($selector)) as $elemento) {
                    $vistos[$selector]++;
                    $clases = preg_split('/\s+/', trim($elemento->getAttribute('class')));
                    $nombre = sprintf('%s → «%s»', $pagina, $elemento->getAttribute('aria-label') ?: Str::limit(trim((string) preg_replace('/\s+/', ' ', $elemento->textContent)), 40));

                    $this->assertSame([], array_values(preg_grep('/^hover:/', $clases)), "{$nombre} declara su hover en la vista: vive en eventos-editorial.css, tras la puerta de puntero fino.");

                    if (in_array($selector, $enlaces, true)) {
                        $this->assertContains('enlace-accion', $clases, "{$nombre} no lleva `.enlace-accion`: es un enlace de texto y no acusa el toque.");
                    } elseif (in_array('enlace-accion', $clases, true)) {
                        $this->assertSame('var(--duracion-instante) var(--ease-out)', $this->relojDe($selector)['opacity'] ?? null, "{$nombre} lleva `.enlace-accion` y la transición de {$selector} no nombra la opacidad: la atenuación vuelve de golpe al soltar.");
                    }
                }
            }
        }

        foreach ($vistos as $selector => $veces) {
            $this->assertGreaterThan(0, $veces, "Ninguna página de Eventos pinta {$selector}: esta guardia no mira nada.");
        }
    }

    /**
     * El trazo del foco es medible y se ve sobre todo fondo editorial.
     *
     * Un anillo fuera de toda media, de 2 px o más, separado del borde y con un
     * token de color, a 3:1 como poco sobre los cuatro fondos de los dos temas.
     */
    private function assertAnilloDeFocoMedible(string $selector): void
    {
        $foco = $this->reglasDeEstado($selector, 'focus-visible');

        $this->assertCount(1, $foco, "{$selector} no declara :focus-visible.");
        $this->assertSame('', $foco[0]['contexto'], "El :focus-visible de {$selector} vive dentro de «{$foco[0]['contexto']}»: el teclado lo necesita siempre.");

        $anillo = $this->declaraciones($foco[0]['cuerpo']);

        $this->assertSame(1, preg_match('/^(\d+(?:\.\d+)?)px solid var\((--[\w-]+)\)$/', $anillo['outline'] ?? '', $trazo), "El foco de {$selector} tiene que ser un trazo sólido de N px con un token de color, para poder medirlo.");
        $this->assertGreaterThanOrEqual(2, (float) $trazo[1], "Un trazo de foco de menos de 2 px no alcanza el área de §1.4.11 en {$selector}.");
        $this->assertGreaterThan(0, $this->aPixeles($anillo['outline-offset'] ?? '0'), "El anillo de {$selector} tiene que separarse del borde: pegado, se pinta sobre el relleno y deja de medirse contra el fondo.");

        $color = $this->tokenDelTema($trazo[2]);

        foreach (['claro', 'oscuro'] as $tema) {
            foreach (['--evt-bg', '--evt-bg-alta', '--evt-surface', '--evt-surface-alta'] as $fondo) {
                $razon = $this->contraste($color, $this->hexEditorial($fondo, $tema));

                $this->assertGreaterThanOrEqual(self::MINIMO_ANILLO, $razon, sprintf('El anillo de %s da %.2f:1 sobre %s en %s.', $selector, $razon, $fondo, $tema));
            }
        }
    }

    // --- Lectura de las páginas ---

    /**
     * Las cuatro páginas que carga esta hoja, con lo que cada una pinta solo
     * cuando hay datos: dos eventos próximos para que el riel tenga mandos, un
     * mes con evento para la rejilla y la agenda, un mes vacío para la vuelta a
     * los próximos, y la ficha para la vuelta a la agenda.
     *
     * @return array<string, DOMXPath>
     */
    private function paginasDeEventos(): array
    {
        $this->travelTo(Carbon::create(2026, 9, 16, 12));

        $evento = Evento::factory()->publicado()->elDia(Carbon::create(2026, 9, 18), 19)->create(['titulo' => 'Foro de la agenda']);
        Evento::factory()->publicado()->elDia(Carbon::create(2026, 10, 2), 10)->create(['titulo' => 'Feria del riel']);

        $paginas = [
            'próximos' => route('eventos.index', ['cuando' => 'proximos']),
            'mes con eventos' => route('eventos.calendario', [2026, '09']),
            'mes vacío' => route('eventos.calendario', [2026, '11']),
            'ficha' => route('eventos.show', $evento),
        ];

        return array_map(fn (string $url): DOMXPath => $this->arbol($this->get($url)->assertOk()->getContent()), $paginas);
    }

    /**
     * Los nodos de una página que casan con alguno de los selectores, por su
     * ruta en el árbol y con el primer selector que los alcanza.
     *
     * @param  list<string>  $selectores
     * @return array<string, string>
     */
    private function nodosQueCasan(DOMXPath $arbol, array $selectores): array
    {
        $nodos = [];

        foreach ($selectores as $selector) {
            foreach ($arbol->query($this->aXPath($selector)) as $nodo) {
                $nodos[$nodo->getNodePath()] ??= $selector;
            }
        }

        return $nodos;
    }

    private function aXPath(string $selector): string
    {
        try {
            return (new CssSelectorConverter(true))->toXPath($selector);
        } catch (ExpressionErrorException $error) {
            $this->fail("«{$selector}» no se puede leer como XPath ({$error->getMessage()}): esta guardia no sabe a qué elementos alcanza.");
        }
    }

    /**
     * Los selectores con los que la hoja dibuja una caja --fondo, borde o
     * sombra-- sobre un elemento en reposo. Los estados y los pseudoelementos
     * no cuentan: no deciden si algo es una superficie.
     *
     * @return list<string>
     */
    private function selectoresQuePintanCaja(): array
    {
        $pintores = [];

        foreach ($this->reglas($this->hoja()) as $regla) {
            $pinta = preg_grep(
                '/^(?:background(?:-color|-image)?|border(?:-(?:top|right|bottom|left))?(?:-(?:color|width|style))?|box-shadow)$/',
                array_keys($this->declaraciones($regla['cuerpo']))
            );

            if ($pinta === []) {
                continue;
            }

            foreach ($regla['selectores'] as $selector) {
                if (! preg_match('/::|:(?:hover|active|focus|focus-visible|focus-within)\b/', $selector)) {
                    $pintores[] = $selector;
                }
            }
        }

        return array_values(array_unique($pintores));
    }

    // --- Lectura de las hojas ---

    private function hoja(): string
    {
        return File::get(resource_path('css/eventos-editorial.css'));
    }

    /**
     * Las reglas de una hoja con el contexto de @-reglas que las envuelve.
     *
     * Se recorre con las llaves contadas y sin comentarios: la misma regla
     * dentro y fuera de una media son dos comportamientos distintos.
     *
     * @return list<array{contexto: string, selectores: list<string>, cuerpo: string}>
     */
    private function reglas(string $css): array
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $reglas = [];
        $pila = [];
        $inicio = 0;

        for ($i = 0, $largo = strlen($css); $i < $largo; $i++) {
            if ($css[$i] === ';') {
                $inicio = $i + 1;
            } elseif ($css[$i] === '{') {
                $preludio = (string) preg_replace('/\s+/', ' ', trim(substr($css, $inicio, $i - $inicio)));

                if (str_starts_with($preludio, '@')) {
                    $pila[] = $preludio;
                    $inicio = $i + 1;

                    continue;
                }

                $fin = strpos($css, '}', $i);

                if ($fin === false) {
                    $this->fail("La regla «{$preludio}» no cierra.");
                }

                $reglas[] = [
                    'contexto' => implode(' ', $pila),
                    'selectores' => array_map(trim(...), explode(',', $preludio)),
                    'cuerpo' => substr($css, $i + 1, $fin - $i - 1),
                ];

                $i = $fin;
                $inicio = $fin + 1;
            } elseif ($css[$i] === '}') {
                array_pop($pila);
                $inicio = $i + 1;
            }
        }

        return $reglas;
    }

    /** @return array<string, string> */
    private function declaraciones(string $cuerpo): array
    {
        $declaraciones = [];

        foreach (explode(';', $cuerpo) as $trozo) {
            if (! str_contains($trozo, ':')) {
                continue;
            }

            [$propiedad, $valor] = explode(':', $trozo, 2);
            $declaraciones[trim($propiedad)] = (string) preg_replace('/\s+/', ' ', trim($valor));
        }

        return $declaraciones;
    }

    /**
     * Las declaraciones de un selector fuera de toda media, fundidas en orden.
     *
     * @return array<string, string>
     */
    private function declaracionesDe(string $selector, ?string $css = null): array
    {
        $declaraciones = [];

        foreach ($this->reglas($css ?? $this->hoja()) as $regla) {
            if ($regla['contexto'] === '' && in_array($selector, $regla['selectores'], true)) {
                $declaraciones = array_merge($declaraciones, $this->declaraciones($regla['cuerpo']));
            }
        }

        return $declaraciones;
    }

    /**
     * Las reglas de un estado de la superficie, en cualquier contexto: el
     * propio (`sel:hover`), el de una variante (`sel:not(...):hover`) y el de
     * un descendiente que cambia con él (`sel:hover .hijo`).
     *
     * @return list<array{contexto: string, selectores: list<string>, cuerpo: string}>
     */
    private function reglasDeEstado(string $selector, string $estado): array
    {
        $patron = '/^'.preg_quote($selector, '/').'(?::not\([^)]*\))*:'.$estado.'(?:\s|$)/';

        return array_values(array_filter(
            $this->reglas($this->hoja()),
            fn (array $regla): bool => array_filter($regla['selectores'], fn (string $uno): bool => (bool) preg_match($patron, $uno)) !== []
        ));
    }

    /**
     * La transición de un selector como propiedad => «duración curva».
     *
     * @return array<string, string>
     */
    private function relojDe(string $selector): array
    {
        $reloj = [];

        foreach ($this->transiciones($this->declaracionesDe($selector)['transition'] ?? '') as [$propiedad, $duracion, $curva]) {
            $reloj[$propiedad] = "{$duracion} {$curva}";
        }

        return $reloj;
    }

    /** @return list<array{string, string, string}> */
    private function transiciones(string $valor): array
    {
        if (trim($valor) === '') {
            return [];
        }

        return array_map(
            fn (string $entrada): array => array_pad(preg_split('/\s+/', trim($entrada)), 3, ''),
            preg_split('/,(?![^(]*\))/', $valor)
        );
    }

    /**
     * Los tokens que el bloque de movimiento reducido de `tokens.css` pisa.
     *
     * @return array<string, string>
     */
    private function tokensAnuladosPorMovimientoReducido(): array
    {
        $anulados = [];

        foreach ($this->reglas(File::get(resource_path('css/tokens.css'))) as $regla) {
            if (str_contains($regla['contexto'], self::REDUCIDO) && in_array(':root', $regla['selectores'], true)) {
                $anulados = array_merge($anulados, $this->declaraciones($regla['cuerpo']));
            }
        }

        if ($anulados === []) {
            $this->fail('tokens.css ya no tiene bloque de movimiento reducido.');
        }

        return $anulados;
    }

    // --- Resolución de colores por tema ---

    /** El valor crudo de un token `--evt-*` en un tema, como lo hereda el navegador. */
    private function valorEditorial(string $token, string $tema): string
    {
        $claro = $this->declaracionesDe('.eventos-editorial');
        $oscuro = array_merge($claro, $this->declaracionesDe('.dark .eventos-editorial'));
        $valor = ($tema === 'oscuro' ? $oscuro : $claro)[$token] ?? null;

        if ($valor === null) {
            $this->fail("eventos-editorial.css no declara {$token} para el tema {$tema}.");
        }

        return $valor;
    }

    private function hexEditorial(string $token, string $tema): string
    {
        return $this->resolverColor($this->valorEditorial($token, $tema), $tema);
    }

    /**
     * Un valor de color hasta su hexadecimal: sigue los `var()` por la hoja y
     * por `tokens.css` con las reglas del tema, igual que la cascada.
     */
    private function resolverColor(string $valor, string $tema): string
    {
        for ($saltos = 0; $saltos < 5; $saltos++) {
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $valor)) {
                return strtolower($valor);
            }

            if (! preg_match('/^var\((--[\w-]+)\)$/', $valor, $token)) {
                $this->fail("«{$valor}» no es un hexadecimal ni un var(): esta prueba no sabe medirlo.");
            }

            $valor = str_starts_with($token[1], '--evt-')
                ? $this->valorEditorial($token[1], $tema)
                : $this->tokenDelTema($token[1], $tema);
        }

        $this->fail("«{$valor}» no llega a un color en cinco saltos.");
    }

    /**
     * Un token de `tokens.css`: el de `.dark` en oscuro si lo redefine, el de
     * `:root` si no, y los de `@theme` para la paleta de marca.
     */
    private function tokenDelTema(string $token, string $tema = 'claro'): string
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $claro = $this->declaracionesDe(':root', $tokens);
        $oscuro = array_merge($claro, $this->declaracionesDe('.dark', $tokens));
        $valor = ($tema === 'oscuro' ? $oscuro : $claro)[$token] ?? null;

        if ($valor === null && preg_match('/'.preg_quote($token, '/').':\s*(#[0-9a-fA-F]{6});/', $tokens, $deMarca)) {
            $valor = $deMarca[1];
        }

        if ($valor === null) {
            $this->fail("tokens.css no declara {$token}.");
        }

        return $this->resolverColor($valor, $tema);
    }

    private function aPixeles(string $longitud): float
    {
        if (! preg_match('/^(-?\d*\.?\d+)(px|rem)?$/', trim($longitud), $partes)) {
            $this->fail("«{$longitud}» no es una longitud en px o rem.");
        }

        return (float) $partes[1] * (($partes[2] ?? '') === 'rem' ? 16 : 1);
    }

    private function arbol(string $html): DOMXPath
    {
        $documento = new DOMDocument;
        $anteriores = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($anteriores);

        return new DOMXPath($documento);
    }
}

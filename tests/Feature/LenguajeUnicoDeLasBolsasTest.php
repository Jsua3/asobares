<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * Las tres bolsas —artistas, proveedores y empleo— hablan el lenguaje del resto
 * del sitio sin perder el suyo: ninguna superficie pulsable queda recta, y todo
 * lo que se pulsa responde al puntero, al dedo y al teclado.
 *
 * Las hojas editoriales se cargan sin capa, y una regla sin capa le gana siempre
 * a las de `app.css`, que viven en `@layer`. Así una hoja puede pisar en silencio
 * el acuse de un portador de la casa, dejar un hover pegado en el teléfono o
 * volver recta una esquina sin que cambie una sola vista. Por eso lo que se
 * vigila se lee del CSS fuente, regla por regla y con los `@media` que envuelven
 * a cada una, y del árbol de las páginas servidas cuando lo que importa es qué
 * clases lleva cada elemento.
 */
class LenguajeUnicoDeLasBolsasTest extends TestCase
{
    use MideContraste;
    use RefreshDatabase;

    private const array HOJAS = ['artistas', 'proveedores', 'empleo'];

    private const string PUERTA_DE_PUNTERO_FINO = '@media (hover: hover) and (pointer: fine)';

    private const string MOVIMIENTO_REDUCIDO = '(prefers-reduced-motion: reduce)';

    private const array PROPIEDADES_DE_MOVIMIENTO = ['transform', 'translate', 'scale', 'rotate'];

    /** Lo único que el bloque de movimiento reducido puede apagar. */
    private const array APAGABLES_EN_MOVIMIENTO_REDUCIDO = ['transform', 'translate', 'scale', 'rotate', 'animation', 'animation-name'];

    private const array PROPIEDADES_DE_COLOR = ['color', 'background-color', 'border-color', 'outline-color', 'text-decoration-color'];

    /** Texto normal, WCAG 2.1 §1.4.3. */
    private const float MINIMO_TEXTO = 4.5;

    /** Indicador de foco, WCAG 2.1 §1.4.11. */
    private const float MINIMO_FOCO = 3.0;

    /** Portadores de `app.css` que traen su propia transición desde el marcado. */
    private const array PORTADORES_CON_TRANSICION = ['pulsable', 'tarjeta-pulsable', 'tarjeta-hover', 'enlace-accion', 'fila-pulsable'];

    /**
     * Lo que se pulsa en cada bolsa y quién pone el acuse al pulsar.
     *
     * `hoja` quiere decir que el `:active` lo declara la hoja. Un nombre de clase
     * quiere decir que lo pone ese portador de `app.css` desde el marcado, y
     * entonces se vigila que el marcado lo lleve y que la hoja no se lo pise.
     * `ninguno` es para los campos de texto: pulsar en ellos es poner el cursor
     * o arrastrar una selección, y una caja que encoge bajo el puntero mueve el
     * texto que se selecciona. Responden al ratón y al teclado, y no encogen.
     *
     * `superficie` marca lo que la hoja dibuja con borde o fondo, que es lo que
     * no puede quedar recto. Los campos no la llevan: su caja la dibuja
     * `<x-publico.campo>`, y sus esquinas se miran en la página servida.
     *
     * Proveedores no tiene nada que pulsar en su hoja: sus categorías no enlazan
     * a ninguna parte.
     *
     * @return array<string, array<string, array{acuse: string, superficie: bool}>>
     */
    private static function pulsables(): array
    {
        return [
            'artistas' => [
                '.artistas-editorial-card' => ['acuse' => 'hoja', 'superficie' => true],
                '.artistas-editorial-similar' => ['acuse' => 'tarjeta-pulsable', 'superficie' => true],
                '.artistas-editorial select' => ['acuse' => 'hoja', 'superficie' => false],
            ],
            'proveedores' => [],
            'empleo' => [
                '.empleo-editorial-hero__cta' => ['acuse' => 'pulsable', 'superficie' => true],
                '.empleo-editorial-oferta__cta' => ['acuse' => 'pulsable', 'superficie' => true],
                '.empleo-editorial-filtros__limpiar' => ['acuse' => 'pulsable', 'superficie' => true],
                '.empleo-editorial select' => ['acuse' => 'hoja', 'superficie' => false],
                '.empleo-editorial input' => ['acuse' => 'ninguno', 'superficie' => false],
                '.empleo-editorial textarea' => ['acuse' => 'ninguno', 'superficie' => false],
                '.empleo-editorial-retorno' => ['acuse' => 'enlace-accion', 'superficie' => false],
                '.empleo-editorial-oferta__cargo a' => ['acuse' => 'enlace-accion', 'superficie' => false],
                '.empleo-editorial-oferta__empresa a' => ['acuse' => 'enlace-accion', 'superficie' => false],
                '.empleo-editorial-whatsapp a' => ['acuse' => 'enlace-accion', 'superficie' => false],
            ],
        ];
    }

    /**
     * Lo que gobierna cada portador de `app.css`. Una regla sin capa que declare
     * cualquiera de estas propiedades sobre el mismo elemento le gana al portador
     * entero, sin que la especificidad ni el `:active` entren en juego. Un
     * nombre cubre también sus largas: `transition` incluye
     * `transition-duration`.
     *
     * @return array<string, list<string>>
     */
    private static function propiedadesDeLosPortadores(): array
    {
        return [
            'pulsable' => ['transition', 'transform'],
            'tarjeta-pulsable' => ['transition', 'transform'],
            'enlace-accion' => ['transition', 'opacity'],
            'revelar' => ['transition', 'opacity', 'translate'],
        ];
    }

    /**
     * Ninguna superficie pulsable queda recta: la hoja que la dibuja le da
     * esquinas, y ningún estado ni ningún `@media` se las quita. Los campos se
     * miran en las páginas servidas, porque su caja es del componente.
     *
     * Rotura: quitar el `border-radius` de `.artistas-editorial-card` o el de
     * `.artistas-editorial-similar`; poner `border-radius: 0` en el hover de un
     * CTA de empleo.
     */
    public function test_ninguna_superficie_pulsable_de_las_bolsas_queda_recta(): void
    {
        foreach (self::pulsables() as $modulo => $componentes) {
            $reglas = $this->reglasDe($modulo);

            foreach ($componentes as $componente => $ficha) {
                if (! $ficha['superficie']) {
                    continue;
                }

                $propias = array_filter(
                    $reglas,
                    fn (array $regla): bool => in_array($componente, $regla['selectores'], true)
                );

                $this->assertNotEmpty(
                    $this->valores($propias, ['border', 'border-color', 'background', 'background-color']),
                    "{$modulo}: la hoja no dibuja {$componente}, y el registro dice que sí."
                );

                $this->assertNotEmpty(
                    $this->valores($propias, ['border-radius']),
                    "{$modulo}: {$componente} tiene borde o fondo y ningún radio, así que queda recta."
                );
            }

            foreach ($reglas as $regla) {
                foreach ($regla['selectores'] as $selector) {
                    foreach ($componentes as $componente => $ficha) {
                        if (! $ficha['superficie'] || ! $this->apuntaA($selector, $componente)) {
                            continue;
                        }

                        foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                            if (str_contains($propiedad, 'radius')) {
                                $this->assertDoesNotMatchRegularExpression(
                                    '/(?:^|\s)0(?:px|rem|em|%)?(?=\s|$)/',
                                    $valor,
                                    "{$modulo}: «{$selector}» deja recta una esquina de {$componente} ({$propiedad}: {$valor})."
                                );
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->paginasDeLasBolsas() as $modulo => $paginas) {
            foreach (self::pulsables()[$modulo] as $componente => $ficha) {
                if ($ficha['superficie'] || ! $this->esCampo($componente)) {
                    continue;
                }

                $campos = 0;

                foreach ($paginas as $ruta => $html) {
                    foreach ($this->servidos($this->xpathDe($html), $componente) as $campo) {
                        $campos++;

                        $this->assertMatchesRegularExpression(
                            '/(?:^|\s)rounded(?:-(?!none(?:\s|$))\S+)?(?=\s|$)/',
                            $campo->getAttribute('class'),
                            "{$ruta}: {$componente} se sirve sin esquinas."
                        );
                    }
                }

                $this->assertGreaterThan(0, $campos, "{$modulo}: ninguna página pintó {$componente}, y la guarda no miró nada.");
            }
        }
    }

    /**
     * Todo lo que se pulsa responde a las tres manos: al ratón con un `:hover`
     * tras la puerta de puntero fino, al dedo con un `:active` y al teclado con
     * un `:focus-visible` de trazo sólido y separado del borde.
     *
     * Donde el acuse al pulsar lo pone un portador de la casa, se comprueba en
     * las páginas servidas que cada elemento lo lleve, y en la hoja que ninguna
     * regla le declare lo que ese portador gobierna. Los campos de texto
     * responden al ratón y al teclado y no encogen al pulsar.
     *
     * Rotura: borrar `.artistas-editorial-card:active`, el `:focus-visible` de
     * los desplegables de empleo o el hover del enlace de WhatsApp; declarar una
     * `transition` en `.empleo-editorial-hero__cta`; quitar `pulsable` del
     * «Limpiar» de empleo o `tarjeta-pulsable` de «Otros artistas»; encoger
     * `.empleo-editorial textarea` en su `:active`.
     */
    public function test_todo_lo_que_se_pulsa_en_las_bolsas_responde_al_puntero_al_dedo_y_al_teclado(): void
    {
        $paginas = $this->paginasDeLasBolsas();

        foreach (self::pulsables() as $modulo => $componentes) {
            $reglas = $this->reglasDe($modulo);

            foreach ($componentes as $componente => $ficha) {
                $hover = array_filter(
                    $this->reglasDelEstado($reglas, $componente, ':hover'),
                    fn (array $regla): bool => in_array(self::PUERTA_DE_PUNTERO_FINO, $regla['medias'], true)
                        && ! $this->dentroDelMovimientoReducido($regla)
                        && $regla['declaraciones'] !== []
                );

                $this->assertNotEmpty($hover, "{$modulo}: {$componente} no responde al ratón con un :hover tras la puerta de puntero fino.");

                $foco = $this->reglasDelEstado($reglas, $componente, ':focus-visible');

                $this->assertNotEmpty($foco, "{$modulo}: {$componente} no dibuja su :focus-visible.");

                foreach ($foco as $regla) {
                    $this->assertMatchesRegularExpression(
                        '/^[2-9]px solid var\(--[a-z]+-acento\)$/',
                        $this->valor($regla, 'outline') ?? '',
                        "{$modulo}: el foco de {$componente} no es un trazo sólido de 2 px o más con el acento de la bolsa."
                    );

                    $this->assertMatchesRegularExpression(
                        '/^[1-9]\d*px$/',
                        $this->valor($regla, 'outline-offset') ?? '',
                        "{$modulo}: el foco de {$componente} se dibuja pegado al borde."
                    );
                }

                if ($ficha['acuse'] === 'hoja') {
                    $this->assertAcuseDeLaHoja($modulo, $reglas, $componente);
                } elseif ($ficha['acuse'] === 'ninguno') {
                    $this->assertCampoQueNoEncoge($modulo, $reglas, $componente);
                } else {
                    $this->assertAcuseDelPortador($modulo, $reglas, $componente, $ficha['acuse'], $paginas[$modulo]);
                }
            }
        }
    }

    /**
     * Nada de lo que se toca en una bolsa se queda sin transición: cada enlace,
     * botón y campo que sirven sus páginas lleva un portador de la casa o es un
     * componente del registro al que la hoja declara la transición.
     *
     * Se mide la página y no el registro, así que un elemento que nadie anotó
     * sale aquí como huérfano. Por esto la tarjeta del listado de artistas es el
     * enlace mismo: si la dibuja el <article> que lo envuelve, lo que se mueve
     * es el <article> y el <a> que se pulsa se queda sin transición propia.
     *
     * Fuera queda lo que no se pinta —los campos ocultos y la trampa de bots— y
     * la casilla de datos, que es de `<x-publico.habeas-data>` y la dibuja el
     * navegador.
     *
     * Rotura: devolver la clase de la tarjeta de artistas al <article>; quitar la
     * `transition` de `.empleo-editorial textarea`.
     */
    public function test_nada_de_lo_que_se_toca_en_las_bolsas_se_queda_sin_transicion(): void
    {
        foreach ($this->paginasDeLasBolsas() as $modulo => $paginas) {
            $reglas = $this->reglasDe($modulo);
            $conTransicion = array_filter(
                array_keys(self::pulsables()[$modulo]),
                fn (string $componente): bool => $this->declaraTransicion($reglas, $componente)
            );
            $mirados = 0;
            $huerfanos = [];

            foreach ($paginas as $ruta => $html) {
                $xpath = $this->xpathDe($html);
                $cubiertos = [];

                foreach ($conTransicion as $componente) {
                    foreach ($this->servidos($xpath, $componente) as $elemento) {
                        $cubiertos[$elemento->getNodePath()] = true;
                    }
                }

                $tocables = $xpath->query(
                    $this->xpathDeSelector(".{$modulo}-editorial").'//*[self::a or self::button or self::select or self::input or self::textarea or self::summary]'
                );

                foreach ($tocables as $elemento) {
                    if (! $this->sePinta($elemento) || $elemento->getAttribute('id') === 'habeas-datos') {
                        continue;
                    }

                    $mirados++;

                    if (array_intersect(self::PORTADORES_CON_TRANSICION, $this->clases($elemento)) !== []
                        || isset($cubiertos[$elemento->getNodePath()])) {
                        continue;
                    }

                    $huerfanos[] = sprintf('%s: <%s class="%s">', $ruta, $elemento->tagName, $elemento->getAttribute('class'));
                }
            }

            $this->assertGreaterThan(0, $mirados, "{$modulo}: ninguna página sirvió nada que tocar, y la guarda no miró nada.");
            $this->assertSame([], $huerfanos, "En {$modulo} se sirve algo que se toca y no tiene transición:\n".implode("\n", $huerfanos));
        }
    }

    /**
     * En táctil un `:hover` se queda pegado tras el toque: la tarjeta sigue
     * levantada y roja como si estuviera elegida. Lo que sobrevive a quitar los
     * bloques de puntero fino es exactamente lo que queda fuera de la puerta, y
     * ahí no puede quedar ningún `:hover`.
     *
     * Rotura: sacar de su puerta el hover de `.proveedores-editorial-categoria`
     * o el de `.empleo-editorial-retorno`.
     */
    public function test_todo_hover_de_las_bolsas_espera_al_puntero_fino(): void
    {
        foreach (self::HOJAS as $modulo) {
            $css = $this->sinComentarios($this->hoja($modulo));

            $this->assertStringContainsString(':hover', $css, "{$modulo}: la hoja no declara ningún hover, y la guarda no miraría nada.");

            $this->assertStringNotContainsString(
                ':hover',
                $this->sinBloquesDeHoverFino($css),
                "{$modulo}-editorial.css declara un :hover fuera de la puerta de puntero fino."
            );
        }
    }

    /**
     * Movimiento reducido quita lo que se mueve y nada más. El fundido del
     * borde, el color y el subrayado se quedan: no son movimiento, y sin ellos el
     * hover deja de avisar.
     *
     * Son dos mitades. Dentro del bloque solo se apaga movimiento, así que ni
     * `transition: none` ni `text-decoration: none`. Y cada estado de la hoja
     * que mueve algo tiene ahí su pareja apagada, con el mismo selector; un
     * estado que ya declara `none` no mueve nada y no la necesita.
     *
     * Rotura: `transition: none` en el bloque de proveedores; borrar del bloque
     * de empleo `.empleo-editorial select:active`.
     */
    public function test_el_movimiento_reducido_de_las_bolsas_quita_el_movimiento_y_deja_el_color(): void
    {
        foreach (self::HOJAS as $modulo) {
            $reglas = $this->reglasDe($modulo);
            $reducidas = array_values(array_filter($reglas, $this->dentroDelMovimientoReducido(...)));

            $this->assertNotEmpty($reducidas, "{$modulo}-editorial.css no tiene bloque de movimiento reducido.");

            foreach ($reducidas as $regla) {
                foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                    $this->assertContains(
                        $propiedad,
                        self::APAGABLES_EN_MOVIMIENTO_REDUCIDO,
                        "{$modulo}: el movimiento reducido apaga {$propiedad}, que no es movimiento."
                    );

                    $this->assertSame('none', $valor, "{$modulo}: el movimiento reducido deja {$propiedad}: {$valor} en vez de apagarlo.");
                }
            }

            $estadosQueMueven = 0;

            foreach ($reglas as $regla) {
                if ($this->dentroDelMovimientoReducido($regla)) {
                    continue;
                }

                foreach ($regla['selectores'] as $selector) {
                    if (! preg_match('/:(?:hover|active)(?![\w-])/', $this->sinNegaciones($selector))) {
                        continue;
                    }

                    foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                        if (! in_array($propiedad, self::PROPIEDADES_DE_MOVIMIENTO, true) || $valor === 'none') {
                            continue;
                        }

                        $estadosQueMueven++;

                        $apagadas = array_filter(
                            $reducidas,
                            fn (array $reducida): bool => in_array($selector, $reducida['selectores'], true)
                                && $this->valor($reducida, $propiedad) === 'none'
                        );

                        $this->assertNotEmpty($apagadas, "{$modulo}: «{$selector}» se mueve con {$propiedad} y el movimiento reducido no lo apaga.");
                    }
                }
            }

            $this->assertGreaterThan(0, $estadosQueMueven, "{$modulo}: ningún estado se mueve, y la segunda mitad no miró nada.");
        }
    }

    /**
     * Las transiciones de las bolsas hablan en tokens: ni una duración ni una
     * curva escritas a mano. El color funde con `--ease-color` al reloj del
     * botón y lo que se mueve sale con `--ease-out`, que es el reparto de la
     * casa. La única duración suelta que se admite es el `0ms` de un `:active`:
     * es lo que hace que pulsar sea instantáneo.
     *
     * Rotura: `border-color 180ms ease` en la tarjeta de artistas;
     * `transform var(--duracion-boton) var(--ease-color)` en la categoría de
     * proveedores.
     */
    public function test_las_transiciones_de_las_bolsas_hablan_en_tokens(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        foreach (self::HOJAS as $modulo) {
            $tramos = 0;

            foreach ($this->reglasDe($modulo) as $regla) {
                foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                    if ($propiedad === 'transition') {
                        foreach ($this->partirPorComas($valor) as $tramo) {
                            $tramos++;

                            $this->assertSame(
                                1,
                                preg_match('/^([a-z-]+) var\((--duracion-[a-z-]+)\) var\((--ease-[a-z-]+)\)$/', $tramo, $partes),
                                "{$modulo}: «{$tramo}» no habla en tokens."
                            );

                            [, $animada, $duracion, $curva] = $partes;

                            $this->assertMatchesRegularExpression('/'.preg_quote($duracion, '/').':\s*\d+ms;/', $tokens, "{$modulo}: {$duracion} no existe en tokens.css.");
                            $this->assertMatchesRegularExpression('/'.preg_quote($curva, '/').':/', $tokens, "{$modulo}: {$curva} no existe en tokens.css.");

                            if (in_array($animada, self::PROPIEDADES_DE_COLOR, true)) {
                                $this->assertSame('--ease-color', $curva, "{$modulo}: «{$tramo}» funde un color sin la curva de color.");
                                $this->assertSame('--duracion-boton', $duracion, "{$modulo}: «{$tramo}» funde un color fuera del reloj del botón.");
                            }

                            if (in_array($animada, self::PROPIEDADES_DE_MOVIMIENTO, true)) {
                                $this->assertSame('--ease-out', $curva, "{$modulo}: «{$tramo}» mueve algo sin la curva de movimiento.");
                            }
                        }
                    } elseif (str_starts_with($propiedad, 'transition-')) {
                        $this->assertSame('transition-duration: 0ms', "{$propiedad}: {$valor}", "{$modulo}: {$propiedad}: {$valor} no habla en tokens.");

                        foreach ($regla['selectores'] as $selector) {
                            $this->assertMatchesRegularExpression('/:active(?![\w-])/', $selector, "{$modulo}: «{$selector}» anula la duración fuera de un :active.");
                        }
                    }
                }
            }

            $this->assertGreaterThan(0, $tramos, "{$modulo}: la hoja no declara ninguna transición, y la guarda no miró nada.");
        }
    }

    /**
     * Ninguna hoja le pisa a un portador de la casa lo que gobierna. Se recorren
     * las páginas servidas buscando elementos que lleven a la vez una clase de la
     * bolsa y un portador, y ninguna regla de la hoja que alcance esa clase puede
     * declarar lo que el portador gobierna.
     *
     * Es la trampa del CTA del hero de empleo: una `transition` sin capa sobre él
     * le gana a la de `.pulsable`, con su `transition-duration: 0ms` incluido, y
     * el botón baja con retardo. Y es la de los contenedores que entran en
     * pantalla con `.revelar`: una transición de la hoja sobre ellos apaga el
     * fundido.
     *
     * Rotura: `transition: transform 180ms ease` en
     * `.empleo-editorial-hero__cta`; `opacity` en `.proveedores-editorial-panel`.
     */
    public function test_ninguna_hoja_de_las_bolsas_pisa_a_un_portador_de_la_casa(): void
    {
        foreach ($this->paginasDeLasBolsas() as $modulo => $paginas) {
            $reglas = $this->reglasDe($modulo);
            $portados = 0;
            $pisados = [];

            foreach ($paginas as $ruta => $html) {
                foreach ($this->xpathDe($html)->query('//*[@class]') as $elemento) {
                    $clases = $this->clases($elemento);
                    $propias = preg_grep('/^'.$modulo.'-editorial/', $clases);
                    $portadores = array_intersect(array_keys(self::propiedadesDeLosPortadores()), $clases);

                    if ($propias === [] || $portadores === []) {
                        continue;
                    }

                    $portados++;

                    foreach ($reglas as $regla) {
                        foreach ($regla['selectores'] as $selector) {
                            $ultimo = $this->ultimoCompuesto($selector);

                            foreach ($propias as $clase) {
                                if (! preg_match('/\.'.preg_quote($clase, '/').'(?![\w-])/', $ultimo)) {
                                    continue;
                                }

                                foreach ($regla['declaraciones'] as [$propiedad]) {
                                    foreach ($portadores as $portador) {
                                        foreach (self::propiedadesDeLosPortadores()[$portador] as $delPortador) {
                                            if ($propiedad === $delPortador || str_starts_with($propiedad, $delPortador.'-')) {
                                                $pisados[] = "{$ruta}: «{$selector}» declara {$propiedad} sobre .{$clase}, que lleva .{$portador}";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->assertGreaterThan(0, $portados, "{$modulo}: ninguna página sirvió un portador junto a una clase de la bolsa, y la guarda no miró nada.");
            $this->assertSame([], array_values(array_unique($pisados)), "La hoja de {$modulo} pisa a un portador de la casa:\n".implode("\n", array_unique($pisados)));
        }
    }

    /**
     * El rótulo crema de los dos CTA llenos de empleo alcanza el 4,5:1 del texto
     * normal en los dos temas, en reposo y en hover.
     *
     * Con el acento del módulo de relleno, el tema oscuro pinta Pub Red y el
     * rótulo se queda en 3,66:1. La última aserción es el control de que la
     * cuenta recorre de verdad el tema oscuro: con ese relleno tiene que
     * suspender.
     *
     * Rotura: `background: var(--emp-acento)` en cualquiera de los dos.
     */
    public function test_el_rotulo_de_los_cta_de_empleo_se_lee_en_los_dos_temas(): void
    {
        $reglas = $this->reglasDe('empleo');
        $tokens = $this->reglas(File::get(resource_path('css/tokens.css')));

        foreach (['claro', 'oscuro'] as $tema) {
            $variables = $this->variablesDe($tokens, ':root', '.dark', $tema)
                + $this->variablesDe($reglas, '.empleo-editorial', '.dark .empleo-editorial', $tema);

            foreach (['.empleo-editorial-hero__cta', '.empleo-editorial-oferta__cta'] as $cta) {
                $rotuloDeclarado = $this->valorEnTema($reglas, $cta, ['color'], $tema);
                $this->assertNotNull($rotuloDeclarado, "{$cta} no declara el color de su rótulo.");
                $rotulo = $this->resolverColor($rotuloDeclarado, $variables);

                $rellenoEnReposo = $this->valorEnTema($reglas, $cta, ['background', 'background-color'], $tema);
                $this->assertNotNull($rellenoEnReposo, "{$cta} no declara su relleno.");

                $rellenos = [
                    'en reposo' => $rellenoEnReposo,
                    'en hover' => $this->valorEnTema($reglas, "{$cta}:hover", ['background', 'background-color'], $tema) ?? $rellenoEnReposo,
                ];

                foreach ($rellenos as $estado => $relleno) {
                    $color = $this->resolverColor($relleno, $variables);
                    $razon = $this->contraste($rotulo, $color);

                    $this->assertGreaterThanOrEqual(
                        self::MINIMO_TEXTO,
                        $razon,
                        sprintf('%s %s en %s: rótulo %s sobre %s da %.2f:1, y el texto normal pide %.1f:1.', $cta, $estado, $tema, $rotulo, $color, $razon, self::MINIMO_TEXTO)
                    );
                }

                if ($tema === 'oscuro') {
                    $this->assertLessThan(
                        self::MINIMO_TEXTO,
                        $this->contraste($rotulo, $this->resolverColor('var(--emp-acento)', $variables)),
                        'El acento oscuro de empleo ya sostiene el rótulo crema: o la paleta cambió y esta decisión sobra, o la medida no está leyendo el tema oscuro.'
                    );
                }
            }
        }
    }

    /**
     * El foco de las bolsas se dibuja con su acento, y se mide contra su fondo y
     * su superficie en los dos temas: un indicador de foco pide 3:1.
     *
     * Rotura: pintar el foco de artistas con `var(--art-linea)`; oscurecer
     * `--emp-acento` del tema oscuro hasta `#7d1e1b`.
     */
    public function test_el_foco_de_las_bolsas_se_ve_en_los_dos_temas(): void
    {
        foreach (['artistas' => 'art', 'empleo' => 'emp'] as $modulo => $prefijo) {
            $reglas = $this->reglasDe($modulo);
            $trazos = [];

            foreach ($reglas as $regla) {
                if ($this->valor($regla, 'outline') !== null) {
                    $trazos[] = $this->valor($regla, 'outline');
                }
            }

            $this->assertNotEmpty($trazos, "{$modulo}: la hoja no dibuja ningún foco.");
            $this->assertSame(["2px solid var(--{$prefijo}-acento)"], array_values(array_unique($trazos)), "{$modulo}: el foco no se dibuja siempre con el acento de la bolsa.");

            foreach (['claro', 'oscuro'] as $tema) {
                $variables = $this->variablesDe($reglas, ".{$modulo}-editorial", ".dark .{$modulo}-editorial", $tema);
                $trazo = $this->resolverColor("var(--{$prefijo}-acento)", $variables);

                foreach (['bg', 'surface'] as $fondo) {
                    $detras = $this->resolverColor("var(--{$prefijo}-{$fondo})", $variables);
                    $razon = $this->contraste($trazo, $detras);

                    $this->assertGreaterThanOrEqual(
                        self::MINIMO_FOCO,
                        $razon,
                        sprintf('%s en %s: el foco %s sobre --%s-%s (%s) da %.2f:1, y un indicador de foco pide %.1f:1.', $modulo, $tema, $trazo, $prefijo, $fondo, $detras, $razon, self::MINIMO_FOCO)
                    );
                }
            }
        }
    }

    /**
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     */
    private function assertAcuseDeLaHoja(string $modulo, array $reglas, string $componente): void
    {
        $activas = array_filter(
            $this->reglasDelEstado($reglas, $componente, ':active'),
            fn (array $regla): bool => ! $this->dentroDelMovimientoReducido($regla)
        );

        $this->assertNotEmpty($activas, "{$modulo}: {$componente} no acusa el dedo, le falta el :active.");

        $hunden = array_filter(
            $activas,
            fn (array $regla): bool => preg_match('/^scale\(var\(--asb-encogimiento-[a-z]+\)\)$/', $this->valor($regla, 'transform') ?? '') === 1
                && $this->valor($regla, 'transition-duration') === '0ms'
        );

        $this->assertNotEmpty($hunden, "{$modulo}: el :active de {$componente} tiene que encoger con un token de encogimiento y bajar en 0 ms.");

        $tramos = [];

        foreach ($reglas as $regla) {
            if ($regla['medias'] === [] && in_array($componente, $regla['selectores'], true)) {
                array_push($tramos, ...$this->partirPorComas($this->valor($regla, 'transition') ?? ''));
            }
        }

        $this->assertContains(
            'transform var(--duracion-instante) var(--ease-out)',
            $tramos,
            "{$modulo}: {$componente} no devuelve el encogimiento al reloj del acuse."
        );
    }

    /**
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     * @param  array<string, string>  $paginas
     */
    private function assertAcuseDelPortador(string $modulo, array $reglas, string $componente, string $portador, array $paginas): void
    {
        foreach ($reglas as $regla) {
            foreach ($regla['selectores'] as $selector) {
                if (! $this->apuntaA($selector, $componente)) {
                    continue;
                }

                foreach ($regla['declaraciones'] as [$propiedad]) {
                    foreach (self::propiedadesDeLosPortadores()[$portador] as $delPortador) {
                        $this->assertFalse(
                            $propiedad === $delPortador || str_starts_with($propiedad, $delPortador.'-'),
                            "{$modulo}: «{$selector}» declara {$propiedad}, que gobierna .{$portador}, y sin capa se lo pisa."
                        );
                    }
                }
            }
        }

        $servidos = 0;

        foreach ($paginas as $ruta => $html) {
            foreach ($this->xpathDe($html)->query($this->xpathDeSelector($componente)) as $elemento) {
                $servidos++;

                $this->assertContains($portador, $this->clases($elemento), "{$ruta}: {$componente} se sirve sin .{$portador}, y sin él no acusa el dedo.");
            }
        }

        $this->assertGreaterThan(0, $servidos, "{$modulo}: ninguna página pintó {$componente}, y la guarda no miró nada.");
    }

    /**
     * Un campo de texto funde su borde al reloj del botón y no se mueve al
     * pulsarlo.
     *
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     */
    private function assertCampoQueNoEncoge(string $modulo, array $reglas, string $componente): void
    {
        $tramos = [];

        foreach ($reglas as $regla) {
            if ($regla['medias'] === [] && $this->declaraEnReposo($regla, $componente)) {
                array_push($tramos, ...$this->partirPorComas($this->valor($regla, 'transition') ?? ''));
            }
        }

        $this->assertContains(
            'border-color var(--duracion-boton) var(--ease-color)',
            $tramos,
            "{$modulo}: {$componente} no funde su borde al reloj del botón."
        );

        foreach ($this->reglasDelEstado($reglas, $componente, ':active') as $regla) {
            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                $this->assertFalse(
                    in_array($propiedad, self::PROPIEDADES_DE_MOVIMIENTO, true) && $valor !== 'none',
                    "{$modulo}: {$componente} se mueve al pulsarlo ({$propiedad}: {$valor}), y mueve el texto que se selecciona."
                );
            }
        }
    }

    /**
     * Si la hoja le declara la transición al componente en reposo y sin
     * ninguna condición de `@media`.
     *
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     */
    private function declaraTransicion(array $reglas, string $componente): bool
    {
        foreach ($reglas as $regla) {
            if ($regla['medias'] === [] && $this->declaraEnReposo($regla, $componente) && $this->valor($regla, 'transition') !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Si alguno de los selectores de la regla alcanza al componente sin
     * esperar a un estado: las negaciones no cuentan como estado.
     *
     * @param  array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}  $regla
     */
    private function declaraEnReposo(array $regla, string $componente): bool
    {
        foreach ($regla['selectores'] as $selector) {
            if ($this->apuntaA($selector, $componente)
                && preg_match('/:(?:hover|active|focus|focus-visible|focus-within)(?![\w-])/', $this->sinNegaciones($selector)) === 0) {
                return true;
            }
        }

        return false;
    }

    /** Los componentes del registro que son campos de `<x-publico.campo>`. */
    private function esCampo(string $componente): bool
    {
        return preg_match('/\s(?:select|input|textarea)$/', $componente) === 1;
    }

    /**
     * Lo que un componente del registro alcanza en la página y se pinta. Un
     * `input` del registro son los campos de texto: la hoja deja fuera las
     * casillas y los botones de opción.
     *
     * @return list<\DOMElement>
     */
    private function servidos(\DOMXPath $xpath, string $componente): array
    {
        $elementos = [];

        foreach ($xpath->query($this->xpathDeSelector($componente)) as $elemento) {
            if (! $this->sePinta($elemento)
                || ($elemento->tagName === 'input' && in_array(strtolower($elemento->getAttribute('type')), ['checkbox', 'radio'], true))) {
                continue;
            }

            $elementos[] = $elemento;
        }

        return $elementos;
    }

    /**
     * Un campo oculto no se pinta, ni nada que viva bajo `aria-hidden`, que es
     * donde el formulario esconde la trampa de bots.
     */
    private function sePinta(\DOMElement $elemento): bool
    {
        if ($elemento->tagName === 'input' && strtolower($elemento->getAttribute('type')) === 'hidden') {
            return false;
        }

        for ($nodo = $elemento; $nodo instanceof \DOMElement; $nodo = $nodo->parentNode) {
            if ($nodo->getAttribute('aria-hidden') === 'true') {
                return false;
            }
        }

        return true;
    }

    /**
     * Las páginas de las tres bolsas con todo lo pulsable a la vista: una ficha
     * de artista con similares, filtros aplicados para que aparezca «Limpiar», y
     * una vacante de un establecimiento publicado, con WhatsApp y con otra
     * vacante del área para que salgan sus similares.
     *
     * @return array<string, array<string, string>> módulo => [ruta => html]
     */
    private function paginasDeLasBolsas(): array
    {
        $artista = Artista::factory()->publicado()->create();
        Artista::factory()->publicado()->count(2)->create();

        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->publicado()->for($asociado)->create();
        Vacante::factory()->publicado()->for($asociado)->create();

        $rutas = [
            'artistas' => [route('artistas.index'), route('artistas.show', $artista)],
            'proveedores' => [route('proveedores.index')],
            'empleo' => [
                route('empleo.index', ['categoria' => CargoDelSector::Barra->value]),
                route('empleo.show', $vacante),
            ],
        ];

        $paginas = [];

        foreach ($rutas as $modulo => $direcciones) {
            foreach ($direcciones as $direccion) {
                $paginas[$modulo][$direccion] = $this->get($direccion)->assertOk()->getContent();
            }
        }

        return $paginas;
    }

    private function hoja(string $modulo): string
    {
        return File::get(resource_path("css/{$modulo}-editorial.css"));
    }

    /**
     * @return list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>
     */
    private function reglasDe(string $modulo): array
    {
        return $this->reglas($this->hoja($modulo));
    }

    /**
     * Las reglas de una hoja en orden de aparición, cada una con los at-rules que
     * la envuelven, de fuera hacia dentro.
     *
     * Basta recorrer llaves: estas hojas no anidan reglas dentro de reglas ni
     * llevan llaves dentro de cadenas. Un `@theme` o un `@custom-variant` de
     * `tokens.css` pasan de largo sin dejar reglas.
     *
     * @return list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>
     */
    private function reglas(string $css): array
    {
        $css = $this->sinComentarios($css);
        $reglas = [];
        $medias = [];
        $inicio = 0;
        $largo = strlen($css);

        for ($i = 0; $i < $largo; $i++) {
            if ($css[$i] === ';') {
                $inicio = $i + 1;
            } elseif ($css[$i] === '}') {
                array_pop($medias);
                $inicio = $i + 1;
            } elseif ($css[$i] === '{') {
                $preludio = preg_replace('/\s+/', ' ', trim(substr($css, $inicio, $i - $inicio)));

                if (str_starts_with($preludio, '@')) {
                    $medias[] = $preludio;
                    $inicio = $i + 1;

                    continue;
                }

                $cierre = strpos($css, '}', $i);
                $this->assertNotFalse($cierre, "La regla «{$preludio}» no cierra.");

                $reglas[] = [
                    'selectores' => $this->partirPorComas($preludio),
                    'declaraciones' => $this->declaraciones(substr($css, $i + 1, $cierre - $i - 1)),
                    'medias' => $medias,
                ];

                $i = $cierre;
                $inicio = $cierre + 1;
            }
        }

        return $reglas;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function declaraciones(string $cuerpo): array
    {
        $declaraciones = [];

        foreach (explode(';', $cuerpo) as $declaracion) {
            $partes = explode(':', $declaracion, 2);

            if (count($partes) !== 2 || trim($partes[0]) === '') {
                continue;
            }

            $propiedad = trim($partes[0]);

            $declaraciones[] = [
                str_starts_with($propiedad, '--') ? $propiedad : strtolower($propiedad),
                preg_replace('/\s+/', ' ', trim($partes[1])),
            ];
        }

        return $declaraciones;
    }

    /**
     * Parte por las comas de primer nivel: las de dentro de `var()`, `:not()` o
     * un selector de atributo no separan nada.
     *
     * @return list<string>
     */
    private function partirPorComas(string $texto): array
    {
        $partes = [];
        $actual = '';
        $nivel = 0;

        foreach (str_split($texto) as $caracter) {
            if ($caracter === '(' || $caracter === '[') {
                $nivel++;
            } elseif ($caracter === ')' || $caracter === ']') {
                $nivel--;
            } elseif ($caracter === ',' && $nivel === 0) {
                $partes[] = $actual;
                $actual = '';

                continue;
            }

            $actual .= $caracter;
        }

        $partes[] = $actual;

        return array_values(array_filter(
            array_map(fn (string $parte): string => preg_replace('/\s+/', ' ', trim($parte)), $partes),
            fn (string $parte): bool => $parte !== ''
        ));
    }

    private function sinComentarios(string $css): string
    {
        return preg_replace('#/\*.*?\*/#s', '', $css);
    }

    /**
     * Quita del CSS los bloques de puntero fino completos, contando llaves para
     * respetar el anidamiento. Lo que prueba la guarda no es que el `:hover` y la
     * puerta existan en el archivo —eso pasaría con los dos en extremos
     * opuestos— sino que el `:hover` esté dentro. Lo que sobrevive a esta poda
     * es lo que queda fuera.
     */
    private function sinBloquesDeHoverFino(string $css): string
    {
        while (($inicio = strpos($css, self::PUERTA_DE_PUNTERO_FINO)) !== false) {
            $llave = strpos($css, '{', $inicio);

            if ($llave === false) {
                break;
            }

            $nivel = 0;
            $fin = null;

            for ($i = $llave, $largo = strlen($css); $i < $largo; $i++) {
                if ($css[$i] === '{') {
                    $nivel++;
                } elseif ($css[$i] === '}') {
                    $nivel--;

                    if ($nivel === 0) {
                        $fin = $i;

                        break;
                    }
                }
            }

            if ($fin === null) {
                break;
            }

            $css = substr($css, 0, $inicio).substr($css, $fin + 1);
        }

        return $css;
    }

    /**
     * @param  iterable<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     * @param  list<string>  $propiedades
     * @return list<string>
     */
    private function valores(iterable $reglas, array $propiedades): array
    {
        $valores = [];

        foreach ($reglas as $regla) {
            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                if (in_array($propiedad, $propiedades, true)) {
                    $valores[] = $valor;
                }
            }
        }

        return $valores;
    }

    /**
     * El último valor que una regla declara para una propiedad, que es el que
     * gana dentro de la regla.
     *
     * @param  array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}  $regla
     */
    private function valor(array $regla, string $propiedad): ?string
    {
        $encontrado = null;

        foreach ($regla['declaraciones'] as [$nombre, $valor]) {
            if ($nombre === $propiedad) {
                $encontrado = $valor;
            }
        }

        return $encontrado;
    }

    /**
     * Si el selector alcanza al componente mismo, en cualquier estado y en
     * cualquiera de los dos temas: fuera las pseudoclases y el `.dark` del
     * principio, tiene que quedar el componente tal cual.
     */
    private function apuntaA(string $selector, string $componente): bool
    {
        return preg_replace('/^\.dark\s+/', '', $this->sinPseudoclases($selector)) === $componente;
    }

    private function sinPseudoclases(string $selector): string
    {
        return trim(preg_replace('/::?[\w-]+(?:\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\))?/', '', $selector));
    }

    /** Sin las negaciones, para que `:not(:hover)` no cuente como un hover. */
    private function sinNegaciones(string $selector): string
    {
        return preg_replace('/:not\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\)/', '', $selector);
    }

    /** El último compuesto del selector: el elemento sobre el que cae la regla. */
    private function ultimoCompuesto(string $selector): string
    {
        $pasos = preg_split('/\s*[>+~]\s*|\s+/', trim(preg_replace('/:[\w-]+\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\)/', '', $selector)));

        return (string) end($pasos);
    }

    /**
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     * @return list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>
     */
    private function reglasDelEstado(array $reglas, string $componente, string $pseudo): array
    {
        return array_values(array_filter($reglas, function (array $regla) use ($componente, $pseudo): bool {
            foreach ($regla['selectores'] as $selector) {
                if ($this->apuntaA($selector, $componente)
                    && preg_match('/'.preg_quote($pseudo, '/').'(?![\w-])/', $this->sinNegaciones($selector))) {
                    return true;
                }
            }

            return false;
        }));
    }

    /**
     * @param  array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}  $regla
     */
    private function dentroDelMovimientoReducido(array $regla): bool
    {
        foreach ($regla['medias'] as $media) {
            if (str_contains($media, self::MOVIMIENTO_REDUCIDO)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Las propiedades personalizadas que valen en un tema: las de la raíz y, en
     * oscuro, encima las de la raíz oscura. Las de dentro de un `@media` o un
     * `@supports` no cuentan, porque dependen de otra preferencia.
     *
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     * @return array<string, string>
     */
    private function variablesDe(array $reglas, string $raiz, string $raizOscura, string $tema): array
    {
        $variables = [];

        foreach ($tema === 'oscuro' ? [$raiz, $raizOscura] : [$raiz] as $selector) {
            foreach ($reglas as $regla) {
                if ($regla['medias'] !== [] || ! in_array($selector, $regla['selectores'], true)) {
                    continue;
                }

                foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                    if (str_starts_with($propiedad, '--')) {
                        $variables[$propiedad] = $valor;
                    }
                }
            }
        }

        return $variables;
    }

    /**
     * El valor que gana para un selector en un tema: el de la regla del
     * selector y, en oscuro, el de `.dark` delante si lo hay, que pesa más. La
     * puerta de puntero fino no cuenta como condición, porque es donde viven
     * los hover; cualquier otro `@media` sí.
     *
     * @param  list<array{selectores: list<string>, declaraciones: list<array{0: string, 1: string}>, medias: list<string>}>  $reglas
     * @param  list<string>  $propiedades
     */
    private function valorEnTema(array $reglas, string $selector, array $propiedades, string $tema): ?string
    {
        $encontrado = null;

        foreach ($tema === 'oscuro' ? [$selector, ".dark {$selector}"] : [$selector] as $candidato) {
            foreach ($reglas as $regla) {
                if (array_diff($regla['medias'], [self::PUERTA_DE_PUNTERO_FINO]) !== []
                    || ! in_array($candidato, $regla['selectores'], true)) {
                    continue;
                }

                foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                    if (in_array($propiedad, $propiedades, true)) {
                        $encontrado = $valor;
                    }
                }
            }
        }

        return $encontrado;
    }

    /**
     * Sigue las referencias `var()` hasta un hexadecimal opaco. Un color con alfa
     * o una mezcla no se pueden medir sin saber qué tienen detrás, y la prueba lo
     * dice en vez de aprobar a ciegas.
     *
     * @param  array<string, string>  $variables
     */
    private function resolverColor(string $valor, array $variables): string
    {
        for ($vueltas = 0; preg_match('/^var\((--[\w-]+)\)$/', trim($valor), $referencia) === 1; $vueltas++) {
            $this->assertLessThan(10, $vueltas, "Referencia circular resolviendo {$valor}.");
            $this->assertArrayHasKey($referencia[1], $variables, "No se encuentra {$referencia[1]} para medir.");

            $valor = $variables[$referencia[1]];
        }

        $valor = strtolower(trim($valor));

        if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/', $valor, $corto) === 1) {
            $valor = "#{$corto[1]}{$corto[1]}{$corto[2]}{$corto[2]}{$corto[3]}{$corto[3]}";
        }

        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $valor, "«{$valor}» no es un color opaco que se pueda medir.");

        return $valor;
    }

    /** El documento servido, listo para consultar por XPath. */
    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    /**
     * Traduce los selectores del registro al árbol. Solo sabe de clases, de
     * etiquetas y del combinador descendiente, que es todo lo que el registro
     * usa; cualquier otra cosa falla en vez de traducirse mal.
     */
    private function xpathDeSelector(string $selector): string
    {
        $xpath = '';

        foreach (preg_split('/\s+/', trim($selector)) as $paso) {
            $this->assertMatchesRegularExpression('/^(?:\.[\w-]+|[a-z]+)$/', $paso, "El selector «{$selector}» no se sabe traducir al árbol.");

            $xpath .= str_starts_with($paso, '.')
                ? "//*[contains(concat(' ', normalize-space(@class), ' '), ' ".substr($paso, 1)." ')]"
                : '//'.$paso;
        }

        return $xpath;
    }

    /**
     * @return list<string>
     */
    private function clases(\DOMElement $elemento): array
    {
        return preg_split('/\s+/', trim($elemento->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY);
    }
}

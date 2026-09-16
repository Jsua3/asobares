<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * El Gremio es editorial en la composición y habla el idioma pulsable del resto
 * del sitio.
 *
 * `gremio-editorial.css` compone /quienes-somos, /boletin y /contacto con
 * bandas, folios y filetes. Lo que se pulsa en esas páginas —la pieza principal,
 * la secundaria y el índice del boletín, sus filtros, «Volver», los motivos y
 * los canales de /contacto— redondea con la escala de la casa y responde a las
 * tres manos: al puntero tras la puerta de puntero fino, al dedo con un
 * encogimiento que `tokens.css` anula con movimiento reducido, y al teclado con
 * un anillo que se despega de lo que tiene detrás.
 *
 * Las guardias leen la hoja fuente con un analizador que cuenta llaves, porque
 * lo que se prueba es DÓNDE vive cada regla: un `:hover` dentro de la puerta y
 * otro fuera tienen el mismo texto, y buscar la cadena da a los dos por buenos.
 *
 * Lo que esta clase no ve es el píxel. Dónde cae la superficie, qué punto recibe
 * el toque y si el texto se mueve se mide en el navegador; esto impide que lo
 * medido se deshaga en una edición.
 */
class MovimientoEditorialDelGremioTest extends TestCase
{
    use MideContraste;

    private const PUERTA_DE_PUNTERO_FINO = '@media (hover: hover) and (pointer: fine)';

    private const MOVIMIENTO_REDUCIDO = '@media (prefers-reduced-motion: reduce)';

    /** Celdas y tarjetas, piezas grandes y pastillas. */
    private const ESCALA_DE_RADIOS = ['0.75rem', '1rem', '999px'];

    /** Lo que funde al reloj del botón y con la curva del color. */
    private const PROPIEDADES_DE_COLOR = ['color', 'background-color', 'border-color', 'box-shadow', 'text-decoration-color'];

    /**
     * Cada pieza que la hoja dibuja entera: la regla de su superficie, el radio
     * de la escala que le toca y los selectores de sus tres respuestas.
     *
     * El canal de /contacto tiene otra forma, y a propósito: la celda lleva los
     * filetes, así que su superficie es el `::before` de la celda, y lo que se
     * enfoca es el enlace, así que el anillo es el suyo.
     *
     * @return array<string, array{0: array{superficie: string, radio: string, hover: string, active: string, foco: string, anillo: string}}>
     */
    public static function piezasPulsables(): array
    {
        return [
            'la pieza principal del boletín' => [[
                'superficie' => '.gremio-editorial-lead',
                'radio' => '1rem',
                'hover' => '.gremio-editorial-lead:hover',
                'active' => '.gremio-editorial-lead:active',
                'foco' => '.gremio-editorial-lead:focus-visible',
                'anillo' => '.gremio-editorial-lead:focus-visible',
            ]],
            'la pieza secundaria del boletín' => [[
                'superficie' => '.gremio-editorial-secundaria',
                'radio' => '0.75rem',
                'hover' => '.gremio-editorial-secundaria:hover',
                'active' => '.gremio-editorial-secundaria:active',
                'foco' => '.gremio-editorial-secundaria:focus-visible',
                'anillo' => '.gremio-editorial-secundaria:focus-visible',
            ]],
            'las filas del índice de piezas' => [[
                'superficie' => '.gremio-editorial-indice-piezas a',
                'radio' => '0.75rem',
                'hover' => '.gremio-editorial-indice-piezas a:hover',
                'active' => '.gremio-editorial-indice-piezas a:active',
                'foco' => '.gremio-editorial-indice-piezas a:focus-visible',
                'anillo' => '.gremio-editorial-indice-piezas a:focus-visible',
            ]],
            'volver al boletín' => [[
                'superficie' => '.gremio-editorial-retorno',
                'radio' => '999px',
                'hover' => '.gremio-editorial-retorno:hover',
                'active' => '.gremio-editorial-retorno:active',
                'foco' => '.gremio-editorial-retorno:focus-visible',
                'anillo' => '.gremio-editorial-retorno:focus-visible',
            ]],
            'los motivos de contacto' => [[
                'superficie' => '.gremio-editorial-motivos a',
                'radio' => '999px',
                'hover' => '.gremio-editorial-motivos a:hover',
                'active' => '.gremio-editorial-motivos a:active',
                'foco' => '.gremio-editorial-motivos a:focus-visible',
                'anillo' => '.gremio-editorial-motivos a:focus-visible',
            ]],
            'las celdas de canales de contacto' => [[
                'superficie' => '.gremio-editorial-canal:has(a)::before',
                'radio' => '0.75rem',
                'hover' => '.gremio-editorial-canal:has(a:hover)::before',
                'active' => '.gremio-editorial-canal:has(a:active)::before',
                'foco' => '.gremio-editorial-canal:has(a:focus-visible)::before',
                'anillo' => '.gremio-editorial-canal a:focus-visible',
            ]],
        ];
    }

    // --- A. Superficie: nada pulsable queda recto ---

    /**
     * La superficie en reposo declara el radio de su tamaño. Se lee en reposo y
     * no en cualquier regla: un radio que solo existe bajo el puntero deja la
     * pieza recta en táctil.
     *
     * @param  array{superficie: string, radio: string, hover: string, active: string, foco: string, anillo: string}  $pieza
     */
    #[DataProvider('piezasPulsables')]
    public function test_ninguna_superficie_pulsable_queda_recta(array $pieza): void
    {
        $radio = $this->ultimoValor($this->enReposo($this->reglasDe($pieza['superficie'])), 'border-radius');

        $this->assertSame(
            $pieza['radio'],
            $radio,
            "`{$pieza['superficie']}` es pulsable y en reposo tiene que redondear con {$pieza['radio']}."
        );
    }

    /**
     * Ningún radio de la hoja sale de la escala de la casa, y el cero tampoco:
     * es la forma de dejar recta una pieza sin quitarle la declaración.
     *
     * Las fotos de las piezas cuentan aunque no se pulsen solas: ninguna va
     * dentro de un contenedor que las recorte, así que si no redondean ellas, no
     * redondea nadie.
     */
    public function test_la_hoja_solo_usa_radios_de_la_escala_de_la_casa(): void
    {
        $fueraDeEscala = [];
        $declarados = 0;

        foreach ($this->reglasDeLaHoja() as $regla) {
            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                if (! str_contains($propiedad, 'radius')) {
                    continue;
                }

                $declarados++;

                if (! in_array($valor, self::ESCALA_DE_RADIOS, true)) {
                    $fueraDeEscala[] = implode(', ', $regla['selectores'])." → {$propiedad}: {$valor}";
                }
            }
        }

        $this->assertGreaterThan(0, $declarados, 'La hoja no declara ningún radio: o no se leyó, o todo volvió a ser recto.');
        $this->assertSame([], $fueraDeEscala, "Radios fuera de la escala de la casa:\n".implode("\n", $fueraDeEscala));

        foreach (['.gremio-editorial-lead img', '.gremio-editorial-secundaria img', '.gremio-editorial-lectura img'] as $foto) {
            $this->assertNotNull(
                $this->ultimoValor($this->enReposo($this->reglasDe($foto)), 'border-radius'),
                "`{$foto}` no va dentro de nada que la recorte: el radio tiene que ser suyo."
            );
        }
    }

    // --- B. Respuesta: puntero, dedo y teclado ---

    /**
     * Las tres manos, cada una donde le corresponde.
     *
     * El `:hover` va tras la puerta y cambia color, papel o filo. El `:active`
     * va FUERA de ella —en táctil es el único acuse que existe— y encoge con un
     * token de encogimiento, bajando en 0 ms y subiendo con el reloj que declara
     * la superficie en reposo. El anillo es de 2 px en el acento de la hoja.
     *
     * @param  array{superficie: string, radio: string, hover: string, active: string, foco: string, anillo: string}  $pieza
     */
    #[DataProvider('piezasPulsables')]
    public function test_cada_pieza_pulsable_responde_al_puntero_al_dedo_y_al_teclado(array $pieza): void
    {
        $hover = $this->reglasDe($pieza['hover']);
        $this->assertNotEmpty($hover, "`{$pieza['hover']}` no existe: la pieza no responde al puntero.");

        foreach ($hover as $regla) {
            $this->assertContains(self::PUERTA_DE_PUNTERO_FINO, $regla['contexto'], "`{$pieza['hover']}` está fuera de la puerta de puntero fino.");
        }

        $this->assertNotEmpty(
            array_intersect(self::PROPIEDADES_DE_COLOR, $this->propiedades($hover)),
            "`{$pieza['hover']}` no cambia ni color, ni papel, ni filo."
        );

        $active = $this->reglasDe($pieza['active']);
        $this->assertNotEmpty($active, "`{$pieza['active']}` no existe: en táctil la pieza no acusa el dedo.");

        foreach ($active as $regla) {
            $this->assertNotContains(self::PUERTA_DE_PUNTERO_FINO, $regla['contexto'], "`{$pieza['active']}` no puede ir tras la puerta: en táctil no llegaría nunca.");
        }

        $this->assertMatchesRegularExpression(
            '/^scale\(var\(--asb-encogimiento-(tarjeta|control)\)\)$/',
            (string) $this->ultimoValor($active, 'transform'),
            "`{$pieza['active']}` tiene que encoger con un token de encogimiento, que es lo que el movimiento reducido anula."
        );
        $this->assertSame('0ms', $this->ultimoValor($active, 'transition-duration'), "`{$pieza['active']}` tiene que bajar sin retardo.");

        $this->assertStringContainsString(
            'transform var(--duracion-instante) var(--ease-out)',
            (string) $this->ultimoValor($this->enReposo($this->reglasDe($pieza['superficie'])), 'transition'),
            "`{$pieza['superficie']}` no declara el reloj de la subida: al soltar, la pieza saltaría."
        );

        $foco = $this->reglasDe($pieza['foco']);
        $this->assertNotEmpty($foco, "`{$pieza['foco']}` no existe: la pieza no responde al teclado.");
        $this->assertNotEmpty(
            array_intersect(['outline', ...self::PROPIEDADES_DE_COLOR], $this->propiedades($foco)),
            "`{$pieza['foco']}` existe pero no dibuja nada."
        );

        $anillo = $this->reglasDe($pieza['anillo']);
        $this->assertSame('2px solid var(--grem-acento)', $this->ultimoValor($anillo, 'outline'), "`{$pieza['anillo']}` no dibuja el anillo de la hoja.");
        $this->assertSame('3px', $this->ultimoValor($anillo, 'outline-offset'), "`{$pieza['anillo']}` pega el anillo a la pieza.");
    }

    /**
     * En táctil el `:hover` se queda pegado después del toque: la pieza se queda
     * encendida como si estuviera elegida. Ninguno de la hoja, y no solo los de
     * las piezas de arriba, puede vivir fuera de la puerta.
     */
    public function test_todo_hover_de_la_hoja_va_tras_la_puerta_de_puntero_fino(): void
    {
        $conHover = 0;
        $fuera = [];

        foreach ($this->reglasDeLaHoja() as $regla) {
            if (! str_contains(implode(', ', $regla['selectores']), ':hover')) {
                continue;
            }

            $conHover++;

            if (! in_array(self::PUERTA_DE_PUNTERO_FINO, $regla['contexto'], true)) {
                $fuera[] = implode(', ', $regla['selectores']);
            }
        }

        $this->assertGreaterThan(0, $conHover, 'La hoja no tiene ni un :hover: la guardia no tendría nada que mirar.');
        $this->assertSame([], $fuera, "Hover fuera de la puerta de puntero fino:\n".implode("\n", $fuera));
    }

    /**
     * Cada tramo de cada `transition` dice su reloj con los tokens, y el reloj
     * depende de lo que se mueve: el color funde con el del botón y la curva del
     * color, el encogimiento sube con el instante y la curva de salida, y la
     * opacidad y el desplazamiento son los de los portadores de `app.css` que la
     * hoja repite. Una duración escrita a mano no cambia cuando cambia la casa.
     */
    public function test_las_transiciones_dicen_su_reloj_con_los_tokens_de_la_casa(): void
    {
        $relojes = [
            'transform' => ['instante', 'out'],
            'opacity' => ['instante|panel', 'out'],
            'translate' => ['panel', 'out'],
            ...array_fill_keys(self::PROPIEDADES_DE_COLOR, ['boton', 'color']),
        ];

        $tramos = 0;
        $fallos = [];

        foreach ($this->reglasDeLaHoja() as $regla) {
            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                $selector = implode(', ', $regla['selectores']);

                if ($propiedad === 'transition-duration' && $valor !== '0ms') {
                    $fallos[] = "{$selector} → transition-duration: {$valor}";
                }

                if ($propiedad !== 'transition') {
                    continue;
                }

                foreach ($this->partirEnNivelCero($valor, ',') as $tramo) {
                    $tramos++;
                    [$animada] = explode(' ', $tramo);

                    if (! isset($relojes[$animada])) {
                        $fallos[] = "{$selector} → `{$tramo}` anima una propiedad sin reloj en la casa";

                        continue;
                    }

                    [$duracion, $curva] = $relojes[$animada];

                    if (! preg_match("/^{$animada} var\(--duracion-({$duracion})\) var\(--ease-{$curva}\)$/", $tramo)) {
                        $fallos[] = "{$selector} → `{$tramo}`";
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $tramos, 'La hoja no declara ninguna transición: nada de lo pulsable se mueve.');
        $this->assertSame([], $fallos, "Relojes fuera de los tokens de la casa:\n".implode("\n", $fallos));
    }

    /**
     * Esta hoja no va en ninguna capa, así que un `transition` suyo pisa ENTERO
     * al de un portador de `@layer components` sobre el mismo elemento, sin que
     * cuente la especificidad. Donde una vista junta una clase de la hoja con un
     * portador, la transición de la hoja repite los tramos del portador: sin
     * ellos, la pieza principal aparece de golpe en vez de revelarse, y «Volver»
     * deja de fundir el color.
     *
     * `.pulsable` va aparte porque sus portadores no llevan clase de la hoja:
     * los filtros del boletín se alcanzan por descendencia, y ahí la hoja no
     * declara ni `transition` ni `transform`, que son del portador.
     */
    public function test_ninguna_transicion_de_la_hoja_pisa_a_un_portador_de_app_css(): void
    {
        $tramosDelPortador = [
            'revelar' => ['opacity var(--duracion-panel) var(--ease-out)', 'translate var(--duracion-panel) var(--ease-out)'],
            'enlace-accion' => ['color var(--duracion-boton) var(--ease-color)', 'opacity var(--duracion-instante) var(--ease-out)'],
        ];

        $parejas = [];

        foreach ($this->vistasDelGremio() as $vista) {
            foreach ($this->listasDeClases(File::get($vista)) as $lista) {
                $clases = preg_split('/\s+/', trim($lista)) ?: [];

                foreach (array_intersect(array_keys($tramosDelPortador), $clases) as $portador) {
                    foreach ($clases as $clase) {
                        if (! str_starts_with($clase, 'gremio-editorial-')) {
                            continue;
                        }

                        $transicion = $this->ultimoValor($this->enReposo($this->reglasDe('.'.$clase)), 'transition');

                        if ($transicion === null) {
                            continue;
                        }

                        $parejas[] = "{$clase} + {$portador}";

                        foreach ($tramosDelPortador[$portador] as $tramo) {
                            $this->assertStringContainsString($tramo, $transicion, "`.{$clase}` viaja con `.{$portador}` y su transición pisa la del portador sin repetir `{$tramo}`.");
                        }
                    }
                }
            }
        }

        $this->assertContains('gremio-editorial-lead + revelar', $parejas, 'La guardia no encontró la pieza principal con `.revelar`: no está mirando nada.');
        $this->assertContains('gremio-editorial-retorno + enlace-accion', $parejas, 'La guardia no encontró «Volver» con `.enlace-accion`: no está mirando nada.');

        foreach ($this->reglasDeLaHoja() as $regla) {
            foreach ($regla['selectores'] as $selector) {
                if (! str_starts_with($selector, '.gremio-editorial-indice a')) {
                    continue;
                }

                $propiedades = array_column($regla['declaraciones'], 0);
                $this->assertNotContains('transition', $propiedades, "`{$selector}` pisa la transición de `.pulsable`.");
                $this->assertNotContains('transform', $propiedades, "`{$selector}` pisa el encogimiento de `.pulsable`.");
            }
        }
    }

    /**
     * Los filtros del boletín son pastillas de `.pulsable`, que pone el
     * encogimiento, con el radio de celda en la propia vista. La hoja les da lo
     * que al portador le falta: la respuesta al puntero, el tinte del dedo y el
     * anillo de la hoja.
     *
     * Se mira cada enlace del índice y no «alguna lista con `pulsable`»: los
     * filtros son dos plantillas, «Todas» y la de cada categoría, y perder el
     * portador en una sola deja la otra en verde.
     */
    public function test_los_filtros_del_boletin_responden_con_su_portador(): void
    {
        $vista = File::get(resource_path('views/publico/boletin/index.blade.php'));

        $this->assertSame(1, preg_match('/<nav class="gremio-editorial-indice.*?<\/nav>/s', $vista, $indice), 'La vista del boletín ya no pinta su índice de filtros.');

        $enlaces = preg_split('/<a\s/', $indice[0]) ?: [];
        array_shift($enlaces);

        $this->assertNotEmpty($enlaces, 'El índice del boletín no tiene enlaces: la guardia no está mirando nada.');

        foreach ($enlaces as $enlace) {
            $clases = implode(' ', $this->listasDeClases((string) strstr($enlace, '</a>', true)));

            $this->assertMatchesRegularExpression('/(^|\s)pulsable(\s|$)/', $clases, 'Un filtro del boletín no lleva `.pulsable`: no encoge al pulsar.');
            $this->assertMatchesRegularExpression('/(^|\s)rounded-xl(\s|$)/', $clases, 'Un filtro del boletín no redondea: es una celda pulsable y le toca 0.75rem.');
        }

        foreach ($this->reglasDe('.gremio-editorial-indice a:hover') as $regla) {
            $this->assertContains(self::PUERTA_DE_PUNTERO_FINO, $regla['contexto']);
        }

        $this->assertNotNull($this->ultimoValor($this->reglasDe('.gremio-editorial-indice a:hover'), 'background-color'), 'Los filtros no responden al puntero.');
        $this->assertSame('var(--asb-fila-pulsada)', $this->ultimoValor($this->reglasDe('.gremio-editorial-indice a:active'), 'background-color'), 'Los filtros no se tiñen bajo el dedo.');
        $this->assertSame('2px solid var(--grem-acento)', $this->ultimoValor($this->reglasDe('.gremio-editorial-indice a:focus-visible'), 'outline'));
    }

    // --- C. Movimiento reducido ---

    /**
     * Movimiento reducido apaga lo que se mueve y solo eso.
     *
     * Lo que se mueve en esta hoja sale de tokens que `tokens.css` pone a su
     * identidad —1 los encogimientos, cero los desplazamientos—, así que aquí se
     * comprueba el mecanismo entero: que ningún `transform` ni `translate` lleva
     * una cifra escrita a mano, y que cada token que usa está anulado en
     * `tokens.css`. La animación de entrada del folio no tiene token, y la apaga
     * el bloque de la hoja.
     *
     * Y el bloque no apaga nada más: el color, el papel y el subrayado no son
     * movimiento, y sin ellos el puntero se queda sin respuesta. Tampoco toca el
     * reloj, que es lo que deja fundir al color.
     */
    public function test_el_movimiento_reducido_apaga_lo_que_se_mueve_y_deja_color_y_subrayado(): void
    {
        $reglas = $this->reglasDeLaHoja();
        $anulados = $this->anuladosPorTokensConMovimientoReducido();
        $bloque = array_values(array_filter($reglas, fn (array $regla): bool => in_array(self::MOVIMIENTO_REDUCIDO, $regla['contexto'], true)));

        $this->assertNotEmpty($bloque, 'La hoja no tiene bloque de movimiento reducido.');

        $noSonMovimiento = [];

        foreach ($bloque as $regla) {
            foreach ($regla['declaraciones'] as [$propiedad]) {
                if (preg_match('/^(color|background|border|outline|box-shadow|text-decoration|opacity|transition)/', $propiedad)) {
                    $noSonMovimiento[] = implode(', ', $regla['selectores'])." → {$propiedad}";
                }
            }
        }

        $this->assertSame([], $noSonMovimiento, "El bloque de movimiento reducido apaga algo que no se mueve:\n".implode("\n", $noSonMovimiento));

        $movimientos = 0;

        foreach ($reglas as $regla) {
            if (in_array(self::MOVIMIENTO_REDUCIDO, $regla['contexto'], true) || $this->dentroDeFotogramas($regla)) {
                continue;
            }

            $selector = implode(', ', $regla['selectores']);

            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                if (in_array($propiedad, ['animation', 'animation-name'], true) && $valor !== 'none') {
                    $this->assertSame(
                        'none',
                        $this->ultimoValor(array_filter($bloque, fn (array $apagada): bool => $apagada['selectores'] === $regla['selectores']), 'animation'),
                        "`{$selector}` anima y el bloque de movimiento reducido no la apaga."
                    );
                }

                if (! in_array($propiedad, ['transform', 'translate', 'scale', 'rotate'], true) || $valor === 'none') {
                    continue;
                }

                $movimientos++;
                preg_match_all('/var\((--[\w-]+)\)/', $valor, $tokens);

                $this->assertNotEmpty($tokens[1], "`{$selector}` mueve con `{$propiedad}: {$valor}` sin ningún token que el movimiento reducido pueda anular.");

                preg_match_all('/-?\d*\.?\d+[a-z%]*/i', preg_replace('/var\(--[\w-]+\)/', '', $valor), $cifras);
                $this->assertSame([], array_values(array_diff($cifras[0], ['0', '-1'])), "`{$selector}` mueve con una cifra escrita a mano en `{$propiedad}: {$valor}`.");

                foreach ($tokens[1] as $token) {
                    $identidad = str_contains($valor, "scale(var({$token}))") ? ['1'] : ['0', '0px', '0%'];

                    $this->assertContains(
                        $anulados[$token] ?? null,
                        $identidad,
                        "`{$token}` mueve `{$selector}` y `tokens.css` no lo anula con movimiento reducido."
                    );
                }
            }
        }

        $this->assertGreaterThan(0, $movimientos, 'La hoja no encoge ni desplaza nada: el acuse al pulsar desapareció.');
    }

    // --- D. Formas que se pierden en silencio ---

    /**
     * En la celda de canal lo que se enciende es exactamente lo que responde.
     *
     * La superficie y el área que recibe el toque salen de la MISMA regla, con
     * la misma caja, así que no pueden separarse. La superficie no recibe el
     * toque y va por debajo del texto; la celda la aísla para que ese «por
     * debajo» no atraviese la página. Y se enciende con `:has(a:…)`: el `:hover`
     * de la celda también cubre la franja que la separa del filete, que no lleva
     * a ninguna parte.
     */
    public function test_la_celda_de_canal_se_enciende_donde_responde(): void
    {
        $compartida = array_values(array_filter(
            $this->enReposo($this->reglasDe('.gremio-editorial-canal a::after')),
            fn (array $regla): bool => in_array('.gremio-editorial-canal:has(a)::before', $regla['selectores'], true)
        ));

        $this->assertNotEmpty($compartida, 'La superficie del canal y el área de su enlace no salen de la misma regla: pueden separarse.');
        $this->assertSame('var(--grem-canal-superficie)', $this->ultimoValor($compartida, 'inset'));
        $this->assertSame("''", $this->ultimoValor($compartida, 'content'));
        $this->assertSame('absolute', $this->ultimoValor($compartida, 'position'));

        $celda = $this->enReposo($this->reglasDe('.gremio-editorial-canal'));
        $this->assertSame('relative', $this->ultimoValor($celda, 'position'), 'Sin `position: relative` la superficie y el área del enlace cuelgan de otro sitio.');
        $this->assertSame('isolate', $this->ultimoValor($celda, 'isolation'), 'Sin aislar la celda, la superficie de `z-index: -1` se va detrás de la página.');
        $this->assertNotNull($this->ultimoValor($celda, '--grem-canal-superficie'));

        $superficie = $this->enReposo($this->reglasDe('.gremio-editorial-canal:has(a)::before'));
        $this->assertSame('none', $this->ultimoValor($superficie, 'pointer-events'), 'La superficie le robaría el toque al enlace.');
        $this->assertSame('-1', $this->ultimoValor($superficie, 'z-index'), 'La superficie taparía el texto.');

        foreach ($this->reglasDeLaHoja() as $regla) {
            foreach ($regla['selectores'] as $selector) {
                $this->assertDoesNotMatchRegularExpression('/\.gremio-editorial-canal(--[\w-]+)?:(hover|active)/', $selector, 'La celda se enciende por la franja que no lleva a ninguna parte.');
            }
        }
    }

    /**
     * El filete al pie de la pieza principal es el canto de su papel: una sombra
     * interior de un píxel. Tiene que seguir ahí bajo el puntero, que es cuando
     * `box-shadow` cambia, porque un hover que reescribe la sombra sin él borra
     * el filete justo mientras se mira.
     */
    public function test_la_pieza_principal_conserva_su_filete_bajo_el_puntero(): void
    {
        $filete = 'inset 0 -1px 0 var(--grem-linea)';

        $this->assertStringContainsString($filete, (string) $this->ultimoValor($this->enReposo($this->reglasDe('.gremio-editorial-lead')), 'box-shadow'));
        $this->assertStringContainsString($filete, (string) $this->ultimoValor($this->reglasDe('.gremio-editorial-lead:hover'), 'box-shadow'));
    }

    /**
     * El anillo de la hoja se despega de lo que tiene detrás en los dos temas.
     *
     * Se mide contra el fondo y contra el papel elevado porque en el canal el
     * anillo cae SOBRE el papel encendido. Medido: en claro, 5,56:1 sobre el
     * fondo y 4,93:1 sobre el papel; en oscuro, 4,88:1 y 4,62:1. El rojo de marca
     * del sitio no sirve aquí: en claro, sobre el papel, se queda en 2,92:1. Por
     * eso ningún anillo de la hoja usa otro color.
     */
    public function test_el_anillo_de_la_hoja_alcanza_3_a_1_sobre_el_fondo_y_sobre_el_papel(): void
    {
        $css = File::get(resource_path('css/gremio-editorial.css'));

        foreach (['.gremio-editorial' => 'claro', '.dark .gremio-editorial' => 'oscuro'] as $selector => $tema) {
            $paleta = $this->enReposo($this->reglasDe($selector));
            $acento = (string) $this->ultimoValor($paleta, '--grem-acento');

            foreach (['--grem-bg', '--grem-elevada'] as $fondo) {
                $contraste = $this->contraste($acento, (string) $this->ultimoValor($paleta, $fondo));

                $this->assertGreaterThanOrEqual(3.0, $contraste, sprintf('En %s el anillo %s sobre %s da %.2f:1.', $tema, $acento, $fondo, $contraste));
            }
        }

        preg_match_all('/outline:\s*([^;]+);/', $css, $anillos);

        $this->assertNotEmpty($anillos[1]);
        $this->assertSame(['2px solid var(--grem-acento)'], array_values(array_unique($anillos[1])), 'Un anillo de la hoja usa otro color que el acento medido.');
    }

    /**
     * El borde rojo del campo con error lo pone `campo.blade.php` con una utilidad
     * en capa, y el borde de reposo de la hoja, que no va en capa, lo pisa. La
     * hoja lo devuelve con más especificidad que ese reposo, campo por campo:
     * con la misma o menos, gana el reposo y el error vuelve a ser invisible.
     */
    public function test_un_campo_con_error_conserva_su_borde_rojo(): void
    {
        $reglas = $this->reglasDeLaHoja();

        foreach (['input', 'select', 'textarea'] as $campo) {
            $reposo = $this->selectoresQueDeclaran($reglas, "/^\.gremio-editorial-formulario {$campo}(:not\([^)]*\))*$/", 'border-color');
            $error = $this->selectoresQueDeclaran($reglas, "/^\.gremio-editorial-formulario {$campo}.*\[aria-invalid='true'\]$/", 'border-color', 'var(--grem-acento)');

            $this->assertNotEmpty($reposo, "No se encontró el borde de reposo de `{$campo}`.");
            $this->assertNotEmpty($error, "`{$campo}` con error no recupera su borde rojo.");

            $this->assertGreaterThan(
                max(array_map($this->especificidad(...), $reposo)),
                max(array_map($this->especificidad(...), $error)),
                "El borde de error de `{$campo}` no gana al de reposo: {$error[0]} contra {$reposo[0]}."
            );
        }
    }

    // --- Lectura de la hoja ---

    /**
     * @return list<array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>
     */
    private function reglasDeLaHoja(): array
    {
        return $this->reglas(File::get(resource_path('css/gremio-editorial.css')));
    }

    /**
     * Las reglas de una hoja, cada una con las at-rules que la envuelven.
     *
     * Cuenta llaves por lo mismo que `MovimientoTest::sinBloquesDeHoverFino()`:
     * lo que se prueba es dónde vive cada regla. Las at-rules sin bloque, como
     * `@custom-variant`, terminan en punto y coma y no se confunden con la
     * cabecera de la regla siguiente.
     *
     * @return list<array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>
     */
    private function reglas(string $css): array
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $reglas = [];
        $contexto = [];
        $cabecera = '';
        $largo = strlen($css);

        for ($i = 0; $i < $largo; $i++) {
            $caracter = $css[$i];

            if ($caracter === ';') {
                $cabecera = '';

                continue;
            }

            if ($caracter === '}') {
                array_pop($contexto);
                $cabecera = '';

                continue;
            }

            if ($caracter !== '{') {
                $cabecera .= $caracter;

                continue;
            }

            $cabecera = trim((string) preg_replace('/\s+/', ' ', $cabecera));

            if (str_starts_with($cabecera, '@')) {
                $contexto[] = $cabecera;
                $cabecera = '';

                continue;
            }

            $cierre = strpos($css, '}', $i);

            if ($cierre === false) {
                break;
            }

            $reglas[] = [
                'contexto' => $contexto,
                'selectores' => array_map('trim', $this->partirEnNivelCero($cabecera, ',')),
                'declaraciones' => $this->declaraciones(substr($css, $i + 1, $cierre - $i - 1)),
            ];

            $cabecera = '';
            $i = $cierre;
        }

        return $reglas;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function declaraciones(string $cuerpo): array
    {
        $declaraciones = [];

        foreach ($this->partirEnNivelCero($cuerpo, ';') as $trozo) {
            $dosPuntos = strpos($trozo, ':');

            if ($dosPuntos === false) {
                continue;
            }

            $declaraciones[] = [
                trim(substr($trozo, 0, $dosPuntos)),
                trim((string) preg_replace('/\s+/', ' ', substr($trozo, $dosPuntos + 1))),
            ];
        }

        return $declaraciones;
    }

    /**
     * Parte por el separador solo fuera de paréntesis y de comillas: las comas de
     * `color-mix()` y de `:not()` no separan nada.
     *
     * @return list<string>
     */
    private function partirEnNivelCero(string $texto, string $separador): array
    {
        $trozos = [];
        $actual = '';
        $nivel = 0;
        $comilla = null;
        $largo = strlen($texto);

        for ($i = 0; $i < $largo; $i++) {
            $caracter = $texto[$i];

            if ($comilla !== null) {
                $comilla = $caracter === $comilla ? null : $comilla;
            } elseif ($caracter === '"' || $caracter === "'") {
                $comilla = $caracter;
            } elseif ($caracter === '(') {
                $nivel++;
            } elseif ($caracter === ')') {
                $nivel--;
            } elseif ($caracter === $separador && $nivel === 0) {
                $trozos[] = trim($actual);
                $actual = '';

                continue;
            }

            $actual .= $caracter;
        }

        $trozos[] = trim($actual);

        return array_values(array_filter($trozos, fn (string $trozo): bool => $trozo !== ''));
    }

    /**
     * Las reglas de la hoja que nombran este selector exacto, en cualquier sitio.
     *
     * @return list<array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>
     */
    private function reglasDe(string $selector): array
    {
        return array_values(array_filter(
            $this->reglasDeLaHoja(),
            fn (array $regla): bool => in_array($selector, $regla['selectores'], true)
        ));
    }

    /**
     * Solo las que aplican en reposo: fuera de toda at-rule.
     *
     * @param  array<int, array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>  $reglas
     * @return list<array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>
     */
    private function enReposo(array $reglas): array
    {
        return array_values(array_filter($reglas, fn (array $regla): bool => $regla['contexto'] === []));
    }

    /**
     * El valor que gana entre esas reglas: el de la última declaración.
     *
     * @param  array<int, array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>  $reglas
     */
    private function ultimoValor(array $reglas, string $propiedad): ?string
    {
        $valor = null;

        foreach ($reglas as $regla) {
            foreach ($regla['declaraciones'] as [$declarada, $declarado]) {
                if ($declarada === $propiedad) {
                    $valor = $declarado;
                }
            }
        }

        return $valor;
    }

    /**
     * @param  array<int, array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>  $reglas
     * @return list<string>
     */
    private function propiedades(array $reglas): array
    {
        return array_merge([], ...array_map(fn (array $regla): array => array_column($regla['declaraciones'], 0), $reglas));
    }

    /**
     * @param  array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}  $regla
     */
    private function dentroDeFotogramas(array $regla): bool
    {
        foreach ($regla['contexto'] as $atRule) {
            if (str_starts_with($atRule, '@keyframes')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Los selectores que casan con el patrón y declaran esa propiedad, con ese
     * valor si se pide.
     *
     * @param  list<array{contexto: list<string>, selectores: list<string>, declaraciones: list<array{0: string, 1: string}>}>  $reglas
     * @return list<string>
     */
    private function selectoresQueDeclaran(array $reglas, string $patron, string $propiedad, ?string $valor = null): array
    {
        $selectores = [];

        foreach ($this->enReposo($reglas) as $regla) {
            $declarado = $this->ultimoValor([$regla], $propiedad);

            if ($declarado === null || ($valor !== null && $declarado !== $valor)) {
                continue;
            }

            foreach ($regla['selectores'] as $selector) {
                if (preg_match($patron, $selector)) {
                    $selectores[] = $selector;
                }
            }
        }

        return $selectores;
    }

    /**
     * Especificidad como un solo número —ids, clases y tipos en tres cifras de
     * cien—, con lo que usa esta hoja: `:not()`, `:is()` y `:has()` cuentan lo
     * que llevan dentro y `:where()` no cuenta.
     */
    private function especificidad(string $selector): int
    {
        $ids = 0;
        $clases = 0;
        $tipos = 0;

        while (preg_match('/:(not|is|has|where)\(/', $selector, $funcion, PREG_OFFSET_CAPTURE)) {
            $inicio = $funcion[0][1];
            $apertura = $inicio + strlen($funcion[0][0]) - 1;
            $nivel = 0;
            $cierre = $apertura;

            for ($i = $apertura, $largo = strlen($selector); $i < $largo; $i++) {
                $nivel += match ($selector[$i]) {
                    '(' => 1,
                    ')' => -1,
                    default => 0,
                };

                if ($nivel === 0) {
                    $cierre = $i;

                    break;
                }
            }

            if ($funcion[1][0] !== 'where') {
                $interior = max(array_map($this->especificidad(...), $this->partirEnNivelCero(substr($selector, $apertura + 1, $cierre - $apertura - 1), ',')));
                $ids += intdiv($interior, 10000);
                $clases += intdiv($interior % 10000, 100);
                $tipos += $interior % 100;
            }

            $selector = substr($selector, 0, $inicio).substr($selector, $cierre + 1);
        }

        $clases += preg_match_all('/\[[^\]]*\]/', $selector);
        $selector = (string) preg_replace('/\[[^\]]*\]/', '', $selector);
        $tipos += preg_match_all('/::[\w-]+/', $selector);
        $selector = (string) preg_replace('/::[\w-]+/', '', $selector);

        $ids += preg_match_all('/#[\w-]+/', $selector);
        $clases += preg_match_all('/\.[\w-]+/', $selector) + preg_match_all('/:[\w-]+/', $selector);
        $tipos += preg_match_all('/(?:^|[\s>+~])[a-z][\w-]*/i', $selector);

        return $ids * 10000 + $clases * 100 + $tipos;
    }

    /**
     * Los tokens que `tokens.css` fija dentro de su bloque de movimiento
     * reducido, con el valor que les da.
     *
     * @return array<string, string>
     */
    private function anuladosPorTokensConMovimientoReducido(): array
    {
        $anulados = [];

        foreach ($this->reglas(File::get(resource_path('css/tokens.css'))) as $regla) {
            if (! in_array(self::MOVIMIENTO_REDUCIDO, $regla['contexto'], true)) {
                continue;
            }

            foreach ($regla['declaraciones'] as [$propiedad, $valor]) {
                if (str_starts_with($propiedad, '--')) {
                    $anulados[$propiedad] = $valor;
                }
            }
        }

        return $anulados;
    }

    /**
     * Las vistas de El Gremio: las que cargan esta hoja y el folio que comparten.
     *
     * @return list<string>
     */
    private function vistasDelGremio(): array
    {
        return [
            resource_path('views/publico/quienes-somos.blade.php'),
            resource_path('views/publico/boletin/index.blade.php'),
            resource_path('views/publico/boletin/show.blade.php'),
            resource_path('views/publico/contacto.blade.php'),
            resource_path('views/components/publico/folio-gremio.blade.php'),
        ];
    }

    /**
     * Toda lista de clases de una vista: las de `class="…"` y las cadenas de
     * `@class([…])`, que es donde viven la pieza principal y los filtros.
     *
     * @return list<string>
     */
    private function listasDeClases(string $contenido): array
    {
        $listas = [];

        preg_match_all('/class="([^"]*)"/', $contenido, $atributos);

        foreach ($atributos[1] as $lista) {
            $listas[] = $lista;
        }

        preg_match_all('/@class\(\[(.*?)\]\)/s', $contenido, $arreglos);

        foreach ($arreglos[1] as $arreglo) {
            preg_match_all("/'([^']*)'/", $arreglo, $cadenas);

            foreach ($cadenas[1] as $lista) {
                $listas[] = $lista;
            }
        }

        return $listas;
    }
}

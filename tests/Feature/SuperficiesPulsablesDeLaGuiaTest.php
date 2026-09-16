<?php

namespace Tests\Feature;

use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Abre tu negocio conserva su composición editorial y habla el lenguaje de la
 * casa en todo lo que se pulsa: ninguna superficie pulsable recta, las tres
 * respuestas en cada pieza —hover tras la puerta de puntero fino, acuse al
 * pulsar y foco visible— y un movimiento reducido que apaga lo que se mueve y
 * deja el color.
 *
 * La hoja se lee regla por regla, sin comentarios y sabiendo qué at-rules
 * envuelven a cada una: que un `:hover` y la puerta aparezcan en el archivo no
 * prueba nada si están en extremos opuestos. El cableado se afirma sobre el
 * árbol servido, porque una regla cuya clase no llega a la vista no pinta nada.
 */
class SuperficiesPulsablesDeLaGuiaTest extends TestCase
{
    use RefreshDatabase;

    private const PUERTA_DE_PUNTERO_FINO = '@media (hover: hover) and (pointer: fine)';

    private const MOVIMIENTO_REDUCIDO = '@media (prefers-reduced-motion: reduce)';

    private const ENLACE_DE_LA_ENTIDAD = 'https://entidad.example.org/tramites/uso-del-suelo';

    /**
     * Las tres piezas pulsables que dibuja la hoja y el acuse que le toca a cada
     * forma. El chip y el enlace son controles y encogen; la cabecera de cada
     * trámite es una fila y se tiñe, porque encogerla arrastraría la flecha que
     * gira al abrir.
     *
     * @return array<string, array{transicion: list<string>, acuse: array<string, string>, desplazamientoDelFoco: string}>
     */
    private static function piezasPulsables(): array
    {
        $control = [
            'transicion' => [
                'transform var(--duracion-instante) var(--ease-out)',
                'color var(--duracion-boton) var(--ease-color)',
                'border-color var(--duracion-boton) var(--ease-color)',
            ],
            'acuse' => [
                'transform' => 'scale(var(--asb-encogimiento-tarjeta))',
                'transition-duration' => '0ms',
            ],
            'desplazamientoDelFoco' => '3px',
        ];

        return [
            '.guia-editorial-municipio' => $control,
            '.guia-editorial-enlace' => $control,
            '.guia-editorial-requisito__cabecera' => [
                'transicion' => [
                    'background-color var(--duracion-boton) var(--ease-color)',
                ],
                'acuse' => [
                    'background-color' => 'var(--asb-fila-pulsada)',
                    'transition-duration' => '0ms',
                ],
                // Hacia dentro: la tarjeta recorta lo que se pinte por fuera.
                'desplazamientoDelFoco' => '-2px',
            ],
        ];
    }

    /**
     * Ninguna superficie pulsable recta, y cada una con el radio de la escala
     * de la casa que le toca a su forma: la celda del selector 0.75rem y la
     * tarjeta de cada trámite, pieza grande, 1rem. Las esquinas a cero que
     * quedan en la hoja son del decorado del hero y están ahí a propósito:
     * capas que la escena recorta con su propio radio, la foto que va dentro y
     * el logotipo vivo. Fuera de esa lista cerrada ninguna regla endereza una
     * esquina, sea de una pieza que se pulsa o de una tarjeta que no, y la
     * exención se comprueba en el árbol: la escena es decorado mientras esté
     * oculta a los lectores de pantalla y no tenga nada que pulsar.
     *
     * Y se comprueba en la pantalla: esas capas rectas solo no se ven porque
     * la escena se funde con la página por los cuatro lados con una máscara,
     * y el video del logotipo con una elipse que llega a transparente en sus
     * cuatro lados (`closest-side`). Con un radio solo a la derecha, la foto
     * se cortaba en seco arriba, abajo y contra el texto, y la caja del video
     * se veía como un cuadrado.
     *
     * El enlace a la entidad toma su caja de las utilidades de la vista, así
     * que su radio se mira en el elemento servido: `rounded-xl` es la celda de
     * 0.75rem de Tailwind 4, y ninguna otra utilidad de radio la pisa.
     */
    public function test_ninguna_superficie_pulsable_de_la_guia_es_recta(): void
    {
        $reglas = $this->reglasDeLaHoja();

        foreach (['.guia-editorial-municipio' => '0.75rem', '.guia-editorial-requisito' => '1rem'] as $superficie => $escala) {
            $radio = $this->declaracionesBase($reglas, $superficie)['border-radius'] ?? null;

            $this->assertNotNull($radio, "{$superficie} se pulsa y no declara radio: se pinta recta.");
            $this->assertFalse(
                $this->tieneEsquinaRecta($radio),
                "{$superficie} se pulsa y tiene una esquina recta: border-radius: {$radio}."
            );
            $this->assertSame($escala, $radio, "{$superficie} redondea fuera de la escala de la casa.");
        }

        $decorado = [
            '.guia-editorial-escena',
            '.guia-editorial-escena__campo',
            '.guia-editorial-escena__placeholder',
            '.guia-editorial-escena__velo',
            'img.guia-editorial-escena__foto',
            '.guia-editorial-logo-vivo video',
        ];

        $rectas = [];

        foreach ($reglas as $regla) {
            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if (preg_match('/^border(?:-[a-z]+)*-radius$/', $propiedad) !== 1 || ! $this->tieneEsquinaRecta($valor)) {
                    continue;
                }

                foreach (array_diff($regla['selectores'], $decorado) as $selector) {
                    $rectas[] = "{$selector} { {$propiedad}: {$valor} }";
                }
            }
        }

        $this->assertSame([], $rectas, "Esquinas rectas fuera del decorado del hero:\n".implode("\n", $rectas));

        $mascara = $this->declaracionesBase($reglas, '.guia-editorial-escena')['mask-image'] ?? '';
        $this->assertMatchesRegularExpression(
            '/^linear-gradient\(90deg, transparent [^,]+, #000 [^,]+, #000 [^,]+, transparent 100%\), linear-gradient\(180deg, transparent 0%, #000 [^,]+, #000 [^,]+, transparent 100%\)$/',
            $mascara,
            'La escena del hero no se funde con la página por los cuatro lados: sus capas rectas se ven.'
        );

        foreach ($this->reglasDe($reglas, '.guia-editorial-escena') as $regla) {
            $declaraciones = $this->declaraciones($regla['cuerpo']);

            $this->assertArrayNotHasKey('border-radius', $declaraciones, 'La escena vuelve a recortarse con un radio en vez de fundirse.');
            $this->assertNotSame('none', $declaraciones['mask-image'] ?? null, 'Algún ancho le quita la máscara a la escena.');
        }

        $mascaraDelLogo = $this->declaracionesBase($reglas, '.guia-editorial-logo-vivo video')['mask-image'] ?? '';
        $this->assertStringStartsWith('radial-gradient(closest-side,', $mascaraDelLogo, 'La elipse del logotipo no toca los lados de su caja: el video se ve como un cuadrado.');
        $this->assertStringEndsWith('transparent 100%)', $mascaraDelLogo);

        $xpath = $this->guiaServida();
        $escena = $xpath->query('//*['.$this->conClase('guia-editorial-escena').']');

        $this->assertSame(1, $escena->length, 'La guía no pintó la escena del hero.');
        $this->assertSame(
            'true',
            $escena->item(0)->getAttribute('aria-hidden'),
            'La escena dejó de ser decorado: sus capas rectas ya no están justificadas.'
        );
        $this->assertSame(
            0,
            $xpath->query('.//a | .//button | .//summary | .//input | .//select | .//textarea | .//*[@tabindex]', $escena->item(0))->length,
            'Hay algo que pulsar dentro de la escena: sus capas rectas dejaron de ser decorado.'
        );

        $radiosDelEnlace = array_values(preg_grep('/^(?:[a-z0-9-]+:)*rounded(?:-|$)/', $this->clases($this->enlaceDeLaEntidad($xpath))));

        $this->assertSame(
            ['rounded-xl'],
            $radiosDelEnlace,
            'El enlace a la entidad no redondea su caja con la celda de la casa, o alguna utilidad le endereza una esquina.'
        );
    }

    /**
     * Cada pieza pulsable responde a las tres manos: al puntero con un hover
     * tras la puerta de puntero fino, al dedo con un acuse sin puerta —en
     * táctil es el único que existe— y al teclado con el anillo del acento de
     * la guía. Todo lo que cambia funde con los tokens de la casa: lo que se
     * mueve en `--duracion-instante` con `--ease-out`, el color en
     * `--duracion-boton` con `--ease-color`.
     */
    public function test_cada_pieza_pulsable_responde_al_puntero_al_dedo_y_al_teclado(): void
    {
        $reglas = $this->reglasDeLaHoja();

        foreach (self::piezasPulsables() as $pieza => $esperado) {
            $transicion = $this->partir($this->declaracionesBase($reglas, $pieza)['transition'] ?? '', ',');
            $fundidas = array_map(fn (string $tramo): string => explode(' ', $tramo, 2)[0], $transicion);

            foreach ($esperado['transicion'] as $tramo) {
                $this->assertContains($tramo, $transicion, "{$pieza} no funde «{$tramo}» con los tokens de la casa.");
            }

            $hover = $this->reglasDe($reglas, "{$pieza}:hover");

            $this->assertNotEmpty($hover, "{$pieza} no responde al puntero: le falta :hover.");

            $escritasEnHover = [];

            foreach ($hover as $regla) {
                $this->assertContains(
                    self::PUERTA_DE_PUNTERO_FINO,
                    $regla['envolventes'],
                    "{$pieza}:hover va fuera de la puerta de puntero fino: en táctil se queda pegado."
                );

                foreach (array_keys($this->declaraciones($regla['cuerpo'])) as $propiedad) {
                    $this->assertContains($propiedad, $fundidas, "{$pieza}:hover cambia {$propiedad} de golpe: no está en su transición.");
                    $escritasEnHover[] = $propiedad;
                }
            }

            $activa = $this->reglasDe($reglas, "{$pieza}:active");

            $this->assertCount(1, $activa, "{$pieza} no acusa el dedo: le falta un único :active.");
            $this->assertSame(
                [],
                $activa[0]['envolventes'],
                "{$pieza}:active va dentro de una at-rule: en táctil es el único acuse y no puede tener puerta."
            );

            $acuse = $this->declaraciones($activa[0]['cuerpo']);

            foreach ($esperado['acuse'] as $propiedad => $valor) {
                $this->assertSame($valor, $acuse[$propiedad] ?? null, "{$pieza}:active no acusa con {$propiedad}: {$valor}.");

                if ($propiedad !== 'transition-duration') {
                    $this->assertContains($propiedad, $fundidas, "{$pieza}:active cambia {$propiedad} sin reloj que lo devuelva al soltar.");
                }
            }

            $pisadas = array_values(array_intersect(array_keys($acuse), $escritasEnHover));

            if ($pisadas !== []) {
                $this->assertGreaterThan(
                    max(array_column($hover, 'orden')),
                    $activa[0]['orden'],
                    "{$pieza}:active va antes que su :hover y los dos escriben ".implode(', ', $pisadas).': con el puntero encima, el hover tapa el acuse.'
                );
            }

            $foco = $this->reglasDe($reglas, "{$pieza}:focus-visible");

            $this->assertCount(1, $foco, "{$pieza} no enseña el foco del teclado.");

            $anillo = $this->declaraciones($foco[0]['cuerpo']);

            $this->assertSame('2px solid var(--guia-acento)', $anillo['outline'] ?? null, "{$pieza}:focus-visible no dibuja el anillo con el acento de la guía.");
            $this->assertSame(
                $esperado['desplazamientoDelFoco'],
                $anillo['outline-offset'] ?? null,
                "{$pieza}:focus-visible dibuja el anillo donde no se ve."
            );
        }

        /*
         * Pulsar la cabecera abre la tarjeta y su borde pasa al rojo: ese cambio
         * también funde, o el acuse de la fila termina en un salto.
         */
        $this->assertArrayHasKey(
            'border-color',
            $this->declaracionesBase($reglas, '.guia-editorial-requisito[open]'),
            'La tarjeta abierta no cambia de borde.'
        );
        $this->assertContains(
            'border-color var(--duracion-boton) var(--ease-color)',
            $this->partir($this->declaracionesBase($reglas, '.guia-editorial-requisito')['transition'] ?? '', ','),
            'La tarjeta del trámite cambia el borde de golpe al abrirse.'
        );
    }

    /**
     * Ningún `:hover` de la hoja queda fuera de la puerta, sea de una pieza
     * nombrada arriba o de una que se añada después: en táctil el estado se
     * queda pegado tras el toque y la pieza parece seleccionada.
     */
    public function test_ningun_hover_de_la_guia_queda_fuera_de_la_puerta_de_puntero_fino(): void
    {
        $sueltos = [];

        foreach ($this->reglasDeLaHoja() as $regla) {
            if (in_array(self::PUERTA_DE_PUNTERO_FINO, $regla['envolventes'], true)) {
                continue;
            }

            foreach ($regla['selectores'] as $selector) {
                if (str_contains($selector, ':hover')) {
                    $sueltos[] = $selector;
                }
            }
        }

        $this->assertSame([], $sueltos, "Hover sin puerta de puntero fino:\n".implode("\n", $sueltos));
    }

    /**
     * Movimiento reducido apaga lo que se mueve y solo eso. La flecha de cada
     * trámite deja de girar pero sigue cambiando; los encogimientos salen de un
     * token que `tokens.css` pone a 1; y el bloque no toca color, fondo, borde,
     * subrayado, anillo ni opacidad, ni apaga los fundidos que los llevan, que
     * no son movimiento y son la señal de hover y de foco que le queda a quien
     * lo pidió.
     */
    public function test_el_movimiento_reducido_apaga_lo_que_se_mueve_y_deja_el_color(): void
    {
        $reglas = $this->reglasDeLaHoja();
        $reducidas = array_values(array_filter(
            $reglas,
            fn (array $regla): bool => in_array(self::MOVIMIENTO_REDUCIDO, $regla['envolventes'], true)
        ));

        $this->assertNotEmpty($reducidas, 'La hoja no tiene bloque de movimiento reducido.');

        $this->assertContains(
            'rotate var(--duracion-boton) var(--ease-out)',
            $this->partir($this->declaracionesBase($reglas, '.guia-editorial-requisito__flecha')['transition'] ?? '', ','),
            'La flecha no gira con la curva de movimiento: `group-open:rotate-180` escribe `rotate`, no `transform`.'
        );

        $flecha = $this->reglasDe($reducidas, '.guia-editorial-requisito__flecha');

        $this->assertCount(1, $flecha, 'Con movimiento reducido la flecha sigue girando.');
        $this->assertSame(
            'none',
            $this->declaraciones($flecha[0]['cuerpo'])['transition'] ?? null,
            'Con movimiento reducido la flecha sigue girando.'
        );

        $senales = '/^(?:color|opacity|background(?:-color)?|border(?:-(?:top|right|bottom|left|block|inline)(?:-(?:start|end))?)?(?:-(?:color|style|width))?|outline(?:-[a-z]+)?|text-decoration(?:-[a-z]+)?)$/';
        $apagadas = [];

        foreach ($reducidas as $regla) {
            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if (preg_match($senales, $propiedad) === 1) {
                    $apagadas[] = implode(', ', $regla['selectores'])." { {$propiedad}: {$valor} }";
                }
            }
        }

        $this->assertSame([], $apagadas, "El movimiento reducido apaga señales que no son movimiento:\n".implode("\n", $apagadas));

        /*
         * Apagar la transición entera también apaga el color. Dentro del
         * bloque, una pieza cuya transición base funde algo más que geometría
         * solo puede reescribirla conservando esos fundidos tal cual.
         */
        $geometria = ['transform', 'rotate', 'translate', 'scale'];
        $fundidosPerdidos = [];

        foreach ($reducidas as $regla) {
            foreach ($this->declaraciones($regla['cuerpo']) as $propiedad => $valor) {
                if (! str_starts_with($propiedad, 'transition')) {
                    continue;
                }

                foreach ($regla['selectores'] as $selector) {
                    $fundidos = array_values(array_filter(
                        $this->partir($this->declaracionesBase($reglas, $selector)['transition'] ?? '', ','),
                        fn (string $tramo): bool => ! in_array(explode(' ', $tramo, 2)[0], $geometria, true)
                    ));

                    $conservados = $propiedad === 'transition' ? $this->partir($valor, ',') : [];

                    if (array_diff($fundidos, $conservados) !== []) {
                        $fundidosPerdidos[] = "{$selector} { {$propiedad}: {$valor} }";
                    }
                }
            }
        }

        $this->assertSame([], $fundidosPerdidos, "El movimiento reducido apaga fundidos de color junto con el movimiento:\n".implode("\n", $fundidosPerdidos));

        $aMano = [];

        foreach ($reglas as $regla) {
            $transformacion = $this->declaraciones($regla['cuerpo'])['transform'] ?? '';

            if (str_contains($transformacion, 'scale(') && preg_match('/^scale\(var\(--asb-encogimiento-[a-z]+\)\)$/', $transformacion) !== 1) {
                $aMano[] = implode(', ', $regla['selectores'])." { transform: {$transformacion} }";
            }
        }

        $this->assertSame([], $aMano, "Encogimientos escritos a mano, que el movimiento reducido no alcanza:\n".implode("\n", $aMano));

        /*
         * La otra mitad vive en `tokens.css`. Se lee con el mismo analizador,
         * y el valor de fuera del bloque sirve de control: si el analizador
         * confundiera qué regla va dentro de qué @media, las dos lecturas no
         * podrían salir distintas.
         */
        $raices = $this->reglasDe($this->reglas(File::get(resource_path('css/tokens.css'))), ':root');
        $encogimiento = [];

        foreach ($raices as $raiz) {
            $valor = $this->declaraciones($raiz['cuerpo'])['--asb-encogimiento-tarjeta'] ?? null;

            if ($valor !== null) {
                $encogimiento[implode(' ', $raiz['envolventes'])] = $valor;
            }
        }

        $this->assertSame(
            ['' => '0.985', self::MOVIMIENTO_REDUCIDO => '1'],
            $encogimiento,
            'tokens.css ya no anula el encogimiento de tarjeta con movimiento reducido.'
        );
    }

    /**
     * Las reglas solo pintan si su clase llega a la pieza. Se sigue cada una en
     * el árbol servido: los chips del selector, la cabecera y la flecha de cada
     * trámite —con el `group` del que cuelga su giro— y el enlace a la entidad,
     * que no puede llevar una utilidad `hover:`: en Tailwind 4 esa variante
     * solo pregunta `(hover: hover)` y la pieza se quedaría pegada en táctil.
     */
    public function test_la_vista_cablea_cada_pieza_con_su_regla(): void
    {
        $xpath = $this->guiaServida();

        $chips = $xpath->query('//*['.$this->conClase('guia-editorial-municipios').']/a');

        $this->assertSame(2, $chips->length, 'El selector no pintó los dos municipios con guía.');

        foreach ($chips as $chip) {
            $this->assertContains('guia-editorial-municipio', $this->clases($chip), 'Un chip de municipio no lleva su clase: se queda sin superficie ni respuesta.');
        }

        $tramites = $xpath->query('//details['.$this->conClase('guia-editorial-requisito').']');

        $this->assertSame(2, $tramites->length, 'La guía no pintó los dos trámites del municipio.');

        foreach ($tramites as $tramite) {
            $this->assertContains('group', $this->clases($tramite), 'La tarjeta del trámite no lleva `group`: la flecha deja de girar al abrir.');

            $cabecera = $xpath->query('./summary', $tramite);

            $this->assertSame(1, $cabecera->length, 'El trámite no tiene cabecera que pulsar.');
            $this->assertContains(
                'guia-editorial-requisito__cabecera',
                $this->clases($cabecera->item(0)),
                'La cabecera del trámite no lleva su clase: no acusa el dedo ni enseña el foco.'
            );

            $flecha = $xpath->query('.//*[local-name()="svg"]['.$this->conClase('guia-editorial-requisito__flecha').']', $cabecera->item(0));

            $this->assertSame(1, $flecha->length, 'La flecha del trámite no lleva su clase: gira sin la curva de la casa y sin apagarse con movimiento reducido.');
            $this->assertContains('group-open:rotate-180', $this->clases($flecha->item(0)), 'La flecha del trámite no gira al abrir.');
        }

        $clasesDelEnlace = $this->clases($this->enlaceDeLaEntidad($xpath));

        $this->assertContains('guia-editorial-enlace', $clasesDelEnlace, 'El enlace a la entidad no lleva su clase: se queda sin respuesta.');
        $this->assertSame(
            [],
            array_values(preg_grep('/^hover:/', $clasesDelEnlace)),
            'El enlace a la entidad lleva una utilidad hover: sin puntero fino.'
        );
    }

    /**
     * La guía servida con dos municipios con guía y dos trámites en el primero,
     * uno con enlace a la entidad y otro sin él: cada pieza se pinta al menos
     * una vez y el enlace exactamente una.
     */
    private function guiaServida(): \DOMXPath
    {
        $this->seed(DatabaseSeeder::class);

        RequisitoApertura::query()->delete();

        [$primero, $segundo] = Municipio::query()->activos()->ordenados()->take(2)->get()->all();

        RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => $primero->getKey(),
            'enlace_externo' => self::ENLACE_DE_LA_ENTIDAD,
            'orden' => 1,
        ]);
        RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => $primero->getKey(),
            'enlace_externo' => null,
            'orden' => 2,
        ]);
        RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => $segundo->getKey(),
            'orden' => 1,
        ]);

        return $this->xpathDe($this->get(route('guia.index'))->assertOk()->getContent());
    }

    private function enlaceDeLaEntidad(\DOMXPath $xpath): \DOMElement
    {
        $enlaces = $xpath->query('//a[@href="'.self::ENLACE_DE_LA_ENTIDAD.'"]');

        $this->assertSame(1, $enlaces->length, 'La guía no pintó el enlace a la entidad.');

        return $enlaces->item(0);
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

    /** Condición XPath para una clase entera, no para un trozo del atributo. */
    private function conClase(string $clase): string
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " '.$clase.' ")';
    }

    /** @return list<string> */
    private function clases(\DOMElement $elemento): array
    {
        return preg_split('/\s+/', trim($elemento->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** @return list<array{orden: int, envolventes: list<string>, selectores: list<string>, cuerpo: string}> */
    private function reglasDeLaHoja(): array
    {
        return $this->reglas(File::get(resource_path('css/guia-editorial.css')));
    }

    /**
     * Una hoja como lista plana de reglas, cada una con las at-rules que la
     * envuelven, de fuera a dentro. Los comentarios se quitan antes: un
     * `:hover` comentado no es un hover.
     *
     * @return list<array{orden: int, envolventes: list<string>, selectores: list<string>, cuerpo: string}>
     */
    private function reglas(string $css): array
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $reglas = [];
        $envolventes = [];
        $cabecera = '';

        for ($i = 0, $largo = strlen($css); $i < $largo; $i++) {
            $caracter = $css[$i];

            if ($caracter === ';') {
                $cabecera = '';
            } elseif ($caracter === '}') {
                array_pop($envolventes);
                $cabecera = '';
            } elseif ($caracter !== '{') {
                $cabecera .= $caracter;
            } elseif (str_starts_with(trim($cabecera), '@')) {
                $envolventes[] = (string) preg_replace('/\s+/', ' ', trim($cabecera));
                $cabecera = '';
            } else {
                $cierre = strpos($css, '}', $i);

                $this->assertNotFalse($cierre, 'La hoja tiene una regla sin cerrar.');

                $reglas[] = [
                    'orden' => count($reglas),
                    'envolventes' => $envolventes,
                    'selectores' => $this->partir($cabecera, ','),
                    'cuerpo' => substr($css, $i + 1, $cierre - $i - 1),
                ];
                $cabecera = '';
                $i = $cierre;
            }
        }

        return $reglas;
    }

    /**
     * @param  list<array{orden: int, envolventes: list<string>, selectores: list<string>, cuerpo: string}>  $reglas
     * @return list<array{orden: int, envolventes: list<string>, selectores: list<string>, cuerpo: string}>
     */
    private function reglasDe(array $reglas, string $selector): array
    {
        return array_values(array_filter(
            $reglas,
            fn (array $regla): bool => in_array($selector, $regla['selectores'], true)
        ));
    }

    /**
     * Lo que declaran las reglas de un selector fuera de toda at-rule, en orden:
     * si una propiedad se repite gana la última, como en el navegador.
     *
     * @param  list<array{orden: int, envolventes: list<string>, selectores: list<string>, cuerpo: string}>  $reglas
     * @return array<string, string>
     */
    private function declaracionesBase(array $reglas, string $selector): array
    {
        $declaraciones = [];

        foreach ($this->reglasDe($reglas, $selector) as $regla) {
            if ($regla['envolventes'] === []) {
                $declaraciones = array_merge($declaraciones, $this->declaraciones($regla['cuerpo']));
            }
        }

        return $declaraciones;
    }

    /**
     * Las declaraciones de una regla con los espacios normalizados; si una
     * propiedad se repite gana la última.
     *
     * @return array<string, string>
     */
    private function declaraciones(string $cuerpo): array
    {
        $declaraciones = [];

        foreach ($this->partir($cuerpo, ';') as $declaracion) {
            if (! str_contains($declaracion, ':')) {
                continue;
            }

            [$propiedad, $valor] = explode(':', $declaracion, 2);
            $declaraciones[strtolower(trim($propiedad))] = trim($valor);
        }

        return $declaraciones;
    }

    /** Si alguna esquina de un valor de radio vale cero. */
    private function tieneEsquinaRecta(string $valor): bool
    {
        foreach ($this->partir($valor, ' /') as $esquina) {
            if (preg_match('/^0(?:\.0+)?(?:px|rem|em|%)?$/', $esquina) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parte un texto por los separadores que caen fuera de paréntesis —la coma
     * de `var(--a, b)` o el espacio de `calc(1rem - 1px)` no separan nada— y
     * devuelve los trozos con los espacios normalizados.
     *
     * @return list<string>
     */
    private function partir(string $texto, string $separadores): array
    {
        $trozos = [];
        $trozo = '';
        $profundidad = 0;

        foreach (str_split($texto) as $caracter) {
            if ($caracter === '(') {
                $profundidad++;
            } elseif ($caracter === ')') {
                $profundidad--;
            }

            if ($profundidad === 0 && str_contains($separadores, $caracter)) {
                $trozos[] = $trozo;
                $trozo = '';

                continue;
            }

            $trozo .= $caracter;
        }

        $trozos[] = $trozo;

        return array_values(array_filter(
            array_map(fn (string $trozo): string => (string) preg_replace('/\s+/', ' ', trim($trozo)), $trozos),
            fn (string $trozo): bool => $trozo !== ''
        ));
    }
}

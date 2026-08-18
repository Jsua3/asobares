<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El frontend llegó a tener 160 `hover:` sin puerta táctil, cero `:active` en
 * todo el repositorio y una sola curva escrita a mano que además era la
 * prohibida. Nada de eso fue descuido: fue que ningún sitio decía cómo se
 * escribe el movimiento. Esta guardia lo dice, y falla cuando se improvisa.
 */
class MovimientoTest extends TestCase
{
    public function test_los_tokens_de_movimiento_existen(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        // Curvas: van en @theme para que Tailwind genere las utilidades y para
        // que redefinir --ease-out pise la nativa en todo el proyecto.
        $this->assertStringContainsString('--ease-out: cubic-bezier(0.23, 1, 0.32, 1)', $tokens);
        $this->assertStringContainsString('--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)', $tokens);
        $this->assertStringContainsString('--ease-cajon: cubic-bezier(0.32, 0.72, 0, 1)', $tokens);
        $this->assertStringContainsString('--ease-color: ease', $tokens);

        // Duraciones: la escala codifica que la salida es más rápida que la
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);

        // Desplazamientos: son tokens y no literales porque el interruptor de
        // `prefers-reduced-motion` los anula sin tocar las duraciones.
        $this->assertStringContainsString('--asb-levante: -2px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: -4%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: -25%', $tokens);
    }

    /**
     * La regla no dice «quitar toda animación»: dice quitar el movimiento y
     * conservar los fundidos de opacidad y color, que ayudan a comprender.
     * Por eso se anulan los desplazamientos y NO las duraciones — si se
     * pusieran las duraciones a cero moriría también el fundido del borde.
     */
    public function test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tokens);
        $this->assertStringContainsString('--asb-levante: 0px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: 0%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: 0%', $tokens);

        // Las duraciones NO se anulan: si alguien las pone a cero aquí, está
        // deshaciendo la precisión de arriba.
        $this->assertStringNotContainsString('--duracion-boton: 0ms', $tokens);
        $this->assertStringNotContainsString('--duracion-entrada: 0ms', $tokens);
    }

    /**
     * Los tokens solo cubren lo que los usa. Filament y Leaflet traen su propio
     * movimiento, y el scroll suave global lo dispara el enlace «Saltar al
     * contenido», que es navegación de teclado.
     */
    public function test_hay_red_de_seguridad_para_lo_que_no_usa_los_tokens(): void
    {
        $app = File::get(resource_path('css/app.css'));

        // Barrido estrecho: solo `animation` y `scroll-behavior`. NO toca
        // `transition`, para no deshacer la precisión del interruptor.
        $this->assertStringContainsString('animation-duration: 1ms !important', $app);
        $this->assertStringContainsString('animation-iteration-count: 1 !important', $app);
        $this->assertStringContainsString('scroll-behavior: auto !important', $app);
        /*
         * Lo que se prohíbe es que el BARRIDO anule `transition`, no que nadie
         * escriba nunca una duración de cero: `.pulsable:active` usa
         * `transition-duration: 0ms` a propósito para que el botón baje sin
         * retardo. Por eso se buscan las formas con `!important`, que son las
         * que solo puede escribir un barrido.
         */
        $this->assertStringNotContainsString('transition-duration: .01ms', $app);
        $this->assertStringNotContainsString('transition-duration: 0ms !important', $app);
        $this->assertStringNotContainsString('transition: none !important', $app);

        // El scroll suave deja de ser incondicional.
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $app);
    }

    /**
     * Quita del CSS los bloques `@media (hover: hover) and (pointer: fine)`
     * completos, contando llaves para respetar el anidamiento.
     *
     * Se hace así y no con una expresión regular porque lo que hay que probar
     * no es que ambas cosas existan en el archivo —eso pasaría aunque
     * estuvieran en extremos opuestos— sino que el `:hover` está DENTRO de la
     * puerta. Lo que sobrevive a esta poda es exactamente lo que queda fuera.
     */
    private function sinBloquesDeHoverFino(string $css): string
    {
        $marca = '@media (hover: hover) and (pointer: fine)';

        while (($inicio = strpos($css, $marca)) !== false) {
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
     * En táctil un `:hover` con `transform` se queda pegado tras el toque: la
     * tarjeta del directorio se quedaba elevada y con borde rojo, como si
     * estuviera seleccionada. La puerta va alrededor del bloque `:hover`, no
     * de la declaración `transition`.
     */
    public function test_todo_hover_con_transform_tiene_puerta_tactil(): void
    {
        $hojas = [
            resource_path('css/app.css'),
            resource_path('css/filament/admin/theme.css'),
        ];

        foreach ($hojas as $hoja) {
            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $hoja);
            $fuera = $this->sinBloquesDeHoverFino(File::get($hoja));

            $this->assertDoesNotMatchRegularExpression(
                '/:hover\s*\{[^}]*transform:/',
                $fuera,
                "{$ruta} eleva en :hover fuera de la puerta táctil."
            );
        }
    }

    /** Cero `:active` en todo el repositorio era la mayor pérdida por línea. */
    public function test_existe_el_portador_del_acuse_de_pulsacion(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('.pulsable:active', $app);
        $this->assertStringContainsString('transform: scale(0.97)', $app);

        // Bajar instantáneo, subir en 100 ms: el retardo al presionar se nota.
        $this->assertStringContainsString('transition-duration: 0ms', $app);
    }

    /** El `translateY(-2px)` y el `200ms ease` estaban duplicados literales. */
    public function test_los_portadores_no_repiten_valores_de_movimiento(): void
    {
        $app = File::get(resource_path('css/app.css'));
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        foreach (['app.css' => $app, 'theme.css' => $tema] as $nombre => $contenido) {
            $this->assertStringNotContainsString('translateY(-2px)', $contenido, "{$nombre} cablea el levante.");
            $this->assertStringNotContainsString('200ms ease', $contenido, "{$nombre} cablea la duración.");
            $this->assertStringContainsString('var(--asb-levante)', $contenido);
        }
    }

    /**
     * Patrones que ninguna vista debe contener, con su motivo.
     *
     * Es `public static` y no `private` siguiendo el patrón de guardia
     * compartida del repositorio: `TemaClaroOscuroTest::clasesProhibidas()`
     * la reutiliza `ComponentesDelPanelTest`. Si mañana el panel quiere la
     * misma vigilancia, la importa en vez de copiarla.
     *
     * @return array<string, string> patrón => por qué
     */
    public static function patronesProhibidos(): array
    {
        return [
            '/\bease-in\b(?!-out)/' => 'ease-in arranca lento justo cuando más se mira; en interfaz nunca',
            '/\btransition-all\b/' => 'transition: all arrastra propiedades caras que nadie quiso animar',
            '/transition:\s*all\b/' => 'transition: all arrastra propiedades caras que nadie quiso animar',
            '/\bduration-\d+\b/' => 'la duración se toma de los tokens: duration-(--duracion-*)',

            /*
             * En Tailwind 4 el corchete NO envuelve la variable en var(): esto
             * compila a `transition-duration: --duracion-boton`, que el
             * navegador descarta en silencio y deja la animación en el default
             * de 150 ms. Nada falla a la vista, y por eso hace falta vigilarlo.
             */
            '/(?:duration|translate-[xy]|delay)-\[--/' => 'usa el paréntesis: duration-(--var), no el corchete, o el valor no se envuelve en var()',

            /*
             * Estos dos patrones son más anchos que el de arriba: cazan
             * cualquier valor arbitrario entre corchetes, no solo el que
             * intenta envolver una variable. Una duración, un retardo o una
             * curva escritos a mano en una vista es la misma improvisación
             * que la guardia ya prohíbe en `--duracion-\d+` y `ease-in`,
             * solo que con sintaxis de corchete en vez de número o palabra
             * clave.
             */
            '/(?:duration|delay)-\[/' => 'duración o retardo con valor arbitrario: usa los tokens duration-(--duracion-*)',
            '/ease-\[/' => 'curva arbitraria: usa las utilidades ease-* de los tokens',
        ];
    }

    /**
     * La dispersión no fue descuido de nadie: fue que 39 archivos decidían por
     * su cuenta. Esta prueba es la que hace que el sistema sobreviva a la
     * siguiente persona que edite una vista con prisa.
     */
    public function test_ninguna_vista_improvisa_movimiento(): void
    {
        $directorios = array_filter([
            resource_path('views/publico'),
            resource_path('views/components/publico'),
            resource_path('views/components/layouts'),
            resource_path('views/components/panel'),
            resource_path('views/filament'),
            resource_path('views/errors'),
            // Las vistas publicadas de paquetes también pintan en el sitio.
            resource_path('views/vendor'),
        ], File::isDirectory(...));

        $this->assertNotEmpty($directorios, 'No hay vistas que vigilar.');

        $hallazgos = [];

        foreach ($directorios as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                if ($archivo->getExtension() !== 'php') {
                    continue;
                }

                $contenido = $archivo->getContents();
                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                foreach (self::patronesProhibidos() as $patron => $motivo) {
                    if (preg_match_all($patron, $contenido, $coincidencias) > 0) {
                        $hallazgos[] = sprintf(
                            '%s → %s (%s)',
                            $ruta,
                            implode(', ', array_unique($coincidencias[0])),
                            $motivo
                        );
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Movimiento improvisado en vistas:\n".implode("\n", $hallazgos));
    }

    /**
     * Leaflet anima el fundido de teselas y el zoom por su cuenta, y el
     * barrido de CSS no lo alcanza porque son transiciones, no keyframes.
     * El control existe en su propia API: hay que consultarla allí.
     */
    public function test_el_mapa_consulta_la_preferencia_de_movimiento(): void
    {
        $mapa = File::get(resource_path('views/components/publico/mapa.blade.php'));

        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $mapa);
        $this->assertStringContainsString('fadeAnimation', $mapa);
        $this->assertStringContainsString('zoomAnimation', $mapa);
        $this->assertStringContainsString('markerZoomAnimation', $mapa);
    }

    /**
     * La cadena de submit primario estaba repetida IDÉNTICA ocho veces, y de
     * los 34 botones primarios 18 tenían `transition-colors` y 16 no: dos
     * botones iguales en padding y color se comportaban distinto al pasar el
     * ratón. Un componente es la única forma de que eso no vuelva a pasar.
     *
     * Ya no se afirma `duration-(--duracion-boton)` en el HTML renderizado:
     * la transición de color salió del portador (`$base`) y ahora vive
     * completa dentro de `.pulsable`, en `app.css`, para que la capa de
     * utilidades de Tailwind no vuelva a pisarla. Esa guardia la cubre
     * `test_el_boton_no_reintroduce_la_utilidad_que_pisa_al_portador`.
     */
    public function test_el_boton_rinde_las_dos_variantes_con_acuse_de_pulsacion(): void
    {
        $primaria = Blade::render(
            '<x-publico.boton>Enviar solicitud</x-publico.boton>'
        );

        $this->assertStringContainsString('Enviar solicitud', $primaria);
        $this->assertStringContainsString('<button', $primaria);
        $this->assertStringContainsString('type="submit"', $primaria);
        $this->assertStringContainsString('bg-marca-500', $primaria);
        $this->assertStringContainsString('pulsable', $primaria);

        $contorno = Blade::render(
            '<x-publico.boton variante="contorno" href="/directorio">Ver el directorio</x-publico.boton>'
        );

        $this->assertStringContainsString('<a', $contorno);
        $this->assertStringContainsString('href="/directorio"', $contorno);
        $this->assertStringContainsString('border-linea-fuerte', $contorno);
        $this->assertStringNotContainsString('bg-marca-500', $contorno);
    }

    /**
     * `.pulsable` vive en `@layer components`, y en Tailwind 4 una utilidad
     * de `@layer utilities` gana siempre a `@layer components` sin importar
     * especificidad. Si `transition-colors` volviera al portador, pisaría
     * de nuevo la transición de color de `.pulsable` — y con ella su
     * `transition-duration: 0ms` del `:active` — en los 43 botones del
     * sitio, tal como pasaba antes de este arreglo.
     */
    public function test_el_boton_no_reintroduce_la_utilidad_que_pisa_al_portador(): void
    {
        $componente = File::get(resource_path('views/components/publico/boton.blade.php'));

        $this->assertStringNotContainsString('transition-colors', $componente);
    }

    /** Nueve botones de envío llevaban `w-full ... sm:w-auto`: debe pasar. */
    public function test_el_boton_deja_pasar_las_clases_de_maquetacion(): void
    {
        $html = Blade::render(
            '<x-publico.boton class="w-full sm:w-auto">Enviar</x-publico.boton>'
        );

        $this->assertStringContainsString('w-full', $html);
        $this->assertStringContainsString('sm:w-auto', $html);
        $this->assertStringContainsString('bg-marca-500', $html);
    }

    /**
     * El prop se llama `tipo`, pero el atributo HTML nativo es `type`. Si la
     * Task 11 traduce mecánicamente un `<button type="button">` y escribe
     * `type="button"` en la etiqueta del componente, ese atributo llegaba por
     * `$attributes` y convivía con el que el componente emitía a mano: el
     * navegador se quedaba con la primera ocurrencia (`type="submit"`, el
     * default fijo) y descartaba la del llamador sin ningún error visible.
     */
    public function test_el_atributo_type_del_llamador_sobrescribe_el_tipo_por_defecto(): void
    {
        $conProp = Blade::render(
            '<x-publico.boton tipo="button">Cancelar</x-publico.boton>'
        );

        $this->assertStringContainsString('type="button"', $conProp);
        $this->assertSame(1, substr_count($conProp, 'type='));

        $conAtributoNativo = Blade::render(
            '<x-publico.boton type="button">Cancelar</x-publico.boton>'
        );

        $this->assertStringContainsString('type="button"', $conAtributoNativo);
        $this->assertStringNotContainsString('type="submit"', $conAtributoNativo);
        $this->assertSame(1, substr_count($conAtributoNativo, 'type='));
    }

    /**
     * La alerta es el único acuse de recibo del sitio tras enviar un
     * formulario, y aparecía de golpe. Entra por `transition` +
     * `@starting-style` y no por keyframes: así la cubre la mordaza del
     * cambio de tema, que apaga `transition` pero no `animation`.
     *
     * Y de paso: `role="status"` es una región educada, correcta para un
     * acuse pero no para un fallo. Un error necesita `role="alert"`.
     */
    public function test_la_alerta_entra_y_anuncia_el_error_como_error(): void
    {
        $exito = Blade::render(
            '<x-publico.alerta>Tu solicitud llegó.</x-publico.alerta>'
        );

        $this->assertStringContainsString('role="status"', $exito);
        $this->assertStringContainsString('alerta-animada', $exito);

        $error = Blade::render(
            '<x-publico.alerta tipo="error">No pudimos abrir la pasarela.</x-publico.alerta>'
        );

        $this->assertStringContainsString('role="alert"', $error);

        // El descargo estático de la guía no es un acuse: no debe animarse.
        $estatica = Blade::render(
            '<x-publico.alerta tipo="aviso" :animado="false">Texto fijo.</x-publico.alerta>'
        );

        $this->assertStringNotContainsString('alerta-animada', $estatica);
    }

    /**
     * El servidor ya se protege del doble cobro con la idempotencia de 24 h de
     * `MiCuentaController::cobroVigente`. Lo que faltaba era que la interfaz lo
     * contara: se pulsaba «Pagar ahora», no pasaba nada visible, y se volvía a
     * pulsar. En la pasarela hay que deshabilitar LOS DOS botones, no solo el
     * pulsado: viven en el mismo formulario.
     */
    public function test_los_botones_que_cobran_acusan_el_envio(): void
    {
        $cuenta = File::get(resource_path('views/publico/mi-cuenta/index.blade.php'));

        $this->assertStringContainsString('x-data="{ enviando: false }"', $cuenta);
        $this->assertStringContainsString('x-on:submit="enviando = true"', $cuenta);
        $this->assertStringContainsString('x-bind:disabled="enviando"', $cuenta);

        $pasarela = File::get(resource_path('views/publico/pago/simulado.blade.php'));

        $this->assertStringContainsString('enviando', $pasarela);

        // Los dos botones, no solo el pulsado.
        $this->assertSame(
            2,
            substr_count($pasarela, 'x-bind:disabled="enviando"'),
            'La pasarela debe deshabilitar los dos botones: viven en el mismo formulario.'
        );
    }

    /**
     * En un sitio de recarga completa, filtrar, paginar y abrir un detalle son
     * SIEMPRE navegación: no hay estado de cliente que animar. Las view
     * transitions son la única palanca real, y degradan a nada donde no haya
     * soporte.
     *
     * Viven en un árbol de pseudoelementos aparte, así que el barrido de
     * `*, *::before, *::after` NO las alcanza: necesitan su propia regla de
     * movimiento reducido o quedarían sin guarda.
     */
    public function test_las_transiciones_de_vista_tienen_su_propia_guarda(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('@view-transition', $app);
        $this->assertStringContainsString('navigation: auto', $app);
        $this->assertStringContainsString('::view-transition-old(root)', $app);
        $this->assertStringContainsString('animation-timing-function: var(--ease-out)', $app);

        // La guarda propia: el barrido con `*` no llega a estos pseudoelementos.
        $this->assertStringContainsString('::view-transition-group(*)', $app);
        $this->assertStringContainsString('animation: none !important', $app);
    }
}

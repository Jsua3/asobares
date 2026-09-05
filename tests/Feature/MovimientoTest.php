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
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms, con tres
        // excepciones con nombre: la apertura de la barra lateral, el
        // asentamiento del resorte de los popovers (spec del 3 sep 2026, D7)
        // y el cambio de estado de la barra de escritorio, un punto más lento
        // a petición de Sua (5 sep). Un resorte «llega» hacia los 250 ms; el
        // resto es la cola que se asienta, y cortarla es quitarle el rebote.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);
        $this->assertStringContainsString('--duracion-rebote: 520ms', $tokens);
        $this->assertStringContainsString('--duracion-estado: 620ms', $tokens);

        // Desplazamientos: son tokens y no literales porque el interruptor de
        // `prefers-reduced-motion` los anula sin tocar las duraciones.
        $this->assertStringContainsString('--asb-levante: -2px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: -4%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: -25%', $tokens);
    }

    /**
     * Tailwind por defecto funde en 150 ms con `cubic-bezier(0.4, 0, 0.2, 1)`,
     * y ahí caían 21 de las 22 `transition-colors` del sitio público: las que
     * no escriben `duration-*` no caen en los tokens, caen en el default. Se
     * cierra declarando el default con los tokens, no editando 21 vistas.
     *
     * La UBICACIÓN es la mitad de la prueba. `--default-transition-*` es
     * variable de Tailwind, no nuestra, y `tokens.css` lo importa también el
     * tema del panel: declararla allí reescribiría el reloj de todas las
     * transiciones de Filament en /admin —barra lateral, modales, tablas,
     * notificaciones— con una curva de COLOR aplicada a paneles que deslizan,
     * y sin que nadie lo haya pedido ni medido.
     */
    public function test_el_reloj_por_defecto_de_tailwind_usa_los_tokens(): void
    {
        $app = File::get(resource_path('css/app.css'));
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertMatchesRegularExpression(
            '/@theme\s*\{[^}]*--default-transition-duration:\s*var\(--duracion-boton\)\s*;/',
            $app,
            'El default de duración debe declararse en un @theme de app.css con el token.'
        );
        $this->assertMatchesRegularExpression(
            '/@theme\s*\{[^}]*--default-transition-timing-function:\s*var\(--ease-color\)\s*;/',
            $app,
            'El default de curva debe declararse en un @theme de app.css con el token.'
        );

        $this->assertStringNotContainsString(
            '--default-transition',
            $tokens,
            'El default de Tailwind en tokens.css cambiaría en silencio el reloj de /admin.'
        );
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

    /**
     * `.pulsable` acusaba el dedo en 43 botones y nada más: encoger un 3 % una
     * fila de 350 px se lee como una arruga y sobre texto en prosa saca las
     * letras de la rejilla de píxeles. De ahí que sean tres portadores y no
     * uno, cada uno con la respuesta que le corresponde a su forma.
     *
     * Lo que se vigila aquí no es que existan —eso se ve— sino las tres cosas
     * que se pierden en silencio si alguien los reescribe:
     */
    public function test_los_portadores_de_acuse_cubren_las_tres_formas_de_control(): void
    {
        $app = File::get(resource_path('css/app.css'));
        $tokens = File::get(resource_path('css/tokens.css'));

        /*
         * 1. `.tarjeta-pulsable` declara la transición COMPLETA y viaja junto a
         *    `.tarjeta-hover`. Dos atajos `transition` sobre el mismo elemento
         *    no se suman: gana el último de la capa. Si alguien recorta este a
         *    `transform`, el fundido del borde a rojo muere en las diez
         *    tarjetas sin ningún error.
         */
        $this->assertMatchesRegularExpression(
            '/\.tarjeta-pulsable\s*\{[^}]*\bborder-color\b/',
            $app,
            '.tarjeta-pulsable pisa a .tarjeta-hover: si no nombra border-color, el borde deja de fundir.'
        );

        /*
         * 2. Y se define DESPUÉS del `:hover` de `.tarjeta-hover`, que es lo
         *    que hace que su `:active` gane al levante y la tarjeta se hunda
         *    al pulsarla en vez de quedarse arriba.
         */
        $this->assertGreaterThan(
            strpos($app, '.tarjeta-hover:hover'),
            strpos($app, '.tarjeta-pulsable'),
            '.tarjeta-pulsable tiene que ir después de .tarjeta-hover:hover o pierde por orden.'
        );

        /*
         * 3. Los tres acuses salen de tokens y no de literales, porque el
         *    encogimiento es geometría y el interruptor de movimiento reducido
         *    lo anula por token. Los otros dos son fundidos y sobreviven: eso
         *    es la regla del proyecto escrita en CSS.
         */
        $this->assertMatchesRegularExpression(
            '/\.tarjeta-pulsable:active\s*\{[^}]*transform:\s*scale\(var\(--asb-encogimiento-tarjeta\)\)/',
            $app,
            'El encogimiento de la tarjeta debe salir del token, o el movimiento reducido no puede anularlo.'
        );
        $this->assertMatchesRegularExpression(
            '/\.fila-pulsable:active\s*\{[^}]*background-color:\s*var\(--asb-fila-pulsada\)/',
            $app
        );
        $this->assertMatchesRegularExpression(
            '/\.enlace-accion:active\s*\{[^}]*opacity:\s*var\(--asb-atenuacion-pulsada\)/',
            $app
        );

        $this->assertStringContainsString('--asb-atenuacion-pulsada: 0.55', $tokens);
        $this->assertStringContainsString('--asb-encogimiento-tarjeta: 0.985', $tokens);

        // La fila se tiñe con velo en claro y con luz en oscuro: sobre Pub
        // Black un velo negro no existe. Es la misma receta doble del vidrio.
        $this->assertStringContainsString('--asb-fila-pulsada: rgb(11 9 10 / 0.09)', $tokens);
        $this->assertStringContainsString('--asb-fila-pulsada: rgb(255 255 255 / 0.11)', $tokens);

        // Se anula la geometría y NADA más: los otros dos son fundidos.
        $this->assertStringContainsString('--asb-encogimiento-tarjeta: 1;', $tokens);
        $this->assertStringNotContainsString('--asb-atenuacion-pulsada: 1', $tokens);
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

            /*
             * En Tailwind 4 las utilidades de movimiento no compilan a
             * `transform`, sino a las propiedades independientes `translate`,
             * `scale` y `rotate`. Una transición que solo declara `transform`
             * no anima ninguna de las tres: la opacidad funde y la geometría
             * salta, sin ningún error visible. Los tres desplegables del
             * sitio estuvieron así.
             *
             * Se permite nombrar `transform` si además se nombra `translate`,
             * porque entonces quien lo escribió sabía que son distintas.
             */
            '/transition-\[[^\]]*\btransform\b(?![^\]]*\btranslate\b)/' => 'translate/scale/rotate no son `transform` en Tailwind 4: usa el portador .transicion-desplegable',

            /*
             * Poppins no dibuja flechas. Su subconjunto trae 217 glifos por
             * peso y no incluye ni U+2191 ni U+2193, que el `@font-face` sí
             * promete en su `unicode-range`; U+2192, U+2190 y U+2197 ni
             * siquiera entran en ese rango. Una flecha escrita como carácter
             * la pinta la fuente del sistema y nadie la ve fallar: solo se ve
             * distinta en cada equipo, en medio de una línea en Poppins.
             * Van por `<x-publico.flecha />`, que es un SVG y hereda color y
             * tamaño del texto.
             *
             * Ojo al editar este archivo: el barrido lee el fichero crudo,
             * comentarios incluidos, así que aquí los codepoints se nombran y
             * no se pegan.
             */
            '/[\x{2190}-\x{21FF}\x{25B6}\x{25C0}\x{2B00}-\x{2BFF}]/u' => 'Poppins no trae glifos de flecha: usa <x-publico.flecha /> en vez del carácter',
        ];
    }

    /**
     * El portador que arregla la trampa de arriba tiene que existir y tiene
     * que nombrar las cuatro propiedades. Si alguien lo recorta a `transform`
     * volvemos al punto de partida con la guardia en verde.
     */
    public function test_el_portador_de_los_desplegables_cubre_las_propiedades_reales(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('.transicion-desplegable', $app);
        $this->assertMatchesRegularExpression(
            '/\.transicion-desplegable\s*\{[^}]*transition-property:[^;]*\btranslate\b[^;]*;/',
            $app,
            '.transicion-desplegable debe nombrar `translate`, que es la propiedad que de verdad anima el desplazamiento.'
        );

        foreach (['scale', 'rotate'] as $propiedad) {
            $this->assertMatchesRegularExpression(
                '/\.transicion-desplegable\s*\{[^}]*transition-property:[^;]*\b'.$propiedad.'\b[^;]*;/',
                $app,
                ".transicion-desplegable debe nombrar `{$propiedad}`."
            );
        }
    }

    /**
     * Todos los desplegables tienen que usar el portador. Si uno se queda con
     * su propia lista, se mueve distinto que los demás y nadie lo nota.
     *
     * Eran tres —menú móvil, hamburguesa y menú de usuario— y con la
     * reagrupación de la barra son cinco: los dos grupos de escritorio salen
     * del mismo componente, así que basta con que ese componente entre en la
     * lista.
     */
    public function test_los_desplegables_usan_el_portador(): void
    {
        $vistas = [
            'components/publico/navbar.blade.php',
            'components/publico/menu-usuario.blade.php',
            'components/publico/menu-grupo.blade.php',
        ];

        foreach ($vistas as $vista) {
            $contenido = File::get(resource_path('views/'.$vista));

            $this->assertStringContainsString(
                'transicion-desplegable',
                $contenido,
                "{$vista} declara transiciones de desplegable sin el portador."
            );
        }
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
                    $veces = preg_match_all($patron, $contenido, $coincidencias);

                    /*
                     * Desde que hay un patrón con el modificador `/u`, este
                     * barrido puede fallar en vez de no encontrar nada: sobre
                     * un archivo que no sea UTF-8 válido, `preg_match_all`
                     * devuelve `false`, y `false > 0` es falso. El archivo se
                     * saltaría en silencio, que es exactamente el modo de
                     * fallo contra el que existe toda esta clase.
                     */
                    $this->assertNotFalse(
                        $veces,
                        "No se pudo barrer {$ruta} con {$patron}: revisa que el archivo sea UTF-8 válido."
                    );

                    if ($veces > 0) {
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

    /**
     * Los cuatro portadores de acuse: los que declaran su propia `transition`
     * COMPLETA y un `:active` con `transition-duration: 0ms`.
     *
     * `.transicion-desplegable` no está aquí, y es la excepción explícita: es el
     * único portador del sistema que dice QUÉ se anima y deja el CUÁNTO a la
     * vista, así que convive con `duration-(--duracion-*)` y `ease-*` por
     * diseño. Meterlo en esta lista pondría en rojo los tres desplegables.
     *
     * @return list<string>
     */
    public static function portadoresDeAcuse(): array
    {
        return ['pulsable', 'enlace-accion', 'fila-pulsable', 'tarjeta-pulsable'];
    }

    /**
     * Toda lista de clases de un archivo, venga de donde venga: el atributo
     * `class`, los que escribe Alpine (`:class`, `x-bind:class`), las cadenas de
     * `@class([...])` —donde viven los chips y los conmutadores— y las variables
     * de cadena de un bloque `@php`.
     *
     * Las variables no son un extra. El paginador declara su lista una sola vez
     * en `$enlace` y la pinta cinco veces: un barrido que solo mirara
     * `class="..."` no vería la única línea del repositorio donde había que
     * quitar TRES utilidades en vez de una.
     *
     * @return list<string>
     */
    private function listasDeClases(string $contenido): array
    {
        $listas = [];

        preg_match_all('/(?:x-bind)?:?class="([^"]*)"/s', $contenido, $atributos);
        foreach ($atributos[1] as $lista) {
            $listas[] = $lista;
        }

        preg_match_all('/@class\(\[(.*?)\]\)/s', $contenido, $arreglos);
        foreach ($arreglos[1] as $arreglo) {
            preg_match_all("/'([^'\n]*)'/", $arreglo, $cadenas);
            foreach ($cadenas[1] as $lista) {
                $listas[] = $lista;
            }
        }

        preg_match_all('/\$\w+ = (?:"([^"\n]*)"|\'([^\'\n]*)\');/', $contenido, $variables);
        foreach (array_merge($variables[1], $variables[2]) as $lista) {
            $listas[] = $lista;
        }

        return $listas;
    }

    /**
     * Lo mismo pero por ELEMENTO: todas las listas de clases de una etiqueta
     * fundidas en una, para pillar al portador y a la utilidad repartidos entre
     * dos atributos de la misma etiqueta (`class` y `x-bind:class`). El
     * navegador los ve juntos y la vista por listas no.
     *
     * Se funden solo los atributos de CLASE, no la etiqueta entera: un
     * `data-algo="transition-colors"` no pinta nada y no es un defecto.
     *
     * Se quitan antes los valores de `x-transition:*`: son clases que Alpine
     * pone solo mientras dura la transición y llevan `duration-*` por diseño.
     * Sin quitarlas, el único sitio donde la convivencia es CORRECTA sería el
     * único que saldría en rojo.
     *
     * El `(?<![=-])` del cierre es lo que deja pasar `=>` y `->` sin cortar la
     * etiqueta: dentro de un `@class([...])` y de un `{{ $a->b }}` hay `>` que
     * no cierran nada.
     *
     * @return list<string>
     */
    private function elementos(string $contenido): array
    {
        $sinAlpine = preg_replace('/x-transition:[\w-]+="[^"]*"/', '', $contenido);

        preg_match_all('/<[a-zA-Z][^<]*?(?<![=-])>/s', $sinAlpine, $etiquetas);

        $elementos = [];

        foreach ($etiquetas[0] as $etiqueta) {
            $listas = $this->listasDeClases($etiqueta);

            if ($listas !== []) {
                $elementos[] = implode(' ', $listas);
            }
        }

        return $elementos;
    }

    /**
     * Un portador y una utilidad de transición en el mismo elemento: el modo de
     * fallo que no da ningún error y ya costó cinco rondas.
     *
     * En Tailwind 4 una utilidad de `@layer utilities` gana SIEMPRE a un
     * portador de `@layer components`, sin importar la especificidad. Así que
     * una `transition-colors`, una `duration-*` o una `ease-*` sueltas en la
     * misma etiqueta reescriben la `transition` que el portador declara — y con
     * ella el `transition-duration: 0ms` de su `:active`. El control deja de
     * acusar el dedo, o lo acusa 140 ms tarde. Nada peta y nada se ve rojo.
     *
     * La peor de las tres es `duration-(--duracion-boton)`: usa el paréntesis,
     * usa el token y pasa todos los patrones prohibidos de arriba. Parece
     * exactamente lo que el proyecto pide escribir, y es la que mata el acuse.
     * Estuvo en el paginador, que alimenta cinco anclajes en ocho vistas.
     *
     * Se barre en dos pasadas porque el defecto puede esconderse en dos sitios:
     * por LISTA (que alcanza a la variable PHP del paginador, fuera de toda
     * etiqueta) y por ETIQUETA (que alcanza a los dos atributos de clase de un
     * mismo elemento).
     */
    public function test_ningun_portador_de_acuse_convive_con_una_utilidad_que_lo_pise(): void
    {
        $directorios = array_filter([
            resource_path('views/publico'),
            resource_path('views/components/publico'),
            resource_path('views/components/layouts'),
            resource_path('views/components/panel'),
            resource_path('views/filament'),
            resource_path('views/errors'),
            resource_path('views/vendor'),
        ], File::isDirectory(...));

        $this->assertNotEmpty($directorios, 'No hay vistas que vigilar.');

        // El prefijo opcional son las variantes: `sm:`, `focus:`, `hover:`...
        $utilidadDeReloj = '/^(?:[^\s:]+:)*(?:transition|duration|ease|delay)(?:-|$)/';

        $hallazgos = [];

        foreach ($directorios as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                if ($archivo->getExtension() !== 'php') {
                    continue;
                }

                $contenido = $archivo->getContents();
                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                $trozos = array_merge(
                    $this->listasDeClases($contenido),
                    $this->elementos($contenido),
                );

                foreach ($trozos as $trozo) {
                    /*
                     * Las comillas se caen del token porque un `x-bind:class`
                     * es una EXPRESIÓN, no una lista: la clase viaja dentro de
                     * `'…'` en cada rama del ternario.
                     */
                    $piezas = array_map(
                        static fn (string $pieza): string => trim($pieza, "'\"`"),
                        preg_split('/\s+/', trim($trozo)) ?: []
                    );

                    $portadores = array_values(array_intersect(self::portadoresDeAcuse(), $piezas));

                    if ($portadores === []) {
                        continue;
                    }

                    foreach ($piezas as $pieza) {
                        if (preg_match($utilidadDeReloj, $pieza)) {
                            $hallazgos[] = sprintf(
                                '%s -> `.%s` convive con `%s`: la utilidad pisa al portador y muere su :active',
                                $ruta,
                                implode('`/`.', $portadores),
                                $pieza
                            );
                        }
                    }

                    /*
                     * `.fila-pulsable` tiñe el fondo en `:active` y trae su
                     * propio `:hover` tras la puerta táctil. Una utilidad
                     * `hover:bg-*` no es una transición, pero escribe la MISMA
                     * propiedad desde la capa que gana: mientras el puntero está
                     * encima, el fondo del `:active` no llega a verse.
                     */
                    if (in_array('fila-pulsable', $portadores, true)) {
                        foreach ($piezas as $pieza) {
                            if (str_starts_with($pieza, 'hover:bg-')) {
                                $hallazgos[] = sprintf(
                                    '%s -> `.fila-pulsable` convive con `%s`: el hover se declara en el portador, no en la vista',
                                    $ruta,
                                    $pieza
                                );
                            }
                        }
                    }
                }
            }
        }

        $hallazgos = array_values(array_unique($hallazgos));

        $this->assertSame([], $hallazgos, "Portadores pisados por una utilidad:\n".implode("\n", $hallazgos));
    }

    /**
     * Las once filas de los dos desplegables cambiaron de dueño: su fondo de
     * hover se mudó de la vista al portador, porque en la vista pisaba al
     * `:active`. Si alguien devuelve la utilidad, la prueba de arriba lo caza;
     * si alguien quita el portador y se olvida de devolverla, esas filas se
     * quedan sin ningún hover en escritorio y no lo caza nadie. Esto es esa
     * segunda mitad.
     *
     * Las seis filas de los dos grupos nuevos de la barra nacieron ya con esta
     * regla, y entran aquí para que no puedan salirse de ella.
     */
    public function test_las_filas_de_los_dos_desplegables_ceden_su_hover_al_portador(): void
    {
        $vistas = [
            'components/publico/navbar.blade.php',
            'components/publico/menu-usuario.blade.php',
            'components/publico/menu-grupo.blade.php',
        ];

        foreach ($vistas as $vista) {
            $contenido = File::get(resource_path('views/'.$vista));

            $this->assertStringContainsString(
                'fila-pulsable',
                $contenido,
                "{$vista} perdió el portador de acuse de sus filas: en táctil no hay hover, así que el :active era el único acuse que existía."
            );

            $this->assertStringNotContainsString(
                'hover:bg-superficie-alta',
                $contenido,
                "{$vista} declara el fondo de hover en la vista: `.fila-pulsable` lo trae detrás de la puerta táctil y la utilidad lo pisaría."
            );
        }
    }

    /**
     * El paginador aparte, porque es el peor caso del inventario y el único
     * donde había que quitar tres utilidades y no una. Vale la pena que el
     * mensaje de fallo las nombre en vez de que salgan en una lista genérica.
     */
    public function test_la_paginacion_acusa_el_dedo_y_no_recupera_su_reloj_propio(): void
    {
        $paginador = File::get(resource_path('views/vendor/pagination/tailwind.blade.php'));

        $this->assertStringContainsString(
            'pulsable',
            $paginador,
            'Los cinco anclajes del paginador salen de $enlace: sin el portador, ninguno acusa el dedo.'
        );

        foreach (['transition-colors', 'duration-(--duracion-boton)', 'ease-color'] as $utilidad) {
            $this->assertStringNotContainsString(
                $utilidad,
                $paginador,
                "El paginador recuperó `{$utilidad}`: pisa a `.pulsable` y la pastilla vuelve a bajar con retardo."
            );
        }
    }

    /**
     * Los dos botones de zoom del mapa son los únicos controles del sitio cuyo
     * CSS no escribimos: lo sirve Leaflet desde un CDN, sin capa, y una regla en
     * `@layer components` pierde siempre contra una hoja sin capa. Por eso su
     * acuse vive junto al `<link>`, en el componente, y no en `app.css`.
     */
    public function test_los_controles_del_mapa_acusan_el_dedo_desde_el_componente(): void
    {
        $mapa = File::get(resource_path('views/components/publico/mapa.blade.php'));
        $app = File::get(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.leaflet-control-zoom a:active\s*\{[^}]*transform:\s*scale\(var\(--asb-encogimiento-control\)\)/',
            $mapa,
            'El acuse del zoom sale del token, o el movimiento reducido no puede anularlo.'
        );

        $this->assertStringNotContainsString(
            'leaflet-control-zoom',
            $app,
            'En `app.css` la regla iría en capa y perdería contra la hoja del CDN: va en el componente.'
        );
    }
}

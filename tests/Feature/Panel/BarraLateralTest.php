<?php

namespace Tests\Feature\Panel;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * La barra lateral del panel: cristal, luz y resorte (Parte III de la spec,
 * aprobada por Sua el 7 sep 2026).
 *
 * Esta clase vigila lo que ninguna otra puede: que la barra no vuelva a tener
 * paleta privada, que su material viva en los pseudoelementos, que las señales
 * del sistema la alcancen, y que el velo sostenga por aritmética el rótulo de
 * grupo, que mide 11,52 px y por tanto es texto pequeño sin discusión.
 */
class BarraLateralTest extends TestCase
{
    use MideContraste;

    /** Los diecinueve tokens de la Parte III, en el orden de su tabla. */
    private const TOKENS = [
        '--asb-admin-barra-velo',
        '--asb-admin-barra-velo-cajon',
        '--asb-admin-barra-desenfoque',
        '--asb-admin-barra-luz',
        '--asb-admin-barra-halo',
        '--asb-admin-barra-borde',
        '--asb-admin-barra-tinta',
        '--asb-admin-barra-tenue',
        '--asb-admin-barra-activo',
        '--asb-admin-barra-activo-fondo',
        '--asb-admin-barra-hover-fondo',
        '--asb-admin-barra-fila-alto',
        '--asb-admin-barra-fila-radio',
        '--asb-admin-barra-aviso-alto',
        '--asb-admin-barra-brote',
        '--asb-admin-barra-empuje',
        '--asb-admin-foco-anillo',
        '--asb-admin-foco-halo',
    ];

    private function tema(): string
    {
        return File::get(resource_path('css/filament/admin/theme.css'));
    }

    /**
     * El bloque de nivel superior que abre con `$marca`, contando llaves. No
     * sirve `strstr`: el archivo anida medias dentro de medias.
     */
    private function bloque(string $css, string $marca): string
    {
        $inicio = strpos($css, $marca);
        $this->assertNotFalse($inicio, "no existe el bloque {$marca}");

        $profundidad = 0;
        $desde = strpos($css, '{', $inicio);

        for ($i = $desde; $i < strlen($css); $i++) {
            if ($css[$i] === '{') {
                $profundidad++;
            } elseif ($css[$i] === '}') {
                $profundidad--;

                if ($profundidad === 0) {
                    return substr($css, $desde, $i - $desde + 1);
                }
            }
        }

        $this->fail("el bloque {$marca} no cierra");
    }

    /**
     * Un token declarado dos veces en el mismo bloque deja sin efecto a la
     * media que lo reasigna, y nadie se entera: es exactamente el defecto que
     * `--asb-vidrio-desenfoque` arrastraba en este archivo.
     * Rotura: declarar dos veces cualquiera de los diecinueve, o borrar uno.
     */
    public function test_los_tokens_de_la_barra_se_declaran_una_sola_vez_en_su_raiz(): void
    {
        $tema = $this->tema();
        $raiz = $this->bloque($tema, "\n:root {");

        foreach (self::TOKENS as $token) {
            $veces = substr_count($raiz, $token.':');

            $this->assertSame(1, $veces, "{$token} se declara {$veces} veces en el `:root` del tema, y tiene que ser una.");
        }
    }

    /**
     * Los que cambian con el tema se redeclaran en `.dark`; los de geometría y
     * los de movimiento no, porque no dependen del tema.
     * Rotura: mover `--asb-admin-barra-fila-alto` al bloque oscuro.
     */
    public function test_solo_los_tokens_de_color_se_redeclaran_en_oscuro(): void
    {
        $oscuro = $this->bloque($this->tema(), "\n.dark {");

        foreach (['--asb-admin-barra-luz', '--asb-admin-barra-activo', '--asb-admin-foco-anillo'] as $token) {
            $this->assertStringContainsString($token.':', $oscuro, "{$token} depende del tema y falta en `.dark`.");
        }

        foreach (['--asb-admin-barra-fila-alto', '--asb-admin-barra-fila-radio', '--asb-admin-barra-brote', '--asb-admin-barra-empuje'] as $token) {
            $this->assertStringNotContainsString($token.':', $oscuro, "{$token} es geometría o movimiento: no puede depender del tema.");
        }
    }

    /**
     * `tokens.css` apaga `--asb-vidrio-desenfoque` bajo transparencia reducida,
     * pero el tema lo redeclaraba después con la misma especificidad y la
     * anulación no llegaba: el panel seguía desenfocando para quien pidió que
     * no. Afectaba al vidrio de ModerarFotos y al widget de pendientes.
     * Rotura: borrar el bloque de transparencia reducida del tema.
     */
    public function test_la_transparencia_reducida_alcanza_al_vidrio_del_panel(): void
    {
        $tema = $this->tema();

        $this->assertStringContainsString(
            '@media (prefers-reduced-transparency: reduce)',
            $tema,
            'El tema redeclara `--asb-vidrio-desenfoque` después del import, así que tiene que reasignarlo él mismo bajo transparencia reducida.'
        );

        $reducida = $this->bloque($tema, '@media (prefers-reduced-transparency: reduce)');

        $this->assertMatchesRegularExpression(
            '/--asb-vidrio-desenfoque:\s*none;/',
            $reducida,
            'La transparencia reducida no apaga el desenfoque del vidrio del panel.'
        );
    }

    /**
     * El material va en los pseudoelementos y nunca en el elemento. Un
     * `backdrop-filter` en el elemento lo vuelve raíz de fondo y deja a todo lo
     * que anide dentro sin página que desenfocar, así que el halo del ítem
     * activo no funcionaría. Y el desenfoque solo se consume en el cajón: en
     * escritorio la barra es `lg:sticky` y detrás no pasa contenido, así que
     * ahí desenfocar es pagar compositor por nada (medido el 7 sep).
     * Rotura: mover el `backdrop-filter` al elemento, o sacarlo de la media.
     */
    public function test_el_material_de_la_barra_vive_en_los_pseudoelementos(): void
    {
        $tema = $this->tema();
        $elemento = $this->regla($tema, '.fi-sidebar');

        $this->assertStringNotContainsString('backdrop-filter', $elemento, 'El desenfoque no puede ir en el elemento: lo vuelve raíz de fondo para todo lo que anide dentro.');
        $this->assertStringNotContainsString('filter:', $elemento, 'Ningún filtro en el elemento, por la misma razón.');
        $this->assertMatchesRegularExpression('/background:\s*transparent;/', $elemento, 'Filament da fondo opaco propio a la barra bajo `lg`: hay que ponerlo transparente explícitamente o el cajón queda opaco bajo el velo.');

        // Desde D-L26 el velo no lo pinta la barra sino cada apartado: el campo
        // de puntos es el fondo de toda la interfaz y tiene que verse también
        // debajo de la barra.
        $velo = $this->regla($tema, '.fi-sidebar-group::before');
        $this->assertStringContainsString('var(--asb-admin-barra-velo)', $velo, 'El velo vive en el `::before` de cada apartado.');
        $suelo = $this->regla($tema, '.fi-sidebar::before');
        $this->assertStringNotContainsString('var(--asb-admin-barra-velo)', $suelo, 'La barra no puede pintar velo propio: taparía el campo de puntos.');
        $this->assertStringContainsString('var(--asb-admin-barra-resplandor)', $suelo, 'Sin fondo propio, lo que marca la zona de la barra es su resplandor.');
        $this->assertStringNotContainsString('filter: blur', $suelo, 'El resplandor va con un radial: un `filter` obligaría al compositor a rehacerlo en cada fotograma del campo.');

        // Todo consumo del desenfoque tiene que caer dentro de la media del
        // cajón: se cuenta en el archivo entero y dentro del bloque, y los dos
        // números tienen que coincidir.
        $cajon = $this->bloque($tema, '@media (max-width: 63.999rem)');
        $enElArchivo = substr_count($tema, 'var(--asb-admin-barra-desenfoque)');
        $enElCajon = substr_count($cajon, 'var(--asb-admin-barra-desenfoque)');

        $this->assertGreaterThan(0, $enElCajon, 'El cajón no consume el desenfoque, que es el único sitio donde hay página que refractar.');
        $this->assertSame($enElArchivo, $enElCajon, "El desenfoque se consume {$enElArchivo} veces y solo {$enElCajon} caen en la media del cajón: fuera de ahí no desenfoca nada y cuesta compositor.");
    }

    /**
     * El límite de la región lo hacen una línea y una sombra, en luminancia. El
     * filo luminiscente va encima como segunda capa: el rojo claro contra la
     * página clara da 2,60:1 y el oscuro contra la oscura 1,93:1, así que
     * ninguno de los dos llega solo a los 3:1 que pide un borde de región.
     * Rotura: quitar la línea y dejar solo el filo.
     */
    public function test_el_limite_de_la_barra_no_lo_hace_solo_la_luz(): void
    {
        $tema = $this->tema();
        $elemento = $this->regla($tema, '.fi-sidebar');

        // D-L25 sustituyó la línea por la unión: lo que separa ya no es un
        // canto, sino el fondo propio de la barra (el campo de puntos) más la
        // sombra y el degradado que la unen al contenido.
        $this->assertStringNotContainsString('border-inline-end', $elemento, 'La barra no puede separarse con un canto: se une con la sombra de su pseudoelemento.');

        // Lo único que dibuja `::after` es el resplandor de la esquina: desde
        // que Sua pidió continuidad, ahí no va nada que separe.
        $capa = $this->regla($tema, '.fi-sidebar::after');
        $this->assertStringContainsString('var(--asb-admin-barra-halo)', $capa, 'Se perdió el resplandor de la esquina, que es de donde nace la luz.');
    }

    /**
     * Se acabó la paleta privada. La barra tenía nueve colores en hexadecimal
     * repartidos en dos bloques, y el segundo, el de puntero fino, es el que
     * se olvida: ahí vivían `rgb(255 255 255)`, `rgb(255 255 255 / 0.06)`,
     * `#ff8a82` y `#ff7168`. Con colores propios la barra no sigue al tema, y
     * el día que la paleta se mueva se queda atrás.
     * Rotura: devolver `#ff7168` al rótulo del ítem activo.
     */
    /**
     * Se acabó la paleta privada. La barra tenía nueve colores en hexadecimal
     * repartidos en dos bloques, y el segundo, el de puntero fino, es el que
     * se olvida: ahí vivían `rgb(255 255 255)`, `rgb(255 255 255 / 0.06)`,
     * `#ff8a82` y `#ff7168`. Con colores propios la barra no sigue al tema, y
     * el día que la paleta se mueva se queda atrás.
     *
     * Barrido por líneas y no por bloques: el archivo anida medias dentro de
     * medias y las listas de selectores ocupan varias líneas, así que partir
     * por llaves se equivoca de bloque sin avisar.
     * Rotura: devolver `#ff7168` al rótulo del ítem activo.
     */
    public function test_ningun_selector_de_la_barra_lleva_un_color_literal(): void
    {
        $sinComentarios = preg_replace('#/\*.*?\*/#s', '', $this->tema());

        $hallazgos = [];
        $selector = null;
        $acumulado = '';

        foreach (explode('
', $sinComentarios) as $numero => $linea) {
            $limpia = trim($linea);

            if ($limpia === '') {
                continue;
            }

            // Una lista de selectores puede ocupar varias líneas: se acumulan
            // hasta la que abre la llave.
            if (str_ends_with($limpia, ',')) {
                $acumulado .= ' '.$limpia;

                continue;
            }

            if (str_ends_with($limpia, '{')) {
                $encabezado = trim($acumulado.' '.substr($limpia, 0, -1));
                $selector = str_contains($encabezado, '.fi-sidebar') ? $encabezado : null;
                $acumulado = '';

                continue;
            }

            $acumulado = '';

            if ($limpia === '}') {
                $selector = null;

                continue;
            }

            if ($selector === null) {
                continue;
            }

            foreach (['/#[0-9a-fA-F]{3,8}/', '/\brgba?\(/', '/\bhsla?\(/', '/\boklch\(/'] as $patron) {
                if (preg_match($patron, $limpia)) {
                    $hallazgos[] = sprintf('línea %d, %s: %s', $numero + 1, $selector, $limpia);

                    break;
                }
            }
        }

        $this->assertSame([], $hallazgos, 'La barra vuelve a tener paleta privada:
'.implode('
', $hallazgos));
    }

    /**
     * El foco no puede depender del puntero. El único `:focus-visible` de la
     * barra vivía dentro de `@media (hover: hover) and (pointer: fine)`, así
     * que en un portátil táctil o en una tableta con teclado la barra se
     * navegaba sin ningún indicador, con el contorno nativo ya quitado por
     * Filament. Se comprueba por posición en el archivo, que es lo que de
     * verdad falla: la regla existía y no aplicaba.
     * Rotura: devolver el `:focus-visible` al bloque de puntero fino.
     */
    public function test_el_foco_de_la_barra_no_depende_del_puntero(): void
    {
        $tema = $this->tema();
        $puntero = $this->bloque($tema, '@media (hover: hover) and (pointer: fine)');

        $this->assertStringContainsString('.fi-sidebar .fi-sidebar-item-btn:focus-visible', $tema, 'La barra no declara foco visible en ningún sitio.');
        $atrapadas = [];

        foreach (explode('
', $puntero) as $linea) {
            if (str_contains($linea, '.fi-sidebar') && str_contains($linea, 'focus-visible')) {
                $atrapadas[] = trim($linea);
            }
        }

        $this->assertSame([], $atrapadas, 'El foco de la barra sigue atrapado dentro de la media de puntero fino:
'.implode('
', $atrapadas));

        foreach (['--asb-admin-foco-anillo', '--asb-admin-foco-halo'] as $token) {
            $this->assertStringContainsString($token, $tema, "El anillo de foco de dos colores no usa {$token}.");
        }
    }

    /**
     * Las 24 filas medían 43,5 px el 7 sep en el panel de Sua, y ninguna
     * pasaba la comprobación de las cuatro esquinas del cuadrado de 44. La
     * altura sale de un token para que la medición y el CSS no se separen.
     * Rotura: devolver la fila a 2.72rem.
     */
    public function test_la_fila_de_la_barra_llega_a_los_44_px(): void
    {
        $tema = $this->tema();
        $raiz = $this->bloque($tema, '
:root {');

        $this->assertSame(
            1,
            preg_match('/--asb-admin-barra-fila-alto: ([0-9.]+)rem;/', $raiz, $alto),
            'La altura de la fila no se declara en un token.'
        );

        $enPixeles = ((float) $alto[1]) * 16;

        $this->assertGreaterThanOrEqual(44, $enPixeles, "La fila declara {$alto[1]}rem, que son {$enPixeles} px: por debajo del mínimo táctil.");

        // 48 y no 44 es elección, no mínimo: la corrección del 7 sep subió la
        // fila para que la barra respirara, sabiendo el coste (la lista pasa de
        // 1.216 a 1.312 px y se corta el 22 % en vez del 16 % a 1.019 de hueco).
        // Bajarla otra vez deshace esa decisión sin que nadie se entere.
        $this->assertSame(48.0, $enPixeles, "La fila declara {$alto[1]}rem: la decisión del 7 sep fue 3rem, o sea 48 px.");

        $this->assertStringContainsString(
            'min-height: var(--asb-admin-barra-fila-alto);',
            $this->regla($tema, '.fi-sidebar .fi-sidebar-item-btn'),
            'La fila no consume el token de altura.'
        );
    }

    /**
     * La barra es UNA superficie, no cajas dentro de cajas (corrección del
     * 7 sep). Con los módulos encajados la fila se quedaba en 210 px útiles de
     * 244, y los cantos apilaban tres niveles: barra, módulo y fila. El ritmo
     * lo hace el aire.
     *
     * Los tokens de módulo siguen vivos, pero solo para las capas que flotan
     * sobre la página: la hoja de la cuenta y el popover del tema. Ahí el canto
     * sí separa algo.
     * Rotura: devolver el canto al módulo de navegación.
     */
    public function test_la_barra_es_una_sola_superficie(): void
    {
        $tema = $this->tema();

        foreach (['.fi-sidebar-nav', '.fi-sidebar-header'] as $selector) {
            $this->assertStringNotContainsString(
                'var(--asb-admin-barra-modulo-canto)',
                $this->regla($tema, $selector),
                "{$selector} vuelve a ser una caja dentro de la barra."
            );
        }

        $this->assertStringNotContainsString(
            'body[data-barra-estado="scroll"] .fi-sidebar-nav',
            preg_replace('/\s+/', ' ', $tema),
            'El estado del desplazamiento ya no enciende ningún módulo: alimenta solo el aviso de lista cortada.'
        );

        // Las capas que sí flotan conservan su canto.
        foreach (['.asb-barra-hoja', '.asb-panel-tema__popover'] as $flotante) {
            $this->assertStringContainsString(
                'var(--asb-admin-barra-modulo-canto)',
                $this->regla($tema, $flotante),
                "{$flotante} flota sobre la página y necesita su canto."
            );
        }
    }

    /**
     * La cuenta vive en el cromo superior, junto al control de tema. Pasó por
     * el pie de la barra y por la primera fila de la lista el mismo día; Sua la
     * quiso arriba, al lado de la configuración de claro y oscuro.
     * Rotura: devolverla a SIDEBAR_FOOTER o a SIDEBAR_NAV_START.
     */
    public function test_la_cuenta_vive_en_el_cromo_superior(): void
    {
        $proveedor = File::get(app_path('Providers/Filament/AdminPanelProvider.php'));

        $this->assertStringContainsString("view('filament.components.cuenta-en-la-barra')", $proveedor, 'La cuenta no se pinta en ninguna parte.');
        $this->assertStringNotContainsString('PanelsRenderHook::SIDEBAR_FOOTER', $proveedor, 'La cuenta sigue anclada al pie de la barra.');
        $this->assertStringNotContainsString('PanelsRenderHook::SIDEBAR_NAV_START', $proveedor, 'La cuenta sigue siendo la primera fila de la lista.');
        $this->assertSame(2, substr_count($proveedor, 'PanelsRenderHook::TOPBAR_END'), 'El cromo superior tiene que llevar dos piezas: el control de tema y la cuenta.');
        $this->assertStringContainsString('->userMenu(false)', $proveedor, 'Con nuestro chip arriba, el menú de usuario de Filament sobra.');
    }

    /**
     * Los módulos, traducidos de la barra de escritorio (recordatorio de Sua el
     * 7 sep: «se usarán módulos así como está compuesta la navBar de escritorio
     * pero en vertical»). El grupo es el módulo, y como allí nace APAGADO: en
     * el estado inicial el vidrio lo pone la barra y el módulo no dibuja nada.
     * Enciende su brillo y su canto cuando la lista se ha desplazado.
     *
     * Pintarlos siempre fue el error de la primera pasada: se leían como cajas
     * dentro de cajas y por eso la barra parecía apeñuscada.
     * Rotura: encender los pseudoelementos sin el estado, o quitarlos.
     */
    public function test_los_modulos_nacen_apagados_y_encienden_con_el_estado(): void
    {
        $tema = preg_replace('/\s+/', ' ', $this->tema());

        foreach (['.fi-sidebar-group::before', '.fi-sidebar-group::after'] as $capa) {
            $this->assertStringContainsString($capa, $tema, "El módulo no tiene su {$capa}.");
        }

        // D-L26: el cristal es permanente, porque con el campo de puntos detrás
        // ya hay algo que refractar y la lámina deja de leerse como caja. Lo que
        // añade el desplazamiento es la sombra que la despega.
        $this->assertStringContainsString('opacity: 1;', $this->regla($this->tema(), '.fi-sidebar-group::before'), 'El cristal del apartado tiene que estar puesto, no esperando al scroll.');
        $this->assertStringContainsString('body[data-barra-estado="scroll"] .fi-sidebar-group', $tema, 'El desplazamiento ya no afirma el cristal con su sombra.');
    }

    /**
     * Nada separa la barra del contenido. El límite pasó por tres formas y las
     * tres eran la misma: una línea de un píxel, un filo rojo y una franja
     * difusa de 40 px. Difusa o no, se leía como un corte vertical, y Sua lo
     * rechazó las tres veces. Lo que marca la zona es el resplandor; lo que la
     * ordena son los cristales de sus apartados.
     * Rotura: devolver el borde, el filo o la franja.
     */
    public function test_nada_corta_la_barra_del_contenido(): void
    {
        $tema = $this->tema();
        $barra = $this->regla($tema, '.fi-sidebar');
        $capa = $this->regla($tema, '.fi-sidebar::after');

        $this->assertStringNotContainsString('border-inline-end', $barra, 'La barra vuelve a cortarse con un borde.');
        $this->assertStringNotContainsString('box-shadow', $barra, 'La sombra del elemento la anula Filament en escritorio, y además volvería a marcar el corte.');
        $this->assertStringNotContainsString('linear-gradient(to right', $capa, 'Vuelve la franja vertical que rompe la continuidad.');

        // El resplandor de D-L28 sí lleva un lavado horizontal, y por eso hay
        // que vigilar su DIRECCIÓN: anclado al canto izquierdo y apagándose
        // hacia dentro no separa nada; al revés es la franja del límite otra vez.
        // Se lee la dirección declarada en vez de buscar cadenas: con el
        // paréntesis y el salto de línea, `linear-gradient(270deg` no aparece
        // nunca tal cual y la guardia daba verde con el lavado invertido.
        $suelo = preg_replace('/\s+/', ' ', $this->regla($tema, '.fi-sidebar::before'));

        preg_match_all('/linear-gradient\(\s*([^,]+),/', $suelo, $lavados);

        $this->assertNotEmpty($lavados[1], 'El resplandor perdió su lavado horizontal.');

        foreach ($lavados[1] as $direccion) {
            $this->assertSame(
                '90deg',
                trim($direccion),
                "El lavado del resplandor va en «{$direccion}»: si no nace en el canto izquierdo y se apaga hacia dentro, vuelve a ser la franja del límite."
            );
        }
        $this->assertStringNotContainsString('--asb-admin-barra-union', $tema, 'Los tokens de la unión siguen vivos sin consumidor.');

        // Lo único que queda en esa capa es el resplandor de la esquina, que no
        // separa nada: nace arriba a la izquierda y se apaga hacia dentro.
        $this->assertStringContainsString('radial-gradient', $capa, 'Se perdió el resplandor de la esquina, que es de donde nace la luz.');
    }

    /**
     * La barra superior va anclada. Sin esto se va con el desplazamiento y el
     * título de la página y el control de tema desaparecen en cuanto bajas.
     * Rotura: devolverla a `position: relative`.
     */
    public function test_la_barra_superior_va_anclada(): void
    {
        $topbar = $this->regla($this->tema(), '.fi-topbar-ctn');

        $this->assertStringContainsString('position: sticky;', $topbar, 'La barra superior no está anclada.');
        $this->assertStringContainsString('inset-block-start: 0;', $topbar, 'La barra superior no dice a qué se ancla.');
    }

    /**
     * El campo de puntos del fondo (D-L24). Se dibuja en un lienzo y no con
     * nodos: con 18 px de paso, una columna de 244 por 1.000 son más de
     * setecientos puntos. Tres condiciones de la decisión, cada una con su
     * afirmación: colores por token (invierte con el tema), repulsión apagada
     * bajo movimiento reducido, y lienzo sin puntero ni texto.
     * Rotura: cablear el color, mover los puntos con movimiento reducido, o
     * dejar que el lienzo reciba el puntero.
     */
    public function test_el_campo_de_puntos_respeta_las_tres_condiciones(): void
    {
        $js = File::get(resource_path('js/panel-barra-puntos.js'));
        $tema = $this->tema();

        $this->assertStringContainsString("getPropertyValue('--asb-admin-barra-punto')", $js, 'El color del punto no sale de un token, así que no invertiría con el tema.');
        // Un solo color literal en el módulo, y es el respaldo por si el token
        // faltara: dos serían paleta privada entrando por la puerta de atrás.
        preg_match_all('/#[0-9a-fA-F]{3,8}|rgba?\(/', $js, $literales);
        $this->assertLessThanOrEqual(1, count($literales[0]), 'El campo de puntos tiene colores cableados: '.implode(', ', $literales[0]));

        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $js, 'La repulsión no consulta el movimiento reducido.');
        $this->assertStringContainsString('if (corriendo || quieto.matches)', $js, 'El bucle arranca aunque el sistema pida movimiento reducido.');
        $this->assertStringContainsString("addEventListener('pointermove'", $js, 'El seguimiento no usa `pointermove`: con `mousemove`, en híbridos el toque deja el campo empujado.');

        $lienzo = $this->regla($tema, '.asb-barra-puntos');
        $this->assertStringContainsString('pointer-events: none;', $lienzo, 'El lienzo intercepta el puntero.');
        // Fijo desde D-L26: el campo es de toda la interfaz, no de una zona, y
        // no puede desplazarse con nada. En flujo empujaría el contenido, que
        // es lo que pasó el 7 sep cuando una regla le quitó la posición.
        $this->assertStringContainsString('position: fixed;', $lienzo, 'El lienzo tiene que estar fuera del flujo y fijo al viewport.');
        $this->assertStringContainsString('100dvh', $lienzo, 'El lienzo tiene que cubrir el alto del viewport.');
        // El lienzo va en z-index 0 y todo lo demás en 1: con negativo se colaba
        // por detrás del fondo del cuerpo y no se veía.
        $this->assertStringContainsString('z-index: 0;', $lienzo, 'El lienzo tiene que quedar sobre el fondo del cuerpo.');
        $this->assertStringContainsString('z-index: 1;', $this->regla($tema, '.fi-layout'), 'El contenido tiene que ir por encima del campo.');

        $vista = File::get(resource_path('views/filament/components/puntos-de-la-barra.blade.php'));
        $this->assertStringContainsString('aria-hidden="true"', $vista, 'El lienzo es decoración y tiene que estar oculto a la tecnología de apoyo.');

        foreach (['--asb-admin-barra-punto'] as $token) {
            $this->assertStringContainsString($token, $this->bloque($tema, '
:root {'), "{$token} falta en el tema claro.");
            $this->assertStringContainsString($token, $this->bloque($tema, '
.dark {'), "{$token} falta en el tema oscuro: el campo no invertiría.");
        }
    }

    /**
     * Sin barra de desplazamiento visible (D-L24), la única pista de que la
     * lista sigue es la máscara de desvanecido. Por eso las dos cosas se
     * afirman juntas: esconder la barra sin el aviso deja al usuario sin saber
     * que hay más.
     * Rotura: quitar la máscara, o devolver la barra de desplazamiento.
     */
    public function test_esconder_la_barra_de_scroll_obliga_al_aviso(): void
    {
        $tema = $this->tema();
        $lista = $this->regla($tema, '.fi-sidebar-nav');

        $this->assertStringContainsString('scrollbar-width: none;', $lista, 'La barra de desplazamiento sigue a la vista.');
        $this->assertStringContainsString('.fi-sidebar-nav::-webkit-scrollbar', $tema, 'Falta esconderla en los navegadores de WebKit.');

        $normalizado = preg_replace('/\s+/', ' ', $tema);

        foreach (['arriba', 'abajo', 'ambos'] as $borde) {
            $this->assertStringContainsString(
                'body[data-barra-borde="'.$borde.'"] .fi-sidebar-nav',
                $normalizado,
                "Sin barra de desplazamiento, el aviso de borde «{$borde}» es la única pista de que hay más lista."
            );
        }
    }

    /**
     * El aire vertical de la lista tiene que ser MAYOR que el desvanecido del
     * aviso. Si no, en reposo la primera lámina nace dentro de la máscara y la
     * última muere en ella: el módulo tiene canto, y un canto a medio pintar se
     * lee como una caja cortada, que es justo lo que Sua vio el 8 sep.
     *
     * La cuenta se hace, no se afirma de memoria: se resuelve el `calc()` y se
     * compara con el token. Rotura: bajar el relleno por debajo del aviso, o
     * subir el aviso sin subir el relleno.
     */
    public function test_el_aire_de_la_lista_supera_al_desvanecido(): void
    {
        $tema = $this->tema();
        $claro = $this->bloque($tema, '
:root {');

        $this->assertSame(
            1,
            preg_match('/--asb-admin-barra-aviso-alto: ([\d.]+)rem;/', $claro, $suyo),
            'El alto del aviso ya no es un valor en rem: la cuenta no se puede hacer.'
        );

        $aviso = (float) $suyo[1];
        $lista = $this->regla($tema, '.fi-sidebar-nav');

        $this->assertSame(
            1,
            preg_match('/padding-block: ([^;]+);/', $lista, $relleno),
            'La lista no declara relleno vertical.'
        );

        $crudo = trim($relleno[1]);

        $aire = match (true) {
            preg_match('/^calc\(var\(--asb-admin-barra-aviso-alto\) \+ ([\d.]+)rem\)$/', $crudo, $suma) === 1 => $aviso + (float) $suma[1],
            preg_match('/^([\d.]+)rem$/', $crudo, $solo) === 1 => (float) $solo[1],
            default => -1.0,
        };

        $this->assertGreaterThan(-1.0, $aire, "El relleno vertical de la lista no se deja medir: {$crudo}");

        $this->assertGreaterThan(
            $aviso,
            $aire,
            "El relleno vertical ({$aire}rem) no supera al desvanecido ({$aviso}rem): en reposo la máscara corta el canto del módulo."
        );
    }

    /**
     * El aire lateral de la lista tiene que ser el MISMO a los dos lados. Con
     * 0,75 rem a la izquierda y 0,3 a la derecha, el canto derecho del módulo
     * se quedaba a 4,8 px del borde de la barra teniendo 16 px de radio: la
     * curva no tenía fondo contra el que leerse y Sua vio un corte donde solo
     * había estrechez (8 sep). Lo que compensa el ancho del módulo es el ancho
     * de la barra, no el aire de un solo canto.
     *
     * Rotura: dejar los dos valores distintos, o bajar el aire por debajo de
     * 0,75 rem.
     */
    public function test_el_aire_lateral_de_la_lista_es_simetrico(): void
    {
        $lista = $this->regla($this->tema(), '.fi-sidebar-nav');

        $this->assertSame(
            1,
            preg_match('/padding-inline: ([^;]+);/', $lista, $relleno),
            'La lista no declara relleno lateral.'
        );

        $lados = preg_split('/\s+/', trim($relleno[1]));

        $this->assertCount(
            1,
            $lados,
            'El relleno lateral trae dos valores: el módulo tendría más aire a un lado que al otro y el canto corto se lee como un corte.'
        );

        $this->assertSame(1, preg_match('/^([\d.]+)rem$/', $lados[0], $suyo), "El aire lateral no se deja medir: {$lados[0]}");

        $this->assertGreaterThanOrEqual(
            0.75,
            (float) $suyo[1],
            'Menos de 0,75 rem de aire no dan para leer la curva de 1 rem del módulo contra el canto de la barra.'
        );
    }

    /**
     * El resplandor marca la zona del panel de arriba abajo (D-L28). Con solo
     * el radial de la esquina se apagaba a poco más de media altura: en una
     * pantalla de 1.080 px se acababa sobre los 594 y la mitad de abajo se
     * quedaba sin marca. Se afirman las DOS capas por separado, porque cada
     * una hace una cosa distinta.
     * Rotura: quitar el lavado y dejar solo el radial, o al revés.
     */
    public function test_el_resplandor_cubre_todo_el_lado(): void
    {
        $suelo = preg_replace('/\s+/', ' ', $this->regla($this->tema(), '.fi-sidebar::before'));

        $this->assertStringContainsString(
            'linear-gradient( 90deg, var(--asb-admin-barra-resplandor)',
            $suelo,
            'Falta el lavado que lleva el resplandor a toda la altura del lado.'
        );

        $this->assertStringContainsString(
            'radial-gradient( 120% 55% at 0% 0%',
            $suelo,
            'Se perdió el radial de la esquina, que es de donde nace la luz.'
        );
    }

    /**
     * Las cuatro señales del sistema llegan a la barra (D-L17). Se afirman las
     * cuatro por separado, con los tokens que cada una reasigna, porque cada
     * una responde a una necesidad distinta y borrar una no rompe a las otras.
     *
     * Y se afirma lo que de verdad las hace funcionar: que viven FUERA de
     * `@layer components`. Dentro de la capa la reasignación pierde contra el
     * `:root` sin capa de este mismo archivo y la señal no llega. Es el defecto
     * que este archivo ya pagó una vez con `--asb-vidrio-desenfoque`, y una
     * guardia que solo mirase que el bloque existe lo habría dado por bueno.
     *
     * Rotura: borrar un bloque; meter uno dentro de la capa; dejar un `blur()`
     * literal donde la media no lo alcanza.
     */
    public function test_las_cuatro_senales_alcanzan_a_la_barra(): void
    {
        $tema = $this->tema();

        $senales = [
            '(prefers-reduced-motion: reduce)' => ['--asb-admin-barra-brote', '--asb-admin-barra-empuje'],
            '(prefers-reduced-transparency: reduce)' => ['--asb-admin-barra-velo', '--asb-admin-barra-desenfoque'],
            '(prefers-contrast: more)' => ['--asb-admin-barra-velo', '--asb-admin-barra-modulo-canto'],
            '(forced-colors: active)' => [],
        ];

        foreach ($senales as $senal => $tokens) {
            $bloque = $this->medias($tema, $senal);

            $this->assertNotSame('', $bloque, "La barra no responde a {$senal}.");

            foreach ($tokens as $token) {
                $this->assertStringContainsString(
                    $token.':',
                    $bloque,
                    "Bajo {$senal} nadie reasigna {$token}, así que la señal no cambia nada."
                );
            }
        }

        // Contraste forzado: el indicador se repinta con lo que sobrevive.
        $forzado = $this->medias($tema, '(forced-colors: active)');

        foreach (['outline:', 'CanvasText'] as $recurso) {
            $this->assertStringContainsString(
                $recurso,
                preg_replace('/\s+/', ' ', $forzado),
                'Bajo contraste forzado el indicador se queda sin repintar: el navegador descarta la sombra.'
            );
        }

        // El desenfoque nunca es literal EN EL CONSUMO: si lo fuera, ninguna
        // media podría apagarlo. Se quitan todas las declaraciones de token,
        // que son justo donde el `blur()` sí tiene que estar escrito; lo que
        // quede es un literal en una regla, que es el defecto.
        $consumos = preg_replace('/--[a-z-]+:[^;]*;/', '', preg_replace('#/\*.*?\*/#s', '', $tema));

        // `assertFalse` sobre `str_contains` y no `assertStringNotContainsString`:
        // aquel vuelca el archivo entero en el mensaje de fallo y lo deja ilegible.
        $this->assertFalse(
            str_contains($consumos, 'blur('),
            'Hay un blur() literal en el tema del panel: ninguna media puede apagar lo que no es token.'
        );
    }

    /**
     * El cristal del apartado tiene que DEJAR VER el campo de puntos (D-L27).
     * Al 88 % lo tapaba y la lámina se leía como tarjeta opaca sobre un fondo
     * con textura. El velo del cajón es otro y se queda donde estaba: ese sí
     * se apoya sobre contenido que hay que tapar.
     * Rotura: devolver el velo del módulo por encima del 80 %.
     */
    public function test_el_cristal_del_apartado_deja_ver_el_campo(): void
    {
        $tema = $this->tema();

        foreach (['claro' => '
:root {', 'oscuro' => '
.dark {'] as $nombre => $marca) {
            $bloque = $this->bloque($tema, $marca);

            $velo = $this->porcentaje($bloque, '--asb-admin-barra-velo', $nombre);
            $cajon = $this->porcentaje($bloque, '--asb-admin-barra-velo-cajon', $nombre);

            $this->assertLessThanOrEqual(
                0.8,
                $velo,
                sprintf('El velo del módulo %s está al %d %%: tapa el campo de puntos y la lámina vuelve a ser una tarjeta.', $nombre, $velo * 100)
            );

            $this->assertGreaterThan(
                $velo,
                $cajon,
                sprintf('El cajón %s no puede ser más transparente que el módulo: se apoya sobre contenido que hay que tapar.', $nombre)
            );
        }
    }

    /**
     * El módulo de JavaScript escribe el estado en `<body>` y lo alimenta el
     * scroll INTERNO de la lista, no el del documento: en escritorio la barra
     * es `lg:sticky` y no se mueve con la página. Se afirma definición y
     * llamada de cada pieza por separado.
     * Rotura: escribir sobre el nodo de la barra, o escuchar el scroll de la
     * ventana en vez del de la lista.
     */
    public function test_el_modulo_de_la_barra_escribe_el_estado_en_el_cuerpo(): void
    {
        $js = File::get(resource_path('js/panel-barra-lateral.js'));

        foreach ([
            'document.body.dataset.barraEstado' => 'el estado no se escribe en el cuerpo',
            'document.body.dataset.barraBorde' => 'el aviso de borde no se escribe en el cuerpo',
            "nav.addEventListener('scroll', sincronizar" => 'no se escucha el scroll de la lista',
            "matchMedia('(prefers-reduced-motion: reduce)')" => 'el movimiento reducido no se consulta en vivo',
            'livewire:navigated' => 'el estado no se rehace tras navegar',
            'requestAnimationFrame' => 'no se espera a que Filament restaure el scroll de la lista',
        ] as $cadena => $porque) {
            $this->assertStringContainsString($cadena, $js, $porque);
        }

        // La mención en el comentario vale y explica el porqué; lo que no
        // puede haber es una lectura de esa clase, que en el panel nunca está.
        $this->assertStringNotContainsString("classList.contains('sin-desplazamiento')", $js, 'Esa clase la pone el layout público y en el panel no existe: la guarda quedaría siempre en falso.');

        $proveedor = File::get(app_path('Providers/Filament/AdminPanelProvider.php'));
        $this->assertStringContainsString("Js::make('panel-barra-lateral'", $proveedor, 'El módulo no se registra en el panel.');
        $this->assertStringContainsString('panel-barra-lateral.js', File::get(base_path('vite.config.js')), 'El módulo no está en las entradas de Vite, así que `Vite::asset` lanzaría y el panel se quedaría sin activos.');
    }

    /**
     * Ningún token del panel se queda declarado sin que nadie lo consuma. No es
     * higiene: un token huérfano sostiene guardias verdes sobre algo que no
     * pinta nada. `--asb-admin-barra-filo` lo demostró el 8 sep: dos guardias
     * afirmaban que estaba declarado y que la señal de más contraste lo
     * reasignaba, y hacía dos días que no tenía consumidor, desde que Sua
     * rechazó el filo rojo que lo pintaba.
     *
     * La guardia ya existía para un token concreto, `--asb-admin-barra-union`.
     * Esta la generaliza a los cuarenta y dos.
     *
     * Rotura: declarar un token y no consumirlo.
     */
    public function test_ningun_token_del_panel_se_queda_sin_consumidor(): void
    {
        $tema = $this->tema();
        $sinComentarios = preg_replace('#/\*.*?\*/#s', '', $tema);

        preg_match_all('/^\s*(--asb-admin-[a-z0-9-]+)\s*:/m', $sinComentarios, $declarados);

        $this->assertNotEmpty($declarados[1], 'No se encontró ningún token del panel: la lectura del archivo cambió de forma.');

        // Los consumidores viven en el tema, en las vistas del panel y en sus
        // módulos: un token puede consumirse desde cualquiera de los tres.
        $consumidores = $sinComentarios;

        foreach (['views/filament', 'views/components/panel', 'js', 'css'] as $carpeta) {
            $ruta = resource_path($carpeta);

            if (! File::isDirectory($ruta)) {
                continue;
            }

            foreach (File::allFiles($ruta) as $archivo) {
                $consumidores .= File::get($archivo->getPathname());
            }
        }

        $huerfanos = [];

        foreach (array_unique($declarados[1]) as $token) {
            // `var(` en CSS, `getPropertyValue` en JavaScript: el campo de
            // puntos lee su color desde el lienzo y no por cascada, y ese
            // consumo cuenta igual.
            $porCascada = str_contains($consumidores, 'var('.$token);
            $porGuion = str_contains($consumidores, "getPropertyValue('".$token."')");

            if (! $porCascada && ! $porGuion) {
                $huerfanos[] = $token;
            }
        }

        $this->assertSame(
            [],
            $huerfanos,
            'Estos tokens del panel están declarados y nadie los consume, así que cualquier guardia sobre ellos es un verde vacío: '.implode(', ', $huerfanos)
        );
    }

    /**
     * La maqueta con la que se mide (tarea 10 del plan). El panel exige segundo
     * factor, así que ninguna sesión automatizada lo abre: sin poder ver la
     * barra se entregaron dos regresiones visuales seguidas. La maqueta es lo
     * que permite verla, y este comando es lo que la hace reproducible.
     *
     * Se afirma pieza por pieza, porque cada una se ganó el sitio a base de
     * medir mal sin ella: sin `fi-sidebar-open` Filament deja la barra fuera de
     * pantalla; sin el botón de plegado no reprodujo el defecto del canto
     * derecho; y con las rutas a mano se mediría una hoja vieja sin avisar.
     *
     * Rotura: quitarle al comando cualquiera de esas piezas.
     */
    public function test_el_comando_de_la_maqueta_la_deja_medible(): void
    {
        $ruta = 'public/_medicion/prueba-barra.html';

        try {
            $this->artisan('maqueta:barra', ['--ruta' => $ruta])->assertSuccessful();

            $this->assertFileExists(base_path($ruta));

            $maqueta = File::get(base_path($ruta));

            // `strpos` y no `assertStringContainsString`: aquel vuelca los diez
            // kilobytes de la maqueta en el mensaje de fallo.
            $this->assertNotFalse(strpos($maqueta, 'fi-sidebar-nav'), 'La maqueta no trae la lista, que es lo que se mide.');

            $this->assertSame(
                5,
                substr_count($maqueta, 'class="fi-sidebar-group fi-collapsible"'),
                'La maqueta no trae los cinco apartados del panel: cinco láminas es lo que hay que ver.'
            );

            $this->assertNotFalse(
                strpos($maqueta, 'fi-sidebar-open'),
                'Sin `fi-sidebar-open` Filament deja la barra fuera de pantalla y la maqueta no mide nada.'
            );

            $this->assertNotFalse(
                strpos($maqueta, 'fi-sidebar-group-collapse-btn'),
                'Sin el botón de plegado la maqueta no reproduce el marcado real del grupo, y así dio verde sobre un defecto que en el panel se veía.'
            );

            $this->assertNotFalse(strpos($maqueta, '[x-cloak]'), 'Falta el estilo de x-cloak.');
            $this->assertNotFalse(strpos($maqueta, '$store'), 'Falta el almacén de mentira: sin él la consola se llena de errores que esconden a los de verdad.');

            // Las rutas salen del manifiesto, no escritas a mano: si no, la
            // maqueta mediría una hoja vieja sin que nadie se entere.
            $manifiesto = json_decode(File::get(public_path('build/manifest.json')), true);
            $tema = null;

            foreach ($manifiesto as $clave => $entrada) {
                if (str_ends_with($clave, 'filament/admin/theme.css')) {
                    $tema = $entrada['file'];
                }
            }

            $this->assertNotNull($tema, 'El manifiesto no trae el tema del panel.');
            $this->assertNotFalse(
                strpos($maqueta, '/build/'.$tema),
                'La maqueta no apunta a la hoja compilada de hoy: mediría una vieja sin avisar.'
            );
        } finally {
            File::deleteDirectory(base_path('public/_medicion'));
        }
    }

    /**
     * La maqueta escribe dentro de `public/`, así que lo que genera queda
     * SERVIDO. En una máquina de trabajo eso es justo lo que se quiere; en
     * producción es publicar una página que nadie pidió, con el marcado del
     * panel dentro. El comando se niega, y se niega antes de escribir nada.
     *
     * Rotura: quitarle la negativa al comando.
     */
    public function test_la_maqueta_no_se_genera_en_produccion(): void
    {
        $ruta = 'public/_medicion/produccion.html';

        $this->app->detectEnvironment(fn () => 'production');

        try {
            $this->artisan('maqueta:barra', ['--ruta' => $ruta])->assertFailed();

            $this->assertFileDoesNotExist(
                base_path($ruta),
                'El comando escribió la maqueta en producción: se niega ANTES de escribir, no después.'
            );
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
            File::deleteDirectory(base_path('public/_medicion'));
        }
    }

    /**
     * El contrato con Filament (tarea 9 del plan). Este tema no decora a
     * Filament: se apoya en hechos concretos de su vendor, y cada uno de ellos
     * cambió una decisión de diseño. Si Filament sube de versión y uno se cae,
     * lo que se rompe no es una regla: es el motivo por el que la regla está
     * escrita como está. Por eso el porqué va en el mensaje, uno por uno.
     *
     * Es la única guardia de esta clase que se rompe sola, sin que nadie toque
     * nuestro código.
     *
     * Rotura: borrarle una cadena al archivo del vendor.
     */
    public function test_el_contrato_con_filament_sigue_en_pie(): void
    {
        $css = File::get(base_path('vendor/filament/filament/resources/css/components/sidebar.css'));

        foreach ([
            'lg:shadow-none' => 'Filament anula la sombra del elemento en escritorio. Por eso la sombra de la barra vive en un pseudoelemento: declarada en el elemento computaba rgba(0,0,0,0) 0 0 0 0.',
            'lg:sticky' => 'En escritorio la barra no se mueve con la página, y por eso la máquina de estados lee el scroll INTERNO de la lista y no el del documento.',
            'lg:translate-x-0' => 'Sin la clase fi-sidebar-open la barra queda fuera de pantalla: la maqueta tiene que ponerla o no mide nada.',
            'scrollbar-gutter: stable' => 'Filament reserva canal de barra de desplazamiento; nosotros lo pasamos a auto y escondemos la barra, así que el desvanecido es la única pista de que hay más lista.',
            'overflow-y-auto' => 'La lista es el contenedor que desborda, no la barra: si dejara de serlo, el aviso de borde no tendría a quién escuchar.',
        ] as $cadena => $porque) {
            $this->assertNotFalse(
                strpos($css, $cadena),
                "El vendor de Filament ya no trae «{$cadena}». {$porque}"
            );
        }

        // La cabecera con el logotipo es `lg:hidden`: solo se ve en el cajón.
        $this->assertNotFalse(
            strpos($css, '.fi-sidebar-header'),
            'Desapareció la cabecera de la barra, que este tema solo pinta para el cajón porque Filament la esconde en escritorio.'
        );

        $store = File::get(base_path('vendor/filament/filament/resources/js/stores/sidebar.js'));

        foreach ([
            'livewire:navigated' => 'Sin ese evento nuestro módulo no tendría cuándo rehacer el estado tras navegar.',
            'requestAnimationFrame' => 'Filament restaura el scrollTop dentro de un fotograma; por eso lo nuestro se encola con doble rAF, o mediríamos la lista antes de que vuelva a su sitio.',
            'nav.scrollTop = this.scrollTop' => 'Esa es la restauración que esperamos. Si cambia de forma, el doble rAF deja de tener sentido.',
            'groupIsCollapsed' => 'Abrimos el grupo de la página activa disparando el botón que Filament escucha, no tocando su almacenamiento.',
            'toggleCollapsedGroup' => 'Lo mismo: es el método que está detrás del disparador del grupo.',
        ] as $cadena => $porque) {
            $this->assertNotFalse(
                strpos($store, $cadena),
                "El almacén de la barra de Filament ya no trae «{$cadena}». {$porque}"
            );
        }

        // Y el hecho del panel real, no del vendor: la barra no se pliega en
        // escritorio, así que `x-show="$store.sidebar.isOpen"` no esconde nada
        // y el ancho es siempre `--sidebar-width`.
        $this->assertFalse(
            Filament::getPanel('admin')->isSidebarCollapsibleOnDesktop(),
            'El panel dejó la barra plegable en escritorio: el ancho deja de ser fijo y el rótulo de los grupos puede desaparecer.'
        );
    }

    /**
     * El contraste de la barra, recalculado leyendo los porcentajes del
     * archivo y no repitiéndolos aquí.
     *
     * Lo que la construcción descubrió el 7 sep: el que manda NO es el rótulo
     * de grupo. Sobre el cristal del panel sale a 11,27:1 en claro y 7,68:1 en
     * oscuro, y ni bajando el velo al 40 % baja de 10:1, porque la superficie y
     * el fondo del panel son casi el mismo color. El que tiene el margen justo
     * es el RÓTULO DEL ÍTEM ACTIVO, que se lee sobre el tinte del ítem con el
     * halo compuesto encima: ahí el halo aclara el fondo y el texto sufre.
     * Con los valores de hoy da 6,35:1 en claro y 5,20:1 en oscuro; con el halo
     * al 60 % cae a 4,12:1 y 4,34:1, y con el rojo de marca en vez del acento
     * fuerte, a 2,92:1, que es lo que D-L9 rechazó.
     *
     * Rotura: subir el halo sin recalcular, o poner `#ee4137` de rótulo activo.
     */
    public function test_el_contraste_de_la_barra_aguanta_en_los_dos_temas(): void
    {
        $tema = $this->tema();
        $raiz = $this->bloque($tema, "\n:root {");
        $oscuro = $this->bloque($tema, "\n.dark {");

        // El halo se deriva de la luz, así que su porcentaje es uno solo y vive
        // en la raíz; lo que cambia con el tema es el color de la luz.
        $halo = $this->porcentaje($raiz, '--asb-admin-barra-halo', 'raíz');

        foreach ([
            'claro' => [$raiz, '#ffffff', '#f7f6f5', '#3d393b', '#b71f18'],
            'oscuro' => [$oscuro, '#121011', '#0b090a', '#a8a3a5', '#f27166'],
        ] as $tema_ => [$bloque, $superficie, $fondo, $tenue, $acento]) {
            // El color del rótulo activo se LEE del archivo y se resuelve
            // contra la paleta: si alguien lo cambia, la cuenta cambia con él.
            $activo = $this->resuelve($bloque, '--asb-admin-barra-activo', $tema_);
            $velo = $this->porcentaje($bloque, '--asb-admin-barra-velo', $tema_);
            $tinte = $this->porcentaje($bloque, '--asb-admin-barra-activo-fondo', $tema_);
            $luz = $this->hex($bloque, '--asb-admin-barra-luz');

            $cristal = $this->componer($superficie, $velo, $fondo);
            $conTinte = $this->componer($acento, $tinte, $cristal);
            $conHalo = $this->componer($luz, $halo, $conTinte);

            $delGrupo = $this->contraste($tenue, $cristal);
            $delActivo = $this->contraste($activo, $conHalo);

            $this->assertGreaterThanOrEqual(
                4.5,
                $delGrupo,
                sprintf('El rótulo de grupo sobre el cristal %s al %d %% da %.2f:1.', $tema_, $velo * 100, $delGrupo)
            );

            $this->assertGreaterThanOrEqual(
                4.5,
                $delActivo,
                sprintf('El rótulo del ítem activo %s, sobre el tinte al %d %% y el halo al %d %%, da %.2f:1 contra %s.', $tema_, $tinte * 100, $halo * 100, $delActivo, $conHalo)
            );
        }
    }

    /**
     * Debajo del cajón pasa contenido que no se conoce; debajo de la barra de
     * escritorio, un color plano. Por eso el velo del cajón nunca puede ser más
     * bajo que el de escritorio, y en claro tiene que ser más alto: es la regla
     * de D-L11 y es lo único del velo que de verdad se puede romper, porque el
     * contraste aquí no lo constriñe.
     * Rotura: igualar los dos velos en claro, o bajar el del cajón.
     */
    public function test_el_velo_del_cajon_nunca_baja_del_de_escritorio(): void
    {
        $tema = $this->tema();

        foreach ([
            'claro' => [$this->bloque($tema, '
:root {'), true],
            'oscuro' => [$this->bloque($tema, '
.dark {'), false],
        ] as $nombre => [$bloque, $exigeMas]) {
            $escritorio = $this->porcentaje($bloque, '--asb-admin-barra-velo', $nombre);
            $cajon = $this->porcentaje($bloque, '--asb-admin-barra-velo-cajon', $nombre);

            $this->assertGreaterThanOrEqual($escritorio, $cajon, "El velo del cajón {$nombre} es más bajo que el de escritorio, y debajo del cajón pasa contenido desconocido.");

            if ($exigeMas) {
                $this->assertGreaterThan($escritorio, $cajon, 'En claro el velo de cierre de Filament solo llega al 50 %, así que el cajón necesita más velo que el escritorio.');
            }
        }
    }

    /**
     * Todo lo que declara el archivo para `$selector`, juntando las reglas
     * donde aparece como selector completo. Junta y no elige la primera porque
     * el archivo usa listas (`.fi-sidebar::before, .fi-sidebar::after`) y
     * quedarse con la primera coincidencia leería el bloque equivocado.
     */
    /**
     * El cuerpo de los bloques `@media` cuya condición contiene `$senal`, y
     * SOLO los que viven fuera de `@layer`: un bloque dentro de la capa no
     * reasigna nada, porque el `:root` sin capa de este archivo le gana.
     */
    private function medias(string $css, string $senal): string
    {
        $limpio = preg_replace('#/\*.*?\*/#s', '', $css);
        $cuerpos = '';
        $capa = null;
        $profundidad = 0;

        for ($i = 0; $i < strlen($limpio); $i++) {
            if ($limpio[$i] === '{') {
                $profundidad++;

                continue;
            }

            if ($limpio[$i] === '}') {
                $profundidad--;

                if ($capa !== null && $profundidad < $capa) {
                    $capa = null;
                }

                continue;
            }

            if ($limpio[$i] !== '@') {
                continue;
            }

            if ($capa === null && str_starts_with(substr($limpio, $i, 6), '@layer')) {
                $capa = $profundidad + 1;

                continue;
            }

            if ($capa !== null || ! str_starts_with(substr($limpio, $i, 6), '@media')) {
                continue;
            }

            $abre = strpos($limpio, '{', $i);

            if ($abre === false || ! str_contains(substr($limpio, $i, $abre - $i), $senal)) {
                continue;
            }

            $nivel = 0;

            for ($j = $abre; $j < strlen($limpio); $j++) {
                if ($limpio[$j] === '{') {
                    $nivel++;
                }

                if ($limpio[$j] === '}') {
                    $nivel--;

                    if ($nivel === 0) {
                        $cuerpos .= substr($limpio, $abre + 1, $j - $abre - 1)."\n";

                        break;
                    }
                }
            }
        }

        return $cuerpos;
    }

    private function regla(string $css, string $selector): string
    {
        // Sin comentarios: si no, el bloque de comentario que precede a una
        // regla entra en la captura del selector y nada casa.
        $limpio = preg_replace('#/\*.*?\*/#s', '', $css);

        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $limpio, $reglas, PREG_SET_ORDER);

        $cuerpos = [];

        foreach ($reglas as $regla) {
            foreach (explode(',', $regla[1]) as $suyo) {
                if (trim($suyo) === $selector) {
                    $cuerpos[] = $regla[2];
                }
            }
        }

        $this->assertNotEmpty($cuerpos, "no existe ninguna regla para {$selector}");

        return implode('
', $cuerpos);
    }

    /**
     * El valor de un token de color del tema, resuelto hasta el hexadecimal: si
     * apunta a la paleta con `var(--asb-…)`, se busca en `tokens.css`, en el
     * bloque del mismo tema. Sin esto, cambiar el token no cambiaría la cuenta
     * y la guardia daría un verde falso.
     */
    private function resuelve(string $bloque, string $token, string $tema): string
    {
        $this->assertSame(
            1,
            preg_match('/'.preg_quote($token, '/').': ([^;]+);/', $bloque, $valor),
            "{$token} no se declara en el bloque {$tema}"
        );

        $crudo = trim($valor[1]);

        if (str_starts_with($crudo, '#')) {
            return $crudo;
        }

        $this->assertSame(1, preg_match('/^var\((--asb-[a-z-]+)\)$/', $crudo, $apunta), "{$token} no es un hexadecimal ni un `var()` de la paleta: {$crudo}");

        $paleta = File::get(resource_path('css/tokens.css'));
        $suyo = $this->bloque($paleta, $tema === 'oscuro' ? '
.dark {' : '
:root {');

        return $this->hex($suyo, $apunta[1]);
    }

    private function porcentaje(string $bloque, string $token, string $tema): float
    {
        $this->assertSame(
            1,
            preg_match('/'.preg_quote($token, '/').': color-mix\(in oklab, [^)]+\) (\d+)%, transparent\);/', $bloque, $valor),
            "{$token} no es un color-mix con porcentaje en el tema {$tema}"
        );

        return ((int) $valor[1]) / 100;
    }

    private function hex(string $bloque, string $token): string
    {
        $this->assertSame(
            1,
            preg_match('/'.preg_quote($token, '/').': (#[0-9a-f]{6});/', $bloque, $valor),
            "{$token} no es un hexadecimal en ese bloque"
        );

        return $valor[1];
    }
}

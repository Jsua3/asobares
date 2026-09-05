<?php

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * La barra llevaba OCHO enlaces de primer nivel y no cabían. «Abre tu negocio»
 * y «Quiénes somos» partían cada uno en dos líneas a 1280, 1440 y 1600 px, y
 * eso dejaba la cabecera en 83,38 px: no era un defecto de estilo, era una
 * navegación que no entraba en su sitio. Medido en Chromium después de
 * reagrupar: 61,69 px, cinco controles, UNA línea, en los dos temas y en los
 * tres anchos, con los cinco en 45,7 px de alto.
 *
 * ESTA CLASE NO MIDE ESA GEOMETRÍA —eso se mide con el navegador, como en
 * `ObjetivoTactilTest`—. Lo que vigila es el reparto que la produce y las tres
 * cosas que el reparto podía perder por el camino sin que nada fallara:
 *
 *   - que ningún destino se quede fuera al plegar dos grupos (y que «Contacto»,
 *     que hasta hoy solo se alcanzaba desde el móvil y el pie, gane el suyo);
 *   - que el desplegable se anuncie y se pueda abrir sin ratón;
 *   - que un grupo plegado siga diciendo que la sección actual es suya, que es
 *     la única señal de ubicación que tenía la barra.
 *
 * El reparto tampoco es de gusto: «Bolsas» es como se llama ese mismo grupo en
 * el menú del panel que ya usa el personal del gremio (§1 del manual de
 * usuario), así que /admin y el sitio público nombran igual las mismas cosas.
 */
class NavegacionAgrupadaTest extends TestCase
{
    use RefreshDatabase;

    /** Los cinco controles de primer nivel del escritorio, en orden. */
    private const CONTROLES = ['Directorio', 'Abre tu negocio', 'Eventos', 'Bolsas', 'El gremio'];

    /** Lo que se plegó, por panel. */
    private const GRUPOS = [
        'menu-bolsas' => ['Empleo', 'Artistas', 'Proveedores'],
        'menu-el-gremio' => ['Quiénes somos', 'Boletín', 'Contacto'],
    ];

    /**
     * El `<header>` aislado del resto del documento. El pie enlaza a medio
     * sitio —«Quiénes somos», «Boletín», las tres bolsas— así que contar sobre
     * la página entera daría por buena una barra vacía.
     */
    private function cabecera(string $ruta): string
    {
        $respuesta = $this->get($ruta);
        $respuesta->assertOk();

        preg_match('/<header\b.*?<\/header>/s', $respuesta->getContent(), $trozos);

        $this->assertNotEmpty($trozos, "No se encontró el <header> en {$ruta}.");

        return $trozos[0];
    }

    /**
     * El mismo `<header>` como árbol. Se navega con XPath y no con expresiones
     * regulares a propósito: lo que hay que afirmar aquí es de qué cuelga cada
     * enlace —primer nivel o dentro de un panel—, y eso una expresión regular
     * no lo ve.
     */
    private function arbol(string $ruta): DOMXPath
    {
        $documento = new DOMDocument;

        // El prefijo declara la codificación: sin él `loadHTML` lee el cuerpo
        // como Latin-1 y «Quiénes» deja de casar con «Quiénes».
        $anteriores = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="utf-8" ?>'.$this->cabecera($ruta));
        libxml_clear_errors();
        libxml_use_internal_errors($anteriores);

        return new DOMXPath($documento);
    }

    private function texto(DOMElement $elemento): string
    {
        return trim(preg_replace('/\s+/u', ' ', $elemento->textContent) ?? '');
    }

    /**
     * El bloque de escritorio: el único hijo del `<nav>` con `gap-1`. Los otros
     * dos son el logo y la esquina de sesión.
     */
    private function controlesDeEscritorio(DOMXPath $arbol): array
    {
        $bloque = $arbol->query('//nav/div[contains(@class, "gap-1")]')->item(0);

        $this->assertNotNull($bloque, 'Desapareció el bloque de enlaces de escritorio de la barra.');

        $etiquetas = [];

        foreach ($arbol->query('./a | ./div/button', $bloque) as $control) {
            $etiquetas[] = $this->texto($control);
        }

        return $etiquetas;
    }

    // --- A. El reparto ---

    public function test_la_barra_de_escritorio_baja_de_ocho_enlaces_a_cinco_controles(): void
    {
        $this->assertSame(
            self::CONTROLES,
            $this->controlesDeEscritorio($this->arbol('/contacto')),
            'La barra de escritorio ya no reparte lo que se decidió: tres directos y dos grupos.'
        );
    }

    /**
     * «Abre tu negocio» es el módulo insignia —es lo que hará que la página se
     * visite, dijo la dirección— y por eso NO se plegó. Si alguien lo mete en
     * un grupo para ganar sitio, esto lo dice.
     */
    public function test_el_modulo_insignia_no_queda_enterrado_en_un_desplegable(): void
    {
        $arbol = $this->arbol('/contacto');

        $this->assertContains('Abre tu negocio', $this->controlesDeEscritorio($arbol));

        foreach (array_keys(self::GRUPOS) as $panel) {
            $this->assertCount(
                0,
                $arbol->query('//div[@id="'.$panel.'"]//a[normalize-space()="Abre tu negocio"]'),
                "«Abre tu negocio» se plegó dentro de `{$panel}`: es el módulo insignia y va directo."
            );
        }
    }

    public function test_cada_grupo_lleva_dentro_exactamente_lo_que_se_le_asigno(): void
    {
        $arbol = $this->arbol('/contacto');

        foreach (self::GRUPOS as $panel => $esperado) {
            $filas = [];

            foreach ($arbol->query('//div[@id="'.$panel.'"]/a') as $fila) {
                $filas[] = $this->texto($fila);
            }

            $this->assertSame($esperado, $filas, "El panel `{$panel}` cambió de contenido.");
        }
    }

    /**
     * Plegar no puede perder destinos. Los ocho de la barra vieja más
     * «Contacto» tienen que seguir alcanzándose desde la cabecera.
     */
    public function test_ningun_destino_se_pierde_al_plegar(): void
    {
        $cabecera = $this->cabecera('/contacto');

        $destinos = [
            'directorio.index', 'guia.index', 'eventos.index',
            'empleo.index', 'artistas.index', 'proveedores.index',
            'quienes-somos', 'boletin.index', 'contacto',
        ];

        foreach ($destinos as $ruta) {
            $this->assertStringContainsString(
                'href="'.route($ruta).'"',
                $cabecera,
                "La barra dejó de enlazar a `{$ruta}`."
            );
        }
    }

    /**
     * «Contacto» solo existía en el menú móvil y en el pie: en escritorio no se
     * alcanzaba desde ningún sitio. Al entrar en «El gremio» gana el suyo, y
     * este es el único sitio que lo afirma.
     */
    public function test_contacto_gana_un_sitio_en_escritorio(): void
    {
        $arbol = $this->arbol('/contacto');

        $this->assertCount(
            1,
            $arbol->query('//div[@id="menu-el-gremio"]/a[@href="'.route('contacto').'"]'),
            'Contacto vuelve a alcanzarse solo desde el móvil y el pie.'
        );
    }

    /**
     * Escritorio y móvil salen del MISMO arreglo. Si cada uno declarara el
     * suyo, divergirían — que es exactamente lo que ya había pasado con
     * «Contacto», presente en uno y ausente del otro.
     */
    public function test_la_navegacion_se_declara_una_sola_vez(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        foreach ([...self::CONTROLES, ...array_merge(...array_values(self::GRUPOS))] as $etiqueta) {
            if (in_array($etiqueta, ['Bolsas', 'El gremio'], true)) {
                continue;
            }

            $this->assertSame(
                1,
                substr_count($navbar, "'texto' => '{$etiqueta}'"),
                "«{$etiqueta}» se declara más de una vez: escritorio y móvil tienen que leer el mismo arreglo."
            );
        }
    }

    // --- B. Que se pueda usar sin ratón ---

    /**
     * Es navegación principal: si el desplegable solo responde al ratón, la
     * reagrupación ha empeorado el sitio en vez de arreglarlo. El botón se
     * anuncia como plegable, dice qué panel controla, y el panel existe.
     */
    public function test_los_dos_desplegables_se_anuncian_y_se_abren_sin_raton(): void
    {
        $cabecera = $this->cabecera('/contacto');
        $arbol = $this->arbol('/contacto');

        foreach (array_keys(self::GRUPOS) as $panel) {
            $disparador = $arbol->query('//button[@aria-controls="'.$panel.'"]')->item(0);

            $this->assertNotNull($disparador, "No hay disparador para `{$panel}`.");
            $this->assertSame('button', $disparador->getAttribute('type'), 'Sin `type=button` el disparador envía el formulario que lo contenga.');
            $this->assertCount(1, $arbol->query('//div[@id="'.$panel.'"]'), "`{$panel}` no existe: `aria-controls` apunta a la nada.");

            /*
             * El estado lo escribe Alpine, así que en el HTML servido lo que hay
             * es el enlace y no el atributo. Se busca dentro de la etiqueta de
             * ESTE disparador y no en toda la cabecera: el menú de usuario
             * escribe el mismo enlace y contarlos juntos daba por bueno un
             * desplegable mudo.
             */
            preg_match('/<button[^>]*aria-controls="'.$panel.'"[^>]*>/', $cabecera, $etiqueta);

            $this->assertStringContainsString(
                'x-bind:aria-expanded="abierto ? \'true\' : \'false\'"',
                $etiqueta[0] ?? '',
                "El disparador de `{$panel}` no anuncia si está abierto o cerrado."
            );
        }
    }

    /**
     * Las cuatro salidas del desplegable. Las tres primeras son las del menú de
     * usuario, de donde sale entera esta solución; la cuarta es nueva y es la
     * que el teclado necesita: sin ella, tabular de un grupo al siguiente deja
     * el primero abierto, y con el ratón eso no pasa nunca porque
     * `click.outside` lo tapa.
     */
    public function test_el_desplegable_tiene_las_cuatro_salidas(): void
    {
        $vista = File::get(resource_path('views/components/publico/menu-grupo.blade.php'));

        $salidas = [
            'x-on:click.outside="cerrar()"' => 'clic fuera',
            'x-on:keydown.escape.window="cerrarYVolverAlFoco()"' => 'Escape',
            'x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"' => 'tabular fuera del grupo',
        ];

        foreach ($salidas as $codigo => $porque) {
            $this->assertStringContainsString($codigo, $vista, "El desplegable perdió la salida por {$porque}.");
        }

        // El cuerpo de las salidas vive en el componente compartido de app.js
        // desde el 5 sep; lo que devuelve el foco al disparador se vigila ahí.
        $this->assertStringContainsString(
            '$refs.disparador.focus()',
            File::get(resource_path('js/app.js')),
            'El desplegable perdió la salida por: el foco vuelve al disparador y el siguiente Tab no reinicia.'
        );
    }

    /**
     * El panel tiene que nacer del disparador y volver por el mismo camino. Sin
     * origen escrito, `scale-95` crece desde el CENTRO del panel y el
     * desplegable parece aparecer flotando en vez de desplegarse de su botón.
     */
    public function test_el_panel_nace_del_disparador_y_sale_por_donde_entro(): void
    {
        $vista = File::get(resource_path('views/components/publico/menu-grupo.blade.php'));

        $this->assertStringContainsString('origin-top-left', $vista, 'El panel cuelga del borde izquierdo del disparador: ahí va su origen.');

        // Entrada y salida son la misma pareja de estados, y la salida más
        // rápida que la entrada, que es la regla de duraciones del proyecto.
        $this->assertStringContainsString('x-transition:enter-start="opacity-0 scale-95"', $vista);
        $this->assertStringContainsString('x-transition:leave-end="opacity-0 scale-95"', $vista);
        $this->assertStringContainsString('duration-(--duracion-entrada)', $vista);
        $this->assertStringContainsString('duration-(--duracion-salida)', $vista);
    }

    // --- C. El estado activo ---

    /**
     * @return array<string, array{string, ?string, string}>
     */
    public static function seccionesYSuGrupo(): array
    {
        return [
            'empleo marca Bolsas' => ['/empleo', 'menu-bolsas', 'Empleo'],
            'artistas marca Bolsas' => ['/artistas', 'menu-bolsas', 'Artistas'],
            'proveedores marca Bolsas' => ['/proveedores', 'menu-bolsas', 'Proveedores'],
            'quiénes somos marca El gremio' => ['/quienes-somos', 'menu-el-gremio', 'Quiénes somos'],
            'boletín marca El gremio' => ['/boletin', 'menu-el-gremio', 'Boletín'],
            'contacto marca El gremio' => ['/contacto', 'menu-el-gremio', 'Contacto'],
            'eventos no marca ningún grupo' => ['/eventos', null, 'Eventos'],
        ];
    }

    #[DataProvider('seccionesYSuGrupo')]
    public function test_el_grupo_se_marca_activo_cuando_la_seccion_actual_es_una_de_las_suyas(string $ruta, ?string $panelActivo, string $etiqueta): void
    {
        $arbol = $this->arbol($ruta);

        foreach (array_keys(self::GRUPOS) as $panel) {
            $clases = $arbol->query('//button[@aria-controls="'.$panel.'"]')->item(0)->getAttribute('class');

            if ($panel === $panelActivo) {
                $this->assertStringContainsString('text-acento', $clases, "`{$panel}` esconde la sección actual y no lo dice: plegado, el color del grupo es la única señal de ubicación que queda.");
            } else {
                $this->assertStringNotContainsString('text-acento', $clases, "`{$panel}` se marca activo en {$ruta}, que no es suya.");
            }
        }

        // Y el color no puede ser la única señal: el enlace de la página actual
        // lo dice también con `aria-current`, que es lo que oye un lector de
        // pantalla. Sale dos veces porque el móvil repite la lista.
        $anunciados = [];

        foreach ($arbol->query('//*[@aria-current="page"]') as $enlace) {
            $anunciados[] = $this->texto($enlace);
        }

        $this->assertSame([$etiqueta], array_values(array_unique($anunciados)));
        $this->assertCount(2, $anunciados, 'La página actual se anuncia una vez en escritorio y otra en móvil.');
    }
}

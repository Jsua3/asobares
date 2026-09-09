<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * La barra pública por debajo de 64rem: dos módulos de vidrio, el superior
 * con marca, tema y cuenta, y el inferior fijo abajo con las cinco secciones
 * y dos hojas que suben al tocar (Parte II de la spec, aprobada por Sua el
 * 6 sep 2026).
 *
 * Esta clase se llamó `MenuMovilTest` y protegía LO CONTRARIO: un panel en
 * plano bajo la cabecera, sin desplegables anidados, porque «sobra vertical
 * y lo escaso es el número de toques» (772 px de panel medidos en 390x844).
 * Sua decidió el 6 sep (D-M11) invertirlo: los seis destinos plegados pasan
 * de un toque a dos a cambio de una barra siempre visible, sin hamburguesa,
 * con los tres directos y el tema a un toque. El `git mv` conserva la
 * historia de aquella decisión; este docblock conserva su razón para que
 * nadie la deshaga sin saberlo.
 *
 * Cada prueba nombra en su docblock la rotura que la pone roja.
 */
class NavbarMovilTest extends TestCase
{
    use MideContraste;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Roturas: borrar `--asb-retirada-barra: 0%` del bloque de movimiento
     * reducido; anular `--asb-alto-modulo-inferior` ahí (es layout, no se
     * anula); devolver el reloj de la barra lateral; duplicar
     * `--asb-hoja-velo` en `.dark`.
     */
    public function test_los_tokens_del_movil_y_su_anulacion(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        foreach ([
            '--asb-alto-modulo-superior: 3.5rem;',
            '--asb-alto-modulo-superior-compacto: 3rem;',
            '--asb-alto-modulo-inferior: 4.25rem;',
            '--asb-alto-modulo-inferior-compacto: 3rem;',
            '--asb-alto-rotulo-pestana: calc(2 * 1.65 * 0.6875rem);',
            '--asb-desplazamiento-hoja: 6px;',
            '--asb-retirada-barra: 100%;',
            '--asb-hoja-velo: color-mix(in oklab, var(--asb-superficie) 84%, transparent);',
        ] as $declaracion) {
            $this->assertStringContainsString($declaracion, $tokens);
        }

        // Una sola receta del velo de la hoja fuera de las medias de
        // accesibilidad: `.dark` es el propio <html> y el var() ya resuelve.
        $this->assertSame(1, substr_count($tokens, '--asb-hoja-velo: color-mix('), 'el velo de la hoja se declara una vez');
        $this->assertSame(2, substr_count($tokens, '--asb-cromo-apoyo-inferior:'), 'el apoyo inferior tiene sus dos recetas');

        $reducido = strstr($tokens, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($reducido);
        $this->assertStringContainsString('--asb-desplazamiento-hoja: 0px;', $reducido);
        $this->assertStringContainsString('--asb-retirada-barra: 0%;', $reducido);
        $this->assertStringNotContainsString('--asb-alto-modulo', $reducido, 'los altos son layout y no se anulan');

        $transparencia = $this->bloque($tokens, '@media (prefers-reduced-transparency: reduce)');
        $this->assertStringContainsString('--asb-hoja-velo: var(--asb-superficie);', $transparencia);

        $contraste = $this->bloque($tokens, '@media (prefers-contrast: more)');
        $this->assertStringContainsString('--asb-cromo-velo: var(--asb-fondo);', $contraste);
        $this->assertStringContainsString('--asb-cromo-desenfoque: none;', $contraste);
        $this->assertStringContainsString('--asb-hoja-velo: var(--asb-superficie);', $contraste);

        // Los dos tokens que se retiran con su único consumidor. Se
        // concatenan para que esta prueba no se delate a sí misma.
        $muertos = ['--duracion-'.'cromo', '--asb-desplazamiento-'.'panel'];

        foreach ($muertos as $muerto) {
            $this->assertStringNotContainsString($muerto, $tokens, "{$muerto} sigue en tokens.css sin consumidor");
        }

        // Un solo recorrido de tests/: cada archivo se lee una vez.
        foreach (File::allFiles(base_path('tests')) as $archivo) {
            $contenido = $archivo->getContents();

            foreach ($muertos as $muerto) {
                $this->assertStringNotContainsString($muerto, $contenido, "{$archivo->getFilename()} sigue afirmando {$muerto}");
            }
        }
    }

    /**
     * Los rótulos de 11 px y el rango del chip van sobre el vidrio, y el
     * único velo del sitio calibrado para texto era el del hero: el cromo al
     * 72 / 62 % da unos 3:1 con una foto detrás. Como `VeloDelHeroTest`, se
     * recalcula desde el archivo contra la imagen más hostil (negro bajo el
     * tema claro, blanco bajo el oscuro), con la composición alfa que pinta
     * el navegador (D-M18).
     *
     * Roturas: bajar el velo móvil claro al 72 %; devolver `text-apagado` al
     * rango del chip.
     */
    public function test_el_velo_del_movil_sostiene_el_rotulo(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $movil = $this->bloque($tokens, '@media (max-width: 63.999rem)');
        $raiz = $this->bloque($tokens, ':root {');
        $oscuro = $this->bloque($tokens, '.dark {');

        foreach ([
            'claro' => [$raiz, $this->bloque($movil, ':root {'), '#000000'],
            'oscuro' => [$oscuro, $this->bloque($movil, ':root.dark {'), '#ffffff'],
        ] as $tema => [$paleta, $velo, $peorImagen]) {
            $this->assertSame(1, preg_match('/--asb-cromo-velo: color-mix\(in oklab, var\(--asb-fondo\) (\d+)%, transparent\);/', $velo, $porcentaje), "el velo móvil {$tema} no reasigna --asb-cromo-velo");
            $alfa = ((int) $porcentaje[1]) / 100;
            $fondo = $this->hex($paleta, '--asb-fondo');
            $compuesto = $this->componer($fondo, $alfa, $peorImagen);

            foreach (['--asb-acento', '--asb-tenue'] as $texto) {
                $contraste = $this->contraste($this->hex($paleta, $texto), $compuesto);
                $this->assertGreaterThanOrEqual(4.5, $contraste, sprintf('%s a 11 px sobre el velo %s al %d %% da %.2f:1 contra %s', $texto, $tema, $porcentaje[1], $contraste, $peorImagen));
            }
        }
    }

    /**
     * El 88 / 85 % es del móvil. La base de `:root` sigue en 72 y `.dark` en
     * 62 para la píldora de escritorio de la Parte I, y solo el bloque bajo
     * 64rem reasigna el velo: la revisión del 6 sep encontró la base subida
     * al 88 sin decisión ni guardia, y el escritorio claro había cambiado de
     * material sin que ninguna prueba lo viera.
     */
    public function test_el_velo_de_escritorio_no_hereda_el_del_movil(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $patron = '/--asb-cromo-velo: color-mix\(in oklab, var\(--asb-fondo\) (\d+)%, transparent\);/';

        foreach ([
            'base' => [$this->bloque($tokens, ':root {'), 72],
            'oscuro' => [$this->bloque($tokens, '.dark {'), 62],
        ] as $paleta => [$bloque, $esperado]) {
            $this->assertSame(1, preg_match($patron, $bloque, $porcentaje), "la paleta {$paleta} de escritorio no declara --asb-cromo-velo");
            $this->assertSame($esperado, (int) $porcentaje[1], "el velo de escritorio {$paleta} cambió sin decisión: el 88 / 85 vive solo bajo 64rem");
        }
    }

    /**
     * Desplazarse ES cerrar (24 px, el umbral con el que el header suelta la
     * atención con dedo), salvo con el foco DENTRO: quien baja con una flecha
     * o AvPág mientras recorre la hoja está usando el teclado, y cerrarle el
     * panel bajo el foco lo tira al body, el defecto que el 5 sep se corrigió
     * para Escape.
     *
     * Roturas: quitar el `contains`; borrar el método dejando el cableado;
     * volver a `window.scrollY` sin clamp en `posicionDelDocumento`.
     */
    public function test_las_hojas_cierran_al_desplazar_sin_robar_el_foco(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertMatchesRegularExpression(
            '/cerrarSiSeDesplaza\(\) \{\s*if \(! this\.abierto \|\| this\.\$root\.contains\(document\.activeElement\)\) \{\s*return;\s*\}\s*if \(Math\.abs\(posicionDelDocumento\(\) - this\.scrollAlAbrir\) > 24\) \{\s*this\.cerrar\(\);/',
            $js,
            'la hoja cierra a los 24 px de desplazamiento salvo con el foco dentro'
        );
        $this->assertMatchesRegularExpression(
            '/const posicionDelDocumento = \(\) => Math\.min\(Math\.max\(window\.scrollY, 0\), Math\.max\(document\.documentElement\.scrollHeight - window\.innerHeight, 0\)\);/',
            $js,
            'la posición se acota al documento con el alto vigente del viewport, que es con el que el navegador acota scrollY'
        );
    }

    /**
     * Con sesión, «aparecerá su nombre y su rango» (Sua): el chip es el
     * disparador de siempre, con el nombre visible en los dos anchos y un
     * renglón de rango solo en móvil, en `text-tenue` porque 11 px en
     * `text-apagado` sobre el vidrio no llegan a 4,5:1. El nombre accesible
     * empieza por el texto visible (WCAG 2.5.3). Y la hoja se ancla al módulo
     * y no al chip: anclada al chip, a 320 px desborda 4 px por la izquierda.
     *
     * Roturas: devolver `hidden ... lg:block` al nombre; `text-apagado` en el
     * rango; quitar `max-lg:static` de la raíz; poner el rol antes del nombre
     * en el `sr-only`.
     */
    public function test_el_chip_de_cuenta_lleva_nombre_y_rango_en_los_dos_anchos(): void
    {
        $vista = File::get(resource_path('views/components/publico/menu-usuario.blade.php'));

        $this->assertStringContainsString('class="relative min-w-0 max-lg:static"', $vista);
        $this->assertStringContainsString('-m-1 flex items-center gap-2 rounded-full p-1 min-w-0', $vista);
        $this->assertStringNotContainsString('text-2xs text-apagado', $vista);
        $this->assertStringNotContainsString('lg:block', $vista, 'el nombre ya no es solo de escritorio');
        $this->assertStringNotContainsString('leading-', $vista, 'la escala tipográfica gobierna el chip');
        $this->assertStringContainsString("\$rangoCorto = \$rol === 'Establecimiento afiliado' ? 'Afiliado' : \$rol;", $vista);

        $html = $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<button[^>]*aria-controls="menu-cuenta"[^>]*>(.*?)<\/button>/s', $html, $chip), 'existe el disparador de cuenta');
        $this->assertMatchesRegularExpression('/<span class="sr-only">Sec\. Lola Pantoja, Secretaría del gremio: configuración y sesión<\/span>/', $chip[1]);
        $this->assertMatchesRegularExpression('/<span class="block truncate text-2xs text-tenue lg:hidden">\s*Secretaría del gremio\s*<\/span>/', $chip[1]);
        $this->assertStringContainsString('>Sec.<', $chip[1]);
    }

    /**
     * El chip de idioma se ve y no funciona a propósito (Parte I, D1); en 360
     * px no sobra un solo control. Se esconde bajo 64rem y sigue en el DOM
     * para las guardias. La raíz no fusiona atributos, así que va en el literal.
     *
     * Rotura: quitar `max-lg:hidden`.
     */
    public function test_el_chip_de_idioma_se_esconde_en_movil(): void
    {
        $this->assertStringContainsString('class="relative max-lg:hidden"', File::get(resource_path('views/components/publico/control-idioma.blade.php')));
    }

    /**
     * Un componente, dos pinturas: en escritorio el botón con galón y la hoja
     * hacia abajo; en la pestaña el icono sobre el rótulo y la hoja hacia
     * ARRIBA, con id propio para no pisar `menu-bolsas`. La fila de invitado
     * viaja como prop desde los arreglos y solo se pinta sin sesión.
     *
     * Roturas: quitar `-movil` del id; quitar `aria-current="true"` del botón
     * activo; pintar la fila de pie con sesión; quitar `origin-bottom` de la hoja.
     */
    public function test_la_pestana_de_grupo_abre_una_hoja_hacia_arriba(): void
    {
        $enlaces = "[['ruta' => 'empleo.index', 'texto' => 'Empleo'], ['ruta' => 'artistas.index', 'texto' => 'Artistas']]";
        $pie = "[['ruta' => 'mi-cuenta.entrar', 'texto' => 'Entrar como afiliado', 'solo' => 'guest']]";

        $pestana = Blade::render('<x-publico.menu-grupo variante="pestana" titulo="Bolsas" icono="briefcase" :enlaces="'.$enlaces.'" :pie="'.$pie.'" />');

        $this->assertStringContainsString('aria-controls="menu-bolsas-movil"', $pestana);
        $this->assertStringContainsString('id="menu-bolsas-movil"', $pestana);
        $this->assertStringContainsString('x-ref="disparador"', $pestana);
        $this->assertStringContainsString('x-bind:aria-expanded="abierto ? \'true\' : \'false\'"', $pestana);
        $this->assertStringContainsString('pestana fila-pulsable flex min-h-11 w-full flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium', $pestana);
        $this->assertStringContainsString('<span class="pestana__rotulo"><span class="text-balance">Bolsas</span></span>', $pestana);
        $this->assertStringContainsString('hoja-flotante hoja-inferior absolute inset-x-2 bottom-full z-50 mx-auto mb-2 max-w-sm origin-bottom rounded-2xl p-2', $pestana);
        $this->assertStringContainsString('translate-y-(--asb-desplazamiento-hoja)', $pestana);
        $this->assertStringContainsString('ease-rebote-vivo duration-(--duracion-rebote)', $pestana);
        $this->assertStringContainsString('role="group"', $pestana);
        $this->assertStringContainsString('aria-label="Bolsas"', $pestana);
        $this->assertStringContainsString('Entrar como afiliado', $pestana);
        $this->assertStringContainsString(route('mi-cuenta.entrar'), $pestana);
        $this->assertStringNotContainsString('aria-current', $pestana, 'ninguna sección activa: ni el botón ni las filas lo llevan');
        $this->assertStringNotContainsString('origin-top-left', $pestana);
        // El icono: contorno en reposo, y del vendor, no un path a mano.
        $this->assertMatchesRegularExpression('/<button[^>]*aria-controls="menu-bolsas-movil"[^>]*>\s*<svg[^>]*class="h-6 w-6 shrink-0"/s', $pestana);
        foreach (['role="menu"', 'aria-haspopup', 'x-collapse', 'line-clamp', 'leading-'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $pestana);
        }

        // Con sesión la fila de invitado no se pinta.
        $this->actingAs($this->usuarioCon([User::ROL_ASOCIADO], Asociado::query()->firstOrFail()));
        $this->assertStringNotContainsString('Entrar como afiliado', Blade::render('<x-publico.menu-grupo variante="pestana" titulo="Bolsas" icono="briefcase" :enlaces="'.$enlaces.'" :pie="'.$pie.'" />'));

        // Y la variante por defecto rinde lo de siempre.
        $barra = Blade::render('<x-publico.menu-grupo titulo="Bolsas" :enlaces="'.$enlaces.'" />');
        $this->assertStringContainsString('aria-controls="menu-bolsas"', $barra);
        $this->assertStringContainsString('origin-top-left', $barra);
        $this->assertStringNotContainsString('pestana', $barra);
        $this->assertStringNotContainsString('Entrar como afiliado', $barra);
    }

    /**
     * Roturas: mover el <nav> a `publico.blade.php` tras <main>; quitar
     * `lg:hidden`; poner `gap-1` en `.pestanas`; quitar el `x-bind:aria-label`
     * de la bandeja.
     */
    public function test_el_modulo_inferior_vive_en_el_header_y_es_el_landmark(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));

        $this->assertSame(1, $xpath->query('//header/nav[@id="menu-movil" and @aria-label="Navegación principal"]')->length);
        $this->assertSame(0, $xpath->query('//nav[1]//*[@id="menu-movil"]')->length, 'el inferior no cuelga de la bandeja');
        $clase = $xpath->query('//header/nav[2]/@class')->item(0)?->nodeValue ?? '';
        $this->assertStringContainsString('lg:hidden', $clase);
        $this->assertStringContainsString('modulo-inferior', $clase);
        $this->assertSame(0, $xpath->query('//header/nav[2]/*[contains(@class, "modulo") or contains(@class, "gap-1")]')->length, 'ningún hijo del inferior se confunde con un módulo de escritorio');

        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));
        $this->assertStringContainsString('x-bind:aria-label="esEscritorio ? \'Navegación principal\' : \'Marca y cuenta\'"', $navbar);
    }

    /**
     * Roturas: quitar Eventos; meter Eventos en la hoja; redeclarar la fila de
     * invitado en el componente; quitar un `'icono'`.
     */
    public function test_los_cinco_destinos_en_orden_y_las_dos_hojas(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));
        $inferior = $xpath->query('//header/nav[@id="menu-movil"]')->item(0);
        $this->assertNotNull($inferior);

        $etiquetas = [];
        foreach ($xpath->query('.//a[contains(@class, "pestana")] | .//button[contains(@class, "pestana")]', $inferior) as $control) {
            $etiquetas[] = trim(preg_replace('/\s+/u', ' ', $control->textContent));
        }
        $this->assertSame(['Directorio', 'Abre tu negocio', 'Eventos', 'Bolsas', 'El gremio'], $etiquetas);

        foreach (['menu-bolsas-movil' => ['Empleo', 'Artistas', 'Proveedores'], 'menu-el-gremio-movil' => ['Quiénes somos', 'Boletín', 'Contacto']] as $hoja => $esperadas) {
            $this->assertSame(1, $xpath->query('.//button[@type="button" and @aria-controls="'.$hoja.'"]', $inferior)->length);
            $filas = [];
            foreach ($xpath->query('//div[@id="'.$hoja.'"]/a') as $fila) {
                $filas[] = trim(preg_replace('/\s+/u', ' ', $fila->textContent));
            }
            $this->assertSame($esperadas, array_slice($filas, 0, 3), "la hoja {$hoja} cambió de contenido");
        }
        $this->assertSame(1, $xpath->query('//div[@id="menu-el-gremio-movil"]/a[@href="'.route('mi-cuenta.entrar').'"]')->length, 'la entrada del afiliado es la última fila de El gremio');

        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));
        $this->assertSame(5, substr_count($navbar, "'icono' => '"), 'los cinco controles llevan icono');
        $this->assertSame(1, substr_count($navbar, "'texto' => 'Entrar como afiliado'"), 'la fila de invitado se declara una vez, con el resto de la navegación');
        $this->assertStringNotContainsString("'El gremio'", File::get(resource_path('views/components/publico/menu-grupo.blade.php')), 'el componente no sabe qué grupo lleva pie');
    }

    /**
     * Roturas: envolver una fila de hoja en un segundo `x-data="desplegable"`;
     * mover una fila fuera de la hoja.
     */
    public function test_los_seis_destinos_plegados_siguen_a_dos_toques_como_maximo(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));

        foreach (['empleo.index', 'artistas.index', 'proveedores.index', 'quienes-somos', 'boletin.index', 'contacto'] as $ruta) {
            $fila = $xpath->query('//header/nav[@id="menu-movil"]//a[@href="'.route($ruta).'"]')->item(0);
            $this->assertNotNull($fila, "el inferior dejó de enlazar a {$ruta}");
            $this->assertSame(1, (int) $xpath->evaluate('count(ancestor::*[@x-data="desplegable"])', $fila), "{$ruta} está anidado en más de una hoja");
            $this->assertMatchesRegularExpression('/^menu-(bolsas|el-gremio)-movil$/', $fila->parentNode->getAttribute('id'), "{$ruta} no es hija directa de una hoja");
        }
    }

    /**
     * Tres capas y sin tocar la regla de conteo: la pestaña directa de la
     * página lleva `aria-current="page"`; la pestaña de grupo cuya sección
     * está activa lleva `aria-current="true"` (el valor genérico que el
     * proyecto ya usa en dos filtros) y el icono sólido; la fila de la hoja
     * lleva el `page`. En /empleo el conjunto anunciado sigue siendo
     * ['Empleo'] y dos, que es lo que NavegacionAgrupadaTest cuenta.
     *
     * Roturas: poner `page` en el botón de grupo; quitar el `true`; pintar el
     * icono de contorno con la sección activa.
     */
    public function test_el_activo_se_anuncia_una_vez_por_ancho_y_el_grupo_por_su_cuenta(): void
    {
        $xpath = $this->arbol($this->cabecera('/empleo'));
        $bolsas = $xpath->query('//header/nav[@id="menu-movil"]//button[@aria-controls="menu-bolsas-movil"]')->item(0);
        $gremio = $xpath->query('//header/nav[@id="menu-movil"]//button[@aria-controls="menu-el-gremio-movil"]')->item(0);
        $this->assertSame('true', $bolsas->getAttribute('aria-current'));
        $this->assertStringContainsString('text-acento', $bolsas->getAttribute('class'));
        $this->assertSame('', $gremio->getAttribute('aria-current'));
        $this->assertStringNotContainsString('text-acento', $gremio->getAttribute('class'));
        $this->assertSame(1, $xpath->query('//div[@id="menu-bolsas-movil"]/a[@aria-current="page"]')->length);

        $xpath = $this->arbol($this->cabecera('/eventos'));
        $eventos = $xpath->query('//header/nav[@id="menu-movil"]//a[@aria-current="page"]')->item(0);
        $this->assertNotNull($eventos);
        $this->assertSame(route('eventos.index'), $eventos->getAttribute('href'));
        $this->assertStringContainsString('text-acento', $eventos->getAttribute('class'));
        $this->assertSame(1, $xpath->query('.//*[local-name()="svg"][@fill="currentColor"]', $eventos)->length, 'la sección activa lleva el icono sólido');
    }

    /**
     * La máquina móvil: por DIRECCIÓN y no por posición, con ancla en el
     * extremo del recorrido (24 px para irse, 12 para volver), extremos del
     * documento como zona muerta, saltos de más de 200 px ignorados, nada
     * bajo movimiento reducido, y la frontera del CSS (64rem) como única
     * frontera. Abrir una hoja no cambia el tamaño de la barra.
     *
     * Roturas: intercambiar 24 y 12; invertir `recorrido > 24`; borrar la rama
     * de extremo; volver al alto del bloque contenedor inicial en el clamp;
     * borrar la puerta de movimiento; devolver el ancho en píxeles.
     */
    public function test_la_maquina_movil_decide_por_direccion(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        foreach ([
            'compactar() {', 'posicion() {', 'menosMovimiento() {', 'medirTeclado() {', 'campoEnfocado() {', 'esCampo(',
            'esEscritorio', "matchMedia('(min-width: 64rem)')", 'if (! this.esEscritorio) {',
            'dentro <= 8', 'dentro >= tope', 'Math.abs(recorrido) > 200',
            '(this.compacta && recorrido > 0) || (! this.compacta && recorrido < 0)',
            'recorrido > 24', 'recorrido < -12',
            'x-on:focusin.window="$nextTick(() => medirTeclado())"',
            'x-on:focusout.window="if (! esCampo($event.relatedTarget)) teclado = false"',
            'x-bind:data-teclado="teclado ? \'abierto\' : null"',
            'x-bind:class="{ \'cromo-apoyado\': desplazado }"',
            "this.\$dispatch('desplegable-abierto', null);",
        ] as $literal) {
            $this->assertStringContainsString($literal, $navbar, "el x-data del header perdió {$literal}");
        }

        $this->assertMatchesRegularExpression('/posicion\(\) \{[^}]*window\.innerHeight/', $navbar, 'el clamp usa el alto vigente del viewport');
        $this->assertMatchesRegularExpression('/compactar\(\) \{\s*if \(this\.menosMovimiento\(\)\) \{\s*this\.compacta = false;\s*return;\s*\}/', $navbar, 'bajo movimiento reducido no hay estado scroll');
        $this->assertMatchesRegularExpression('/menosMovimiento\(\) \{\s*return document\.documentElement\.classList\.contains\(\'sin-desplazamiento\'\);/', $navbar);
        $this->assertMatchesRegularExpression('/medirTeclado\(\) \{[^}]*altoReferencia/', $navbar, 'el teclado se mide contra el mayor alto visto, no contra el del layout');

        foreach (['menuMovil', 'x-on:resize.window', 'reposar', 'clientHeight', 'innerWidth'] as $muerto) {
            $this->assertStringNotContainsString($muerto, $navbar, "{$muerto} volvió al header");
        }
    }

    /**
     * Roturas: volver a esconder el módulo de cuenta con `hidden lg:flex`;
     * quitar el `@guest` de la fila de invitado; duplicar `control-tema`.
     */
    public function test_el_tema_y_la_cuenta_en_los_dos_anchos(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'id="popover-tema"'));
        $this->assertSame(2, substr_count($html, 'aria-label="Apariencia del sitio"'), 'el botón y el role=group del único control');
        $this->assertStringContainsString('Entrar como afiliado', $html);
        $this->assertStringNotContainsString('menu-cuenta', $html);
        $this->assertStringNotContainsString('Cerrar sesión', $html);
        $this->assertStringNotContainsString('modulo-cuenta hidden', File::get(resource_path('views/components/publico/navbar.blade.php')));
        $this->assertMatchesRegularExpression('/<footer.*href="'.preg_quote(route('mi-cuenta.entrar'), '/').'"[^>]*>\s*Entrar a mi cuenta/s', $html, 'el pie enlaza la entrada en todos los anchos y sin JavaScript');

        $conSesion = $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->assertOk()->getContent();
        $this->assertStringNotContainsString('Entrar como afiliado', $conSesion);
        $this->assertStringContainsString('menu-cuenta', $conSesion);
        $this->assertStringContainsString('Cerrar sesión', $conSesion);
    }

    /**
     * Roturas: quitar `viewport-fit=cover`; volver a `scroll-pt-24` sin
     * variante; volver a `min-h-screen`; devolver `pb-20` al hero.
     */
    public function test_el_layout_reserva_la_zona_segura_y_el_aire_del_movil(): void
    {
        $layout = File::get(resource_path('views/components/layouts/publico.blade.php'));
        $this->assertStringContainsString('content="width=device-width, initial-scale=1, viewport-fit=cover"', $layout);
        $this->assertStringContainsString('<html lang="es" class="lg:scroll-pt-24">', $layout);
        $this->assertStringContainsString('<body class="min-h-svh bg-fondo text-tinta antialiased">', $layout);
        $this->assertStringNotContainsString('min-h-screen', $layout);
        $this->assertStringNotContainsString('barra-tema', $layout);

        $hero = File::get(resource_path('views/components/publico/hero.blade.php'));
        $this->assertStringContainsString("'flex min-h-[calc(100svh-1rem)] items-center pb-28 pt-28 sm:pt-32 lg:pb-24 lg:pt-36' => \$portada", $hero);
        $this->assertStringNotContainsString('pb-20', $hero);
    }

    /**
     * Roturas: devolver `transform: translateY(0)` a `.cromo`; devolver la
     * clase de ocultación del cromo.
     */
    public function test_el_cromo_ya_no_es_bloque_contenedor(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $cromo = $this->regla($css, '.cromo');

        foreach (['transform', 'filter', 'will-change', 'contain', 'opacity'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $cromo, ".cromo con {$prohibido} es bloque contenedor de todo fixed descendiente");
        }
        $this->assertStringContainsString('isolation: isolate;', $cromo);
        $this->assertStringNotContainsString('.cromo-'.'oculto', $css, 'la clase de ocultación del cromo, que nadie usaba, se retiró con su transform');
        $this->assertStringNotContainsString('.tema-lateral', $css);
        $this->assertStringNotContainsString('view-transition-'.'name', $css, 'un elemento con nombre de transición de vista es raíz de fondo');
    }

    /** Rotura: mover el vidrio del pseudoelemento al módulo; escribir `blur(14px)`. */
    public function test_los_dos_modulos_son_vidrio_por_token_en_su_pseudoelemento(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $vidrio = $this->regla($movil, '.modulo-inferior::before');
        $this->assertStringContainsString('background-color: var(--asb-cromo-velo);', $vidrio);
        $this->assertStringContainsString('-webkit-backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('var(--asb-cromo-apoyo-inferior)', $vidrio);

        $modulo = $this->regla($movil, '.modulo-inferior');
        foreach (['backdrop-filter', 'filter:', 'blur(', 'contain:', 'height'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $modulo);
        }
        $this->assertStringContainsString('position: fixed;', $modulo);
        $this->assertStringContainsString('bottom: 0;', $modulo);
        $this->assertStringContainsString('padding-bottom: env(safe-area-inset-bottom, 0px);', $modulo);
        // El inset de la zona segura no entra en ninguna propiedad transicionada.
        $this->assertSame(1, preg_match('/transition:([^;]*);/s', $modulo, $transicion));
        $this->assertStringNotContainsString('env(', $transicion[1]);
        $this->assertStringNotContainsString('height', $transicion[1]);

        $this->assertStringNotContainsString('blur(', $movil, 'el vidrio del móvil no lleva blur literal');
        $this->assertStringNotContainsString('drop-'.'shadow', $movil, 'las sombras van en box-shadow: una sombra por filtro anula el vidrio de dentro');
    }

    /** Rotura: animar la altura con otro reloj; reasignar un tercer token en el estado. */
    public function test_el_estado_cambia_dos_tokens_y_nada_mas(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $estado = $this->regla($movil, '.cromo[data-estado="scroll"]');
        $this->assertStringContainsString('--asb-alto-modulo-superior: var(--asb-alto-modulo-superior-compacto);', $estado);
        $this->assertStringContainsString('--asb-alto-modulo-inferior: var(--asb-alto-modulo-inferior-compacto);', $estado);
        $this->assertSame(2, substr_count($estado, ';'), 'el estado no hace nada más');

        $this->assertStringContainsString('height: var(--asb-alto-modulo-inferior);', $this->regla($movil, '.pestanas'));
        $this->assertStringContainsString('height var(--duracion-estado) var(--ease-rebote-suave)', $this->regla($movil, '.pestanas'));
        $this->assertStringContainsString('grid-template-rows: 0fr;', $this->regla($movil, '[data-estado="scroll"] .pestana__rotulo'));
        $this->assertSame(0, preg_match('/height var\(--duracion-(?!estado\))/', $movil), 'toda altura del móvil se anima con el reloj de estado');

        // En apaisado el compacto nace en el HEADER y no en :root: tokens.css
        // declara los altos en :root fuera de toda capa, y una regla de
        // @layer components sobre :root pierde siempre contra ella (medido en
        // Chromium el 6 sep: 56 y 68 en vez de 48 y 48). Rotura: volver a :root.
        $apaisado = $this->regla($this->bloque($movil, '@media (orientation: landscape) and (max-height: 30rem)'), '.cromo');
        $this->assertStringContainsString('--asb-alto-modulo-superior: var(--asb-alto-modulo-superior-compacto);', $apaisado);
        $this->assertStringContainsString('--asb-alto-modulo-inferior: var(--asb-alto-modulo-inferior-compacto);', $apaisado);
    }

    /** Rotura: borrar la raya del inferior. */
    public function test_las_dos_rayas_de_apoyo_se_encienden_juntas(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $antes = strstr($css, '@media (min-width: 64rem) {', true);
        $despues = strstr($css, '@media (min-width: 64rem) {');

        $this->assertStringContainsString('.cromo-apoyado::before {', $antes);
        $this->assertStringContainsString('.cromo-apoyado .modulo-inferior::after {', $despues);
        $this->assertStringContainsString('opacity: 1;', $this->regla($despues, '.cromo-apoyado .modulo-inferior::after'));

        // La bandeja es `relative` con `z-index: 2` y aísla: sin z-index propio,
        // la raya del superior (absoluta, z auto) se pintaba DEBAJO del vidrio
        // al 88 % y no se veía (medido el 6 sep: los dos bordes en 48,0).
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)');
        $this->assertSame(1, preg_match('/z-index: (\d+);/', $this->regla($movil, '.cromo::before'), $raya), 'la raya del superior no declara z-index en móvil');
        $this->assertSame(1, preg_match('/z-index: (\d+);/', $this->regla($movil, '.bandeja'), $bandeja), 'la bandeja no declara z-index en móvil');
        $this->assertGreaterThan((int) $bandeja[1], (int) $raya[1], 'la raya del superior queda bajo el vidrio de la bandeja');
    }

    /** Rotura: poner `line-clamp`; quitar `overflow-wrap: anywhere`. */
    public function test_el_rotulo_no_recorta(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $this->assertStringContainsString('overflow-wrap: anywhere;', $this->regla($movil, '.pestana__rotulo > span'));
        $this->assertStringContainsString('min-height: var(--asb-alto-rotulo-pestana);', $this->regla($movil, '.pestana__rotulo'));
        foreach (['navbar', 'menu-grupo'] as $vista) {
            $this->assertStringNotContainsString('line-clamp', File::get(resource_path("views/components/publico/{$vista}.blade.php")));
        }
    }

    /**
     * En vertical la hoja SE ARRASTRA, y para eso tiene que quedarse el gesto.
     *
     * ⚠️ Esta prueba afirmaba lo contrario hasta el 9 de septiembre de 2026.
     * El contrato de entonces era «en vertical desplazarse es cerrar»: un gesto
     * vertical que empezaba sobre la hoja desplazaba la página, y ese
     * desplazamiento la cerraba de rebote. Por eso `touch-action` estaba
     * PROHIBIDO aquí.
     *
     * El contrato nuevo es manipulación directa: la hoja sigue al dedo 1:1,
     * resiste con goma hacia arriba y al soltar proyecta el momento para
     * decidir si se va. El navegador solo cede un gesto vertical con
     * `touch-action: none`, así que lo que antes se prohibía ahora se exige.
     *
     * Lo que NO cambia, y se sigue comprobando: en vertical la hoja no es
     * contenedor de scroll, y el cierre por desplazamiento de la PÁGINA sigue
     * existiendo para los gestos que empiezan fuera de ella.
     *
     * Roturas: quitar `touch-action: none` de `.hoja-inferior` (la hoja deja de
     * poder arrastrarse y el navegador se lleva el gesto); sacar
     * `overflow-y: auto` de la media de apaisado; borrar `cerrarSiSeDesplaza`.
     */
    public function test_la_hoja_se_queda_el_gesto_vertical_para_poder_arrastrarse(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);
        $apaisado = $this->bloque($movil, '@media (orientation: landscape) and (max-height: 30rem)');

        $hoja = $this->regla($movil, '.hoja-inferior');

        $this->assertStringContainsString(
            'touch-action: none;',
            $hoja,
            'Sin esto el navegador se queda el gesto vertical y la hoja no se puede arrastrar.'
        );

        // Sigue sin ser contenedor de scroll en vertical: lo que se mueve es la
        // hoja entera, no su contenido.
        foreach (['overflow-y', 'overscroll-behavior'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $hoja, 'en vertical la hoja no desplaza por dentro');
        }

        // Y el cierre por desplazamiento de la página no se ha perdido: es lo
        // que cubre los gestos que empiezan fuera de la hoja.
        $this->assertStringContainsString('cerrarSiSeDesplaza() {', File::get(resource_path('js/app.js')));

        $this->assertStringContainsString('touch-action: pan-y pinch-zoom;', $this->regla($apaisado, '.hoja-flotante'));
        $this->assertStringContainsString('overscroll-behavior: contain;', $this->regla($apaisado, '.hoja-flotante'));

        $primero = $this->bloque($css, '@media (max-width: 63.999rem)', 1);
        $this->assertLessThan(strpos($css, '.hoja-inferior {'), strpos($css, '.hoja-flotante {'), 'la hoja del móvil se apoya en el material de siempre');
        $this->assertStringContainsString('var(--asb-hoja-velo)', $this->regla($css, '.hoja-flotante'));
        $this->assertStringNotContainsString('color-mix(', $this->regla($css, '.hoja-flotante'));
        $this->assertStringNotContainsString('.modulo-inferior', $primero, 'el módulo inferior vive en el segundo bloque, tras el de escritorio');
    }

    /** Rotura: poner el cruce antes de `.logo-doble {`; quitar `marca-compacta`. */
    public function test_la_marca_cruza_sin_recortarse(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString("'marca-compacta' => auth()->check()", $navbar);
        $this->assertStringContainsString("'min-h-11',", $navbar);
        $this->assertGreaterThan(strpos($css, '.logo-doble {'), strpos($css, '.marca-compacta .logo-doble {'), 'el cruce va DESPUÉS de la primera regla del logo, que es la que la guardia de escritorio lee');
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);
        $this->assertStringContainsString('[data-estado="scroll"] .logo-doble,', $movil);
        foreach (['object-fit', 'clip-path', 'mask'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $movil, 'la marca no se recorta ni se recolorea');
        }
        $this->assertSame(0, preg_match('/(?<![\w-])filter:/', $movil), 'ningún filtro sobre la marca ni sobre los módulos');

        $this->assertStringNotContainsString('marca-compacta', $this->cabecera('/contacto'));
        $this->assertStringContainsString('marca-compacta', $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->getContent());
    }

    /** Rotura: quitar `visibility: hidden` de la retirada. */
    public function test_la_barra_se_retira_ante_el_teclado(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $retirada = $this->regla($this->bloque($css, '@media (max-width: 63.999rem)', 2), '.cromo[data-teclado="abierto"] .modulo-inferior');

        $this->assertStringContainsString('visibility: hidden;', $retirada);
        $this->assertStringContainsString('translate: 0 var(--asb-retirada-barra);', $retirada);
    }

    /** Rotura: devolver el `+` al selector; borrar el padding del body; quitar la media de 20rem. */
    public function test_el_apartado_no_cuelga_del_orden_de_landmarks_ni_del_zoom(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $this->assertStringContainsString('.cromo-fijo ~ main > section:first-child:not(.hero-portada) {', $css);
        $this->assertStringNotContainsString('.cromo-fijo + ', $css);
        $this->assertStringContainsString('padding-bottom: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px));', $this->regla($movil, 'body'));
        $this->assertStringContainsString('padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px);', $this->regla($movil, 'body'));
        $this->assertStringContainsString('scroll-padding-top: calc(var(--asb-alto-modulo-superior)', $this->regla($movil, 'html'));
        $this->assertStringContainsString('scroll-padding-bottom', $this->regla($movil, 'html'));
        $this->assertStringContainsString('safe-area-inset-left', $this->regla($movil, '.cromo-fijo'));
        // A 320x180 medidos el 6 sep, `position: static` en el módulo no lo
        // devolvía al flujo: seguía dentro del header fijo y solo lo hacía de
        // 96 px sobre 180 de alto. Sale del fijo el header entero.
        $corto = $this->bloque($movil, '@media (max-height: 20rem)');
        $this->assertStringContainsString('position: static;', $this->regla($corto, '.cromo-fijo'));
        $this->assertStringContainsString('position: relative;', $this->regla($corto, '.modulo-inferior'));
        $this->assertStringContainsString('padding-bottom: 0;', $this->regla($corto, 'body'));
        $this->assertStringContainsString('scroll-padding-top: 0;', $this->regla($corto, 'html'));
        $this->assertStringContainsString('padding-top: 0;', $this->regla($corto, '.cromo-fijo ~ main > section:first-child:not(.hero-portada)'));
    }

    /**
     * La fila «Entrar a mi cuenta» del pie nació en esta rama para que la
     * entrada del afiliado exista sin JavaScript (D-M4). Como la fila de la
     * hoja de El gremio, es del anónimo: a quien ya tiene sesión el formulario
     * de afiliados le reemplazaría la suya.
     * Rotura: sacar la fila del @guest.
     */
    public function test_el_pie_ofrece_la_entrada_solo_a_quien_no_tiene_sesion(): void
    {
        $this->get('/contacto')->assertSee('Entrar a mi cuenta');

        $this->actingAs($this->usuarioCon(['socio']))
            ->get('/contacto')
            ->assertDontSee('Entrar a mi cuenta');
    }

    /**
     * El chip diría «Sec. Secretaría del c…» a 360 px con el usuario demo de
     * la oficina (D-M15). Es dato de semilla, no de producción.
     * Rotura: devolver «Secretaría del capítulo».
     */
    public function test_el_usuario_demo_de_secretaria_es_una_persona(): void
    {
        $sembrador = File::get(database_path('seeders/UsuarioSeeder.php'));

        $this->assertStringNotContainsString('Secretaría del capítulo', $sembrador);
        $this->assertStringContainsString("'name' => 'Mariana Restrepo',", $sembrador);
    }

    /**
     * @param  list<string>  $roles
     */
    private function usuarioCon(array $roles, ?Asociado $asociado = null): User
    {
        foreach ($roles as $rol) {
            Role::findOrCreate($rol, 'web');
        }

        $usuario = User::factory()->create([
            'name' => 'Lola Pantoja',
            'asociado_id' => $asociado?->id,
        ]);
        $usuario->syncRoles($roles);

        return $usuario->fresh();
    }

    /** El primer `<header>` de la página: donde se cuenta todo. */
    private function cabecera(string $ruta): string
    {
        $html = $this->get($ruta)->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<header\b.*?<\/header>/s', $html, $trozos), "{$ruta} no tiene <header>");

        return $trozos[0];
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

    /** El cuerpo de la PRIMERA regla cuyo selector empieza así. */
    private function regla(string $css, string $selector): string
    {
        $inicio = strpos($css, $selector.' {');
        $this->assertNotFalse($inicio, "no existe la regla {$selector}");
        $fin = strpos($css, '}', $inicio);

        return substr($css, $inicio, $fin - $inicio);
    }

    /**
     * El bloque n-ésimo que empieza por esa marca, entero y con sus llaves
     * contadas: `strstr` daría desde la primera marca hasta el final del
     * archivo, que para el segundo bloque móvil es justo lo que no sirve.
     */
    private function bloque(string $css, string $marca, int $ordinal = 1): string
    {
        $desde = 0;
        $inicio = false;

        for ($n = 0; $n < $ordinal; $n++) {
            $inicio = strpos($css, $marca, $desde);
            $this->assertNotFalse($inicio, "no existe el bloque {$ordinal} de {$marca}");
            $desde = $inicio + strlen($marca);
        }

        $llave = strpos($css, '{', $inicio);
        $nivel = 0;

        for ($i = $llave, $largo = strlen($css); $i < $largo; $i++) {
            if ($css[$i] === '{') {
                $nivel++;
            } elseif ($css[$i] === '}') {
                $nivel--;

                if ($nivel === 0) {
                    return substr($css, $inicio, $i - $inicio + 1);
                }
            }
        }

        $this->fail("el bloque {$marca} no cierra");
    }

    private function hex(string $bloque, string $propiedad): string
    {
        $this->assertSame(1, preg_match('/'.preg_quote($propiedad, '/').': (#[0-9a-f]{6});/', $bloque, $valor), "{$propiedad} no es un hexadecimal en ese bloque");

        return $valor[1];
    }
}

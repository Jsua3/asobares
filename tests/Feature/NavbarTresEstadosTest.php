<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * La barra pública de escritorio en tres estados (spec:
 * docs/ingenieria/navbar-tres-estados-diseno.md). Cada prueba nombra en su
 * docblock la rotura que la pone roja; se hizo antes de darla por buena.
 */
class NavbarTresEstadosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
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

    /**
     * Rotura: invertir el orden del `match` que resuelve `$prefijoRol`.
     */
    public function test_el_disparador_de_cuenta_lleva_el_prefijo_del_rol(): void
    {
        $asociado = Asociado::query()->firstOrFail();

        $this->actingAs($this->usuarioCon([User::ROL_ASOCIADO], $asociado))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('Lola Pantoja')
            ->assertDontSee('>Sec.<', false)
            ->assertDontSee('>Admin<', false);

        $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Sec.<', false)
            ->assertSee('Secretaría del gremio')
            ->assertDontSee('>Admin<', false);

        $this->actingAs($this->usuarioCon([User::ROL_SUPER_ADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Admin<', false)
            ->assertSee('Dirección del gremio')
            ->assertDontSee('>Sec.<', false);

        // Con los dos roles gana Admin.
        $this->actingAs($this->usuarioCon([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Admin<', false)
            ->assertDontSee('>Sec.<', false);
    }

    /**
     * Rotura: borrar `--asb-caida-modulo: 0px` del bloque de movimiento reducido.
     */
    public function test_el_rebote_es_un_token_que_el_movimiento_reducido_anula(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('--duracion-rebote: 520ms', $tokens);
        $this->assertStringContainsString('--asb-separacion-modulos: 0.75rem', $tokens);
        $this->assertStringContainsString('--asb-caida-modulo: 6px', $tokens);
        $this->assertStringContainsString('--asb-escala-popover: 0.92', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-popover: -6px', $tokens);
        $this->assertStringContainsString('--asb-escala-isotipo: 0.9', $tokens);

        $reducido = strstr($tokens, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($reducido, 'tokens.css ya no tiene el bloque de movimiento reducido');

        $this->assertStringContainsString('--asb-caida-modulo: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-popover: 1', $reducido);
        $this->assertStringContainsString('--asb-desplazamiento-popover: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-isotipo: 1', $reducido);
        $this->assertStringContainsString('--ease-rebote-suave: var(--ease-cajon)', $reducido);
        $this->assertStringContainsString('--ease-rebote-vivo: var(--ease-cajon)', $reducido);

        // La separación es layout, no movimiento: sobrevive.
        $this->assertStringNotContainsString('--asb-separacion-modulos: 0', $reducido);
    }

    /**
     * El respaldo para navegadores sin linear() no puede ser una segunda
     * declaración: var() es inválido en tiempo de cómputo y la propiedad
     * caería a `ease`, no a la declaración anterior. Por eso el token nace
     * como cubic-bezier y solo dentro de @supports pasa a linear().
     *
     * Rotura: borrar la declaración con cubic-bezier de fuera del @supports.
     */
    public function test_el_rebote_lleva_respaldo_por_supports(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $soporte = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))');
        $this->assertNotFalse($soporte, 'falta el bloque @supports de linear()');

        $antes = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))', true);

        foreach (['--ease-rebote-suave', '--ease-rebote-vivo'] as $curva) {
            $this->assertStringContainsString("{$curva}: cubic-bezier(0.32, 0.72, 0, 1)", $antes, "{$curva} sin respaldo cubic-bezier");
            $this->assertStringContainsString("{$curva}: linear(0, ", $soporte, "{$curva} sin linear() dentro del @supports");
        }
    }

    /**
     * Rotura: en `leer()` volver a `['light', 'dark'].includes(guardado)`.
     */
    public function test_el_store_de_tema_acepta_sistema_y_distingue_lo_resuelto(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("resuelto: 'light'", $js);
        $this->assertStringContainsString("['light', 'dark', 'system'].includes(guardado)", $js);
        $this->assertStringContainsString('this.resuelto = this.resolver(', $js);
        $this->assertStringContainsString("matchMedia('(prefers-color-scheme: dark)').addEventListener('change'", $js);
    }

    /**
     * Rotura: cambiar `data-pais="co"` por `data-pais="es"`.
     */
    public function test_las_banderas_son_colombia_y_estados_unidos(): void
    {
        $bandera = File::get(resource_path('views/components/publico/bandera.blade.php'));

        $this->assertStringContainsString('data-pais="co"', $bandera);
        $this->assertStringContainsString('data-pais="us"', $bandera);
        $this->assertStringNotContainsString('data-pais="es"', $bandera);
        $this->assertStringNotContainsString('data-pais="gb"', $bandera);

        $colombia = Blade::render('<x-publico.bandera pais="co" />');
        $this->assertStringContainsString('<svg', $colombia);
        $this->assertStringContainsString('aria-hidden="true"', $colombia);
        $this->assertStringContainsString('#FCD116', $colombia);
    }

    /**
     * Rotura: quitar el atributo `media` de la precarga del isotipo.
     */
    public function test_el_isotipo_existe_se_pinta_doble_y_se_precarga_solo_en_escritorio(): void
    {
        $this->assertFileExists(public_path('img/monograma-asobares.png'));

        $doble = Blade::render('<x-publico.logo doble alto="h-8" />');
        $this->assertStringContainsString('logo-doble__completo', $doble);
        $this->assertStringContainsString('logo-doble__isotipo', $doble);
        $this->assertStringContainsString('img/logo-asobares.png', $doble);
        $this->assertStringContainsString('img/monograma-asobares.png', $doble);
        $this->assertSame(1, substr_count($doble, 'alt="ASOBARES Capítulo Quindío"'), 'la marca se anuncia una sola vez');
        $this->assertStringContainsString('alt=""', $doble);
        $this->assertStringContainsString('width="156" height="108"', $doble);

        $simple = Blade::render('<x-publico.logo alto="h-8" />');
        $this->assertStringNotContainsString('logo-doble', $simple, 'sin `doble` el componente rinde lo de siempre');

        $this->get('/contacto')
            ->assertOk()
            ->assertSee('rel="preload" as="image" href="http://localhost:8000/img/monograma-asobares.png" media="(min-width: 64rem)"', false);
    }

    /**
     * Rotura: poner computer-desktop en el botón, quitar la fila Sistema, o
     * quitar dark:hidden del sol.
     */
    public function test_el_control_de_tema_muestra_sol_o_luna_y_ofrece_sistema_en_el_popover(): void
    {
        $html = Blade::render('<x-publico.control-tema />');

        [$boton, $popover] = explode('id="popover-tema"', $html, 2);

        $this->assertStringContainsString('aria-label="Apariencia del sitio"', $boton);
        $this->assertStringContainsString('aria-controls="popover-tema"', $boton);
        // El icono lo decide CSS por la clase `dark` del <html>, no Alpine:
        // sin `x-show`, no hay destello del icono equivocado antes de que
        // arranque el script. Rotura: quitar `dark:hidden` del sol.
        $this->assertStringContainsString('dark:hidden', $boton, 'el sol se esconde en oscuro por CSS');
        $this->assertStringContainsString('hidden h-5 w-5 dark:block', $boton, 'la luna solo aparece en oscuro, por CSS');
        $this->assertStringNotContainsString('x-show', $boton, 'el icono no depende de Alpine para el primer pintado');
        $this->assertStringNotContainsString('computer-desktop', $boton, 'el botón nunca muestra el monitor');
        $this->assertStringNotContainsString('M9 17.25v1.007', $boton, 'ni el path del monitor');

        $this->assertStringContainsString('>Claro<', $popover);
        $this->assertStringContainsString('>Oscuro<', $popover);
        $this->assertStringContainsString('>Sistema<', $popover);
        $this->assertStringContainsString("\$store.tema.elegir('light')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('dark')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('system')", $popover);
        $this->assertSame(3, substr_count($popover, 'x-bind:aria-pressed='), 'las tres filas marcan la activa');

        // Lo que las guardias globales exigen a todo desplegable de la barra.
        $this->assertStringContainsString('transicion-desplegable', $html);
        $this->assertStringContainsString('fila-pulsable', $html);
        $this->assertStringContainsString('ease-rebote-vivo duration-(--duracion-rebote)', $html);
        $this->assertStringContainsString('scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)', $html);
    }

    /**
     * Rotura: quitar el atributo disabled (y solo ese) de la fila de English.
     */
    public function test_el_chip_de_idioma_se_ve_y_el_ingles_no_funciona_a_proposito(): void
    {
        $html = Blade::render('<x-publico.control-idioma />');

        [$boton, $popover] = explode('id="popover-idioma"', $html, 2);

        $this->assertStringContainsString('>ES<', $boton);
        $this->assertStringContainsString('aria-label="Idioma del sitio"', $boton);
        $this->assertStringContainsString('aria-controls="popover-idioma"', $boton);

        $this->assertStringContainsString('>Español<', $popover);
        $this->assertStringContainsString('>English<', $popover);
        $this->assertStringContainsString('próximamente', $popover);
        $this->assertStringContainsString('data-pais="co"', $popover);
        $this->assertStringContainsString('data-pais="us"', $popover);

        // La fila de English va desde su `lang="en"` hasta el cierre del botón;
        // `aria-disabled` contiene `disabled` como subcadena, así que el
        // atributo propio se exige con un espacio delante y un cierre detrás.
        // Rotura: quitar `disabled` (y solo `disabled`) de esa fila.
        [$antesDeIngles, $filaIngles] = explode('lang="en"', $popover, 2);
        $filaIngles = strstr($filaIngles, '</button>', true);
        $etiquetaDeApertura = strstr($filaIngles, '>', true);

        $this->assertMatchesRegularExpression('/\sdisabled(\s|$)/', $etiquetaDeApertura, 'la fila de English lleva el atributo `disabled` propio');
        $this->assertStringContainsString('aria-disabled="true"', $etiquetaDeApertura);
        $this->assertStringContainsString('>English<', $filaIngles);

        // Y la de Español no está deshabilitada.
        $this->assertStringNotContainsString('disabled', strstr($antesDeIngles, 'lang="es"'));

        $this->assertStringContainsString('aria-pressed="true"', $popover);
        $this->assertStringContainsString('transicion-desplegable', $html);
        $this->assertStringContainsString('fila-pulsable', $html);
    }

    /**
     * Rotura: escribir `blur(20px)` literal en `.modulo`, o sacar
     * `.modulo::before` de la puerta táctil.
     */
    public function test_el_vidrio_usa_tokens_y_el_brillo_tiene_puerta_tactil(): void
    {
        $css = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('.bandeja {', $css);
        $this->assertStringContainsString('.modulo {', $css);
        $this->assertStringContainsString('.control-plegable {', $css);
        $this->assertStringContainsString('.indicador-mas {', $css);
        $this->assertStringContainsString('.logo-doble__isotipo {', $css);

        // Retiradas: sustituidas por las de arriba.
        $this->assertStringNotContainsString('.cromo-bandeja', $css);
        $this->assertStringNotContainsString('.cromo-compacto', $css);
        $this->assertStringNotContainsString('.cromo-desplegable', $css);

        $modulo = $this->regla($css, '.modulo');
        $this->assertStringContainsString('var(--asb-cromo-desenfoque)', $this->regla($css, '[data-estado="atencion"] .modulo'));
        $this->assertStringNotContainsString('blur(', $modulo);
        $this->assertStringContainsString('var(--ease-rebote-suave)', $modulo);
        $this->assertStringContainsString('translate var(--duracion-rebote)', $modulo);

        $this->assertMatchesRegularExpression(
            '/@media \(hover: hover\) and \(pointer: fine\) \{\s*\.modulo::before \{[^}]*--puntero-x/',
            $css,
            'el brillo que sigue al puntero va dentro de la puerta táctil'
        );
        $this->assertSame(
            1,
            preg_match_all('/\.modulo::before \{[^}]*--puntero-x/', $css),
            'solo la regla con puerta usa --puntero-x'
        );

        $this->assertMatchesRegularExpression(
            '/@media \(hover: none\) \{\s*\[data-estado="scroll"\] \.indicador-mas \{/',
            $css,
            'el indicador solo aparece con puntero grueso y en scroll'
        );

        $this->assertStringContainsString('[data-estado="scroll"]:not(:focus-within) .control-plegable {', $css);
    }

    /**
     * Rotura: borrar el método alternarAtencion() del x-data del header (no la llamada).
     */
    public function test_el_header_declara_los_tres_estados(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString('x-bind:data-estado="estado"', $navbar);
        foreach (["'inicial'", "'scroll'", "'atencion'"] as $estado) {
            $this->assertStringContainsString($estado, $navbar);
        }
        // Las DEFINICIONES, no las llamadas: borrar el método deja viva la
        // llamada en el atributo y la barra táctil muerta con la suite verde.
        foreach (['get estado() {', 'punteroFino() {', 'sincronizar() {', 'atender() {', 'soltar() {', 'alternarAtencion() {'] as $definicion) {
            $this->assertStringContainsString($definicion, $navbar, "el x-data del header ya no define {$definicion}");
        }

        // Las expresiones de la máquina, literales: invertir el getter o
        // cambiar un umbral tiene que verse. Rotura: intercambiar 'atencion'
        // y 'scroll' en el getter.
        $this->assertStringContainsString("return this.atendiendo ? 'atencion' : 'scroll';", $navbar);
        $this->assertStringContainsString('this.desplazado = actual > 8;', $navbar);
        $this->assertStringContainsString('Math.abs(actual - this.scrollAlAtender) > 24', $navbar);
        $this->assertStringContainsString('}, 280);', $navbar);
        $this->assertStringContainsString("if (! \$event.target.closest('a, button')) alternarAtencion()", $navbar);

        // Lo que el panel móvil sigue exigiendo, literal.
        $this->assertStringContainsString('x-on:keydown.escape.window="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:click.outside="menuMovil = false"', $navbar);
        $this->assertStringNotContainsString('cromo-compacto', $navbar);
        $this->assertStringNotContainsString('cromo-expandido', $navbar);

        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertStringContainsString('data-estado="inicial"', $html, 'el servidor pinta el estado inicial antes de que Alpine arranque');
    }

    /**
     * Rotura: añadir `control-plegable` al enlace de Eventos.
     */
    public function test_los_cinco_controles_siguen_en_un_solo_bloque_y_solo_dos_se_pliegan(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<header\b.*?<\/header>/s', $html, $header), 'la página tiene un <header>');

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$header[0]);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $this->assertSame(1, $xpath->query('//nav')->length, 'una sola <nav>');
        $this->assertSame(3, $xpath->query('//nav/*[contains(@class, "modulo")]')->length, 'tres módulos, hijos directos de <nav>');

        $principal = $xpath->query('//nav/div[contains(@class, "gap-1")]')->item(0);
        $this->assertNotNull($principal);
        $this->assertStringContainsString('modulo-principal', $principal->getAttribute('class'));

        $plegables = $xpath->query('.//*[contains(@class, "control-plegable")]', $principal);
        $this->assertSame(2, $plegables->length);

        // Por forma y no por texto: la raíz del grupo lleva dentro su panel.
        $abreTuNegocio = $plegables->item(0);
        $this->assertSame('a', $abreTuNegocio->nodeName);
        $this->assertSame(route('guia.index'), $abreTuNegocio->getAttribute('href'));

        $elGremio = $plegables->item(1);
        $this->assertSame('div', $elGremio->nodeName);
        $this->assertSame(1, $xpath->query('.//button[@aria-controls="menu-el-gremio"]', $elGremio)->length);

        $this->assertSame(1, $xpath->query('//nav/div[contains(@class, "gap-1")]/span[contains(@class, "indicador-mas")]')->length);
        $this->assertSame(1, $xpath->query('//nav/a[contains(@class, "modulo-logo")]//span[contains(@class, "logo-doble")]')->length);
    }

    /**
     * Rotura: mover `<x-publico.control-tema />` debajo de `<div id="menu-movil"`.
     */
    public function test_los_popovers_van_antes_del_panel_movil_y_el_anonimo_ve_lo_suyo(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();

        $panelMovil = strpos($html, 'id="menu-movil"');
        $popoverTema = strpos($html, 'id="popover-tema"');
        $popoverIdioma = strpos($html, 'id="popover-idioma"');
        $this->assertNotFalse($panelMovil, 'existe el panel móvil');
        $this->assertNotFalse($popoverTema, 'existe el popover de tema');
        $this->assertNotFalse($popoverIdioma, 'existe el popover de idioma');
        $this->assertLessThan($panelMovil, $popoverTema, 'el popover de tema va antes del panel móvil');
        $this->assertLessThan($panelMovil, $popoverIdioma, 'el popover de idioma va antes del panel móvil');

        $inicio = strpos($html, 'modulo modulo-cuenta');
        $fin = strpos($html, 'id="menu-movil"');
        $this->assertNotFalse($inicio, 'existe el módulo de cuenta');
        $this->assertNotFalse($fin, 'existe el panel móvil');
        $cuenta = substr($html, $inicio, $fin - $inicio);

        // El enlace rinde con saltos de línea alrededor del texto: se afirma
        // el texto y el destino, no `>Mi cuenta<`.
        $this->assertStringContainsString('Mi cuenta', $cuenta);
        $this->assertStringContainsString(route('mi-cuenta.index'), $cuenta);
        $this->assertStringContainsString('Afíliate', $cuenta);
        $this->assertStringContainsString('id="popover-tema"', $cuenta);
        $this->assertStringContainsString('id="popover-idioma"', $cuenta);
        $this->assertStringNotContainsString('menu-cuenta', $html);
        $this->assertStringNotContainsString('Cerrar sesión', $html);
    }

    /**
     * Rotura: quitar `lg:hidden` del <aside> de la barra lateral.
     */
    public function test_la_barra_lateral_de_tema_se_queda_solo_en_movil(): void
    {
        $barra = File::get(resource_path('views/components/publico/barra-tema.blade.php'));
        $this->assertStringContainsString('tema-lateral fixed', $barra);
        $this->assertStringContainsString('lg:hidden', $barra);

        $this->assertFileDoesNotExist(resource_path('views/components/publico/selector-tema.blade.php'), 'el selector huérfano se borró');
    }

    /** El cuerpo de la primera regla cuyo selector empieza así. */
    private function regla(string $css, string $selector): string
    {
        $inicio = strpos($css, $selector.' {');
        $this->assertNotFalse($inicio, "no existe la regla {$selector}");
        $fin = strpos($css, '}', $inicio);

        return substr($css, $inicio, $fin - $inicio);
    }
}

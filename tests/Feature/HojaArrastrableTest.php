<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La hoja del teléfono se cierra con el dedo.
 *
 * Las dos hojas de la barra inferior --«Bolsas» y «El gremio»-- están a un
 * dedo del pulgar y eran lo único del sitio que se abría y se cerraba sin
 * poder empujarse. Ahora siguen al dedo 1:1, resisten con goma hacia arriba y
 * al soltar proyectan el momento para decidir si se van.
 *
 * Lo que estas guardas protegen son las cuatro decisiones que se pagan caras
 * si alguien las deshace sin saber por qué:
 *
 * 1. El umbral de 10 px, sin el cual un toque en una fila se convierte en un
 *    arrastre de un píxel y el enlace deja de abrirse.
 * 2. Tomar el mando desde la posición EN PANTALLA y no desde la meta, que es
 *    lo que permite agarrar una hoja que ya se estaba yendo.
 * 3. Tragarse el clic que sigue a un arrastre, o soltar encima de una fila
 *    navega.
 * 4. No limpiar el estilo en línea al cerrar, sino al abrir.
 *
 * La física de todo esto --resorte, goma, proyección-- no se prueba aquí: vive
 * en `movimiento.js` y la mide `MotorDeMovimientoTest` ejecutando el módulo.
 */
class HojaArrastrableTest extends TestCase
{
    private function app(): string
    {
        return File::get(resource_path('js/app.js'));
    }

    private function componente(): string
    {
        return File::get(resource_path('views/components/publico/menu-grupo.blade.php'));
    }

    /**
     * El CUERPO de un método de `app.js`, contando llaves.
     *
     * No es un lujo: una expresión regular con `.*?` y el modificador `s` se
     * sale del método sin avisar y encuentra la línea que busca en cualquier
     * otro sitio del archivo. Así se escribió primero la guarda de
     * `test_reabrir_corta_el_cierre_que_estuviera_en_vuelo`, y al mutarla
     * --quitando `pararElReloj()` de `abrir()`-- SIGUIÓ PASANDO, porque
     * encontraba esa misma llamada más abajo, en `tomarLaHoja()`. Un falso
     * verde de manual, el decimocuarto de este proyecto.
     */
    private function cuerpoDe(string $metodo): string
    {
        $js = $this->app();
        $inicio = strpos($js, $metodo.' {');

        $this->assertNotFalse($inicio, "`{$metodo}` ya no existe en app.js.");

        $profundidad = 0;
        $desde = strpos($js, '{', $inicio);

        for ($i = $desde; $i < strlen($js); $i++) {
            $profundidad += (int) ($js[$i] === '{') - (int) ($js[$i] === '}');

            if ($profundidad === 0) {
                return substr($js, $desde, $i - $desde + 1);
            }
        }

        $this->fail("No se pudo delimitar el cuerpo de `{$metodo}`.");
    }

    /**
     * El arrastre se enciende con la referencia, y la referencia solo la
     * declara la variante de pestaña. En escritorio no hay hoja que arrastrar
     * --el panel cuelga de un botón de la barra-- y todos los métodos quedan
     * inertes sin una sola condición de ancho en el JavaScript.
     */
    public function test_solo_la_hoja_del_movil_se_arrastra(): void
    {
        $movil = Blade::render(
            '<x-publico.menu-grupo variante="pestana" titulo="Bolsas" icono="briefcase" :enlaces="$enlaces" />',
            ['enlaces' => [['ruta' => 'empleo.index', 'texto' => 'Empleo']]]
        );

        $this->assertStringContainsString('x-ref="hoja"', $movil);
        $this->assertStringContainsString('x-on:pointerdown="tomarLaHoja($event)"', $movil);
        $this->assertStringContainsString('x-on:pointermove="moverLaHoja($event)"', $movil);
        $this->assertStringContainsString('x-on:pointercancel="soltarLaHoja($event)"', $movil);

        $escritorio = Blade::render(
            '<x-publico.menu-grupo titulo="Bolsas" :enlaces="$enlaces" />',
            ['enlaces' => [['ruta' => 'empleo.index', 'texto' => 'Empleo']]]
        );

        $this->assertStringNotContainsString('x-ref="hoja"', $escritorio, 'el panel de escritorio no se arrastra');
        $this->assertStringNotContainsString('tomarLaHoja', $escritorio);
    }

    /**
     * `pointercancel` tiene que soltar igual que `pointerup`. El sistema lo
     * dispara cuando se lleva el gesto --una llamada entrante, el gesto de
     * volver atrás desde el borde--, y sin él la hoja se queda pegada al dedo
     * para siempre: `arrastrando` no vuelve a falso nunca.
     */
    public function test_el_gesto_interrumpido_por_el_sistema_tambien_suelta(): void
    {
        $this->assertStringContainsString('x-on:pointercancel="soltarLaHoja($event)"', $this->componente());
    }

    /**
     * El clic se intercepta en fase de CAPTURA. En fase de burbuja llegaría
     * después de que el enlace ya hubiera hecho lo suyo.
     */
    public function test_el_clic_que_sigue_a_un_arrastre_se_traga_en_captura(): void
    {
        $this->assertStringContainsString(
            'x-on:click.capture="tragarElClicDelArrastre($event)"',
            $this->componente(),
            'Sin `.capture` el enlace navega antes de que nadie pueda impedirlo.'
        );

        // Y la marca que sobrevive al hueco entre `pointerup` y `click`: con
        // `reclamado` a secas la comprobación llegaría siempre en falso.
        $this->assertStringContainsString('this.acabaDeArrastrar = seArrastro;', $this->app());
        $this->assertMatchesRegularExpression(
            '/tragarElClicDelArrastre\(evento\) \{\s*if \(! this\.acabaDeArrastrar\) \{\s*return;\s*\}\s*this\.acabaDeArrastrar = false;/',
            $this->app(),
            'La marca tiene que apagarse al tragarse el clic, o el siguiente toque legítimo tampoco navega.'
        );
    }

    /**
     * Histéresis antes de reclamar el gesto. Sin ella, el temblor del pulgar
     * al tocar una fila cuenta como arrastre y el enlace deja de abrirse.
     */
    public function test_hay_umbral_antes_de_robarle_el_gesto_al_toque(): void
    {
        $app = $this->app();

        $this->assertStringContainsString('umbralDeArrastre: 10,', $app);
        $this->assertMatchesRegularExpression(
            '/if \(Math\.abs\(recorrido\) < this\.umbralDeArrastre\) \{\s*return;\s*\}/',
            $app,
            'El arrastre se reclama antes de que el dedo se haya movido de verdad.'
        );

        // La captura del puntero llega DESPUÉS de reclamar, no antes: capturar
        // en el `pointerdown` se lleva el gesto aunque acabe siendo un toque.
        // Y va envuelta: capturar lanza si el puntero ya no está activo --el
        // dedo se levantó entre dos eventos, o el sistema se llevó el gesto--,
        // y eso no puede abortar un arrastre que sigue siendo válido.
        $this->assertMatchesRegularExpression(
            '/this\.reclamado = true;.*?try \{\s*this\.\$refs\.hoja\.setPointerCapture\(evento\.pointerId\);\s*\} catch/s',
            $app
        );
    }

    /**
     * La interrupción, que es de lo que va todo esto: se toma el mando desde
     * donde la hoja ESTÁ, no desde donde iba. Con `meta` en vez de `valor`, una
     * hoja agarrada a mitad de cierre daría un salto al punto de destino.
     */
    public function test_el_arrastre_toma_el_mando_desde_la_posicion_en_pantalla(): void
    {
        $this->assertMatchesRegularExpression(
            '/this\.hojaAlEmpezar = this\.resorteDeLaHoja\.valor;\s*this\.resorteDeLaHoja\.fijar\(this\.hojaAlEmpezar\);/',
            $this->app(),
            'Se toma el mando desde la meta y no desde la posición: la hoja saltará al agarrarla en vuelo.'
        );
    }

    /**
     * Decide la proyección del momento, no dónde se soltó el dedo. Comparar
     * contra la posición hace que un golpe corto y rápido --el gesto natural
     * para descartar algo-- no cierre.
     */
    public function test_el_cierre_lo_decide_el_momento_proyectado(): void
    {
        $app = $this->app();

        $this->assertStringContainsString('const reposo = this.resorteDeLaHoja.valor + proyectar(velocidad, DECELERACION_DE_LA_HOJA);', $app);
        $this->assertStringContainsString('if (reposo > this.altoDeLaHoja / 2) {', $app);

        // Y la velocidad se le entrega al resorte en los dos desenlaces, que es
        // lo que quita la costura entre el arrastre y la animación.
        $this->assertStringContainsString('this.resorteDeLaHoja.empujar(velocidad);', $app);
    }

    /**
     * La deceleración de la hoja NO es la del scroll, y esto se midió.
     *
     * El 0,998 por defecto multiplica la velocidad por 499 y está calibrado
     * para una lista que se desplaza miles de píxeles. La hoja mide 155.
     * Medido el 9 sep 2026 sobre «Bolsas»: un arrastre suave de 40 px da unos
     * 143 px/s, que con 0,998 proyectan 71 px y llevan el reposo a 111 --la
     * hoja se CERRABA con un tirón corto y suave--. Con 0,99 esos mismos
     * 143 px/s proyectan 14 px y vuelve a abrirse, mientras que un golpe real
     * de 45 px en 34 ms (1351 px/s medidos) proyecta 134 px y la cierra.
     *
     * Rotura: volver a `proyectar(velocidad)` a secas.
     */
    public function test_la_hoja_proyecta_con_su_propia_deceleracion_y_no_con_la_del_scroll(): void
    {
        $app = $this->app();

        $this->assertStringContainsString('const DECELERACION_DE_LA_HOJA = 0.99;', $app);
        $this->assertStringNotContainsString(
            'proyectar(velocidad);',
            $app,
            'La hoja volvió a proyectar con la deceleración del scroll: un arrastre suave y corto la cerrará.'
        );
    }

    /**
     * Reabrir corta el cierre en vuelo.
     *
     * Sin esto, el resorte del cierre por gesto sigue corriendo, llega a su
     * meta y ejecuta el `cerrar()` que llevaba pendiente: la hoja que se acaba
     * de reabrir se cierra sola unos milisegundos después.
     *
     * Rotura: quitar `this.pararElReloj()` de `abrir()`.
     */
    public function test_reabrir_corta_el_cierre_que_estuviera_en_vuelo(): void
    {
        $this->assertStringContainsString(
            'this.pararElReloj();',
            $this->cuerpoDe('abrir()'),
            'Abrir no cancela el resorte del cierre anterior: la hoja recién reabierta se cerrará sola cuando ese resorte llegue a su meta.'
        );
    }

    /**
     * Sin alto no se arrastra. El alto es el divisor del desvanecido y de la
     * goma: con cero, el primer píxel de arrastre apagaría la hoja entera.
     * Pasa de verdad mientras la transición de entrada aún no la ha mostrado.
     */
    public function test_no_se_arrastra_una_hoja_sin_alto(): void
    {
        $this->assertMatchesRegularExpression(
            '/const alto = hoja\.offsetHeight;.*?if \(alto === 0\) \{\s*return;\s*\}/s',
            $this->app()
        );
    }

    /**
     * El estilo en línea se limpia al ABRIR y no al cerrar.
     *
     * Al cerrar, Alpine arranca su transición de salida; quitar ahí el
     * `translate` devolvería la hoja de un salto a su sitio en pleno
     * desvanecido. Al abrir es el otro momento seguro, y además el único que
     * garantiza que la hoja nace limpia.
     */
    public function test_la_pintura_se_suelta_al_abrir_y_no_al_cerrar(): void
    {
        $this->assertMatchesRegularExpression(
            '/abrir\(\) \{\s*clearTimeout\(this\.cierre\);.*?this\.soltarLaPintura\(\);\s*this\.resorteDeLaHoja\?\.fijar\(0\);/s',
            $this->app(),
            'Abrir tiene que devolver la hoja a su estado limpio.'
        );
    }

    /**
     * La goma solo va hacia arriba. Hacia abajo el recorrido es real --es el
     * camino de salida-- y frenarlo haría que la hoja se sintiera pegajosa
     * justo en el gesto que se quiere premiar.
     */
    public function test_la_goma_solo_resiste_hacia_arriba(): void
    {
        $this->assertMatchesRegularExpression(
            '/if \(y < 0\) \{\s*y = -goma\(-y, this\.altoDeLaHoja\);\s*\}/',
            $this->app()
        );
    }

    /** El reloj se suelta al acabar: un rAF vivo por hoja abierta es batería. */
    public function test_el_arrastre_se_da_de_baja_del_reloj(): void
    {
        $app = $this->app();

        $this->assertStringContainsString('this.quitarDelReloj = animar((dt) => {', $app);
        $this->assertMatchesRegularExpression(
            '/pararElReloj\(\) \{\s*if \(this\.quitarDelReloj\) \{\s*this\.quitarDelReloj\(\);\s*this\.quitarDelReloj = null;/',
            $app
        );
    }

    /** La háptica marca el cierre, que es el único momento con significado. */
    public function test_la_haptica_solo_marca_el_cierre(): void
    {
        $this->assertMatchesRegularExpression('/vibrar\(8\);\s*this\.cerrarConGesto\(velocidad\);/', $this->app());
    }
}

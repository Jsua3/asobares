<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El sitio corría con la rampa cruda de Tailwind, que no declara ni un
 * `letter-spacing`, y con un `-0.02em` plano sobre h1..h4 que valía -0,32 px en
 * un título de tarjeta de 16 px y -1,20 px en un hero de 60 px: el mismo número
 * para dos problemas opuestos. La escala óptica lo sustituye tamaño por tamaño.
 *
 * Pero la prueba que de verdad importa es la primera, y no habla de tipografía
 * sino de en qué archivo vive: la escala repinta el sitio público donde está y
 * repintaría también /admin si alguien la mudara al archivo que comparten.
 */
class TipografiaTest extends TestCase
{
    /**
     * Quita los comentarios del CSS antes de buscar en él.
     *
     * Hace falta porque las dos prohibiciones de abajo son de subcadena, y los
     * archivos de este proyecto EXPLICAN la regla que cumplen: `tokens.css`
     * dice por qué no puede declarar `--text-*`, y decirlo lo hacía fallar.
     * Un nombre citado en un comentario no repinta nada; lo que repinta es una
     * declaración, y es lo único que sobrevive a esta poda.
     */
    private function sinComentarios(string $css): string
    {
        return (string) preg_replace('#/\*.*?\*/#s', '', $css);
    }

    /**
     * LA PRUEBA QUE IMPIDE EL DESASTRE DEL PANEL.
     *
     * `resources/css/filament/admin/theme.css:3` importa `tokens.css`, y
     * `vite.config.js` compila los dos como entrypoints separados: una variable
     * de namespace de Tailwind declarada allí cae en LOS DOS paquetes. Está
     * medido sobre el CSS compilado del panel — `--text-sm` sale 242 veces,
     * `--text-base` 48 y `--text-xs` 32: 372 reglas de /admin repintadas de
     * golpe, con `--text-sm--line-height` pasando de 20 px fijos a 21,7 px
     * relativos justo debajo de cada celda de tabla, cada etiqueta de
     * formulario y cada ítem de navegación, cuyas alturas de fila y centrados
     * de icono están calculados contra los 20 px de hoy.
     *
     * Y sería invisible en revisión: ninguna prueba del panel mira tipografía.
     * El instinto dice que la tipografía va con la marca, o sea en `tokens.css`
     * junto a `--font-sans`. Esta prueba es la que dice que no.
     */
    public function test_la_escala_no_puede_mudarse_al_archivo_que_comparte_el_panel(): void
    {
        $tokens = $this->sinComentarios(File::get(resource_path('css/tokens.css')));

        foreach (['--text-', '--tracking-', '--leading-'] as $namespace) {
            $this->assertStringNotContainsString(
                $namespace,
                $tokens,
                "`{$namespace}` en tokens.css repinta /admin en silencio: el tema del panel importa este archivo. La escala va en app.css, que solo carga el sitio público."
            );
        }
    }

    /**
     * La escala se audita por su FORMA, no por sus números: lo que la hace una
     * escala óptica es que las dos columnas sean monótonas y que se crucen en
     * el cuerpo. Un retoque suelto —subir un leading porque una tarjeta quedó
     * justa— rompe eso sin romper nada visible, y es exactamente el modo de
     * fallo que devolvió al sitio al `-0.02em` plano la primera vez.
     *
     * Poppins se sirve en seis pesos estáticos y su woff no trae tabla `fvar`:
     * sin ejes de variación no hay `opsz`, así que `font-optical-sizing` sería
     * letra muerta aquí. Esta compensación es a mano o no es.
     */
    public function test_la_escala_optica_es_monotona_y_cruza_el_cero_en_el_cuerpo(): void
    {
        $app = File::get(resource_path('css/app.css'));

        preg_match_all('/--text-([a-z0-9]+):\s*([\d.]+)rem;/', $app, $tamanos);
        preg_match_all('/--text-([a-z0-9]+)--line-height:\s*([\d.]+);/', $app, $alturas);
        preg_match_all('/--text-([a-z0-9]+)--letter-spacing:\s*(-?[\d.]+)em;/', $app, $trackings);

        $escala = array_map('floatval', array_combine($tamanos[1], $tamanos[2]));
        $altura = array_map('floatval', array_combine($alturas[1], $alturas[2]));
        $tracking = array_map('floatval', array_combine($trackings[1], $trackings[2]));

        $this->assertNotEmpty($escala, 'No hay ninguna escala tipográfica declarada en app.css.');
        asort($escala);

        foreach (array_keys($escala) as $paso) {
            $this->assertArrayHasKey($paso, $altura, "`text-{$paso}` no declara line-height: caería en el default de Tailwind.");
            $this->assertArrayHasKey($paso, $tracking, "`text-{$paso}` no declara letter-spacing: se quedaría sin compensación óptica.");
        }

        // Cuanto más grande el tipo, menos aire entre líneas y más apretadas
        // las letras. Estrictamente, sin mesetas: una meseta es un tamaño que
        // se olvidó de compensar.
        $anterior = null;

        foreach (array_keys($escala) as $paso) {
            if ($anterior !== null) {
                $this->assertLessThan(
                    $altura[$anterior],
                    $altura[$paso],
                    "El leading de `text-{$paso}` no baja respecto a `text-{$anterior}`: la escala deja de ser monótona."
                );
                $this->assertLessThan(
                    $tracking[$anterior],
                    $tracking[$paso],
                    "El tracking de `text-{$paso}` no baja respecto a `text-{$anterior}`: la escala deja de ser monótona."
                );
            }

            $anterior = $paso;
        }

        // El punto de giro. Por debajo del cuerpo el texto pequeño necesita
        // aire; por encima, el titular necesita apretarse.
        $this->assertSame(0.0, $tracking['base'], 'El cuerpo de 16 px es el cruce por cero de la escala.');
        $this->assertGreaterThan(0, $tracking['xs'], 'El texto pequeño pide tracking positivo.');
        $this->assertLessThan(0, $tracking['6xl'], 'El titular grande pide tracking negativo.');
    }

    /**
     * El plano y la escala no pueden convivir: `letter-spacing` en `@layer base`
     * lo pisa una utilidad de tamaño, pero solo donde hay utilidad de tamaño, y
     * el resultado sería un sitio con dos reglas de tracking según el elemento.
     *
     * Su gemelo del panel SÍ se queda. Allí no hay escala, la interfaz es densa
     * y no tiene ningún titular de 60 px: el plano es lo correcto. Que las dos
     * hojas digan cosas distintas es la decisión, no el descuido.
     */
    public function test_el_tracking_plano_sale_del_sitio_publico_y_se_queda_en_el_panel(): void
    {
        $app = $this->sinComentarios(File::get(resource_path('css/app.css')));
        $panel = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringNotContainsString(
            'letter-spacing: -0.02em',
            $app,
            'El -0.02em plano es el valor único que la escala vino a sustituir.'
        );
        $this->assertStringContainsString('letter-spacing: -0.02em', $panel);
    }
}

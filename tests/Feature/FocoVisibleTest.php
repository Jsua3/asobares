<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * El indicador de foco es el RNF-12, no un pulido.
 *
 * `app.css` declaraba desde el principio un `:focus-visible` que cumple, y aun
 * así los formularios del sitio llevaban seis meses sin indicador válido: dos
 * vistas escribían `focus:outline-none`, que compila en `@layer utilities` y
 * gana a `@layer base` por orden de capa —sin que la especificidad entre en
 * juego—, y lo cambiaban por un anillo translúcido de 2,21:1. El resultado no
 * era «sin foco», que se habría denunciado el primer día: era un foco de 2,21:1,
 * visible lo justo para que nadie mirara y por debajo del 3:1 de WCAG 2.1
 * §1.4.11.
 *
 * Ninguna prueba del repositorio miraba el foco ni renderizaba
 * `<x-publico.campo>`, y por eso la brecha convivió con la suite en verde. Esta
 * clase es lo que impide que se reabra: dos guardias estructurales que barren
 * las vistas, una numérica que mide con la fórmula de WCAG, y dos que fijan el
 * comportamiento del componente.
 */
class FocoVisibleTest extends TestCase
{
    use MideContraste;

    /** El mínimo de SC 1.4.11 para componentes de interfaz no textuales. */
    private const MINIMO = 3.0;

    // --- A. Que nadie vuelva a apagar el indicador ---

    /**
     * Patrones que apagan el `:focus-visible` de `app.css` o lo tapan.
     *
     * `public static` siguiendo el patrón de guardia compartida del repositorio
     * (`MovimientoTest::patronesProhibidos()`, `TemaClaroOscuroTest::clasesProhibidas()`):
     * si mañana el panel quiere la misma vigilancia, la importa en vez de copiarla.
     *
     * @return array<string, string> patrón => por qué
     */
    public static function patronesQueApaganElFoco(): array
    {
        return [
            '/\boutline-none\b/' => 'en Tailwind 4 compila a `outline-style: none` desde @layer utilities y gana al :focus-visible de @layer base',
            '/\boutline-hidden\b/' => 'misma capa y mismo efecto que outline-none',
            '/\boutline-0\b/' => 'un outline de 0px es un outline apagado con otro nombre',
            '/outline:\s*none/' => 'apagar el outline a mano tiene el mismo efecto y además esquiva el grep de las utilidades',
            '/focus(?:-visible|-within)?:ring-0\b/' => 'anular el anillo en foco deja el control sin ningún acuse propio',
            '/focus(?:-visible|-within)?:shadow-none\b/' => 'donde el acuse se dibuja con sombra, apagarla es apagar el foco',
        ];
    }

    /**
     * Barre `resources/views` ENTERA y no los siete directorios de
     * `MovimientoTest`. La segunda ocurrencia de `focus:outline-none` —el select
     * de postulaciones de `mi-cuenta/vacantes/show`— es justo la que ningún
     * documento registraba, porque la auditoría se detuvo en el componente. Un
     * barrido con lista de directorios vuelve a dejar fuera lo que se añada
     * mañana en un directorio nuevo.
     */
    public function test_ninguna_vista_anula_el_indicador_de_foco(): void
    {
        $hallazgos = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if ($archivo->getExtension() !== 'php') {
                continue;
            }

            $contenido = $archivo->getContents();
            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

            foreach (self::patronesQueApaganElFoco() as $patron => $motivo) {
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

        $this->assertSame([], $hallazgos, "Vistas que anulan el indicador de foco:\n".implode("\n", $hallazgos));
    }

    // --- B. Que el indicador que queda cumpla, y en los dos temas ---

    /**
     * La guardia numérica. No comprueba que exista una regla `:focus-visible`
     * —eso lo vería cualquiera— sino que el color que declara se despega de los
     * CUATRO fondos sobre los que puede caer: fondo y superficie, en claro y en
     * oscuro. Se miden los cuatro y no solo el fondo de página porque el
     * `outline-offset` hace que el trazo se dibuje SOBRE el contenedor, que unas
     * veces es la página y otras una `.tarjeta`.
     *
     * Recalculado en esta sesión contra `--color-marca-500: #ee4137`: 3,4900 /
     * 3,8555 / 5,1496 / 4,9161. El peor de los cuatro es el claro sobre
     * `--asb-fondo`, y le sobra un 16 % sobre el mínimo: no es un aprobado
     * raspado, pero tampoco tanto margen como para que un rojo un punto más
     * claro siga pasando.
     */
    public function test_el_indicador_de_foco_alcanza_3_a_1_en_los_dos_temas(): void
    {
        $app = File::get(resource_path('css/app.css'));
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertSame(
            1,
            preg_match('/:focus-visible\s*\{([^}]*)\}/', $app, $regla),
            'app.css tiene que declarar una regla :focus-visible: es el único indicador de foco del sitio.'
        );

        $this->assertSame(
            1,
            preg_match('/outline:\s*(\d+(?:\.\d+)?)px\s+solid\s+var\((--[\w-]+)\)/', $regla[1], $trazo),
            'El :focus-visible debe dibujar un outline sólido de N px con un token de color, para que se pueda medir.'
        );

        [, $grosor, $token] = $trazo;

        $this->assertGreaterThanOrEqual(
            2,
            (float) $grosor,
            'SC 1.4.11 pide un área mínima: un trazo de 1px no la alcanza en un control de 37 px de alto.'
        );

        /*
         * El desplazamiento es lo que hace que las cuatro cifras de abajo sean
         * las que rigen: sin él el trazo se dibujaría pegado al campo y habría
         * que medirlo también contra el `bg-fondo` del propio control.
         */
        $this->assertMatchesRegularExpression(
            '/outline-offset:\s*[1-9]/',
            $regla[1],
            'Sin outline-offset el trazo se pinta sobre el relleno del campo y cambia contra qué hay que medirlo.'
        );

        $this->assertSame(
            1,
            preg_match('/'.preg_quote($token, '/').':\s*(#[0-9a-fA-F]{6})/', $tokens, $color),
            "El token {$token} tiene que existir en tokens.css con un hexadecimal medible."
        );

        $flojos = [];

        foreach ($this->fondosDeLosDosTemas() as $nombre => $fondo) {
            $razon = $this->contraste($color[1], $fondo);

            if ($razon < self::MINIMO) {
                $flojos[] = sprintf('%s (%s sobre %s): %.4f:1', $nombre, $color[1], $fondo, $razon);
            }
        }

        $this->assertSame(
            [],
            $flojos,
            "El indicador de foco no llega a 3:1 (WCAG 2.1 §1.4.11):\n".implode("\n", $flojos)
        );
    }

    // --- C. Que nadie reponga el anillo translúcido ---

    /**
     * La guardia que cierra la puerta de vuelta. Quitar el anillo de 2,21:1 no
     * sirve de nada si mañana alguien lo «recupera» con otra opacidad, porque a
     * ojo un anillo al 75 % se ve perfectamente y sigue sin cumplir.
     *
     * Se compone el color al alfa que declare la clase sobre cada uno de los
     * cuatro fondos —que es la cuenta que hace el navegador con un box-shadow
     * translúcido— y se exige el 3:1. Medido: al 60 % da 2,2130, al 75 % 2,6671
     * y al 80 % 2,8416; solo a partir del 90 % pasa, y por poco. Con este color
     * el único anillo defendible en los dos temas es el opaco.
     */
    public function test_ningun_anillo_de_foco_baja_del_minimo(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $fondos = $this->fondosDeLosDosTemas();
        $hallazgos = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if ($archivo->getExtension() !== 'php') {
                continue;
            }

            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());
            preg_match_all('/\bring-([a-z]+)-(\d+)\/(\d+)\b/', $archivo->getContents(), $anillos, PREG_SET_ORDER);

            foreach ($anillos as [$clase, $familia, $paso, $alfa]) {
                $token = "--color-{$familia}-{$paso}";

                if (preg_match('/'.preg_quote($token, '/').':\s*(#[0-9a-fA-F]{6})/', $tokens, $color) !== 1) {
                    $hallazgos[] = "{$ruta} → {$clase} usa {$token}, que no se puede resolver ni medir";

                    continue;
                }

                foreach ($fondos as $nombre => $fondo) {
                    $compuesto = $this->componer($color[1], (int) $alfa / 100, $fondo);
                    $razon = $this->contraste($compuesto, $fondo);

                    if ($razon < self::MINIMO) {
                        $hallazgos[] = sprintf('%s → %s sobre %s: %.4f:1', $ruta, $clase, $nombre, $razon);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $hallazgos,
            "Anillos de foco por debajo de 3:1 (WCAG 2.1 §1.4.11):\n".implode("\n", $hallazgos)
        );

        /*
         * Y la calibración, que es lo que impide que esta prueba pase por estar
         * vacía. Fija el número histórico: el anillo que el sitio llevaba puesto
         * daba 2,21:1 sobre el fondo claro. Si algún día esta línea empieza a
         * fallar es que la paleta cambió, y entonces hay que rehacer las cifras
         * de arriba antes de fiarse del barrido.
         */
        $this->assertSame(
            1,
            preg_match('/--color-marca-500:\s*(#[0-9a-fA-F]{6})/', $tokens, $marca)
        );

        $this->assertLessThan(
            self::MINIMO,
            $this->contraste($this->componer($marca[1], 0.60, $fondos['claro/fondo']), $fondos['claro/fondo']),
            'El anillo al 60 % que motivó esta prueba debe seguir midiéndose por debajo del mínimo.'
        );
    }

    // --- D y E. El componente ---

    /**
     * La ayuda del campo nunca estuvo asociada al control: `aria-describedby`
     * solo se emitía al errar, así que las once ayudas del sitio no existían
     * para un lector de pantalla ni cuando se veían en pantalla (SC 1.3.1). Y al
     * fallar la validación, la ayuda desaparecía: la persona perdía justo la
     * instrucción que acababa de incumplir.
     */
    public function test_el_campo_asocia_la_ayuda_y_el_error_con_el_control(): void
    {
        $sinError = $this->renderizarCampo(
            '<x-publico.campo nombre="correo" etiqueta="Correo" ayuda="Te avisamos por aqui." />'
        );

        // El id es estable, no un Str::random que cambia en cada render.
        $this->assertStringContainsString('id="campo-correo"', $sinError);
        $this->assertStringContainsString('<p id="campo-correo-ayuda"', $sinError);
        $this->assertStringContainsString('aria-describedby="campo-correo-ayuda"', $sinError);
        $this->assertStringNotContainsString('aria-invalid', $sinError);

        $conError = $this->renderizarCampo(
            '<x-publico.campo nombre="correo" etiqueta="Correo" ayuda="Te avisamos por aqui." />',
            ['correo' => 'Ese correo no parece valido.']
        );

        $this->assertStringContainsString('aria-invalid="true"', $conError);

        // Los dos ids, y la ayuda primero: describe el formato, y el error es la
        // consecuencia de no seguirlo.
        $this->assertStringContainsString(
            'aria-describedby="campo-correo-ayuda campo-correo-error"',
            $conError
        );

        // El corazón del arreglo: la ayuda SOBREVIVE al error.
        $this->assertStringContainsString('Te avisamos por aqui.', $conError);
        $this->assertStringContainsString('Ese correo no parece valido.', $conError);
    }

    /**
     * «La rejilla no salta», traducido a algo que una máquina puede comprobar.
     *
     * La reserva no puede ser un número a ojo: `min-h-4` (16 px) era exacto con
     * el `line-height: 1.3333` de fábrica de `text-xs` y se quedó corto en cuanto
     * la escala óptica de `app.css` lo subió a 1.6 —la caja pasó a 19,2 px—. Así
     * que la prueba recalcula la caja contra la escala VIGENTE y compara con el
     * `min-h-*` que el componente escribe de verdad. El día que la tipografía se
     * vuelva a mover, esto pide el escalón siguiente en vez de dejar que la
     * rejilla vuelva a saltar en silencio.
     */
    public function test_la_ranura_de_apoyo_se_emite_siempre_y_cubre_una_linea(): void
    {
        $desnudo = $this->renderizarCampo('<x-publico.campo nombre="mensaje" etiqueta="Mensaje" />');

        // El patrón se ancla a la ranura y no a cualquier `min-h-*` del render:
        // desde que el control lleva su propio `min-h-11` para llegar a los
        // 44 px de objetivo táctil, un patrón suelto se quedaba con ESE y medía
        // la reserva contra 2,75rem. La ranura es el único bloque con `text-xs`.
        $this->assertSame(
            1,
            preg_match('/<div class="[^"]*\bmin-h-(\d+)\b[^"]*\btext-xs\b[^"]*">/', $desnudo, $reserva),
            'Un campo sin ayuda y sin error tiene que emitir igualmente la ranura, o estrenar un error empuja al vecino.'
        );

        $app = File::get(resource_path('css/app.css'));

        $this->assertSame(1, preg_match('/--text-xs:\s*([\d.]+)rem/', $app, $tamano));
        $this->assertSame(1, preg_match('/--text-xs--line-height:\s*([\d.]+)/', $app, $interlinea));

        $caja = (float) $tamano[1] * (float) $interlinea[1];
        $reservado = (int) $reserva[1] * 0.25;

        $this->assertGreaterThanOrEqual(
            $caja,
            $reservado,
            sprintf(
                'min-h-%d reserva %.4frem y una línea de text-xs mide %.4frem: la ranura se queda corta y la rejilla salta al errar.',
                (int) $reserva[1],
                $reservado,
                $caja
            )
        );

        // Y no al revés: reservar de más es aire muerto bajo los 44 campos que
        // no traen ayuda. Un escalón de holgura (0,25rem) es todo lo aceptable.
        $this->assertLessThan(
            $caja + 0.25,
            $reservado,
            'La ranura reserva más de un escalón por encima de la línea: sobra aire bajo cada campo.'
        );
    }

    /**
     * El id determinista de `campo.blade.php` colisionaría si una página
     * repitiera un `nombre`. Hoy ninguna de las once lo hace, y sin esta guardia
     * nada lo impediría: dos campos con el mismo id rompen el `<label for>` del
     * segundo, que deja de ser pulsable y de anunciarse.
     *
     * Se mira archivo a archivo. Las dos vistas que componen campos desde otro
     * componente (`vacantes/crear` y `vacantes/editar` con
     * `formulario-vacante`) no declaran ninguno propio, así que ningún par puede
     * cruzarse por composición; si algún día lo hicieran, esto no lo vería.
     */
    public function test_ninguna_vista_repite_el_nombre_de_un_campo(): void
    {
        $hallazgos = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if ($archivo->getExtension() !== 'php') {
                continue;
            }

            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());
            preg_match_all('/<x-publico\.campo\b[^>]*\bnombre="([^"]+)"/', $archivo->getContents(), $campos);

            $repetidos = array_keys(array_filter(array_count_values($campos[1]), static fn (int $veces): bool => $veces > 1));

            foreach ($repetidos as $nombre) {
                $hallazgos[] = "{$ruta} → nombre=\"{$nombre}\" aparece más de una vez: los dos campos comparten id";
            }
        }

        $this->assertSame([], $hallazgos, "Nombres de campo repetidos en una misma vista:\n".implode("\n", $hallazgos));
    }

    /*
     * El otro frente de este mismo RNF-12 —que los N selects de estado y los N
     * botones «Guardar» de `mi-cuenta/vacantes/show` digan de quién son (SC
     * 4.1.2 y 3.3.2)— se vigila en
     * `MisVacantesTest::test_los_controles_de_cada_postulacion_dicen_de_quien_son`,
     * que ya tiene montadas las semillas para rendir esa página de verdad. Esta
     * clase se queda sin base de datos a propósito: mide CSS y rinde un
     * componente, y así corre en menos de un segundo.
     */

    // --- Apoyos ---

    /**
     * Los cuatro fondos sobre los que puede caer un indicador de foco, leídos de
     * `tokens.css` y no cableados: si mañana se retoca la paleta, estas pruebas
     * miden la nueva y fallan solas.
     *
     * @return array<string, string>
     */
    private function fondosDeLosDosTemas(): array
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $claro = $this->bloqueDeReglas($tokens, '/^:root\s*\{/m');
        $oscuro = $this->bloqueDeReglas($tokens, '/^\.dark\s*\{/m');

        return [
            'claro/fondo' => $this->tokenDeColor($claro, '--asb-fondo'),
            'claro/superficie' => $this->tokenDeColor($claro, '--asb-superficie'),
            'oscuro/fondo' => $this->tokenDeColor($oscuro, '--asb-fondo'),
            'oscuro/superficie' => $this->tokenDeColor($oscuro, '--asb-superficie'),
        ];
    }

    /**
     * Recorta un bloque de reglas contando llaves, y no con una expresión
     * regular: `:root` declara `color-mix(...)` y otros valores con paréntesis,
     * y un `[^}]*` se detendría en la primera llave anidada que aparezca mañana.
     */
    private function bloqueDeReglas(string $css, string $selector): string
    {
        $this->assertSame(
            1,
            preg_match($selector, $css, $coincidencia, PREG_OFFSET_CAPTURE),
            "No se encontró el bloque {$selector} en tokens.css."
        );

        $apertura = strpos($css, '{', $coincidencia[0][1]);
        $nivel = 0;

        for ($i = $apertura, $largo = strlen($css); $i < $largo; $i++) {
            if ($css[$i] === '{') {
                $nivel++;
            } elseif ($css[$i] === '}') {
                $nivel--;

                if ($nivel === 0) {
                    return substr($css, $apertura, $i - $apertura);
                }
            }
        }

        $this->fail("El bloque {$selector} de tokens.css no cierra.");
    }

    private function tokenDeColor(string $bloque, string $token): string
    {
        $this->assertSame(
            1,
            preg_match('/'.preg_quote($token, '/').':\s*(#[0-9a-fA-F]{6})/', $bloque, $coincidencias),
            "No se pudo leer `{$token}`: la prueba se quedaría sin referencia contra la que medir."
        );

        return strtolower($coincidencias[1]);
    }

    /**
     * Renderiza el componente fuera de una petición. Hace falta sembrar dos
     * cosas que en el sitio pone el middleware y aquí no existen: la sesión, que
     * es de donde `old()` repuebla, y el `$errors` del que dependen `@error` y
     * `$errors->has()`.
     *
     * @param  array<string, string>  $errores
     */
    private function renderizarCampo(string $plantilla, array $errores = []): string
    {
        $this->startSession();
        $this->app['request']->setLaravelSession($this->app['session']->driver());

        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(
            array_map(static fn (string $mensaje): array => [$mensaje], $errores)
        )));

        return Blade::render($plantilla);
    }
}

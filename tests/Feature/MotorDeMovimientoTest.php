<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * El motor de resortes: `resources/js/movimiento.js`.
 *
 * Esta clase hace DOS cosas distintas y conviene no confundirlas.
 *
 * La primera es la de siempre en este proyecto: leer el archivo como texto y
 * afirmar sobre los contratos que no se pueden perder. Sirve para que nadie
 * borre una salvaguarda por descuido, y **no prueba absolutamente nada sobre
 * la física**: una expresión regular no distingue un oscilador amortiguado de
 * una interpolación lineal.
 *
 * La segunda sí: `test_la_fisica_del_motor_se_comporta` ejecuta
 * `tests/Support/verifica-motor.mjs` con Node, que IMPORTA el módulo de verdad
 * y mide su comportamiento --sobreimpulso, asentamiento, continuidad al
 * redirigir, convergencia con un fotograma monstruoso--. Es la única prueba
 * honesta del motor, y por eso existe aunque el proyecto no tenga runner de JS:
 * montarlo habría sido cambiar dependencias, que está congelado, y ejecutar un
 * archivo con `node` no añade ninguna.
 *
 * Si no hay Node, esa prueba se OMITE en vez de mentir. Las de texto siguen.
 */
class MotorDeMovimientoTest extends TestCase
{
    private function motor(): string
    {
        return File::get(resource_path('js/movimiento.js'));
    }

    // ── Lo que de verdad prueba el motor ──────────────────────────────────

    /**
     * La física, medida sobre el módulo real.
     *
     * Las cuatro mutaciones que se comprobaron en rojo antes de dar esto por
     * bueno: quitar el sub-paso (el resorte se va a 8.422.062 px con un
     * fotograma de 4 s), cambiar la proyección por la fórmula del libro de
     * física, ignorar el movimiento reducido y volver lineal la goma.
     */
    public function test_la_fisica_del_motor_se_comporta(): void
    {
        $node = $this->dondeEstaNode();

        if ($node === null) {
            $this->markTestSkipped('Sin Node no se puede ejecutar el módulo; las guardas de texto de esta misma clase siguen corriendo.');
        }

        $proceso = new Process([
            $node,
            base_path('tests/Support/verifica-motor.mjs'),
            resource_path('js/movimiento.js'),
        ], timeout: 60);

        $proceso->run();

        $this->assertSame(
            0,
            $proceso->getExitCode(),
            "La verificación numérica del motor falló:\n".$proceso->getOutput().$proceso->getErrorOutput()
        );
    }

    private function dondeEstaNode(): ?string
    {
        foreach (['node', 'node.exe'] as $candidato) {
            $proceso = new Process([$candidato, '--version'], timeout: 10);
            $proceso->run();

            if ($proceso->isSuccessful()) {
                return $candidato;
            }
        }

        return null;
    }

    // ── Contratos que no se pueden perder ─────────────────────────────────

    /**
     * La proyección tiene que ser la de Apple y no la del libro de física.
     *
     * A 1000 px/s las dos dan casi lo mismo --499 contra 500-- y por eso el
     * cambio pasa desapercibido; a velocidades bajas y altas se separan, y el
     * gesto se siente pesado o disparado. Se afirma la EXPRESIÓN porque es lo
     * único que distingue una de otra a simple vista.
     */
    public function test_la_proyeccion_es_la_exponencial_y_no_la_del_libro(): void
    {
        $this->assertStringContainsString(
            'return (velocidad / 1000) * deceleracion / (1 - deceleracion);',
            $this->motor(),
            'La proyección dejó de ser la deceleración exponencial de la charla de Apple.'
        );

        $this->assertStringNotContainsString('velocidad * velocidad', $this->motor());
    }

    /**
     * El sub-paso no es una optimización: es lo que impide que el resorte se
     * vaya al infinito cuando la pestaña vuelve del fondo. Medido: sin él, un
     * fotograma de 4 s deja la posición en 8.422.062 px.
     */
    public function test_la_integracion_parte_el_fotograma_y_acota_el_salto(): void
    {
        $motor = $this->motor();

        $this->assertStringContainsString('const trozos = Math.max(1, Math.ceil(dt / PASO_MAXIMO));', $motor);
        $this->assertStringContainsString('const h = dt / trozos;', $motor);
        $this->assertStringContainsString('Math.min((sello - ultimoSello) / 1000, DT_MAXIMO)', $motor);
    }

    /**
     * Un `requestAnimationFrame` corriendo en vacío es batería regalada, y el
     * grueso de las visitas a este sitio llega por teléfono.
     */
    public function test_el_reloj_se_para_cuando_no_queda_nadie_en_vuelo(): void
    {
        $this->assertMatchesRegularExpression(
            '/if \(enVuelo\.size === 0\) \{\s*corriendo = false;\s*return;\s*\}/',
            $this->motor(),
            'El bucle ya no se para solo: se queda pidiendo fotogramas para nadie.'
        );
    }

    /**
     * Se recorre una COPIA del conjunto: un paso que se da de baja a sí mismo
     * al asentarse --que es lo normal-- haría que el recorrido se saltara al
     * siguiente.
     */
    public function test_el_bucle_recorre_una_copia_de_los_pasos(): void
    {
        $this->assertStringContainsString('for (const paso of [...enVuelo])', $this->motor());
    }

    /**
     * El movimiento reducido se consulta EN VIVO y no al cargar. La clase
     * `sin-desplazamiento` del <html> la pone el IIFE de la cabecera una sola
     * vez; un resorte a 60 fotogramas por segundo tiene que enterarse en el
     * acto, sin recargar.
     */
    public function test_el_movimiento_reducido_se_consulta_en_vivo(): void
    {
        $motor = $this->motor();

        $this->assertStringContainsString("window.matchMedia('(prefers-reduced-motion: reduce)')", $motor);
        $this->assertStringContainsString('export const menosMovimiento = () => consultaMovimiento.matches;', $motor);
        // Se prohíbe el USO, no la palabra: el comentario de cabecera del motor
        // explica precisamente por qué no se lee esa clase, y prohibirla como
        // cadena suelta obligaría a borrar la explicación para pasar la prueba.
        $this->assertStringNotContainsString(
            "classList.contains('sin-desplazamiento')",
            $motor,
            'Esa clase la pone el IIFE de la cabecera una sola vez: el motor quedaría sordo a un cambio de preferencia en caliente.'
        );
    }

    /**
     * La háptica no puede tumbar nada. `navigator.vibrate` no existe en iOS y
     * en un documento sin interacción previa algunos navegadores lanzan.
     */
    public function test_la_haptica_no_puede_romper_nada(): void
    {
        $motor = $this->motor();

        $this->assertMatchesRegularExpression(
            '/try \{\s*navigator\.vibrate\?\.\(ms\);\s*\} catch/',
            $motor,
            'La vibración dejó de estar protegida.'
        );

        $this->assertMatchesRegularExpression(
            '/export function vibrar\(ms\) \{\s*if \(menosMovimiento\(\)\) \{\s*return;\s*\}/',
            $motor,
            'Quien pide menos movimiento tampoco quiere que el teléfono le vibre.'
        );
    }

    /** Sin dependencias nuevas: el alcance está congelado. */
    public function test_el_motor_no_importa_nada(): void
    {
        $this->assertStringNotContainsString('import ', $this->motor(), 'El motor tiene que bastarse solo.');

        $paquete = json_decode(File::get(base_path('package.json')), associative: true);

        foreach (['motion', 'framer-motion', 'popmotion', 'gsap', '@react-spring/web'] as $libreria) {
            $this->assertArrayNotHasKey($libreria, $paquete['dependencies'] ?? []);
            $this->assertArrayNotHasKey($libreria, $paquete['devDependencies'] ?? []);
        }
    }
}

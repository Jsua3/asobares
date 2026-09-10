<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * El rojo que lleva texto blanco encima no puede ser el rojo de marca.
 *
 * Pub Red #EE4137 con rótulo blanco da **3,86:1**, y RNF-12 pide 4,5:1 para
 * texto normal. Los botones de acción del sitio --«Afíliate», «Enviar»,
 * «Filtrar», la página actual del paginador, la insignia «Destacado»-- lo
 * llevaban, así que el rótulo más importante de la página no cumplía. Y fallaba
 * en los DOS temas: el relleno del botón es el mismo rojo en claro y en oscuro,
 * de modo que el contraste no depende del fondo de la página.
 *
 * `--asb-accion` (marca 600) da 4,83:1 y su hover (marca 700) 6,52:1.
 *
 * Es la misma decisión que `tokens.css` ya había tomado para el caso contrario
 * --texto rojo sobre fondo claro, que usa marca 700 y no Pub Red-- aplicada
 * ahora al blanco sobre rojo.
 *
 * ⚠️ **Esto no persigue a `bg-marca-500` en general.** El rojo de marca se
 * queda donde no hay texto encima: puntos indicadores, tintes al 15 %, el
 * botón flotante de WhatsApp --que es un icono, y un objeto gráfico solo debe
 * 3:1 por WCAG 1.4.11, que sí cumple--. Cambiar esos sería apagar la marca sin
 * ninguna ganancia medible.
 */
class ContrasteDelBotonTest extends TestCase
{
    use MideContraste;

    private const float MINIMO_AA = 4.5;

    private const string BLANCO = '#ffffff';

    /** La única excepción viva, y por qué. Si aparece otra, esta prueba avisa. */
    private const string ICONO_SIN_TEXTO = 'whatsapp-flotante.blade.php';

    public function test_el_rojo_de_accion_sostiene_texto_blanco(): void
    {
        foreach (['--asb-accion', '--asb-accion-fuerte'] as $token) {
            $color = $this->token($token);
            $razon = $this->contraste($color, self::BLANCO);

            $this->assertGreaterThanOrEqual(
                self::MINIMO_AA,
                $razon,
                sprintf('`%s` (%s) da %.2f:1 con texto blanco, y RNF-12 pide %.1f:1.', $token, $color, $razon, self::MINIMO_AA)
            );
        }
    }

    /**
     * El motivo por el que este token existe. Si alguien «simplifica» volviendo
     * a apuntar `--asb-accion` a Pub Red, el número vuelve a 3,86 y esta
     * prueba lo dice con el nombre del defecto puesto.
     */
    public function test_el_rojo_de_marca_puro_sigue_sin_servir_para_texto_blanco(): void
    {
        $razon = $this->contraste('#ee4137', self::BLANCO);

        $this->assertLessThan(
            self::MINIMO_AA,
            $razon,
            'Pub Red con blanco ya cumple AA. Si la paleta cambió, `--asb-accion` sobra y hay que revisar esta decisión entera.'
        );
    }

    /**
     * La guarda que de verdad impide la regresión: ninguna vista puede volver a
     * poner texto blanco sobre el rojo de marca.
     *
     * Se mira línea a línea y no el archivo entero, porque `bg-marca-500` y
     * `text-white` pueden convivir en una misma plantilla sin tocarse --un
     * punto rojo arriba y un botón blanco abajo-- y afirmar sobre el archivo
     * daría falsos positivos.
     */
    public function test_ninguna_vista_pone_texto_blanco_sobre_el_rojo_de_marca(): void
    {
        $culpables = [];

        foreach (File::allFiles(resource_path('views')) as $vista) {
            if ($vista->getFilename() === self::ICONO_SIN_TEXTO) {
                continue;
            }

            foreach (file($vista->getPathname()) as $numero => $linea) {
                if (str_contains($linea, 'bg-marca-500') && str_contains($linea, 'text-white')) {
                    $culpables[] = $vista->getRelativePathname().':'.($numero + 1);
                }
            }
        }

        $this->assertSame(
            [],
            $culpables,
            "Texto blanco sobre Pub Red (3,86:1, por debajo del 4,5:1 de RNF-12). Usa `bg-accion`:\n".implode("\n", $culpables)
        );
    }

    /**
     * El botón flotante de WhatsApp es la excepción, y se comprueba que siga
     * siendo lo que dice ser: un icono sin texto. El día que alguien le ponga
     * un rótulo, la excepción deja de valer y esto lo destapa.
     */
    public function test_la_unica_excepcion_sigue_siendo_un_icono_sin_rotulo(): void
    {
        $flotante = File::get(resource_path('views/components/publico/'.self::ICONO_SIN_TEXTO));

        $this->assertStringContainsString('bg-marca-500', $flotante, 'La excepción ya no usa el rojo de marca: sobra de esta prueba.');
        $this->assertStringContainsString('aria-label="Escríbenos por WhatsApp"', $flotante, 'El nombre accesible del icono cambió.');

        // Un objeto gráfico debe 3:1, no 4,5:1 (WCAG 2.1 §1.4.11), y lo cumple.
        $this->assertGreaterThanOrEqual(3.0, $this->contraste('#ee4137', self::BLANCO));

        // Y sigue sin texto: entre el <a> y su </a> no hay más que el <svg>.
        $this->assertDoesNotMatchRegularExpression(
            '/asb-whatsapp-flotante.*?>\s*[^<\s][^<]*</su',
            $flotante,
            'El botón flotante ya lleva texto visible sobre el rojo de marca: deja de ser un icono y le toca el 4,5:1.'
        );
    }

    private function token(string $nombre): string
    {
        $css = File::get(resource_path('css/tokens.css'));
        $encontrado = preg_match('/'.preg_quote($nombre, '/').':\s*(#[0-9a-fA-F]{6});/', $css, $partes);

        $this->assertSame(1, $encontrado, "No se pudo leer `{$nombre}` de tokens.css.");

        return $partes[1];
    }
}

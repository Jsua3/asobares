<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * `text-apagado` es el texto tenue de todo el sitio --pies de tarjeta, rótulos
 * de sección, la fecha de una noticia, el sello territorial de un beneficio--
 * y en tema oscuro no llegaba a AA.
 *
 * Medido en Chromium el 9 de septiembre de 2026 sobre la portada servida, con
 * los fondos que resuelven a un color sólido: **quince de los diecisiete
 * elementos medibles quedaban por debajo del 4,5:1** que exige RNF-12 para
 * texto normal. El token daba 4,32:1 contra `--asb-superficie`. En claro no
 * pasaba: el mínimo de los diecisiete era 6,94:1.
 *
 * Esta guarda vuelve a MEDIR leyendo `tokens.css`, en vez de fiarse de que
 * alguien recuerde por qué el valor es el que es. Si mañana se retoca la rampa
 * `noche` y el token vuelve a caer, la suite se pone roja aquí.
 *
 * Se mide contra las TRES superficies del tema y no solo contra el fondo:
 * `--asb-superficie-alta` es la más clara de las tres y por tanto el peor caso
 * para un texto gris, y es la que llevan las tarjetas del directorio y los
 * módulos de la portada. Medir solo contra `--asb-fondo` es lo que dejaba
 * pasar el valor anterior, que ahí daba 4,53:1 y parecía cumplir.
 */
class ContrasteDelTextoTenueTest extends TestCase
{
    use MideContraste;

    /** El mínimo de WCAG 2.1 (y de RNF-12) para texto normal. */
    private const float MINIMO_AA = 4.5;

    /**
     * Las tres superficies sobre las que este proyecto pinta texto tenue.
     * `--asb-superficie-alta` va la última a propósito: es la peor.
     *
     * @var list<string>
     */
    private const array SUPERFICIES = ['--asb-fondo', '--asb-superficie', '--asb-superficie-alta'];

    public function test_el_texto_tenue_del_tema_oscuro_cumple_aa_sobre_las_tres_superficies(): void
    {
        $oscuro = $this->bloque('.dark');
        $apagado = $this->token($oscuro, '--asb-apagado');

        foreach (self::SUPERFICIES as $superficie) {
            $fondo = $this->token($oscuro, $superficie);
            $razon = $this->contraste($apagado, $fondo);

            $this->assertGreaterThanOrEqual(
                self::MINIMO_AA,
                $razon,
                sprintf(
                    '`--asb-apagado` (%s) da %.2f:1 sobre `%s` (%s) en tema oscuro, y RNF-12 pide %.1f:1. '
                    .'Es el token del texto tenue de todo el sitio: si baja, bajan quince elementos de la portada a la vez.',
                    $apagado, $razon, $superficie, $fondo, self::MINIMO_AA
                )
            );
        }
    }

    /**
     * El tema claro no se tocó al corregir el oscuro, y esta guarda existe para
     * que no se toque por descuido: es fácil «arreglar» un token en `:root`
     * creyendo que se arregla el oscuro, porque `.dark` hereda de ahí.
     */
    public function test_el_texto_tenue_del_tema_claro_sigue_cumpliendo_aa(): void
    {
        $claro = $this->bloque(':root');
        $apagado = $this->token($claro, '--asb-apagado');

        foreach (self::SUPERFICIES as $superficie) {
            $razon = $this->contraste($apagado, $this->token($claro, $superficie));

            $this->assertGreaterThanOrEqual(
                self::MINIMO_AA,
                $razon,
                "`--asb-apagado` del tema claro cayó a {$razon}:1 sobre `{$superficie}`."
            );
        }
    }

    /**
     * El tenue y el apagado son dos escalones distintos de la jerarquía. La
     * salida barata del contraste era subir el apagado hasta el tenue, y eso
     * arregla el número y rompe el diseño: dos tokens con el mismo valor son
     * un token, y el sitio se queda sin el escalón más bajo.
     */
    public function test_el_apagado_sigue_siendo_un_escalon_distinto_del_tenue(): void
    {
        foreach ([':root', '.dark'] as $tema) {
            $bloque = $this->bloque($tema);

            $this->assertNotSame(
                $this->token($bloque, '--asb-tenue'),
                $this->token($bloque, '--asb-apagado'),
                "En `{$tema}` el apagado y el tenue son el mismo color: la jerarquía perdió un escalón."
            );
        }
    }

    /**
     * El trozo de `tokens.css` que declara un tema.
     *
     * `:root` se corta antes de `.dark {` para no arrastrar los valores del
     * oscuro; el oscuro empieza justo ahí y se corta en la primera consulta de
     * medio, que es donde termina de declarar la paleta.
     */
    private function bloque(string $tema): string
    {
        $css = File::get(resource_path('css/tokens.css'));

        [$claro, $oscuro] = explode('.dark {', $css, 2);

        return $tema === ':root' ? $claro : explode('@media', $oscuro, 2)[0];
    }

    private function token(string $bloque, string $nombre): string
    {
        $encontrado = preg_match('/'.preg_quote($nombre, '/').':\s*(#[0-9a-fA-F]{6});/', $bloque, $partes);

        $this->assertSame(1, $encontrado, "No se pudo leer `{$nombre}` de tokens.css: sin él esta prueba no mide nada.");

        return $partes[1];
    }
}

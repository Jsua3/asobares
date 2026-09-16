<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Superficies que un auditor en navegador encontró rectas el 16 sep, a
 * 1440 px y en los dos temas, fuera de las hojas que ya tienen guardia propia
 * (la del gremio, la de la guía y las de las bolsas y eventos).
 *
 * Cada una tiene fondo o borde propio y ningún ancestro que la recorte, así
 * que si su regla no redondea, en pantalla es una caja recta. La prueba lee la
 * regla base del selector en su hoja y exige un radio que no deje ninguna
 * esquina a cero.
 */
class SuperficiesSinCantosTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function superficies(): array
    {
        return [
            'formulario de contacto' => ['gremio-editorial.css', '.gremio-editorial-formulario'],
            'franja de cifras del directorio' => ['directorio-editorial.css', '.directorio-editorial-cifras'],
            'fecha de la tarjeta de evento en la portada' => ['home-editorial.css', '.home-editorial-evento__fecha'],
        ];
    }

    #[DataProvider('superficies')]
    public function test_la_superficie_no_tiene_esquinas_rectas(string $hoja, string $selector): void
    {
        $regla = $this->reglaBase(File::get(resource_path("css/{$hoja}")), $selector);

        $this->assertSame(1, preg_match('/(?:^|;)\s*border-radius:\s*([^;]+);/', $regla, $radio), "{$selector} no declara radio: se pinta recta.");

        foreach (preg_split('/\s+/', trim($radio[1])) as $esquina) {
            $this->assertDoesNotMatchRegularExpression('/^0(?:\.0+)?(?:px|rem|em|%)?$/', $esquina, "{$selector} deja una esquina recta: border-radius: {$radio[1]}.");
        }
    }

    /**
     * La franja de cifras no puede llegar a los bordes de la ventana: con
     * `max-width: 80rem` a secas, por debajo de ese ancho era una banda de
     * lado a lado y el radio se perdía contra el borde de la pantalla.
     */
    public function test_la_franja_de_cifras_no_toca_los_bordes_de_la_ventana(): void
    {
        $regla = $this->reglaBase(File::get(resource_path('css/directorio-editorial.css')), '.directorio-editorial-cifras');

        $this->assertStringContainsString('width: min(calc(100% - 2rem), 80rem);', $regla);
    }

    /**
     * La fila de la ficha de evento crece con su cuerpo. Fija a 11.5rem, un
     * lugar de dos líneas empujaba «Ver evento» debajo del recorte de la
     * tarjeta, y la foto tiene que estirarse con la fila.
     */
    public function test_la_ficha_de_evento_no_recorta_su_llamada(): void
    {
        $css = File::get(resource_path('css/eventos-editorial.css'));

        $this->assertStringNotContainsString('grid-template-rows: 11.5rem;', $css);
        $this->assertMatchesRegularExpression('/\.eventos-editorial-ficha__enlace \{[^}]*grid-template-rows: minmax\(11\.5rem, auto\);/', $css);
        $this->assertMatchesRegularExpression('/\.eventos-editorial-ficha__foto \{\s*height: auto;\s*min-height: 11\.5rem;/', $css);
    }

    /**
     * En escritorio la barra mide 4.375rem: las páginas del gremio no pueden
     * abrir con el tope del módulo móvil (3.5rem), que dejaba la primera línea
     * pegada al borde de la barra.
     */
    public function test_las_paginas_del_gremio_respiran_bajo_la_barra_de_escritorio(): void
    {
        $css = File::get(resource_path('css/gremio-editorial.css'));

        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 64rem\) \{\s*\.gremio-editorial \{\s*--grem-tope: calc\(4\.375rem \+ env\(safe-area-inset-top, 0px\) \+ [1-9][0-9.]*rem\);/',
            $css
        );
    }

    /** La primera regla del selector exacto fuera de toda at-rule, sin comentarios. */
    private function reglaBase(string $css, string $selector): string
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $nivel = 0;
        $cabecera = '';

        for ($i = 0, $largo = strlen($css); $i < $largo; $i++) {
            $caracter = $css[$i];

            if ($caracter === '{') {
                if ($nivel === 0 && trim($cabecera) === $selector) {
                    return substr($css, $i + 1, strpos($css, '}', $i) - $i - 1);
                }

                $nivel++;
                $cabecera = '';
            } elseif ($caracter === '}') {
                $nivel--;
                $cabecera = '';
            } elseif ($caracter === ';') {
                $cabecera = '';
            } else {
                $cabecera .= $caracter;
            }
        }

        $this->fail("No existe la regla base {$selector}.");
    }
}

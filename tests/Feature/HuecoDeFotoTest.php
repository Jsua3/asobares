<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\Support\MideContraste;
use Tests\TestCase;

/**
 * El sitio de una foto que todavía no existe.
 *
 * ASOBARES no ha entregado material fotográfico autorizado, y el §9 de
 * `encargo.md` no deja improvisarlo: en producción solo entra contenido de
 * documento oficial del gremio. Así que los huecos se abren con la marca
 * puesta y las fotos entran cuando lleguen — guardando una ruta en un ajuste,
 * sin volver a tocar una plantilla.
 *
 * Lo que se vigila aquí es que el hueco siga siendo un hueco: que no invente
 * contenido, que no le hable a un lector de pantalla y que el día que reciba
 * una foto deje de pintar el marcador.
 */
class HuecoDeFotoTest extends TestCase
{
    use MideContraste;

    /** Sin foto pinta el marcador de marca, y como decoración. */
    public function test_sin_foto_pinta_el_marcador_y_no_una_imagen_rota(): void
    {
        $html = Blade::render('<x-publico.hueco-foto />');

        $this->assertStringContainsString('hueco-foto', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('monograma-asobares.png', $html);

        // Ni un `src` vacío ni un `<img>` sin fuente: eso es lo que pinta el
        // icono de imagen rota del navegador.
        $this->assertStringNotContainsString('src=""', $html);
    }

    /** Con foto desaparece el marcador entero. */
    public function test_con_foto_el_marcador_se_va(): void
    {
        $html = Blade::render('<x-publico.hueco-foto foto="/storage/portadas/bar.jpg" alt="Barra del establecimiento" />');

        $this->assertStringContainsString('src="/storage/portadas/bar.jpg"', $html);
        $this->assertStringContainsString('alt="Barra del establecimiento"', $html);
        $this->assertStringNotContainsString('hueco-foto', $html, 'El marcador sigue puesto por debajo de la foto.');
        $this->assertStringNotContainsString('monograma', $html);
    }

    /**
     * Por defecto la foto es decorativa: `alt` vacío. Un hueco de cabecera no
     * aporta información —la aporta el titular que va encima— y describirlo
     * sería ruido para quien navega con lector de pantalla.
     */
    public function test_la_foto_es_decorativa_mientras_nadie_diga_lo_contrario(): void
    {
        $html = Blade::render('<x-publico.hueco-foto foto="/x.jpg" />');

        $this->assertStringContainsString('alt=""', $html);
    }

    /**
     * La cabecera de una sección es lo primero que se pinta: si el hueco se
     * carga en diferido, el título salta cuando llega la imagen.
     */
    public function test_la_foto_puede_pedir_prioridad_de_carga(): void
    {
        $normal = Blade::render('<x-publico.hueco-foto foto="/x.jpg" />');
        $urgente = Blade::render('<x-publico.hueco-foto foto="/x.jpg" prioridad />');

        $this->assertStringContainsString('loading="lazy"', $normal);
        $this->assertStringContainsString('fetchpriority="high"', $urgente);
        $this->assertStringNotContainsString('loading="lazy"', $urgente);
    }

    /**
     * Las cinco secciones que ya tienen el hueco abierto, y por la ruta que lo
     * hace utilizable: un ajuste. Sin él, meter una foto obligaría a editar la
     * plantilla, que es justo lo que este trabajo viene a evitar.
     */
    public function test_las_secciones_tienen_el_hueco_abierto_y_conectado_a_un_ajuste(): void
    {
        $secciones = [
            'guia/index' => 'guia_foto',
            'empleo/index' => 'empleo_foto',
            'artistas/index' => 'artistas_foto',
            'proveedores/index' => 'proveedores_foto',
        ];

        foreach ($secciones as $vista => $ajuste) {
            $html = File::get(resource_path("views/publico/{$vista}.blade.php"));

            $this->assertStringContainsString('<x-publico.hueco-foto', $html, "«{$vista}» no tiene hueco de foto.");
            $this->assertStringContainsString(
                "ajuste('{$ajuste}', null)",
                $html,
                "«{$vista}» no lee su foto de un ajuste: habría que editar la plantilla para poner una."
            );
        }
    }

    /**
     * El marcador va en la ranura `medio`, que es la que lleva el velo.
     *
     * No es un detalle de colocación: `.hero-medio` y su `::after` son la
     * misma clase a propósito --el velo es un mínimo calculado, no un gusto--
     * y colgar una imagen del hero por fuera de esa ranura la dejaría sin él,
     * con el titular encima de una foto sin garantía de contraste. Es
     * exactamente lo que `VeloDelHeroTest` existe para impedir.
     */
    public function test_el_hueco_del_hero_va_dentro_de_la_ranura_que_lleva_velo(): void
    {
        foreach (['guia/index', 'empleo/index', 'artistas/index', 'proveedores/index'] as $vista) {
            $html = File::get(resource_path("views/publico/{$vista}.blade.php"));

            $this->assertMatchesRegularExpression(
                '/<x-slot:medio>\s*<x-publico\.hueco-foto/',
                $html,
                "En «{$vista}» el hueco está fuera de la ranura `medio` y se quedaría sin velo."
            );
        }
    }

    /**
     * El gris de las diagonales es el MISMO que compone `GeneradorImagen` para
     * las portadas del demo, y por el mismo motivo: da 3,7:1 sobre las
     * superficies de los dos temas, así que una sola receta vale para ambos.
     *
     * Si alguien lo retoca «para que se note menos», el hueco desaparece en un
     * tema o deslumbra en el otro. Aquí se vuelve a medir.
     */
    public function test_el_gris_del_marcador_se_ve_en_los_dos_temas(): void
    {
        $generador = File::get(base_path('database/seeders/Support/GeneradorImagen.php'));

        $this->assertStringContainsString(
            '0x747474',
            $generador,
            'El generador cambió de gris: el CSS del hueco y las portadas del demo ya no hablan el mismo idioma.'
        );

        $css = File::get(resource_path('css/app.css'));
        $this->assertStringContainsString('rgb(116 116 116 / 0.32)', $css, '#747474 es rgb(116 116 116): el hueco dejó de usar el gris del generador.');

        // Y que ese gris siga despegando de las dos superficies de tarjeta.
        foreach (['#ffffff' => 'claro', '#121011' => 'oscuro'] as $superficie => $tema) {
            $razon = $this->contraste('#747474', $superficie);

            $this->assertGreaterThanOrEqual(
                3.0,
                $razon,
                sprintf('El gris del marcador da %.2f:1 sobre la superficie del tema %s.', $razon, $tema)
            );
        }
    }
}

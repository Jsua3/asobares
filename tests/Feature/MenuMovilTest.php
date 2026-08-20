<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El panel móvil vivía EN FLUJO dentro de un `<header sticky>` y se abría con
 * `x-collapse`, o sea interpolando `height` durante 250 ms: cada fotograma
 * reflowaba el documento y empujaba `<main>`.
 *
 * Sacarlo del flujo lo convierte en una capa, y una capa necesita tres
 * salidas que el acordeón no necesitaba. Sin ellas este cambio EMPEORA la
 * accesibilidad en vez de mejorarla, así que se vigilan aquí.
 */
class MenuMovilTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_movil_se_anima_sin_reflowear_el_documento(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        // Fuera la interpolación de altura.
        $this->assertStringNotContainsString('x-collapse', $navbar);

        // Superposición sobre el header, que ya es sticky y establece bloque
        // contenedor. Opaco: el header es translúcido y se vería a través.
        $this->assertStringContainsString('absolute inset-x-0 top-full', $navbar);
        $this->assertStringContainsString('bg-fondo', $navbar);
        $this->assertStringContainsString('overflow-y-auto', $navbar);

        // Solo opacity y transform, con la curva de cajón y salida más rápida.
        $this->assertStringContainsString('duration-(--duracion-panel)', $navbar);
        $this->assertStringContainsString('duration-(--duracion-salida)', $navbar);
        $this->assertStringContainsString('ease-cajon', $navbar);
    }

    public function test_la_capa_tiene_las_tres_salidas_que_un_acordeon_no_necesitaba(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString('x-on:keydown.escape.window="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:click.outside="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:resize.window', $navbar);
    }

    /**
     * La barra de escritorio se reagrupó en dos desplegables, y aquí NO se
     * copia esa forma: el panel móvil abre los dos grupos en plano, bajo un
     * encabezado.
     *
     * El desplegable de escritorio compra ancho, que es lo que falta ahí. Aquí
     * sobra vertical —el panel entero mide 772 px medidos en un 390x844, dentro
     * de sus 780 disponibles, o sea que ni siquiera hace falta desplazarlo— y
     * lo escaso es el número de toques. Anidar cobraría un toque más por cada
     * uno de los seis destinos plegados, y metería una animación dentro de un
     * panel que ya se está animando.
     *
     * Que no haya NINGÚN `aria-expanded` dentro del panel es la forma de
     * decirlo que no se puede cumplir a medias: si mañana alguien mete un
     * acordeón, esto lo dice.
     */
    public function test_el_panel_movil_agrupa_en_plano_y_no_anida_desplegables(): void
    {
        $respuesta = $this->get('/contacto');
        $respuesta->assertOk();

        // Hasta el cierre del <header>, que es lo último que hay: recortar por
        // el `</div>` que toque dependería del sangrado del Blade.
        preg_match('/<div id="menu-movil".*?<\/header>/s', $respuesta->getContent(), $trozos);

        $this->assertNotEmpty($trozos, 'No se encontró el panel móvil en el documento.');

        $panel = $trozos[0];

        $this->assertStringNotContainsString(
            'aria-expanded',
            $panel,
            'El panel móvil anidó un desplegable: aquí sobra vertical y lo escaso son los toques.'
        );

        // Los dos encabezados de grupo, y el de «Apariencia» que ya existía:
        // los tres del mismo rango, para que el panel tenga un solo esquema.
        foreach (['Bolsas', 'El gremio', 'Apariencia'] as $titulo) {
            $this->assertMatchesRegularExpression(
                '/<h2 class="antetitulo[^"]*">\s*'.preg_quote($titulo, '/').'\s*<\/h2>/u',
                $panel,
                "«{$titulo}» dejó de ser un encabezado: un lector de pantalla recorre esta lista saltando por encabezados."
            );
        }

        // Y los seis destinos plegados en escritorio siguen a un solo toque.
        foreach (['empleo.index', 'artistas.index', 'proveedores.index', 'quienes-somos', 'boletin.index', 'contacto'] as $ruta) {
            $this->assertStringContainsString(
                'href="'.route($ruta).'"',
                $panel,
                "El panel móvil dejó de enlazar a `{$ruta}`."
            );
        }
    }
}

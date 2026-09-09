<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mientras el gremio no tenga dominio propio, el sitio no se deja indexar.
 *
 * D-08 llevaba semanas anotada como «decidir `noindex` antes del lanzamiento»,
 * y leerla así escondía lo importante: el daño **se estaba haciendo ya**. El
 * sitio servía `Allow: /`, publicaba su sitemap y --lo peor-- clavaba una
 * etiqueta canónica apuntando a `asobares-production-0jhdcz.laravel.cloud`. O
 * sea, le decía a Google todos los días que la versión autorizada de cada
 * página del gremio vive en un host desechable que nadie va a conservar.
 *
 * La decisión se gobierna con una variable y no con un cambio de código, para
 * que abrirlo el día del dominio sea poner `SITIO_INDEXABLE=true` y redesplegar.
 * Por defecto está cerrado: si alguien despliega en otro sitio sin acordarse,
 * el error seguro es no indexar.
 */
class IndexacionDelSitioTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_dominio_propio_robots_lo_prohibe_todo(): void
    {
        config(['sitio.indexable' => false]);

        $respuesta = $this->get('/robots.txt')->assertOk();

        $respuesta->assertSee('Disallow: /'."\n", escape: false);
        $respuesta->assertDontSee('Allow: /');
    }

    public function test_sin_dominio_propio_las_paginas_llevan_noindex(): void
    {
        config(['sitio.indexable' => false]);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);
    }

    /**
     * El sitemap se sigue generando aunque nadie lo pueda usar todavía: es la
     * pieza que hay que poder entregarle a Google el mismo día que se abre, y
     * romperlo ahora se descubriría entonces.
     */
    public function test_el_sitemap_se_sigue_generando_con_el_sitio_cerrado(): void
    {
        config(['sitio.indexable' => false]);

        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', escape: false);
    }

    public function test_con_el_dominio_puesto_el_sitio_se_abre(): void
    {
        config(['sitio.indexable' => true]);

        $this->get('/robots.txt')->assertOk()->assertSee('Allow: /');

        $this->get(route('inicio'))->assertOk()->assertDontSee('noindex, nofollow');
    }

    /**
     * Abrir el sitio no abre las zonas privadas: el panel, el portal del afiliado
     * y la pasarela siguen fuera pase lo que pase.
     */
    public function test_las_zonas_privadas_siguen_prohibidas_con_el_sitio_abierto(): void
    {
        config(['sitio.indexable' => true]);

        $this->get('/robots.txt')
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /mi-cuenta');
    }

    /**
     * Cerrado por defecto. Un despliegue al que se le olvide la variable tiene
     * que quedarse fuera de Google, no dentro.
     */
    public function test_por_defecto_el_sitio_no_es_indexable(): void
    {
        $this->assertFalse(
            (bool) config('sitio.indexable'),
            'El valor por defecto de sitio.indexable tiene que ser falso.'
        );
    }
}

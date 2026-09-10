<?php

namespace Tests\Feature;

use App\Models\Evento;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Cómo se ve el sitio cuando alguien pega el enlace en WhatsApp.
 *
 * Hasta el 10 de septiembre de 2026 `ogImagen` nacía en `null` y solo cuatro
 * vistas la pasaban --artista, noticia, ficha de asociado y evento--. Todas las
 * demás, **incluida la portada**, se compartían sin miniatura: un enlace pelado.
 * Para un gremio cuyo canal es WhatsApp eso no es un detalle de SEO, es el
 * primer contacto de mucha gente con el sitio.
 *
 * No lo veía nadie porque el `<meta>` existía y estaba bien escrito: el defecto
 * era que su `@if` casi nunca se cumplía. Una prueba que solo mirase la
 * plantilla lo habría dado por bueno.
 */
class ImagenAlCompartirTest extends TestCase
{
    use RefreshDatabase;

    private const string TARJETA = 'img/og-asobares.jpg';

    /** Una tarjeta plana de 1200x630 no tiene por qué pesar más que esto. */
    private const int TOPE_BYTES = 200 * 1024;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * La misma lección que dejó el video del hero: lo que no está en el índice
     * de git no llega a producción, por mucho que esté en el disco de quien
     * programa. Cloud despliega desde git.
     */
    public function test_la_tarjeta_viaja_en_el_repositorio_y_mide_lo_que_dice_medir(): void
    {
        $ruta = public_path(self::TARJETA);

        $this->assertTrue(
            Process::path(base_path())->run(['git', 'ls-files', '--error-unmatch', 'public/'.self::TARJETA])->successful(),
            'La tarjeta de Open Graph no está en el índice de git: en producción el enlace se comparte sin imagen.'
        );

        $this->assertFileExists($ruta);

        $this->assertLessThanOrEqual(
            self::TOPE_BYTES,
            File::size($ruta),
            'La tarjeta engordó. Es un logotipo sobre un fondo liso: si pesa esto es que alguien metió otra cosa.'
        );

        [$ancho, $alto] = getimagesize($ruta);

        $this->assertSame(
            [1200, 630],
            [$ancho, $alto],
            'La plantilla jura 1200x630 en `og:image:width` y `og:image:height`. Si el archivo mide otra cosa, el desplegador recorta contra un tamaño que no existe.'
        );
    }

    /**
     * La que de verdad protege: recorre páginas que NO pasan imagen propia y
     * comprueba que todas se comparten con la tarjeta. Con el `@if` de antes,
     * las cinco fallaban.
     */
    public function test_las_paginas_sin_imagen_propia_se_comparten_con_la_tarjeta(): void
    {
        foreach (['/', '/quienes-somos', '/abre-tu-negocio', '/directorio', '/contacto'] as $ruta) {
            $html = $this->get($ruta)->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '/<meta property="og:image" content="https?:\/\/[^"]*'.preg_quote(self::TARJETA, '/').'"/',
                $html,
                "«{$ruta}» se comparte sin imagen, o con una ruta relativa que Open Graph no acepta."
            );

            $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
            $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        }
    }

    /**
     * Y la otra mitad: una página con imagen propia se comparte con la suya, no
     * con la de respaldo, y **no declara medidas**, porque las de la tarjeta no
     * son las suyas.
     */
    public function test_una_pagina_con_imagen_propia_ni_usa_la_tarjeta_ni_hereda_sus_medidas(): void
    {
        $evento = Evento::factory()->publicado()->create(['imagen' => 'eventos/foto-de-prueba.jpg']);

        $html = $this->get(route('eventos.show', $evento))->assertOk()->getContent();

        $this->assertStringContainsString('foto-de-prueba.jpg', $html);
        $this->assertStringNotContainsString(self::TARJETA, $html);
        $this->assertStringNotContainsString('og:image:width', $html);
    }
}

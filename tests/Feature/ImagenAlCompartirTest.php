<?php

namespace Tests\Feature;

use App\Models\Evento;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Throwable;

/**
 * Cómo se ve el sitio cuando alguien pega el enlace en WhatsApp.
 *
 * `ogImagen` nace en `null` y solo algunas vistas la pasan --artista, noticia,
 * ficha de asociado y evento--, así que el layout pone la tarjeta del gremio a
 * todas las demás. Sin ella, **incluida la portada**, se compartirían sin
 * miniatura: un enlace pelado. Para un gremio cuyo canal es WhatsApp eso no es
 * un detalle de SEO, es el primer contacto de mucha gente con el sitio.
 *
 * El defecto no se ve leyendo la plantilla: el `<meta>` puede existir y estar
 * bien escrito con un `@if` que casi nunca se cumple. Una prueba que solo mire
 * la plantilla lo daría por bueno.
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
     * Lo que no está en el índice de git no llega a producción, por mucho que
     * esté en el disco de quien programa: Cloud despliega desde git.
     */
    public function test_la_tarjeta_viaja_en_el_repositorio_y_mide_lo_que_dice_medir(): void
    {
        $ruta = public_path(self::TARJETA);

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

        // El índice se mira al final: sin `git` o sin repositorio la prueba se
        // omite, pero el peso y las medidas ya se comprobaron.
        $this->omitirSinRepositorio();

        $this->assertTrue(
            Process::path(base_path())->run(['git', 'ls-files', '--error-unmatch', 'public/'.self::TARJETA])->successful(),
            'La tarjeta de Open Graph no está en el índice de git: en producción el enlace se comparte sin imagen.'
        );
    }

    /**
     * Sin `git` o fuera de un repositorio, `ls-files` falla y el mensaje diría
     * que la tarjeta no viaja, que es falso: la prueba se omite.
     */
    private function omitirSinRepositorio(): void
    {
        try {
            $repositorio = Process::path(base_path())->run(['git', 'rev-parse', '--git-dir']);
        } catch (Throwable) {
            $repositorio = null;
        }

        if ($repositorio === null || ! $repositorio->successful()) {
            $this->markTestSkipped('Sin `git` o sin repositorio en esta máquina: no se puede comprobar el índice.');
        }
    }

    /**
     * La que de verdad protege: recorre páginas que NO pasan imagen propia y
     * comprueba que todas se comparten con la tarjeta. Con un `@if` que solo
     * pinte la etiqueta cuando la vista pasa imagen, las cinco fallarían.
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

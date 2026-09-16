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
            $xpath = $this->xpathDe($this->get($ruta)->assertOk()->getContent());

            $this->assertMatchesRegularExpression(
                '/^https?:\/\/.*'.preg_quote(self::TARJETA, '/').'$/',
                $this->contenidoDeMeta($xpath, '//meta[@property="og:image"]', $ruta),
                "«{$ruta}» se comparte sin imagen, o con una ruta relativa que Open Graph no acepta."
            );

            $this->assertSame(
                '1200',
                $this->contenidoDeMeta($xpath, '//meta[@property="og:image:width"]', $ruta),
                "«{$ruta}» no declara el ancho de la tarjeta: el desplegador recorta contra una medida que no existe."
            );

            $this->assertSame(
                'summary_large_image',
                $this->contenidoDeMeta($xpath, '//meta[@name="twitter:card"]', $ruta),
                "«{$ruta}» se comparte en Twitter sin tarjeta grande."
            );
        }
    }

    /**
     * El `content` de la única etiqueta `<meta>` que casa con la consulta.
     *
     * Se pregunta al árbol y no al texto del `<head>`: lo que se vigila es que
     * la página DECLARE la etiqueta con el valor que toca, y eso no depende de
     * con qué atributo empiece la etiqueta, de en qué orden los escriba Blade
     * ni de si el formateador la parte en varias líneas. Exigir exactamente una
     * coincidencia sube el listón: dos `og:image` son tan defecto como ninguna,
     * porque el desplegador se queda con la que quiere.
     */
    private function contenidoDeMeta(\DOMXPath $xpath, string $consulta, string $ruta): string
    {
        $etiquetas = $xpath->query($consulta);

        $this->assertSame(1, $etiquetas->length, "«{$ruta}» no declara exactamente una etiqueta «{$consulta}».");

        return $etiquetas->item(0)->getAttribute('content');
    }

    /** El documento servido, listo para consultar por XPath. */
    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
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

<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;
use Throwable;

/**
 * El video institucional del hero.
 *
 * Vigila un defecto que ninguna otra prueba puede ver: el video se sirve con
 * `file_exists(public_path(...))`, y si `.gitignore` tapa `/public/videos/`,
 * en local el archivo está y el hero se ve, pero Cloud despliega desde git,
 * el archivo no viaja, la condición es falsa siempre y el hero sale mudo
 * **sin error, sin log y sin que la suite se entere**. Un fallo que solo
 * existe del lado del despliegue.
 *
 * De ahí que la primera prueba mire el índice de git y no el disco: que el
 * archivo esté en la máquina de quien programa no demuestra nada.
 *
 * El original del gremio son 58 MB y 48 s con audio; lo que se versiona es el
 * bucle de 10 s, mudo y comprimido, más su póster. El límite de peso es la
 * guardia contra que alguien suelte el original aquí.
 */
class VideoDelHeroTest extends TestCase
{
    use RefreshDatabase;

    private const string VIDEO = 'videos/asobares-institucional.mp4';

    private const string POSTER = 'videos/asobares-institucional.jpg';

    /** El bucle no debe pasar de esto; el original pesa treinta y siete veces más. */
    private const int TOPE_VIDEO_BYTES = 5 * 1024 * 1024;

    private const int TOPE_POSTER_BYTES = 300 * 1024;

    /**
     * Lo que git tiene registrado es lo único que llega a producción.
     *
     * `git ls-files --error-unmatch` devuelve código distinto de cero si la
     * ruta no está en el índice, que es exactamente la avería: el archivo
     * existe en el disco de quien programa y no en el despliegue.
     */
    public function test_el_video_y_su_poster_viajan_en_el_repositorio(): void
    {
        // Sin `git` o fuera de un repositorio, `ls-files` falla y el mensaje
        // diría que el video no viaja, que es falso: la prueba se omite. Las
        // de peso siguen corriendo.
        try {
            $repositorio = Process::path(base_path())->run(['git', 'rev-parse', '--git-dir']);
        } catch (Throwable) {
            $repositorio = null;
        }

        if ($repositorio === null || ! $repositorio->successful()) {
            $this->markTestSkipped('Sin `git` o sin repositorio en esta máquina: no se puede comprobar el índice.');
        }

        foreach ([self::VIDEO, self::POSTER] as $relativa) {
            $resultado = Process::path(base_path())
                ->run(['git', 'ls-files', '--error-unmatch', 'public/'.$relativa]);

            $this->assertTrue(
                $resultado->successful(),
                "«public/{$relativa}» no está registrado en git. Cloud despliega desde git: "
                .'si no está en el índice, en producción no existe y el hero se queda sin video '
                .'sin dar ningún error. Comprueba que `.gitignore` no vuelva a tapar `public/videos/`.'
            );
        }
    }

    public function test_el_video_pesa_lo_que_puede_pesar_un_fondo_de_portada(): void
    {
        $this->assertLessThanOrEqual(
            self::TOPE_VIDEO_BYTES,
            File::size(public_path(self::VIDEO)),
            'El video del hero se pasa de peso. Lo que va al repositorio es el bucle recortado '
            .'y comprimido, no el original del gremio: recórtalo con ffmpeg antes de subirlo.'
        );

        $this->assertLessThanOrEqual(
            self::TOPE_POSTER_BYTES,
            File::size(public_path(self::POSTER)),
            'El póster del hero se pasa de peso: es lo primero que se pinta, antes que el video.'
        );
    }

    /**
     * El póster tiene que ser el primer fotograma del propio video y no una
     * foto de asociado.
     *
     * En producción no hay ninguna ficha publicada --nacen en borrador y no
     * hay autorizaciones--, así que la colección de fotos destacadas viene
     * vacía y el hero se quedaría sin nada que enseñar mientras el video carga.
     */
    public function test_la_portada_sirve_el_video_con_su_propio_poster(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee(self::VIDEO, escape: false)
            ->assertSee(self::POSTER, escape: false);
    }

    /**
     * Quien pidió menos movimiento no ve el video ni paga su descarga.
     *
     * El propio componente `hero` lo deja escrito: «no lo pongas en `autoplay`
     * sin mirar `prefers-reduced-motion`», porque el bloque global de
     * movimiento reducido de `app.css` frena animaciones CSS pero no la
     * reproducción de un `<video>`. La marca sale del servidor sin `autoplay`
     * y con `preload="none"`; arrancarlo es cosa de `videoHero`, que consulta
     * la preferencia antes de tocar nada.
     */
    public function test_el_video_no_arranca_solo_ni_se_descarga_sin_permiso(): void
    {
        $this->seed(DatabaseSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('preload="none"', $html);
        $this->assertStringContainsString('x-ref="video"', $html);
        $this->assertStringContainsString('aria-label="Reproducir video institucional"', $html);
        $this->assertStringContainsString('aria-pressed="false"', $html);
        $this->assertStringContainsString('x-on:click="alternar()"', $html);
        // Se mide sobre el árbol y no sobre el texto del marcado. Un barrido
        // de texto grita sin que falte nada --`data-autoplay="false"` o una
        // clase que contenga la palabra caen dentro de la ventana-- y calla
        // cuando no hay nada que mirar: una aserción negativa pasa sola si el
        // <video> deja de servirse, que es la avería peor porque el hero se
        // queda mudo sin que la guardia se entere. De ahí que primero se exija
        // el elemento colgando de su contenedor y solo después se miren sus
        // atributos, incluido el enlace de Alpine que los escribe.
        $video = $this->xpathDe($html)->query('//div[contains(@class, "hero-video-fondo")]//video');

        $this->assertSame(
            1,
            $video->length,
            'El hero no sirvió su <video> institucional: sin elemento, la guardia del `autoplay` no vigila nada.'
        );

        $arranqueAutomatico = [];

        foreach ($video->item(0)->attributes as $atributo) {
            $nombre = strtolower($atributo->nodeName);

            if ($nombre === 'autoplay' || str_ends_with($nombre, ':autoplay')) {
                $arranqueAutomatico[] = $nombre;
            }
        }

        $this->assertSame(
            [],
            $arranqueAutomatico,
            'El <video> del hero salió con `autoplay`: se reproduce aunque hayan pedido menos movimiento.'
        );

        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.data('videoHero'", $js);
        $this->assertStringContainsString('reproduciendo: false', $js);
        $this->assertStringContainsString('video() {', $js);
        $this->assertStringContainsString('return this.$refs.video', $js);
        $this->assertStringContainsString('alternar() {', $js);
        $this->assertMatchesRegularExpression(
            '/Alpine\.data\(\'videoHero\'.*?reduceMovimiento\(\)/s',
            $js,
            '`videoHero` tiene que consultar `reduceMovimiento()` antes de reproducir.'
        );
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }
}

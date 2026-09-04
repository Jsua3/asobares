<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * El video institucional del hero (OBS3-02, D-22).
 *
 * Esta prueba existe por un defecto que ninguna otra podía ver: el video se
 * servía con `file_exists(public_path(...))` mientras `.gitignore` tenía
 * `/public/videos/`. En local el archivo estaba y el hero se veía; en
 * producción Cloud despliega desde git, el archivo no viajaba, la condición
 * era falsa siempre y el hero salía mudo **sin error, sin log y sin que la
 * suite se enterara**. Un fallo que solo existe del lado del despliegue.
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
     * vacía y el hero se quedaba sin nada que enseñar mientras el video carga.
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
        $this->assertDoesNotMatchRegularExpression(
            '/<video\b[^>]*\bautoplay\b/i',
            $html,
            'El <video> del hero salió con `autoplay`: se reproduce aunque hayan pedido menos movimiento.'
        );

        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.data('videoHero'", $js);
        $this->assertMatchesRegularExpression(
            '/Alpine\.data\(\'videoHero\'.*?reduceMovimiento\(\)/s',
            $js,
            '`videoHero` tiene que consultar `reduceMovimiento()` antes de reproducir.'
        );
    }
}

<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Evento;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HOME-05/06: el banco visual de la portada tiene que existir, resolverse
 * en el orden foto real → asset editorial → fallback gráfico, y no
 * pintarse con las portadas de relleno del demo.
 *
 * Roturas: borrar un asset de `public/img/home/` o `public/videos/`; volver a pintar
 * `foto_portada` del generador por encima del banco; cablear el Hero
 * como respaldo de publicidad.
 */
class BancoVisualDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const array ASSETS = [
        'img/home/establecimiento-01.png',
        'img/home/establecimiento-02.png',
        'img/home/establecimiento-03.png',
        'img/home/beneficios-gremio.png',
        'videos/asobares-institucional.jpg',
        'img/home/cta-afiliacion.png',
        'img/home/publicidad-fallback.png',
    ];

    public function test_los_assets_del_banco_existen_y_estan_mapeados(): void
    {
        foreach (self::ASSETS as $ruta) {
            $this->assertFileExists(
                public_path($ruta),
                "Falta el asset editorial `{$ruta}`."
            );
        }

        $this->assertSame(
            [
                'img/home/establecimiento-01.png',
                'img/home/establecimiento-02.png',
                'img/home/establecimiento-03.png',
            ],
            config('home_banco.establecimientos')
        );
        $this->assertSame('img/home/beneficios-gremio.png', config('home_banco.beneficios'));
        $this->assertSame('videos/asobares-institucional.jpg', config('home_banco.evento'));
        $this->assertSame('img/home/cta-afiliacion.png', config('home_banco.cta'));
        $this->assertSame('img/home/publicidad-fallback.png', config('home_banco.publicidad'));
    }

    public function test_el_relleno_del_generador_no_cuenta_como_foto_real(): void
    {
        $relleno = 'asociados/'.md5('asociado-demo-home').'.png';

        $this->assertTrue(esImagenDeRelleno(null));
        $this->assertTrue(esImagenDeRelleno(''));
        $this->assertTrue(esImagenDeRelleno($relleno));
        $this->assertTrue(esImagenDeRelleno('asociados/no-existe.jpg'));

        Storage::disk('public')->put('asociados/portada-real-home.jpg', 'jpeg-de-verdad');
        $this->assertFalse(esImagenDeRelleno('asociados/portada-real-home.jpg'));
    }

    public function test_la_cadena_elige_foto_real_luego_editorial_luego_nada(): void
    {
        Storage::disk('public')->put('asociados/portada-real-home.jpg', 'jpeg-de-verdad');

        $this->assertStringContainsString(
            'portada-real-home.jpg',
            (string) urlDeFotoDeLaHome('asociados/portada-real-home.jpg', 'img/home/establecimiento-01.png')
        );

        $this->assertStringContainsString(
            'img/home/establecimiento-01.png',
            (string) urlDeFotoDeLaHome('asociados/'.md5('destacado-relleno-home').'.png', 'img/home/establecimiento-01.png')
        );

        $this->assertNull(urlDeFotoDeLaHome(null, 'img/home/no-existe.png'));
    }

    public function test_los_destacados_de_relleno_pintan_el_banco_editorial(): void
    {
        $this->seed(DatabaseSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('img/home/establecimiento-01.png', $html);
        $this->assertStringContainsString('img/home/establecimiento-02.png', $html);
        $this->assertStringContainsString('img/home/establecimiento-03.png', $html);
        $this->assertStringContainsString('img/home/beneficios-gremio.png', $html);
        $this->assertStringContainsString('img/home/cta-afiliacion.png', $html);
    }

    public function test_una_foto_portada_real_no_se_sustituye_por_el_banco(): void
    {
        $this->seed(DatabaseSeeder::class);

        Asociado::query()->update(['destacado' => false]);

        Storage::disk('public')->put(
            'asociados/portada-real-home.jpg',
            File::get(public_path('img/og-asobares.jpg'))
        );

        Asociado::factory()->publicado()->create([
            'nombre' => 'Áabar Foto Real',
            'slug' => 'aabar-foto-real',
            'destacado' => true,
            'foto_portada' => 'asociados/portada-real-home.jpg',
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'Beta Sin Foto',
            'slug' => 'beta-sin-foto',
            'destacado' => true,
            'foto_portada' => null,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'Cedro Sin Foto',
            'slug' => 'cedro-sin-foto',
            'destacado' => true,
            'foto_portada' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('asociados/portada-real-home.jpg', $html);
        $this->assertStringContainsString('img/home/establecimiento-', $html);
    }

    public function test_el_evento_de_relleno_usa_el_asset_editorial(): void
    {
        $this->seed(DatabaseSeeder::class);

        $evento = Evento::publicado()->proximo()->first();

        if ($evento === null || ! esImagenDeRelleno($evento->imagen)) {
            $this->markTestSkipped('No hay evento próximo con portada de relleno para comprobar el banco.');
        }

        $this->get('/')->assertOk()
            ->assertSee('videos/asobares-institucional.jpg', false);
    }

    public function test_la_publicidad_sin_archivo_no_reusa_el_hero(): void
    {
        $css = File::get(resource_path('views/components/publico/home/publicidad.blade.php'));
        $hero = File::get(resource_path('views/components/publico/home/hero.blade.php'));

        $this->assertStringContainsString("config('home_banco.publicidad'", $css);
        $this->assertStringNotContainsString('videos/asobares-institucional', $css);
        $this->assertStringContainsString('videos/asobares-institucional', $hero);
    }

    public function test_el_texto_de_abre_tu_negocio_no_es_la_intro_de_movimiento(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/actualidad.blade.php'));

        $this->assertMatchesRegularExpression(
            '/portada_guia_texto[^;]*sr-only|sr-only[^;]*portada_guia_texto/s',
            $vista,
            'El texto de la guía tiene que quedar fuera de la intro visible de «en movimiento».'
        );
        $this->assertStringContainsString('ajuste(\'portada_videos_intro\'', $vista);
    }

    public function test_los_tokens_de_home_existen_en_los_dos_temas(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));

        foreach (['--home-bg', '--home-bg-elevated', '--home-surface', '--home-border', '--home-text', '--home-muted', '--home-accent', '--home-overlay', '--home-shadow'] as $token) {
            $this->assertStringContainsString($token.':', $css, "Falta el token {$token}.");
        }

        $this->assertStringContainsString('.dark .home-editorial', $css);
        $this->assertStringContainsString('#f5f2ee', $css, 'El modo claro tiene que declarar un fondo marfil, no reutilizar el grafito.');
        $this->assertStringContainsString('#080808', $css, 'El modo oscuro tiene que conservar el grafito.');
    }
}

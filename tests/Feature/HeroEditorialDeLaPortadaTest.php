<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-01: el hero sigue siendo el video institucional, con el
 * titular y el concepto actuales. La animación es editorial y se apaga
 * si pidieron menos movimiento.
 *
 * Roturas: sustituir el video; cablear un segundo mecanismo de video;
 * quitar los CTA; animar el titular sin respetar reduced-motion.
 */
class HeroEditorialDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_el_hero_sigue_sirviendo_el_video_institucional_y_los_cta(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('videos/asobares-institucional.mp4', $html);
        $this->assertStringContainsString('videos/asobares-institucional.jpg', $html);
        $this->assertStringContainsString('La noche construye territorio', $html);
        $this->assertStringContainsString('Gremio, ciudad y noche en una sola voz.', $html);
        $this->assertStringContainsString('href="'.route('directorio.index').'"', $html);
        $this->assertStringContainsString('href="'.route('afiliate').'"', $html);

        $vista = File::get(resource_path('views/components/publico/home/hero.blade.php'));

        $this->assertStringContainsString("public_path('videos/asobares-institucional.mp4')", $vista);
        $this->assertStringNotContainsString('hero_video_src', $vista);
        $this->assertStringNotContainsString('Storage::disk', explode('$videoInstitucional', $vista)[1] ?? $vista);
    }

    public function test_el_titular_de_portada_declara_revelado_editorial(): void
    {
        $hero = File::get(resource_path('views/components/publico/hero.blade.php'));

        $this->assertStringContainsString('$portada', $hero);
        $this->assertStringContainsString('home-editorial-hero-titulo', $hero);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<h1\b[^>]*>.*home-editorial-hero-titulo.*La noche construye territorio/s',
            $html
        );
    }

    public function test_el_css_del_titular_se_apaga_con_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('@keyframes home-editorial-hero-revelar', $css);
        $this->assertStringContainsString('.home-editorial-hero-titulo', $css);
        $this->assertMatchesRegularExpression(
            '/@media \(prefers-reduced-motion: reduce\).*?\.home-editorial-hero-titulo\s*\{[^}]*animation:\s*none/s',
            $css
        );
    }
}

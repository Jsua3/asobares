<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El hero sigue siendo el video institucional, con el titular y el concepto
 * actuales. La animación es editorial y se apaga si pidieron menos movimiento.
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

        // La ruta también vive en el atributo poster del <video>, que es una
        // capa invisible hasta que carga y no arranca con movimiento reducido.
        // La foto fija tiene que ser la <img>, y tiene que colgar del mismo
        // contenedor que el video.
        //
        // Se mide sobre el árbol y no sobre el texto del marcado: ese
        // contenedor lleva el `x-data` del mecanismo de video, y una expresión
        // anclada a `<div class="hero-video-fondo">` se rompe en cuanto ahí
        // entra o sale un atributo, sin que la foto falte. Es el modo de fallo
        // que fabrica guardias inútiles, y este proyecto ya retiró dos.
        $xpath = $this->xpathDe($html);

        $this->assertSame(
            1,
            $xpath->query('//div[contains(@class, "hero-video-fondo")]/img[contains(@src, "videos/asobares-institucional.jpg")]')->length,
            'El hero perdió la <img> fija: con movimiento reducido o sin video se quedaría sin foto.'
        );
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

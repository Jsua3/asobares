<?php

namespace Tests\Unit;

use App\Support\VideoDeYoutube;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Del `video_url` de un artista solo se embebe el ID extraído: nunca la URL
 * cruda que alguien escribió en el panel.
 */
class VideoDeArtistaTest extends TestCase
{
    /** @return list<array{0: ?string, 1: ?string}> */
    public static function urls(): array
    {
        return [
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            ['https://youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            ['https://www.youtube.com/watch?list=PL123&v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s', 'dQw4w9WgXcQ'],
            ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            ['https://youtu.be/dQw4w9WgXcQ?t=42', 'dQw4w9WgXcQ'],
            ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],

            // Nada de esto debe llegar nunca a un iframe.
            ['https://vimeo.com/123456789', null],
            ['https://sitio-malicioso.test/watch?v=dQw4w9WgXcQ', null],
            ['https://youtube.com.sitio-falso.test/watch?v=dQw4w9WgXcQ', null],
            ['javascript:alert(1)', null],
            ['https://www.youtube.com/watch?v=corto', null],
            ['', null],
            [null, null],
        ];
    }

    #[DataProvider('urls')]
    public function test_extrae_el_id_solo_de_urls_legitimas_de_youtube(?string $url, ?string $esperado): void
    {
        $this->assertSame($esperado, VideoDeYoutube::id($url));
    }

    public function test_reconoce_cuando_hay_video_valido(): void
    {
        $this->assertFalse(VideoDeYoutube::esValida(null));
        $this->assertFalse(VideoDeYoutube::esValida('https://vimeo.com/123'));
        $this->assertTrue(VideoDeYoutube::esValida('https://youtu.be/dQw4w9WgXcQ'));
    }
}

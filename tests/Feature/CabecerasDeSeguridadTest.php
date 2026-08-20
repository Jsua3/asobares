<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El proyecto no emitía ninguna cabecera de seguridad: un `grep` sobre todo el
 * repositorio devolvía una sola línea, en la descarga de formatos de la guía.
 */
class CabecerasDeSeguridadTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string}> */
    public static function cabecerasEsperadas(): array
    {
        return [
            'nosniff' => ['X-Content-Type-Options', 'nosniff'],
            'sin enmarcado' => ['X-Frame-Options', 'DENY'],
            'referente acotado' => ['Referrer-Policy', 'strict-origin-when-cross-origin'],
            // Sin `preload`: entrar en esa lista es fácil y salir tarda meses.
            'https obligatorio un año' => ['Strict-Transport-Security', 'max-age=31536000; includeSubDomains'],
        ];
    }

    /**
     * `preload` mete el dominio en una lista que los navegadores traen
     * compilada: hasta que no haya dominio definitivo del gremio, no entra.
     */
    public function test_el_https_obligatorio_no_lleva_preload(): void
    {
        $cabecera = (string) $this->get('/')->headers->get('Strict-Transport-Security');

        $this->assertStringNotContainsString('preload', $cabecera);
    }

    #[DataProvider('cabecerasEsperadas')]
    public function test_el_sitio_publico_las_emite(string $cabecera, string $valor): void
    {
        $this->get('/')->assertHeader($cabecera, $valor);
    }

    public function test_el_login_del_panel_tambien(): void
    {
        $this->get('/admin/login')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * La cookie de sesión sin `Secure` viaja también por http, y ahí cualquiera
     * en la misma red la captura y la reutiliza. `forceScheme('https')` no la
     * cubre: sólo cambia las URLs que Laravel genera.
     */
    public function test_la_cookie_de_sesion_se_marca_segura_en_produccion(): void
    {
        config(['session.secure' => null, 'app.debug' => false, 'mail.default' => 'smtp']);
        $this->app->detectEnvironment(fn (): string => 'production');

        (new AppServiceProvider($this->app))->boot();

        $this->assertTrue(config('session.secure'));
    }
}

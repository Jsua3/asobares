<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El sitio vive en un solo host: el dominio del gremio.
 *
 * Laravel Cloud sigue sirviendo la copia en su host provisional, y cada página
 * de esa copia se declaraba canónica a sí misma. Estas pruebas fijan que ese
 * host manda al dominio con un 301 sin romper lo que no se puede redirigir: el
 * webhook de Bold, los formularios y la comprobación de salud.
 */
class DominioPropioTest extends TestCase
{
    use RefreshDatabase;

    private const string DOMINIO = 'https://asobaresquindio.com';

    private const string PROVISIONAL = 'https://asobares-production-0jhdcz.laravel.cloud';

    protected function setUp(): void
    {
        parent::setUp();

        config(['sitio.indexable' => true, 'app.url' => self::DOMINIO]);
    }

    public function test_el_host_provisional_manda_al_dominio_con_la_misma_ruta_y_consulta(): void
    {
        $this->get(self::PROVISIONAL.'/directorio?municipio=armenia')
            ->assertStatus(301)
            ->assertRedirect(self::DOMINIO.'/directorio?municipio=armenia');
    }

    public function test_la_portada_del_host_provisional_manda_a_la_del_dominio(): void
    {
        $this->get(self::PROVISIONAL.'/')
            ->assertStatus(301)
            ->assertRedirect(self::DOMINIO.'/');
    }

    public function test_el_dominio_se_sirve_sin_redirigir(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(self::DOMINIO.'/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.self::DOMINIO.'">', escape: false);
    }

    /**
     * El host de la petición ya llega en minúsculas; el de `APP_URL` es texto
     * tecleado en el hosting y puede traer mayúsculas.
     */
    public function test_el_host_se_compara_sin_distinguir_mayusculas(): void
    {
        config(['app.url' => 'https://AsobaresQuindio.com']);

        $this->get(self::DOMINIO.'/robots.txt')->assertOk();
    }

    public function test_head_tambien_se_redirige(): void
    {
        $this->call('HEAD', self::PROVISIONAL.'/eventos')
            ->assertStatus(301)
            ->assertRedirect(self::DOMINIO.'/eventos');
    }

    /**
     * Quien manda un POST no lo repite detrás de un 301: el webhook de Bold se
     * perdería. Se atiende donde llega, y aquí lo contesta el propio webhook.
     */
    public function test_un_post_al_host_provisional_no_se_redirige(): void
    {
        $respuesta = $this->postJson(self::PROVISIONAL.'/webhooks/bold', []);

        $this->assertNotSame(301, $respuesta->getStatusCode());
        $this->assertFalse($respuesta->isRedirection());
    }

    public function test_la_comprobacion_de_salud_no_se_redirige(): void
    {
        $this->get(self::PROVISIONAL.'/up')->assertOk();
    }

    public function test_el_301_lleva_las_cabeceras_de_seguridad(): void
    {
        $this->get(self::PROVISIONAL.'/contacto')
            ->assertStatus(301)
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Con el sitio cerrado a los buscadores el host provisional es el único que
     * hay: no se redirige nada.
     */
    public function test_con_el_sitio_cerrado_no_se_redirige(): void
    {
        config(['sitio.indexable' => false]);

        $this->get(self::PROVISIONAL.'/robots.txt')->assertOk();
    }

    /**
     * Si `APP_URL` todavía es el host de Laravel Cloud, redirigir mandaría el
     * dominio del gremio a la copia que se quiere retirar.
     */
    public function test_si_app_url_sigue_en_laravel_cloud_el_dominio_no_se_redirige(): void
    {
        config(['app.url' => self::PROVISIONAL]);

        $this->get(self::DOMINIO.'/robots.txt')->assertOk();
    }

    public function test_sin_app_url_no_se_redirige(): void
    {
        config(['app.url' => '']);

        $this->get(self::PROVISIONAL.'/robots.txt')->assertOk();
    }
}

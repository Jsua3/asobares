<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El botón flotante de WhatsApp del sitio público.
 *
 * Lo que se vigila no es que exista --eso se ve mirando-- sino las tres cosas
 * que se rompen en silencio: que el número salga de Ajustes y no del Blade
 * (RNF-09), que desaparezca entero cuando el gremio vacía el ajuste, y que en
 * el teléfono no se siente encima de la barra inferior de pestañas, que mide
 * 68 px y es fija.
 */
class BotonFlotanteDeWhatsappTest extends TestCase
{
    use RefreshDatabase;

    private const MARCA = 'asb-whatsapp-flotante';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @return list<array{string}> */
    public static function paginasPublicas(): array
    {
        return [
            'portada' => ['inicio'],
            'contacto' => ['contacto'],
            'directorio' => ['directorio.index'],
            'empleo' => ['empleo.index'],
        ];
    }

    #[DataProvider('paginasPublicas')]
    public function test_el_boton_flotante_acompania_a_todo_el_sitio_publico(string $ruta): void
    {
        $this->get(route($ruta))
            ->assertOk()
            ->assertSee(self::MARCA)
            ->assertSee('wa.me/'.ajuste('contacto_whatsapp'), escape: false);
    }

    /**
     * RNF-09, nada quemado en código: el día que el gremio cambie de número lo
     * cambia desde el panel. Si alguien copia el número al Blade «para que sea
     * más simple», esta prueba lo dice.
     */
    public function test_el_numero_del_boton_sale_de_los_ajustes(): void
    {
        Setting::query()->where('clave', 'contacto_whatsapp')->first()?->update(['valor' => '573001112233']);
        Setting::olvidarCache();

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('wa.me/573001112233', escape: false)
            ->assertDontSee('wa.me/573215549513', escape: false);
    }

    /**
     * Vaciar el ajuste tiene que apagar el botón entero, no dejar un enlace a
     * `wa.me/` sin número que lleva a una página de error de WhatsApp.
     */
    public function test_sin_numero_configurado_no_se_pinta_el_boton(): void
    {
        Setting::query()->where('clave', 'contacto_whatsapp')->first()?->update(['valor' => '']);
        Setting::olvidarCache();

        $this->get(route('inicio'))
            ->assertOk()
            ->assertDontSee(self::MARCA);
    }

    /** Un enlace de solo icono sin nombre accesible es un enlace sin texto. */
    public function test_el_boton_flotante_tiene_nombre_accesible(): void
    {
        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('Escríbenos por WhatsApp', escape: false);
    }

    /**
     * En el teléfono la barra de pestañas es fija, mide `--asb-alto-modulo-inferior`
     * y ocupa el borde inferior: un botón flotante a 1 rem del borde se sienta
     * encima de ella. El apartado se calcula con el token y no con un número,
     * porque el día que la barra cambie de alto el botón tiene que moverse solo.
     */
    public function test_el_boton_flotante_se_aparta_de_la_barra_inferior_del_telefono(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.'.self::MARCA.'\s*\{[^}]*bottom:\s*calc\([^)]*var\(--asb-alto-modulo-inferior\)/s',
            $app,
            'El botón flotante no se aparta de la barra inferior con su token: en el teléfono se sienta encima de las pestañas.'
        );
    }

    /**
     * Con el teclado virtual abierto la barra inferior se retira sola; el botón
     * tiene que irse con ella o se queda flotando sobre el campo que la persona
     * está escribiendo.
     */
    public function test_el_boton_flotante_se_retira_con_el_teclado(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/\.cromo\[data-teclado="abierto"\]\s*~\s*\.'.self::MARCA.'/',
            $app,
            'El botón flotante no se retira con el teclado abierto.'
        );
    }
}

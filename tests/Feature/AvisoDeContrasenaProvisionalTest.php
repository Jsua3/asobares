<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El aviso de contraseña provisional: todo el aviso lleva a la pantalla de
 * seguridad, en las páginas de Mi Cuenta que siguen abiertas con la marca.
 */
class AvisoDeContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function afiliado(bool $provisional): User
    {
        $usuario = User::factory()->create(['asociado_id' => Asociado::factory()->publicado()->create()->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        return $usuario->fresh();
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

    /** El aviso es el enlace a seguridad que habla de la contraseña provisional. */
    private function consultaDelAviso(): string
    {
        return '//a[@href="'.route('mi-cuenta.seguridad').'"][contains(normalize-space(.), "contraseña provisional")]';
    }

    /** @return array<string, array{string}> */
    public static function paginasConAviso(): array
    {
        return [
            'mi cuenta' => ['mi-cuenta.index'],
            'mis fotos' => ['mi-cuenta.fotos.index'],
        ];
    }

    #[DataProvider('paginasConAviso')]
    public function test_con_la_marca_el_aviso_entero_lleva_a_seguridad(string $ruta): void
    {
        $html = $this->actingAs($this->afiliado(true))->get(route($ruta))->assertOk()->getContent();

        $avisos = $this->xpathDe($html)->query($this->consultaDelAviso());

        $this->assertSame(1, $avisos->length);
        $this->assertStringContainsString('min-h-11', (string) $avisos->item(0)->getAttribute('class'), 'El aviso necesita 44 px de objetivo táctil.');
    }

    #[DataProvider('paginasConAviso')]
    public function test_sin_la_marca_no_hay_aviso(string $ruta): void
    {
        $html = $this->actingAs($this->afiliado(false))->get(route($ruta))->assertOk()->getContent();

        $this->assertSame(0, $this->xpathDe($html)->query($this->consultaDelAviso())->length);
    }

    public function test_mi_cuenta_ofrece_la_seguridad_aunque_no_haya_marca(): void
    {
        $html = $this->actingAs($this->afiliado(false))->get(route('mi-cuenta.index'))->assertOk()->getContent();

        $this->assertSame(1, $this->xpathDe($html)->query('//nav//a[@href="'.route('mi-cuenta.seguridad').'"]')->length);
    }

    public function test_mi_cuenta_pinta_el_aviso_que_llega_en_la_sesion(): void
    {
        $this->actingAs($this->afiliado(false))
            ->withSession(['aviso' => 'Mensaje de aviso de prueba'])
            ->get(route('mi-cuenta.index'))
            ->assertSeeText('Mensaje de aviso de prueba');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\CategoriaProveedor;
use App\Models\Artista;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ArtistasYProveedoresEditorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_artistas_carga_hero_editorial_sin_exponer_contactos(): void
    {
        Artista::factory()->publicado()->create([
            'nombre' => 'Trío Editorial',
            'slug' => 'trio-editorial',
            'whatsapp' => '3001112233',
            'instagram_url' => 'https://instagram.com/trio-editorial',
        ]);

        $respuesta = $this->get(route('artistas.index'))->assertOk();

        $respuesta
            ->assertSee('resources/css/artistas-editorial.css', escape: false)
            ->assertSee('img/artistas/hero-artistas.png', escape: false)
            ->assertSee('artistas-editorial-card', escape: false)
            ->assertSee('Trío Editorial')
            ->assertDontSee('3001112233')
            ->assertDontSee('instagram.com/trio-editorial');
    }

    public function test_ficha_publica_de_artista_mantiene_escaparate_y_reserva_contacto(): void
    {
        $artista = Artista::factory()->publicado()->create([
            'nombre' => 'Solista Reservado',
            'slug' => 'solista-reservado',
            'whatsapp' => '3004445566',
            'instagram_url' => 'https://instagram.com/solista-reservado',
        ]);

        $this->get(route('artistas.show', $artista))
            ->assertOk()
            ->assertSee('resources/css/artistas-editorial.css', escape: false)
            ->assertSee('artistas-editorial-ficha', escape: false)
            ->assertSee('Solista Reservado')
            ->assertDontSee('3004445566')
            ->assertDontSee('instagram.com/solista-reservado');
    }

    public function test_proveedores_carga_hero_editorial_y_no_filtra_datos_protegidos(): void
    {
        Proveedor::factory()->publicado()->verificado()->create([
            'nombre' => 'Hielo Protegido',
            'slug' => 'hielo-protegido',
            'categoria_proveedor' => CategoriaProveedor::Hielo,
            'correo' => 'pedidos@hielo.test',
            'whatsapp' => '3007778899',
        ]);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee('resources/css/proveedores-editorial.css', escape: false)
            ->assertSee('img/proveedores/hero-proveedores.png', escape: false)
            ->assertSee('proveedores-editorial-categoria', escape: false)
            ->assertSee('Hielo')
            ->assertSee('1')
            ->assertDontSee('Hielo Protegido')
            ->assertDontSee('pedidos@hielo.test')
            ->assertDontSee('3007778899');
    }

    public function test_artistas_y_proveedores_usan_el_velo_lateral_de_empleo(): void
    {
        $empleo = File::get(resource_path('css/empleo-editorial.css'));
        $artistas = File::get(resource_path('css/artistas-editorial.css'));
        $proveedores = File::get(resource_path('css/proveedores-editorial.css'));

        foreach ([$empleo, $artistas, $proveedores] as $css) {
            $this->assertStringContainsString('transparent 78%', $css);
            $this->assertStringContainsString('/ 0.18) 58%', $css);
            $this->assertStringContainsString('inset: 0 0 0 34%', $css);
        }

        $this->assertStringNotContainsString('filter: saturate(1.03)', $artistas);
        $this->assertStringNotContainsString('filter: saturate(0.98)', $proveedores);
    }

    public function test_vite_declara_las_hojas_editoriales_de_artistas_y_proveedores(): void
    {
        $vite = File::get(base_path('vite.config.js'));

        $this->assertStringContainsString('resources/css/artistas-editorial.css', $vite);
        $this->assertStringContainsString('resources/css/proveedores-editorial.css', $vite);
        $this->assertFileExists(public_path('img/artistas/hero-artistas.png'));
        $this->assertFileExists(public_path('img/proveedores/hero-proveedores.png'));
    }
}

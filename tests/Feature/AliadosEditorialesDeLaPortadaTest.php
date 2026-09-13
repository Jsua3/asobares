<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoAliado;
use App\Models\Aliado;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HOME-FINAL-07: la portada muestra aliados como franja institucional,
 * no como listado administrativo ni como detalle de convenio.
 *
 * Roturas: pintar detalle_convenio; href="#"; placeholder abstracto;
 * mezclar niveles; mostrar borradores; inventar destino.
 */
class AliadosEditorialesDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_la_portada_omite_el_bloque_si_no_hay_aliados_visibles(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('home-editorial-aliados', $html);
        $this->assertStringNotContainsString(ajuste('portada_aliados_titulo'), $html);
    }

    public function test_solo_salen_aliados_publicados_y_activos(): void
    {
        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Entidad Visible Home',
            'url' => null,
            'logo' => null,
        ]);
        Aliado::factory()->create([
            'nombre' => 'Entidad Borrador Home',
            'tipo' => TipoAliado::Institucional,
            'estado' => EstadoPublicacion::Borrador,
            'activo' => true,
        ]);
        Aliado::factory()->publicado()->create([
            'nombre' => 'Marca Apagada Home',
            'activo' => false,
        ]);

        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('Entidad Visible Home', $seccion);
        $this->assertStringNotContainsString('Entidad Borrador Home', $seccion);
        $this->assertStringNotContainsString('Marca Apagada Home', $seccion);
    }

    public function test_separa_institucionales_y_comerciales_y_conserva_el_cta(): void
    {
        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Cámara Editorial Home',
            'url' => 'https://camara.example.test',
            'logo' => null,
        ]);
        Aliado::factory()->visible()->create([
            'nombre' => 'Marca Editorial Home',
            'detalle_convenio' => 'Descuento privado del quince por ciento.',
            'url' => null,
            'logo' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeAliados($html);

        $this->assertStringContainsString(ajuste('portada_aliados_titulo'), $seccion);
        $this->assertStringContainsString(ajuste('portada_aliados_institucionales'), $seccion);
        $this->assertStringContainsString(ajuste('portada_aliados_comerciales'), $seccion);
        $this->assertStringContainsString('href="'.route('aliados.index').'"', $seccion);
        $this->assertStringContainsString('Ver todos los aliados', $seccion);
        $this->assertStringContainsString('Cámara Editorial Home', $seccion);
        $this->assertStringContainsString('Marca Editorial Home', $seccion);
        $this->assertTrue(
            strpos($seccion, 'Cámara Editorial Home') < strpos($seccion, 'Marca Editorial Home'),
            'Los institucionales deben ir por encima de los comerciales.'
        );
    }

    public function test_no_pinta_detalle_convenio_ni_inventa_enlace(): void
    {
        Aliado::factory()->visible()->create([
            'nombre' => 'Convenio Sin Sitio',
            'detalle_convenio' => 'Descuento privado del quince por ciento.',
            'url' => null,
            'logo' => null,
        ]);
        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Entidad Con Sitio',
            'url' => 'https://entidad.example.test',
            'logo' => null,
        ]);
        Aliado::factory()->visible()->create([
            'nombre' => 'Marca Javascript',
            'url' => 'javascript:alert(1)',
            'logo' => null,
        ]);

        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        $this->assertStringNotContainsString('Descuento privado del quince por ciento.', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringNotContainsString('javascript:', $seccion);
        $this->assertStringContainsString('href="https://entidad.example.test"', $seccion);
        $this->assertStringContainsString('target="_blank"', $seccion);
        $this->assertStringContainsString('rel="noopener noreferrer"', $seccion);
        $this->assertDoesNotMatchRegularExpression(
            '/Convenio Sin Sitio<\/span>\s*<\/a>/',
            $seccion
        );
    }

    public function test_sin_logo_real_muestra_el_nombre_y_no_un_placeholder(): void
    {
        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Nombre Limpio Home',
            'logo' => null,
            'url' => null,
        ]);

        $vista = File::get(resource_path('views/components/publico/home/aliados.blade.php'));
        $fila = File::get(resource_path('views/components/publico/home/partials/fila-aliados.blade.php'));
        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('Nombre Limpio Home', $seccion);
        $this->assertStringContainsString('home-editorial-aliados__marca--nombre', $seccion);
        $this->assertStringNotContainsString('<img', $seccion);
        $this->assertStringContainsString('enlaceSeguro', $fila);
        $this->assertStringContainsString('esImagenDeRelleno', $fila);
        $this->assertStringNotContainsString('detalle_convenio', $vista.$fila);
        $this->assertStringNotContainsString('placeholder', $vista.$fila);
    }

    /**
     * MUT-12: la prueba de arriba solo crea aliados con logo null y busca la
     * palabra esImagenDeRelleno en el archivo. Aquí se ejerce el filtro: un
     * logo que no está en el disco y uno de relleno del demo (md5.png, aunque
     * exista) caen al nombre sin <img>; uno real sí se pinta, para que la
     * prueba no pase porque la franja haya dejado de pintar imágenes.
     *
     * Rotura: anular la condición esImagenDeRelleno en fila-aliados
     * conservando la palabra.
     */
    public function test_un_logo_inexistente_o_de_relleno_cae_al_nombre_sin_imagen(): void
    {
        config(['almacenamiento.publico' => 'public']);
        Storage::fake('public');

        $relleno = 'aliados/'.md5('logo de relleno').'.png';
        Storage::disk('public')->put($relleno, 'png');
        Storage::disk('public')->put('aliados/logo-real.png', 'png');

        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Logo Inexistente Home',
            'logo' => 'aliados/no-existe-en-el-disco.png',
            'url' => null,
        ]);
        Aliado::factory()->visible()->create([
            'nombre' => 'Logo Relleno Home',
            'logo' => $relleno,
            'url' => null,
        ]);
        Aliado::factory()->visible()->create([
            'nombre' => 'Logo Real Home',
            'logo' => 'aliados/logo-real.png',
            'url' => null,
        ]);

        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        foreach (['Logo Inexistente Home', 'Logo Relleno Home'] as $nombre) {
            $marca = $this->marcaDe($seccion, $nombre);

            $this->assertStringContainsString('home-editorial-aliados__marca--nombre', $marca, "{$nombre} tiene que caer al nombre");
            $this->assertStringNotContainsString('<img', $marca, "{$nombre} no puede pintarse como imagen");
        }

        $real = $this->marcaDe($seccion, 'Logo Real Home');
        $this->assertStringContainsString('home-editorial-aliados__marca--logo', $real);
        $this->assertStringContainsString('<img src="'.Storage::disk('public')->url('aliados/logo-real.png').'"', $real, 'un logo real sí se pinta');
    }

    public function test_con_un_solo_aliado_la_portada_sigue_renderizando(): void
    {
        Aliado::factory()->visible()->create([
            'nombre' => 'Unico Aliado Home',
            'logo' => null,
            'url' => 'https://unico.example.test',
        ]);

        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('Unico Aliado Home', $seccion);
        $this->assertStringContainsString('href="https://unico.example.test"', $seccion);
        $this->assertStringNotContainsString('home-editorial-aliados__logos--desplaza', $seccion);
    }

    public function test_muchos_comerciales_activan_desplazamiento_controlado(): void
    {
        foreach (range(1, 8) as $i) {
            Aliado::factory()->visible()->create([
                'nombre' => "Marca Larga Home {$i}",
                'logo' => null,
                'url' => null,
            ]);
        }

        $seccion = $this->seccionDeAliados($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('home-editorial-aliados__logos--desplaza', $seccion);
        $this->assertStringContainsString('Marca Larga Home 1', $seccion);
        $this->assertStringContainsString('Marca Larga Home 8', $seccion);
    }

    public function test_el_css_declara_franja_compacta_hover_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeAliados($css);

        $this->assertStringContainsString('.home-editorial-aliados__franja', $bloque);
        $this->assertStringContainsString('object-fit: contain', $bloque);
        $this->assertStringContainsString('scale(1.02)', $bloque);
        $this->assertStringContainsString('home-editorial-aliados__logos--desplaza', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('.home-editorial-aliados__marca', $css);
    }

    private function seccionDeAliados(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-aliados[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja editorial de aliados.'
        );

        return $seccion[1];
    }

    /** El <li> de la franja que pinta a ese aliado, exigido una sola vez. */
    private function marcaDe(string $seccion, string $nombre): string
    {
        preg_match_all('/<li class="home-editorial-aliados__item">(.*?)<\/li>/s', $seccion, $items);

        $marcas = array_values(array_filter(
            $items[1],
            fn (string $item): bool => str_contains($item, '>'.$nombre.'</span>')
        ));

        $this->assertCount(1, $marcas, "la franja no pinta a {$nombre} una sola vez");

        return $marcas[0];
    }

    private function bloqueDeAliados(string $css): string
    {
        $inicio = strpos($css, '/* —— Aliados');
        $fin = strpos($css, '/* —— CTA');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}

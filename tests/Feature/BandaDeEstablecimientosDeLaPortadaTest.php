<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Support\BandaDeEstablecimientos;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-02: la franja de establecimientos es una banda, no tres
 * tarjetas fijas, y el punto de partida gira por sesión sin tocar la BD.
 *
 * Roturas: volver a take(3); meter ORDER BY RANDOM(); href="#"; pintar
 * una foto editorial como si fuera la del asociado.
 */
class BandaDeEstablecimientosDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_rotar_conserva_el_ciclo_alfabetico_y_no_baraja(): void
    {
        $base = collect(['Ámbar', 'Colina', 'Mirador', 'Zorba']);

        $this->assertSame(['Ámbar', 'Colina', 'Mirador', 'Zorba'], BandaDeEstablecimientos::rotar($base, 0)->all());
        $this->assertSame(['Colina', 'Mirador', 'Zorba', 'Ámbar'], BandaDeEstablecimientos::rotar($base, 1)->all());
        $this->assertSame(['Ámbar', 'Colina', 'Mirador', 'Zorba'], BandaDeEstablecimientos::rotar($base, 4)->all());
        $this->assertSame(['Único'], BandaDeEstablecimientos::rotar(collect(['Único']), 3)->all());
    }

    public function test_el_origen_es_estable_para_la_misma_semilla(): void
    {
        $this->assertSame(0, BandaDeEstablecimientos::origen('', 8));
        $this->assertSame(0, BandaDeEstablecimientos::origen('sesion-a', 1));
        $this->assertSame(
            BandaDeEstablecimientos::origen('sesion-a', 8),
            BandaDeEstablecimientos::origen('sesion-a', 8)
        );
        $this->assertNotSame(
            BandaDeEstablecimientos::origen('sesion-a', 8),
            BandaDeEstablecimientos::origen('sesion-b', 8)
        );
    }

    public function test_la_portada_pinta_una_banda_con_mas_de_tres_destacados(): void
    {
        $this->seed(DatabaseSeeder::class);
        Asociado::query()->update(['destacado' => false]);

        $nombres = ['Ámbar Gastrobar', 'Colina Nocturna', 'Mirador del Quindío', 'Roble Bar', 'Sauce Club', 'Zorba Bar'];

        foreach ($nombres as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre,
                'slug' => 'banda-'.$indice,
                'destacado' => true,
            ]);
        }

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeDescubre($html);
        $vistos = $this->nombresDeLaBanda($seccion);

        $this->assertGreaterThan(3, count($vistos));
        $this->assertTrue($this->esRotacionDe($nombres, $vistos), 'La banda no es una rotación del alfabeto español.');
        $this->assertStringContainsString('home-editorial-banda__pista', $seccion);
        $this->assertStringContainsString('Ver establecimientos anteriores', $seccion);
        $this->assertStringContainsString('Ver establecimientos siguientes', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringContainsString('La noche del Quindío', $seccion);
        $this->assertStringContainsString('Lugares que dan vida a nuestra ciudad.', $seccion);
        $this->assertStringContainsString('Algunos de los establecimientos afiliados al gremio.', $seccion);
        $this->assertStringContainsString('Ver el directorio completo', $seccion);

        foreach ($vistos as $nombre) {
            $asociado = Asociado::query()->where('nombre', $nombre)->firstOrFail();
            $this->assertStringContainsString(route('directorio.show', $asociado), $seccion);
        }
    }

    public function test_sin_foto_real_se_pinta_el_fallback_y_no_una_foto_editorial(): void
    {
        $this->seed(DatabaseSeeder::class);
        Asociado::query()->update(['destacado' => false]);

        Asociado::factory()->publicado()->create([
            'nombre' => 'Áabar Sin Foto',
            'slug' => 'aabar-sin-foto',
            'destacado' => true,
            'foto_portada' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeDescubre($html);

        $this->assertStringContainsString('home-editorial-establecimiento__fallback', $seccion);
        $this->assertStringNotContainsString('img/home/establecimiento-', $seccion);
        $this->assertStringContainsString(route('directorio.show', Asociado::query()->where('slug', 'aabar-sin-foto')->firstOrFail()), $seccion);
    }

    public function test_el_controlador_no_pide_azar_y_respeta_el_tope(): void
    {
        $controlador = File::get(app_path('Http/Controllers/Publico/InicioController.php'));

        $this->assertStringContainsString('BandaDeEstablecimientos::TOPE', $controlador);
        $this->assertStringNotContainsString('inRandomOrder', $controlador);
        $this->assertDoesNotMatchRegularExpression('/->orderByRaw\(/', $controlador);
    }

    public function test_la_banda_no_tiene_autoplay(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.data('bandaEstablecimientos'", $js);
        $this->assertMatchesRegularExpression(
            '/Alpine\.data\(\'bandaEstablecimientos\'[\s\S]*reduceMovimiento\(\)/',
            $js
        );
        $this->assertDoesNotMatchRegularExpression(
            '/Alpine\.data\(\'bandaEstablecimientos\'[\s\S]*setInterval/',
            $js
        );
    }

    public function test_el_directorio_sigue_sin_usar_la_rotacion_de_la_portada(): void
    {
        $directorio = File::get(app_path('Http/Controllers/Publico/DirectorioController.php'));

        $this->assertStringNotContainsString('BandaDeEstablecimientos', $directorio);
    }

    /**
     * @param  list<string>  $base
     * @param  list<string>  $visto
     */
    private function esRotacionDe(array $base, array $visto): bool
    {
        if ($base === [] || count($base) !== count($visto)) {
            return false;
        }

        $doble = array_merge($base, $base);

        for ($indice = 0; $indice < count($base); $indice++) {
            if (array_slice($doble, $indice, count($visto)) === $visto) {
                return true;
            }
        }

        return false;
    }

    private function seccionDeDescubre(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-descubre[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja de establecimientos.'
        );

        return $seccion[1];
    }

    /**
     * @return list<string>
     */
    private function nombresDeLaBanda(string $seccion): array
    {
        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/s', $seccion, $titulos);

        return array_values(array_map(
            fn (string $titulo): string => trim(html_entity_decode(strip_tags($titulo), ENT_QUOTES, 'UTF-8')),
            $titulos[1]
        ));
    }
}

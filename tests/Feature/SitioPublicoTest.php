<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Noticia;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Todas las rutas públicas responden y no filtran datos internos.
 */
class SitioPublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Los bloques JSON-LD van dentro de un <script>, así que un campo editable
     * desde el panel no puede cerrar la etiqueta. La secretaría, que por
     * diseño no puede publicar, si no ejecutaría código en el navegador de
     * quien sí publica.
     */
    public function test_el_json_ld_no_permite_cerrar_la_etiqueta_script(): void
    {
        $carga = '</script><script>alert(document.domain)</script>';

        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => "La Cava{$carga}",
            'slug' => 'la-cava-con-carga',
        ]);

        $respuesta = $this->get(route('directorio.show', $asociado))->assertSuccessful();

        $respuesta->assertDontSee($carga, escape: false);
        $respuesta->assertDontSee('<script>alert(document.domain)', escape: false);
        // Y sigue estando ahí, escapado, dentro del JSON-LD.
        $respuesta->assertSee('<', escape: false);
    }

    public function test_el_json_ld_de_una_noticia_tampoco_se_puede_romper(): void
    {
        $noticia = Noticia::create([
            'titulo' => 'Cifras</script><script>alert(1)</script>',
            'slug' => 'cifras-con-carga',
            'extracto' => 'Un extracto cualquiera del observatorio.',
            'contenido' => '<p>Cuerpo de la noticia.</p>',
            'publicado_at' => now()->subDay(),
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $this->get(route('boletin.show', $noticia))
            ->assertSuccessful()
            ->assertDontSee('<script>alert(1)', escape: false);
    }

    /**
     * El cuerpo del boletín se imprime sin escapar porque viene de un editor
     * enriquecido, así que todo depende de que el saneo corra de verdad.
     * `symfony/html-sanitizer` está declarado en composer.json por esto: antes
     * solo llegaba de rebote como dependencia de Filament.
     */
    public function test_el_contenido_del_boletin_se_sanea_antes_de_mostrarse(): void
    {
        $noticia = Noticia::create([
            'titulo' => 'Panorama del sector',
            'slug' => 'panorama-del-sector',
            'extracto' => 'Cifras del observatorio.',
            'contenido' => '<p>Texto legítimo.</p><script>alert(1)</script>'
                .'<a href="javascript:alert(2)">enlace</a><img src=x onerror="alert(3)">',
            'publicado_at' => now()->subDay(),
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $respuesta = $this->get(route('boletin.show', $noticia))->assertSuccessful();

        $respuesta->assertSee('Texto legítimo.', escape: false);
        $respuesta->assertDontSee('<script>alert(1)', escape: false);
        $respuesta->assertDontSee('javascript:alert(2)', escape: false);
        $respuesta->assertDontSee('onerror', escape: false);
    }

    /** @return list<array{0: string}> */
    public static function rutasPublicas(): array
    {
        return array_map(fn (string $ruta): array => [$ruta], [
            '/',
            '/quienes-somos',
            '/directorio',
            '/directorio?municipio=salento',
            '/directorio?categoria=cafe&vista=mapa',
            '/abre-tu-negocio',
            '/abre-tu-negocio?municipio=salento',
            '/empleo',
            '/artistas',
            '/proveedores',
            '/eventos',
            '/eventos?cuando=pasados',
            '/boletin',
            '/boletin?categoria=observatorio',
            '/afiliate',
            '/contacto',
            '/politica-de-datos',
            '/mi-cuenta/entrar',
            '/sitemap.xml',
            '/robots.txt',
        ]);
    }

    #[DataProvider('rutasPublicas')]
    public function test_las_rutas_publicas_responden(string $ruta): void
    {
        $this->get($ruta)->assertSuccessful();
    }

    public function test_las_fichas_de_detalle_responden(): void
    {
        $this->get(route('directorio.show', Asociado::publicado()->first()))->assertSuccessful();
        $this->get(route('eventos.show', Evento::publicado()->first()))->assertSuccessful();
        $this->get(route('boletin.show', Noticia::visible()->first()))->assertSuccessful();
        $this->get(route('artistas.show', Artista::publicado()->first()))->assertSuccessful();
    }

    public function test_la_ficha_publica_no_filtra_los_datos_internos_del_asociado(): void
    {
        $asociado = Asociado::publicado()->whereNotNull('notas_internas')->firstOrFail();

        $respuesta = $this->get(route('directorio.show', $asociado));

        $respuesta->assertDontSee($asociado->notas_internas, escape: false);
        $respuesta->assertDontSee($asociado->correo_interno);
        $respuesta->assertDontSee($asociado->representante);
    }

    public function test_un_asociado_sin_publicar_no_es_visible(): void
    {
        $borrador = Asociado::factory()->create();

        $this->get(route('directorio.show', $borrador))->assertNotFound();
        $this->get('/directorio')->assertDontSee($borrador->nombre);
    }

    public function test_una_pagina_inexistente_devuelve_404_con_la_marca(): void
    {
        $this->get('/directorio/no-existe-este-bar')
            ->assertNotFound()
            ->assertSee('ASOBARES');
    }
}

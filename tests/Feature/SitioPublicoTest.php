<?php

namespace Tests\Feature;

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

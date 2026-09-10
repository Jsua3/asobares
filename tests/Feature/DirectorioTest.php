<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_muestra_solo_establecimientos_publicados(): void
    {
        $publicado = Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);
        $borrador = Asociado::factory()->create(['nombre' => 'Bar Todavía En Borrador']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($publicado->nombre);
        $respuesta->assertDontSee($borrador->nombre);
    }

    public function test_los_filtros_se_combinan_y_la_busqueda_por_nombre_funciona(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento-test']);
        $categoria = Categoria::factory()->create(['nombre' => 'Café', 'slug' => 'cafe-test']);

        $buscado = Asociado::factory()->publicado()->create([
            'nombre' => 'La Cava Distinta',
            'municipio_id' => $municipio->id,
            'categoria_id' => $categoria->id,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'Bar De Otro Municipio',
            'categoria_id' => $categoria->id,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'La Cava Distinta en otra categoría',
            'municipio_id' => $municipio->id,
        ]);

        $respuesta = $this->get(route('directorio.index', [
            'q' => 'Cava Distinta',
            'municipio' => $municipio->slug,
            'categoria' => $categoria->slug,
        ]));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($buscado->nombre);
        $respuesta->assertDontSee('Bar De Otro Municipio');
        $respuesta->assertDontSee('en otra categoría');
    }

    public function test_el_directorio_usa_tarjetas_uniformes_y_conserva_distincion_de_destacado(): void
    {
        Asociado::factory()->destacado()->create(['nombre' => 'Bar Amnesia']);
        Asociado::factory()->publicado()->create(['nombre' => 'BBC Pub']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Destacado');
        $respuesta->assertDontSee('tarjeta-escena');
        $respuesta->assertDontSee('sm:col-span-2');
        $respuesta->assertDontSee('lg:col-span-2');
    }

    public function test_el_directorio_ordena_ignorando_solo_el_prefijo_bar(): void
    {
        foreach ([
            'Restaurante Aaa',
            'BBC Pub',
            'Bar Zafiro',
            'BAR Baco',
            'bAr Amnesia',
            'bar Cava',
        ] as $nombre) {
            Asociado::factory()->publicado()->create(['nombre' => $nombre]);
        }

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSeeInOrder([
            'bAr Amnesia',
            'BAR Baco',
            'BBC Pub',
            'bar Cava',
            'Restaurante Aaa',
            'Bar Zafiro',
        ]);
        $respuesta->assertSee('Bar Zafiro');
    }

    public function test_los_destacados_conservan_prioridad_y_orden_comercial_interno(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bar Amnesia']);
        Asociado::factory()->destacado()->create(['nombre' => 'Bar Zulu']);
        Asociado::factory()->destacado()->create(['nombre' => 'BAR Baco']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSeeInOrder([
            'BAR Baco',
            'Bar Zulu',
            'Bar Amnesia',
        ]);
    }

    public function test_el_orden_comercial_ocurre_antes_de_paginar(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bar Zulu']);

        foreach (range(1, 12) as $indice) {
            Asociado::factory()->publicado()->create(['nombre' => sprintf('BBC Pub %02d', $indice)]);
        }

        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('BBC Pub 01')
            ->assertSee('BBC Pub 12')
            ->assertDontSee('Bar Zulu');

        $this->get(route('directorio.index', ['page' => 2]))
            ->assertSuccessful()
            ->assertSee('Bar Zulu');
    }

    public function test_un_parametro_invalido_no_tumba_la_pagina(): void
    {
        $this->get(route('directorio.index', ['municipio' => 'no-existe']))
            ->assertSessionHasErrors('municipio');

        $this->get(route('directorio.index', ['categoria' => 'no-existe']))
            ->assertSessionHasErrors('categoria');

        $this->get(route('directorio.index', ['vista' => 'satelite']))
            ->assertSessionHasErrors('vista');
    }

    public function test_sin_establecimientos_el_directorio_no_habla_de_filtros(): void
    {
        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('Todavía no hay establecimientos publicados')
            ->assertDontSee('con ese filtro');
    }

    public function test_un_filtro_sin_resultados_lo_dice_con_claridad(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);

        $this->get(route('directorio.index', ['q' => 'zzzz-no-existe']))
            ->assertSuccessful()
            ->assertSee('No encontramos establecimientos con ese filtro')
            ->assertDontSee('Bruma Gastrobar');
    }

    public function test_solo_vista_mapa_no_se_hace_pasar_por_un_filtro(): void
    {
        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertSuccessful()
            ->assertDontSee('Limpiar');
    }

    public function test_el_mapa_sin_resultados_no_dice_que_faltan_coordenadas(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);

        $this->get(route('directorio.index', ['vista' => 'mapa', 'q' => 'zzzz-no-existe']))
            ->assertSuccessful()
            ->assertSee('No encontramos establecimientos con ese filtro')
            ->assertDontSee('tiene ubicación registrada');
    }

    public function test_el_mapa_avisa_cuando_hay_fichas_pero_ninguna_tiene_pin(): void
    {
        Asociado::factory()->publicado()->create([
            'nombre' => 'Bar Sin Coordenadas',
            'lat' => null,
            'lng' => null,
        ]);

        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertSuccessful()
            ->assertSee('1 establecimiento')
            ->assertSee('Ningún establecimiento de este filtro tiene ubicación registrada');
    }

    public function test_la_ficha_y_el_listado_no_exponen_datos_internos_ni_de_cartera(): void
    {
        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => 'Bruma Gastrobar',
            'correo_interno' => 'oficina-secreta@gremio.test',
            'representante' => 'Natalia Representante Unica',
            'telefono_interno' => '3009998877',
            'notas_internas' => 'Mora administrativa interna XYZ',
        ]);
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 150000,
            'meses_mora' => 3,
            'actualizado_at' => now(),
        ]);

        foreach ([
            route('directorio.index'),
            route('directorio.show', $asociado),
            route('directorio.index', ['vista' => 'mapa']),
        ] as $url) {
            $respuesta = $this->get($url)->assertSuccessful();
            $respuesta->assertDontSee('oficina-secreta@gremio.test');
            $respuesta->assertDontSee('Natalia Representante Unica');
            $respuesta->assertDontSee('3009998877');
            $respuesta->assertDontSee('Mora administrativa interna XYZ');
            $respuesta->assertDontSee('$150.000');
        }
    }

    public function test_la_ficha_no_lista_vacantes_cerradas_y_enlaza_las_vivas(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $viva = Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Bartender de viernes']);
        Vacante::factory()->for($asociado)->publicado()->cerrada()->create(['cargo' => 'Mesero ya contratado']);
        Vacante::factory()->for($asociado)->publicado()->vencida()->create(['cargo' => 'Portero de anoche']);
        Vacante::factory()->for($asociado)->pendiente()->create(['cargo' => 'Cocinero sin aprobar']);

        $respuesta = $this->get(route('directorio.show', $asociado));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($viva->cargo);
        $respuesta->assertSee(route('empleo.show', $viva), escape: false);
        $respuesta->assertDontSee('Mesero ya contratado');
        $respuesta->assertDontSee('Portero de anoche');
        $respuesta->assertDontSee('Cocinero sin aprobar');
    }
}

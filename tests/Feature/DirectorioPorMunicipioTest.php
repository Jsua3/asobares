<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `/directorio/municipio/{municipio}`: URL propia por municipio.
 *
 * `/directorio?municipio=salento` existe desde siempre, pero su etiqueta
 * canónica (`url()->current()`) siempre colapsa en `/directorio`: para Google
 * las doce variantes son la misma página, así que solo hay una oportunidad de
 * posicionar para todo el directorio, no doce. Esta URL nueva es la que de
 * verdad puede pelear "bares en Salento" por separado de "bares en Armenia".
 */
class DirectorioPorMunicipioTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_de_un_municipio_solo_lista_sus_propios_establecimientos(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);
        $armenia = Municipio::factory()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        $deSalento = Asociado::factory()->publicado()->create(['nombre' => 'Bar Salento Real', 'municipio_id' => $salento->id]);
        Asociado::factory()->publicado()->create(['nombre' => 'Bar De Armenia', 'municipio_id' => $armenia->id]);

        $respuesta = $this->get(route('directorio.municipio', $salento));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($deSalento->nombre);
        $respuesta->assertDontSee('Bar De Armenia');
    }

    public function test_el_titulo_y_la_descripcion_nombran_el_municipio(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);
        Asociado::factory()->publicado()->create(['municipio_id' => $salento->id]);

        $this->get(route('directorio.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('<title>Bares y establecimientos afiliados en Salento, Quindío — ASOBARES Quindío</title>', escape: false)
            ->assertSee('Bares, gastrobares, cafés y discotecas afiliados a ASOBARES en Salento, Quindío.', escape: false);
    }

    public function test_la_pagina_generica_no_menciona_un_municipio_en_el_titulo(): void
    {
        // Control: sin municipio en la URL, el título sigue siendo el genérico
        // y editable por ajuste, no el de un municipio cualquiera.
        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertDontSee('<title>Bares y establecimientos afiliados en', escape: false);
    }

    public function test_la_etiqueta_canonica_apunta_a_la_url_propia_del_municipio(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);
        Asociado::factory()->publicado()->create(['municipio_id' => $salento->id]);

        $this->get(route('directorio.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('<link rel="canonical" href="'.route('directorio.municipio', $salento).'">', escape: false);
    }

    public function test_un_municipio_sin_negocios_publicados_muestra_un_estado_honesto_y_no_se_indexa(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);

        $this->get(route('directorio.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('Todavía no hay establecimientos afiliados publicados en Salento', escape: false)
            ->assertSee('href="'.route('afiliate').'"', escape: false)
            ->assertSee('name="robots" content="noindex, follow"', escape: false);
    }

    /** Contraprueba: con negocios reales, la página no se noindexa a sí misma. */
    public function test_un_municipio_con_negocios_no_lleva_noindex_propio(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);
        Asociado::factory()->publicado()->create(['municipio_id' => $salento->id]);

        $this->get(route('directorio.municipio', $salento))
            ->assertSuccessful()
            ->assertDontSee('name="robots" content="noindex, follow"', escape: false);
    }

    public function test_un_municipio_inactivo_responde_404(): void
    {
        $inactivo = Municipio::factory()->create(['slug' => 'inactivo', 'activo' => false]);

        $this->get(route('directorio.municipio', $inactivo))->assertNotFound();
    }

    public function test_un_slug_de_municipio_inexistente_responde_404(): void
    {
        $this->get('/directorio/municipio/no-existe')->assertNotFound();
    }

    public function test_se_puede_combinar_con_categoria_sin_perder_el_municipio(): void
    {
        $salento = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);
        $categoria = Categoria::factory()->create(['nombre' => 'Café', 'slug' => 'cafe']);

        $buscado = Asociado::factory()->publicado()->create([
            'nombre' => 'Café Central',
            'municipio_id' => $salento->id,
            'categoria_id' => $categoria->id,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'Bar Sin Categoria En Salento',
            'municipio_id' => $salento->id,
        ]);

        $this->get(route('directorio.municipio', ['municipio' => $salento, 'categoria' => 'cafe']))
            ->assertSuccessful()
            ->assertSee($buscado->nombre)
            ->assertDontSee('Bar Sin Categoria En Salento');
    }
}

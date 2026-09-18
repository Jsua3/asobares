<?php

namespace Tests\Feature;

use App\Models\ConsultaGuia;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `/abre-tu-negocio/{municipio}`: URL propia por municipio.
 *
 * Igual que en el Directorio, `/abre-tu-negocio?municipio=salento` colapsa en
 * la canónica de la página base. Esta URL es la que de verdad puede pelear
 * "cómo abrir un bar en Salento" o "requisitos para abrir un bar en Armenia".
 */
class GuiaPorMunicipioTest extends TestCase
{
    use RefreshDatabase;

    private function conGuia(string $nombre, string $slug): Municipio
    {
        $municipio = Municipio::factory()->create(['nombre' => $nombre, 'slug' => $slug]);
        RequisitoApertura::factory()->publicado()->for($municipio)->create(['entidad' => "Entidad de {$nombre}"]);

        return $municipio;
    }

    public function test_la_pagina_de_un_municipio_muestra_solo_sus_propios_requisitos(): void
    {
        $salento = $this->conGuia('Salento', 'salento');
        $this->conGuia('Armenia', 'armenia');

        $this->get(route('guia.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('Entidad de Salento')
            ->assertDontSee('Entidad de Armenia');
    }

    public function test_el_titulo_y_la_descripcion_nombran_el_municipio(): void
    {
        $salento = $this->conGuia('Salento', 'salento');

        $this->get(route('guia.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('<title>Requisitos para abrir un bar en Salento, Quindío — ASOBARES Quindío</title>', escape: false);
    }

    public function test_la_pagina_generica_no_menciona_un_municipio_en_el_titulo(): void
    {
        $this->conGuia('Armenia', 'armenia');

        // Sin municipio explícito, aunque el controlador cargue uno por
        // defecto para mostrar contenido, el título sigue siendo el genérico.
        $this->get(route('guia.index'))
            ->assertSuccessful()
            ->assertDontSee('<title>Requisitos para abrir un bar en', escape: false);
    }

    public function test_la_etiqueta_canonica_apunta_a_la_url_propia_del_municipio(): void
    {
        $salento = $this->conGuia('Salento', 'salento');

        $this->get(route('guia.municipio', $salento))
            ->assertSuccessful()
            ->assertSee('<link rel="canonical" href="'.route('guia.municipio', $salento).'">', escape: false);
    }

    public function test_llegar_directo_a_la_url_registra_la_consulta_en_el_observatorio(): void
    {
        $salento = $this->conGuia('Salento', 'salento');

        $antes = ConsultaGuia::where('municipio_id', $salento->id)->count();

        $this->get(route('guia.municipio', $salento))->assertSuccessful();

        $this->assertSame($antes + 1, ConsultaGuia::where('municipio_id', $salento->id)->count());
    }

    public function test_un_municipio_inactivo_responde_404(): void
    {
        $inactivo = Municipio::factory()->create(['slug' => 'inactivo', 'activo' => false]);

        $this->get(route('guia.municipio', $inactivo))->assertNotFound();
    }

    public function test_un_slug_de_municipio_inexistente_responde_404(): void
    {
        $this->get('/abre-tu-negocio/no-existe')->assertNotFound();
    }

    /** Un municipio activo pero sin guía todavía no desaparece, pero no se indexa ni cuenta. */
    public function test_un_municipio_activo_sin_guia_no_se_indexa_ni_registra_consulta(): void
    {
        $sinGuia = Municipio::factory()->create(['nombre' => 'Génova', 'slug' => 'genova', 'activo' => true]);

        $this->get(route('guia.municipio', $sinGuia))
            ->assertSuccessful()
            ->assertSee('Todavía no hay guía publicada')
            ->assertSee('name="robots" content="noindex, follow"', escape: false);

        $this->assertSame(0, ConsultaGuia::where('municipio_id', $sinGuia->id)->count());
    }

    public function test_el_selector_de_municipios_enlaza_a_la_url_propia(): void
    {
        $salento = $this->conGuia('Salento', 'salento');

        $this->get(route('guia.index'))
            ->assertSuccessful()
            ->assertSee('href="'.route('guia.municipio', $salento).'"', escape: false);
    }
}

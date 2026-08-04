<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Models\Asociado;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BolsaDeEmpleoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_muro_muestra_solo_vacantes_publicadas_y_vivas(): void
    {
        $publicada = Vacante::factory()->publicado()->create(['cargo' => 'Bartender publicado']);
        $pendiente = Vacante::factory()->pendiente()->create(['cargo' => 'Cocinero pendiente']);
        $cerrada = Vacante::factory()->publicado()->cerrada()->create(['cargo' => 'Mesero ya contratado']);
        $vencida = Vacante::factory()->publicado()->vencida()->create(['cargo' => 'Portero de anoche']);

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee($publicada->cargo);
        $respuesta->assertDontSee($pendiente->cargo);
        $respuesta->assertDontSee($cerrada->cargo);
        $respuesta->assertDontSee($vencida->cargo);
    }

    public function test_el_filtro_por_area_del_establecimiento_funciona(): void
    {
        Vacante::factory()->publicado()->create([
            'cargo' => 'Bartender de barra',
            'categoria_cargo' => CargoDelSector::Barra,
        ]);
        Vacante::factory()->publicado()->create([
            'cargo' => 'Auxiliar de cocina',
            'categoria_cargo' => CargoDelSector::Cocina,
        ]);

        $respuesta = $this->get(route('empleo.index', ['categoria' => CargoDelSector::Cocina->value]));

        $respuesta->assertSee('Auxiliar de cocina');
        $respuesta->assertDontSee('Bartender de barra');
    }

    public function test_el_filtro_por_municipio_usa_el_del_establecimiento(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Cargo del municipio buscado']);
        Vacante::factory()->publicado()->create(['cargo' => 'Cargo de otro municipio']);

        $respuesta = $this->get(route('empleo.index', ['municipio' => $asociado->municipio->slug]));

        $respuesta->assertSee('Cargo del municipio buscado');
        $respuesta->assertDontSee('Cargo de otro municipio');
    }

    public function test_una_categoria_inventada_no_rompe_la_pagina(): void
    {
        $this->get(route('empleo.index', ['categoria' => 'no-existe']))->assertSessionHasErrors('categoria');
    }

    public function test_el_detalle_muestra_la_vacante_publicada(): void
    {
        $vacante = Vacante::factory()->publicado()->create([
            'cargo' => 'Chef de cocina',
            'descripcion' => 'Lidera la cocina y el diseño de la carta.',
        ]);

        $respuesta = $this->get(route('empleo.show', $vacante));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Chef de cocina');
        $respuesta->assertSee('Lidera la cocina y el diseño de la carta.');
        $respuesta->assertSee('JobPosting', escape: false);
    }

    public function test_el_detalle_de_una_vacante_no_publicada_da_404(): void
    {
        foreach ([
            Vacante::factory()->pendiente()->create(),
            Vacante::factory()->publicado()->cerrada()->create(),
            Vacante::factory()->publicado()->vencida()->create(),
        ] as $vacante) {
            $this->get(route('empleo.show', $vacante))->assertNotFound();
        }
    }
}

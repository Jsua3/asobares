<?php

namespace Tests\Feature;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AliadosPublicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_publica_de_aliados_responde(): void
    {
        $this->get(route('aliados.index'))->assertOk();
    }

    public function test_muestra_solo_aliados_publicados_y_activos(): void
    {
        Aliado::factory()->visible()->create(['nombre' => 'Aliado Visible']);
        Aliado::factory()->create(['nombre' => 'Aliado Borrador', 'activo' => true]);
        Aliado::factory()->publicado()->create(['nombre' => 'Aliado Apagado', 'activo' => false]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSee('Aliado Visible')
            ->assertDontSee('Aliado Borrador')
            ->assertDontSee('Aliado Apagado');
    }

    public function test_separa_institucionales_y_comerciales(): void
    {
        Aliado::factory()->visible()->institucional()->create(['nombre' => 'Cámara Aliada']);
        Aliado::factory()->visible()->create(['nombre' => 'Marca Aliada', 'tipo' => TipoAliado::Comercial]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSeeInOrder(['Respaldo institucional', 'Cámara Aliada', 'Convenios para afiliados', 'Marca Aliada'], escape: false);
    }

    public function test_no_muestra_el_detalle_privado_del_convenio_a_visitantes(): void
    {
        Aliado::factory()->visible()->conConvenioPrivado()->create([
            'nombre' => 'Convenio Privado',
            'detalle_convenio' => 'Descuento privado del quince por ciento.',
        ]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSee('Convenio Privado')
            ->assertDontSee('Descuento privado del quince por ciento.');
    }

    public function test_el_enlace_externo_es_seguro_y_descarta_protocolos_invalidos(): void
    {
        Aliado::factory()->visible()->create([
            'nombre' => 'Aliado Con Enlace',
            'url' => 'https://example.com/aliado',
        ]);
        Aliado::factory()->visible()->create([
            'nombre' => 'Aliado Sin Enlace Seguro',
            'url' => 'javascript:alert(1)',
        ]);

        $respuesta = $this->get(route('aliados.index'))->assertOk();

        $respuesta->assertSee('href="https://example.com/aliado"', escape: false);
        $respuesta->assertSee('target="_blank"', escape: false);
        $respuesta->assertSee('rel="noopener"', escape: false);
        $respuesta->assertDontSee('javascript:alert(1)', escape: false);
    }

    public function test_un_aliado_sin_logo_no_rompe_la_pagina(): void
    {
        Aliado::factory()->visible()->create([
            'nombre' => 'Aliado Sin Logo',
            'logo' => null,
        ]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSee('Aliado Sin Logo')
            ->assertDontSee('<img src=""', escape: false);
    }

    public function test_un_aliado_sin_descripcion_no_deja_contenido_basura(): void
    {
        Aliado::factory()->visible()->create([
            'nombre' => 'Aliado Sin Descripción',
            'descripcion' => null,
        ]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSee('Aliado Sin Descripción')
            ->assertDontSee('<p class="mt-3 line-clamp-3 text-sm leading-relaxed text-tenue"></p>', escape: false);
    }

    public function test_el_estado_vacio_funciona(): void
    {
        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertSee('No hay aliados publicados')
            ->assertSee('Afiliar mi establecimiento');
    }

    public function test_el_bloque_de_inicio_sigue_funcionando(): void
    {
        Aliado::factory()->visible()->create(['nombre' => 'Aliado En Portada']);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('Aliado En Portada')
            ->assertSee('Ver aliados');
    }

    public function test_el_asociado_sigue_viendo_el_detalle_privado_en_mi_cuenta(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create();
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        Aliado::factory()->visible()->conConvenioPrivado()->create([
            'nombre' => 'Aliado Con Convenio',
            'detalle_convenio' => 'Condición privada para afiliados.',
        ]);

        $this->actingAs($duenio->fresh())
            ->get(route('mi-cuenta.index'))
            ->assertOk()
            ->assertSee('Condición privada para afiliados.');
    }

    public function test_la_regla_de_alcaldias_tambien_aplica_en_la_pagina_publica(): void
    {
        $armenia = Municipio::factory()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);
        Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento']);

        Aliado::factory()->visible()->institucional()->create([
            'nombre' => 'Alcaldía de Armenia',
            'municipio_id' => $armenia->id,
        ]);

        $this->get(route('aliados.index'))
            ->assertOk()
            ->assertDontSee('Alcaldía de Armenia');
    }

    public function test_la_pagina_entra_al_sitemap(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('aliados.index'), escape: false);
    }
}

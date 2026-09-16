<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Models\Aliado;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OrigenDeEventosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($direccion->fresh());
    }

    public function test_el_panel_crea_eventos_de_asobares_sin_aliado(): void
    {
        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'origen' => OrigenEvento::Asobares->value,
                'aliado_id' => null,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $evento = Evento::firstOrFail();

        $this->assertSame(OrigenEvento::Asobares, $evento->origen);
        $this->assertNull($evento->aliado_id);
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $evento->organizadorVisible());
        $this->assertSame([
            '@type' => 'Organization',
            'name' => Evento::ORGANIZADOR_ASOBARES,
            'url' => route('inicio'),
        ], $evento->organizadorJsonLd());
    }

    public function test_el_panel_exige_aliado_cuando_el_origen_es_aliado(): void
    {
        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'origen' => OrigenEvento::Aliado->value,
                'aliado_id' => null,
            ]))
            ->call('create')
            ->assertHasFormErrors(['aliado_id']);

        $this->assertSame(0, Evento::count());
    }

    public function test_el_panel_crea_evento_de_aliado_con_organizador_real(): void
    {
        $aliado = Aliado::factory()->visible()->create([
            'nombre' => 'Cámara de Comercio de Armenia y del Quindío',
            'url' => 'https://camaraarmenia.org.co',
        ]);

        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'origen' => OrigenEvento::Aliado->value,
                'aliado_id' => $aliado->id,
                'permite_inscripcion' => false,
                'enlace_externo' => 'https://example.com/registro-aliado',
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $evento = Evento::with('aliado')->firstOrFail();

        $this->assertSame(OrigenEvento::Aliado, $evento->origen);
        $this->assertTrue($evento->aliado->is($aliado));
        $this->assertSame($aliado->nombre, $evento->organizadorVisible());
        $this->assertSame([
            '@type' => 'Organization',
            'name' => $aliado->nombre,
            'url' => $aliado->url,
        ], $evento->organizadorJsonLd());
        $this->assertTrue($evento->delegaRegistroExterno());
        $this->assertFalse($evento->admiteInscripciones());
    }

    public function test_el_modelo_limpia_un_aliado_cuando_el_evento_es_de_asobares(): void
    {
        $aliado = Aliado::factory()->visible()->create();

        $evento = Evento::factory()->create([
            'origen' => OrigenEvento::Asobares,
            'aliado_id' => $aliado->id,
        ]);

        $this->assertNull($evento->refresh()->aliado_id);
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $evento->organizadorVisible());
    }

    public function test_el_modelo_no_permite_evento_de_aliado_sin_aliado(): void
    {
        $this->expectException(ValidationException::class);

        Evento::factory()->create([
            'origen' => OrigenEvento::Aliado,
            'aliado_id' => null,
        ]);
    }

    public function test_los_eventos_existentes_sin_origen_explicito_quedan_como_asobares(): void
    {
        $evento = Evento::factory()->create();

        $this->assertSame(OrigenEvento::Asobares, $evento->refresh()->origen);
        $this->assertNull($evento->aliado_id);
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $evento->organizadorVisible());
    }

    public function test_un_evento_con_origen_nulo_se_interpreta_como_asobares(): void
    {
        $evento = Evento::factory()->make([
            'origen' => null,
            'aliado_id' => null,
        ]);

        $this->assertNull($evento->origen);
        $this->assertSame(OrigenEvento::Asobares, $evento->origenPublico());
        $this->assertFalse($evento->esDeAliado());
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $evento->organizadorVisible());
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $evento->organizadorJsonLd()['name']);
        $this->assertSame('ASOBARES', $evento->origenPublico()->getLabel());
    }

    /** @return array<string, mixed> */
    private function datosFormulario(array $sobrescribir = []): array
    {
        return array_merge([
            'titulo' => 'Evento con organizador explícito',
            'slug' => 'evento-con-organizador-explicito',
            'tipo' => TipoEvento::Evento->value,
            'origen' => OrigenEvento::Asobares->value,
            'aliado_id' => null,
            'lugar' => 'Armenia, Quindío',
            'fecha_inicio' => '2026-09-20 18:00:00',
            'fecha_fin' => '2026-09-20 23:00:00',
            'descripcion' => 'Prueba del origen del evento.',
            'permite_inscripcion' => true,
            'enlace_externo' => null,
            'cupos' => 40,
            'precio' => 0,
            'imagen' => null,
            'estado' => EstadoPublicacion::Borrador->value,
        ], $sobrescribir);
    }
}

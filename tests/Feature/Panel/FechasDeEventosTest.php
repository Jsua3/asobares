<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Filament\Resources\Eventos\Pages\EditEvento;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FechasDeEventosTest extends TestCase
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

    public function test_el_panel_rechaza_un_evento_que_termina_antes_de_empezar(): void
    {
        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'fecha_inicio' => '2026-09-20 18:00:00',
                'fecha_fin' => '2026-09-20 17:59:00',
            ]))
            ->call('create')
            ->assertHasFormErrors(['fecha_fin']);

        $this->assertSame(0, Evento::count());
    }

    public function test_el_panel_permite_eventos_sin_fecha_final(): void
    {
        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'fecha_fin' => null,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $evento = Evento::firstOrFail();

        $this->assertNull($evento->fecha_fin);
    }

    public function test_el_panel_permite_eventos_de_varios_dias_con_fecha_final_posterior(): void
    {
        Livewire::test(CreateEvento::class)
            ->fillForm($this->datosFormulario([
                'fecha_inicio' => '2026-09-20 18:00:00',
                'fecha_fin' => '2026-09-22 23:00:00',
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $evento = Evento::firstOrFail();

        $this->assertTrue($evento->fecha_fin->greaterThan($evento->fecha_inicio));
    }

    public function test_el_panel_rechaza_una_edicion_que_deja_fecha_final_anterior_al_inicio(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'fecha_inicio' => Carbon::parse('2026-09-20 18:00:00'),
            'fecha_fin' => Carbon::parse('2026-09-20 23:00:00'),
        ]);

        Livewire::test(EditEvento::class, ['record' => $evento->getRouteKey()])
            ->fillForm([
                'fecha_inicio' => '2026-09-21 18:00:00',
                'fecha_fin' => '2026-09-21 17:59:00',
            ])
            ->call('save')
            ->assertHasFormErrors(['fecha_fin']);

        $evento->refresh();

        $this->assertSame('2026-09-20 18:00:00', $evento->fecha_inicio->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-20 23:00:00', $evento->fecha_fin->format('Y-m-d H:i:s'));
    }

    public function test_los_scopes_siguen_usando_la_fecha_final_para_eventos_en_curso_y_rangos(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-21 12:00:00'));

        $enCurso = Evento::factory()->publicado()->create([
            'slug' => 'evento-en-curso',
            'fecha_inicio' => Carbon::parse('2026-09-20 18:00:00'),
            'fecha_fin' => Carbon::parse('2026-09-22 23:00:00'),
        ]);

        $pasado = Evento::factory()->publicado()->create([
            'slug' => 'evento-pasado',
            'fecha_inicio' => Carbon::parse('2026-09-18 18:00:00'),
            'fecha_fin' => Carbon::parse('2026-09-18 23:00:00'),
        ]);

        $borrador = Evento::factory()->create([
            'slug' => 'evento-borrador',
            'fecha_inicio' => Carbon::parse('2026-09-21 18:00:00'),
            'fecha_fin' => Carbon::parse('2026-09-22 23:00:00'),
        ]);

        $this->assertTrue(Evento::publicado()->proximo()->whereKey($enCurso)->exists());
        $this->assertFalse(Evento::publicado()->pasado()->whereKey($enCurso)->exists());
        $this->assertTrue(Evento::publicado()->pasado()->whereKey($pasado)->exists());

        $enRango = Evento::publicado()
            ->enRango(Carbon::parse('2026-09-21 00:00:00'), Carbon::parse('2026-09-21 23:59:59'))
            ->pluck('id');

        $this->assertTrue($enRango->contains($enCurso->id));
        $this->assertFalse($enRango->contains($pasado->id));
        $this->assertFalse($enRango->contains($borrador->id));
    }

    /** @return array<string, mixed> */
    private function datosFormulario(array $sobrescribir = []): array
    {
        return array_merge([
            'titulo' => 'Capacitación de integridad de fechas',
            'slug' => 'capacitacion-integridad-fechas',
            'tipo' => TipoEvento::Capacitacion->value,
            'origen' => OrigenEvento::Asobares->value,
            'aliado_id' => null,
            'lugar' => 'Armenia, Quindío',
            'fecha_inicio' => '2026-09-20 18:00:00',
            'fecha_fin' => '2026-09-20 23:00:00',
            'descripcion' => 'Prueba de validación de fechas del evento.',
            'permite_inscripcion' => true,
            'enlace_externo' => null,
            'cupos' => 40,
            'precio' => 0,
            'imagen' => null,
            'estado' => EstadoPublicacion::Borrador->value,
        ], $sobrescribir);
    }
}

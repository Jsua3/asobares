<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Filament\Resources\Publicidades\Pages\CreatePublicidad;
use App\Filament\Resources\Publicidades\Pages\EditPublicidad;
use App\Filament\Resources\Publicidades\Pages\ListPublicidades;
use App\Models\Publicidad;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PublicidadInternaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolYPermisoSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_el_scope_publico_solo_devuelve_pautas_publicadas_vigentes_y_ordenadas(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->crearPublicidadPublicada([
            'anunciante' => 'Vencida',
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subWeeks(2),
            'fecha_fin' => now()->subDay(),
        ]);
        Publicidad::factory()->enInicio()->create(['anunciante' => 'Borrador']);
        $this->crearPublicidadPublicada([
            'anunciante' => 'Otra ubicacion',
            'ubicacion' => UbicacionPublicidad::Directorio,
        ]);
        $segunda = $this->crearPublicidadPublicada([
            'anunciante' => 'Segunda',
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subHours(2),
        ]);
        $primera = $this->crearPublicidadPublicada([
            'anunciante' => 'Primera',
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subDay(),
        ]);

        $publicas = Publicidad::publicaEn(UbicacionPublicidad::Inicio)->get();

        $this->assertTrue($publicas->first()->is($primera));
        $this->assertTrue($publicas->last()->is($segunda));
        $this->assertCount(2, $publicas);
    }

    public function test_la_visibilidad_publica_exige_estado_publicado_imagen_y_fechas(): void
    {
        $visible = Publicidad::factory()->publicadaVigente()->make();
        $sinImagen = Publicidad::factory()->publicadaVigente()->make(['imagen' => null]);
        $pendiente = Publicidad::factory()->make(['estado' => EstadoPublicidad::PendienteAprobacion]);

        $this->assertTrue($visible->visiblePublicamente());
        $this->assertFalse($sinImagen->visiblePublicamente());
        $this->assertFalse($pendiente->visiblePublicamente());
    }

    public function test_solo_una_publicidad_pagada_puede_publicarse(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        foreach ([EstadoPublicidad::Borrador, EstadoPublicidad::PendientePago, EstadoPublicidad::PendienteAprobacion, EstadoPublicidad::Rechazada] as $estado) {
            $publicidad = Publicidad::factory()->create(['estado' => $estado]);

            try {
                $publicidad->update(['estado' => EstadoPublicidad::Publicada]);
            } catch (ValidationException) {
                // El contrato es que el salto no se persista.
            }

            $this->assertSame($estado, $publicidad->fresh()->estado, "El estado {$estado->value} no debe saltar directo a publicada.");
            $this->assertNull($publicidad->fresh()->aprobado_at);
        }

        $pagada = Publicidad::factory()->create(['estado' => EstadoPublicidad::Pagada]);

        $pagada->update(['estado' => EstadoPublicidad::Publicada]);

        $this->assertSame(EstadoPublicidad::Publicada, $pagada->fresh()->estado);
        $this->assertNotNull($pagada->fresh()->aprobado_at);

        $pagadaDesdePanel = Publicidad::factory()->create(['estado' => EstadoPublicidad::Pagada]);

        Livewire::test(ListPublicidades::class)
            ->callAction(TestAction::make('publicar')->table($pagadaDesdePanel));

        $this->assertSame(EstadoPublicidad::Publicada, $pagadaDesdePanel->fresh()->estado);
        $this->assertNotNull($pagadaDesdePanel->fresh()->aprobado_at);
    }

    public function test_marcar_pagada_no_publica_automaticamente(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $publicidad = Publicidad::factory()->create([
            'estado' => EstadoPublicidad::PendientePago,
        ]);

        Livewire::test(ListPublicidades::class)
            ->callAction(TestAction::make('marcar_pagada')->table($publicidad));

        $publicidad = $publicidad->fresh();

        $this->assertSame(EstadoPublicidad::Pagada, $publicidad->estado);
        $this->assertFalse($publicidad->visiblePublicamente());
        $this->get('/')->assertSuccessful()->assertDontSee($publicidad->anunciante);
    }

    public function test_un_usuario_sin_permiso_no_puede_publicar_aunque_este_pagada(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $publicidad = Publicidad::factory()->create([
            'estado' => EstadoPublicidad::Pagada,
        ]);

        try {
            $publicidad->update(['estado' => EstadoPublicidad::Publicada]);
        } catch (ValidationException) {
            // La proteccion vive en el modelo, no solo en el boton.
        }

        $this->assertSame(EstadoPublicidad::Pagada, $publicidad->fresh()->estado);
        $this->assertNull($publicidad->fresh()->aprobado_at);
    }

    public function test_la_direccion_crea_y_edita_publicidad_desde_el_panel(): void
    {
        Storage::fake('public');
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreatePublicidad::class)
            ->fillForm($this->datosFormulario([
                'imagen' => UploadedFile::fake()->image('pauta.png', 1200, 628),
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $publicidad = Publicidad::firstOrFail();
        $this->assertStringStartsWith('publicidades/', $publicidad->imagen);

        Livewire::test(EditPublicidad::class, ['record' => $publicidad->getRouteKey()])
            ->fillForm(['anunciante' => 'Marca actualizada'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('publicidades', ['anunciante' => 'Marca actualizada']);
    }

    public function test_un_asociado_no_entra_al_admin_de_publicidad(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_ASOCIADO));

        $this->get('/admin/publicidad')->assertForbidden();
    }

    public function test_publicar_sin_imagen_queda_bloqueado(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $publicidad = Publicidad::factory()->create([
            'estado' => EstadoPublicidad::Pagada,
            'imagen' => null,
        ]);

        try {
            Livewire::test(ListPublicidades::class)
                ->callAction(TestAction::make('publicar')->table($publicidad));
        } catch (\Throwable) {
            // Filament captura la validacion en algunas versiones y en otras
            // la expone como excepcion. El contrato es que no publique.
        }

        $this->assertNotSame(EstadoPublicidad::Publicada, $publicidad->fresh()->estado);
        $this->assertNull($publicidad->fresh()->aprobado_at);
    }

    public function test_una_url_javascript_es_rechazada(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreatePublicidad::class)
            ->fillForm($this->datosFormulario(['url_destino' => 'javascript:alert(1)']))
            ->call('create')
            ->assertHasFormErrors(['url_destino']);
    }

    public function test_un_archivo_que_no_es_imagen_es_rechazado(): void
    {
        Storage::fake('public');
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreatePublicidad::class)
            ->fillForm($this->datosFormulario([
                'imagen' => UploadedFile::fake()->create('pauta.pdf', 50, 'application/pdf'),
            ]))
            ->call('create')
            ->assertHasFormErrors(['imagen']);
    }

    public function test_fechas_y_valor_invalidos_son_rechazados(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreatePublicidad::class)
            ->fillForm($this->datosFormulario([
                'fecha_inicio' => now()->addWeek()->format('Y-m-d H:i:s'),
                'fecha_fin' => now()->addDay()->format('Y-m-d H:i:s'),
                'valor' => -1,
            ]))
            ->call('create')
            ->assertHasFormErrors(['fecha_fin', 'valor']);
    }

    public function test_inicio_y_directorio_cargan_sin_publicidad(): void
    {
        $this->get('/')->assertSuccessful()->assertDontSee('Campaña vigente de ASOBARES Capitulo Quindio');
        $this->get('/directorio')->assertSuccessful()->assertDontSee('Campaña vigente de ASOBARES Capitulo Quindio');
    }

    public function test_inicio_y_directorio_muestran_solo_la_pauta_correspondiente_sin_romper_filtros(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->crearPublicidadPublicada([
            'anunciante' => 'Pauta Inicio',
            'nombre_comercial' => null,
            'ubicacion' => UbicacionPublicidad::Inicio,
        ]);
        $this->crearPublicidadPublicada([
            'anunciante' => 'Pauta Directorio',
            'nombre_comercial' => null,
            'ubicacion' => UbicacionPublicidad::Directorio,
        ]);

        $this->get('/')
            ->assertSuccessful()
            ->assertSee('Pauta Inicio')
            ->assertDontSee('Pauta Directorio');

        $this->get('/directorio?q=no-existe')
            ->assertSuccessful()
            ->assertSee('Pauta Directorio')
            ->assertSee('No encontramos establecimientos con ese filtro')
            ->assertDontSee('Pauta Inicio');
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    private function crearPublicidadPublicada(array $sobrescribir = []): Publicidad
    {
        $publicidad = Publicidad::factory()->create(array_merge([
            'estado' => EstadoPublicidad::Pagada,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
        ], $sobrescribir));

        $publicidad->update(['estado' => EstadoPublicidad::Publicada]);

        return $publicidad->fresh();
    }

    private function datosFormulario(array $sobrescribir = []): array
    {
        return array_merge([
            'anunciante' => 'Marca Aliada',
            'nombre_comercial' => 'Marca Aliada Quindio',
            'contacto' => 'Laura Perez',
            'email' => 'pauta@example.com',
            'telefono' => '3101234567',
            'imagen' => null,
            'url_destino' => 'https://example.com/campana',
            'ubicacion' => UbicacionPublicidad::Inicio->value,
            'fecha_inicio' => now()->addDay()->format('Y-m-d H:i:s'),
            'fecha_fin' => now()->addDays(8)->format('Y-m-d H:i:s'),
            'valor' => 350000,
            'estado' => EstadoPublicidad::Borrador->value,
            'notas' => 'Primera pauta interna.',
        ], $sobrescribir);
    }
}

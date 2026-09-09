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
        Publicidad::factory()->vencida()->enInicio()->create(['anunciante' => 'Vencida']);
        Publicidad::factory()->enInicio()->create(['anunciante' => 'Borrador']);
        Publicidad::factory()->publicadaVigente()->enDirectorio()->create(['anunciante' => 'Otra ubicacion']);
        $segunda = Publicidad::factory()->publicadaVigente()->enInicio()->create([
            'anunciante' => 'Segunda',
            'fecha_inicio' => now()->subHours(2),
        ]);
        $primera = Publicidad::factory()->publicadaVigente()->enInicio()->create([
            'anunciante' => 'Primera',
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
            'estado' => EstadoPublicidad::PendienteAprobacion,
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
        Publicidad::factory()->publicadaVigente()->enInicio()->create([
            'anunciante' => 'Pauta Inicio',
            'nombre_comercial' => null,
        ]);
        Publicidad::factory()->publicadaVigente()->enDirectorio()->create([
            'anunciante' => 'Pauta Directorio',
            'nombre_comercial' => null,
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

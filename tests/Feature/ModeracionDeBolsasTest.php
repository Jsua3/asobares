<?php

namespace Tests\Feature;

use App\Enums\EstadoDeGestion;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Artistas\Pages\ListArtistas;
use App\Filament\Resources\Postulaciones\Pages\ListPostulaciones;
use App\Filament\Resources\Proveedors\Pages\ListProveedors;
use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Support\AccionesDeAprobacion;
use App\Mail\FichaDeBolsaPublicada;
use App\Mail\VacanteAprobada;
use App\Mail\VacanteDevuelta;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Postulacion;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ModeracionDeBolsasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_el_equipo_ve_la_bandeja_de_postulaciones(): void
    {
        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $this->actingAs($this->crearUsuario($rol))->get('/admin/postulaciones')->assertSuccessful();
        }
    }

    public function test_nadie_crea_una_postulacion_desde_el_panel(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->get('/admin/postulaciones/create')->assertNotFound();
    }

    public function test_la_secretaria_cambia_el_estado_de_gestion_de_una_postulacion(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $postulacion = Postulacion::factory()->for(Vacante::factory()->publicado())->create();

        Livewire::test(ListPostulaciones::class)
            ->callAction(TestAction::make('gestionar')->table($postulacion), data: [
                'estado' => EstadoDeGestion::Contactado->value,
            ])
            ->assertHasNoErrors();

        $this->assertSame(EstadoDeGestion::Contactado, $postulacion->fresh()->estado);
    }

    public function test_la_secretaria_aprueba_una_vacante_y_avisa_al_establecimiento(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('aprobar')->table($vacante))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        Mail::assertSent(VacanteAprobada::class, 1);
    }

    public function test_devolver_con_motivo_lo_guarda_y_avisa_al_establecimiento(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('devolver')->table($vacante), data: [
                'motivo_devolucion' => 'Falta el horario del turno.',
            ])
            ->assertHasNoErrors();

        $devuelta = $vacante->fresh();

        $this->assertSame(EstadoPublicacion::Borrador, $devuelta->estado);
        $this->assertSame('Falta el horario del turno.', $devuelta->motivo_devolucion);
        Mail::assertSent(VacanteDevuelta::class, 1);
    }

    public function test_devolver_sin_motivo_no_cambia_nada(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('devolver')->table($vacante), data: [
                'motivo_devolucion' => '',
            ])
            ->assertHasFormErrors(['motivo_devolucion' => 'required']);

        $sinCambios = $vacante->fresh();

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $sinCambios->estado);
        $this->assertNull($sinCambios->motivo_devolucion);
        Mail::assertNotSent(VacanteDevuelta::class);
    }

    public function test_aprobar_limpia_el_motivo_de_la_devolucion_anterior(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $vacante = Vacante::factory()->pendiente()->create(['motivo_devolucion' => 'Un motivo viejo.']);

        Livewire::test(ListVacantes::class)->callAction(TestAction::make('aprobar')->table($vacante));

        $this->assertNull($vacante->fresh()->motivo_devolucion);
    }

    /**
     * `route()` no descarta un modelo de sobra cuando la ruta pública no
     * declara parámetros: lo cuelga como query string. Este contrato exige
     * que la función que arma la URL de `proveedores.index` —que no toma
     * parámetros— nunca reciba el registro, así el enlace del correo queda
     * limpio.
     */
    public function test_aprobar_ficha_de_bolsa_arma_la_url_publica_sin_parametros_colgantes(): void
    {
        Mail::fake();

        $proveedor = Proveedor::factory()->create(['correo' => 'contacto@proveedor.test']);

        $accion = AccionesDeAprobacion::aprobarFichaDeBolsa(fn (): string => route('proveedores.index'));
        ($accion->getActionFunction())($proveedor);

        Mail::assertSent(
            FichaDeBolsaPublicada::class,
            fn (FichaDeBolsaPublicada $correo): bool => $correo->urlPublica === route('proveedores.index')
                && ! str_contains($correo->urlPublica, '?')
                && $correo->nombreDeLaFicha === $proveedor->nombre
        );
    }

    public function test_el_panel_ya_no_crea_ni_edita_vacantes(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->get('/admin/vacantes/create')->assertNotFound();
        $this->get('/admin/vacantes/'.Vacante::factory()->create()->id.'/edit')->assertNotFound();
    }

    public function test_el_listado_de_vacantes_sigue_abierto_para_moderar(): void
    {
        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $this->actingAs($this->crearUsuario($rol))->get('/admin/vacantes')->assertSuccessful();
        }
    }

    public function test_aprobar_una_ficha_de_artista_avisa_al_solicitante(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->callAction(TestAction::make('aprobar')->table($artista))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_una_ficha_sin_correo_no_intenta_escribir(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListArtistas::class)
            ->callAction(TestAction::make('aprobar')->table($artista));

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertNothingSent();
    }

    // --- Aprobación en lote (B1: el lote produce el mismo efecto que la fila) ---

    public function test_aprobar_en_lote_de_vacantes_publica_limpia_el_motivo_y_avisa(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create(['motivo_devolucion' => 'Un motivo viejo.']);

        Livewire::test(ListVacantes::class)
            ->selectTableRecords([$vacante->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $publicada = $vacante->fresh();
        $this->assertSame(EstadoPublicacion::Publicado, $publicada->estado);
        $this->assertNull($publicada->motivo_devolucion, 'El lote no puede dejar la tarjeta contradictoria: publicada y devuelta a la vez.');
        Mail::assertSent(VacanteAprobada::class, 1);
    }

    public function test_aprobar_en_lote_de_vacantes_no_revienta_si_el_asociado_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => null]);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->selectTableRecords([$vacante->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_aprobar_en_lote_de_artistas_publica_y_avisa_por_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->selectTableRecords([$artista->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_en_lote_de_artistas_no_revienta_si_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListArtistas::class)
            ->selectTableRecords([$artista->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_aprobar_en_lote_de_proveedores_publica_y_avisa_por_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $proveedor = Proveedor::factory()->pendiente()->create(['correo' => 'contacto@proveedor.test']);

        Livewire::test(ListProveedors::class)
            ->selectTableRecords([$proveedor->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $proveedor->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_en_lote_de_proveedores_no_revienta_si_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $proveedor = Proveedor::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListProveedors::class)
            ->selectTableRecords([$proveedor->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $proveedor->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_la_bandeja_de_aspirantes_sigue_en_pie(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Aspirante::factory()->create(['nombre' => 'Duván Marín']);

        $this->get('/admin/aspirantes')->assertSuccessful()->assertSee('Duván Marín');
    }
}

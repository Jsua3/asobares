<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ejercita la pantalla de login de verdad.
 *
 * Las demás pruebas del panel entran con `actingAs()`, que se salta el
 * formulario: por eso no detectaron que faltaba un contrato de MFA en el
 * modelo User y el login reventaba con un 500.
 */
class LoginDelPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    /**
     * Sin segundo factor dado de alta a propósito: así el paso de credenciales
     * termina en sesión iniciada y estas pruebas siguen midiendo lo suyo —que
     * la contraseña se acepta o se rechaza—. Que un factor ya configurado
     * corta ese paso lo cubre
     * `test_con_segundo_factor_las_credenciales_solas_no_abren_sesion`.
     */
    private function crearUsuario(string $rol, string $clave = 'Asobares2026*'): User
    {
        $usuario = User::factory()->sinSegundoFactor()->create(['password' => Hash::make($clave)]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_pantalla_de_login_carga(): void
    {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSee('ASOBARES');
    }

    public function test_la_direccion_entra_con_sus_credenciales(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $direccion->email,
                'password' => 'Asobares2026*',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($direccion);
    }

    public function test_la_secretaria_entra_con_sus_credenciales(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $secretaria->email,
                'password' => 'Asobares2026*',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($secretaria);
    }

    /**
     * Lo que compra el segundo factor: quien robe la contraseña no entra con
     * ella sola. Antes bastaba, porque nadie estaba obligado a configurarlo.
     */
    public function test_con_segundo_factor_las_credenciales_solas_no_abren_sesion(): void
    {
        $direccion = User::factory()->create(['password' => Hash::make('Asobares2026*')]);
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $direccion->email,
                'password' => 'Asobares2026*',
            ])
            ->call('authenticate');

        // La contraseña correcta sólo supera el primer paso: la sesión no se
        // abre hasta resolver el reto del segundo factor.
        $this->assertGuest();
    }

    public function test_una_contrasena_equivocada_no_deja_entrar(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $direccion->email,
                'password' => 'la-que-no-es',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_el_usuario_implementa_los_tres_contratos_de_mfa_del_panel(): void
    {
        // El panel declara app + correo como segundos factores. Si el modelo
        // deja de cumplir alguno de los contratos, el login revienta en
        // tiempo de ejecución, no al compilar: por eso se comprueba aquí.
        $usuario = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        $this->assertInstanceOf(HasAppAuthentication::class, $usuario);
        $this->assertInstanceOf(HasAppAuthenticationRecovery::class, $usuario);
        $this->assertInstanceOf(HasEmailAuthentication::class, $usuario);
    }

    public function test_el_segundo_factor_por_correo_arranca_apagado_y_se_puede_activar(): void
    {
        $usuario = User::factory()->sinSegundoFactor()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->assertFalse($usuario->hasEmailAuthentication(), 'Nadie debería tener MFA activo sin haberlo configurado.');

        $usuario->toggleEmailAuthentication(true);

        $this->assertTrue($usuario->fresh()->hasEmailAuthentication());
    }

    /**
     * La prueba que demuestra que la exigencia funciona: una cuenta sin
     * segundo factor no llega al escritorio, se queda en el alta.
     */
    public function test_una_cuenta_sin_segundo_factor_no_llega_al_escritorio(): void
    {
        $reciente = User::factory()->sinSegundoFactor()->create();
        $reciente->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($reciente->fresh())
            ->get('/admin')
            ->assertRedirect(Filament::getPanel('admin')->getSetUpRequiredMultiFactorAuthenticationUrl());
    }

    /**
     * Registrar los proveedores sólo los OFRECÍA: como ambos son opcionales
     * por usuario, quien nunca entraba a su perfil a activarlos seguía
     * entrando con la contraseña sola, y este panel gobierna los pagos, la
     * cartera y los datos personales de los afiliados.
     */
    public function test_el_segundo_factor_es_obligatorio_en_el_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue($panel->hasMultiFactorAuthentication());
        $this->assertTrue(
            $panel->isMultiFactorAuthenticationRequired(),
            'Sin esto el segundo factor es una sugerencia, no una defensa.'
        );
    }

    /**
     * Obligatorio no puede significar «quien no lo tenga se queda fuera»: la
     * dirección y la secretaría existentes no llevan factor configurado. El
     * panel los manda a darlo de alta, no les cierra la puerta.
     */
    public function test_quien_no_tiene_factor_pasa_por_el_alta_y_no_por_un_portazo(): void
    {
        $this->assertNotNull(
            Filament::getPanel('admin')->getSetUpRequiredMultiFactorAuthenticationRouteAction(),
            'Sin acción de alta, exigir el factor dejaría al equipo sin entrada.'
        );
    }
}

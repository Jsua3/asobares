<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Auth\Pages\Login;
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

    private function crearUsuario(string $rol, string $clave = 'Asobares2026*'): User
    {
        $usuario = User::factory()->create(['password' => Hash::make($clave)]);
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
        $usuario = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        $this->assertFalse($usuario->hasEmailAuthentication(), 'Nadie debería tener MFA activo sin haberlo configurado.');

        $usuario->toggleEmailAuthentication(true);

        $this->assertTrue($usuario->fresh()->hasEmailAuthentication());
    }
}

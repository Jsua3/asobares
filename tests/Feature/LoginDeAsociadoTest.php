<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * El login de /mi-cuenta, que hasta ahora sólo se ejercitaba de refilón.
 *
 * Las tres pruebas de abajo cubren lo que la auditoría encontró abierto: que
 * la respuesta delataba una contraseña de administrador correcta, que el
 * límite de intentos era por IP y no por cuenta, y que el destino posterior
 * al login salía de la sesión sin comprobar el host.
 */
class LoginDeAsociadoTest extends TestCase
{
    use RefreshDatabase;

    private const string CLAVE = 'Asobares2026*';

    /** El único mensaje que puede salir de un intento fallido, sea cual sea el motivo. */
    private const string MENSAJE_ESPERADO = 'Ese correo y esa contraseña no coinciden.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        RateLimiter::clear('entrar-asociado|admin@asobares.test');
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create([
            'email' => $rol === User::ROL_ASOCIADO ? 'socio@asobares.test' : 'admin@asobares.test',
            'password' => Hash::make(self::CLAVE),
            'asociado_id' => $rol === User::ROL_ASOCIADO ? Asociado::factory()->publicado()->create()->id : null,
        ]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    private function intentar(string $email, string $clave, string $ip = '10.0.0.1'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->from(route('mi-cuenta.entrar'))
            ->post(route('mi-cuenta.entrar.post'), ['email' => $email, 'password' => $clave]);
    }

    public function test_un_asociado_con_sus_datos_correctos_entra(): void
    {
        $socio = $this->crearUsuario(User::ROL_ASOCIADO);

        $this->intentar($socio->email, self::CLAVE)
            ->assertRedirect(route('mi-cuenta.index'));

        $this->assertAuthenticatedAs($socio);
    }

    /**
     * El fondo del asunto: la respuesta a una contraseña de administrador
     * CORRECTA tiene que ser indistinguible de la respuesta a una incorrecta.
     * Si difieren, /mi-cuenta se vuelve un oráculo para adivinar credenciales
     * del panel sin pasar por /admin ni por el segundo factor.
     */
    public function test_la_contrasena_correcta_de_un_administrador_no_se_distingue_de_una_incorrecta(): void
    {
        $admin = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        // Cada aserción va pegada a su petición: las dos respuestas comparten
        // el store de sesión, así que compararlas al final no diría nada.
        $conLaBuena = $this->intentar($admin->email, self::CLAVE)
            ->assertSessionHasErrors(['email' => self::MENSAJE_ESPERADO]);

        $conLaMala = $this->intentar($admin->email, 'esta-no-es')
            ->assertSessionHasErrors(['email' => self::MENSAJE_ESPERADO]);

        $this->assertGuest();
        $this->assertSame($conLaMala->getStatusCode(), $conLaBuena->getStatusCode());
    }

    /**
     * El límite por IP no sirve contra quien rota direcciones. El contador
     * tiene que colgar de la cuenta atacada.
     */
    public function test_los_intentos_se_cuentan_por_cuenta_aunque_cambie_la_ip(): void
    {
        $socio = $this->crearUsuario(User::ROL_ASOCIADO);

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->intentar($socio->email, 'clave-equivocada', "10.0.0.{$intento}")
                ->assertSessionHasErrors('email');
        }

        // Sexto intento, IP nueva y contraseña BUENA: el bloqueo de la cuenta
        // manda por encima de que las credenciales cuadren.
        $this->intentar($socio->email, self::CLAVE, '10.0.0.99')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_bloqueo_de_una_cuenta_no_alcanza_a_las_demas(): void
    {
        $socio = $this->crearUsuario(User::ROL_ASOCIADO);
        $otro = User::factory()->create([
            'email' => 'otro@asobares.test',
            'password' => Hash::make(self::CLAVE),
            'asociado_id' => Asociado::factory()->publicado()->create()->id,
        ]);
        $otro->syncRoles([User::ROL_ASOCIADO]);

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->intentar($socio->email, 'clave-equivocada', "10.0.1.{$intento}");
        }

        $this->intentar($otro->email, self::CLAVE, '10.0.2.1')
            ->assertRedirect(route('mi-cuenta.index'));

        $this->assertAuthenticatedAs($otro->fresh());
    }

    /**
     * `intended()` lee un destino guardado en sesión que puede haber llegado
     * por la cabecera Referer. Un host ajeno no puede salir en el Location
     * justo después de un login legítimo.
     */
    public function test_no_redirige_a_un_host_ajeno_despues_de_entrar(): void
    {
        $socio = $this->crearUsuario(User::ROL_ASOCIADO);

        $this->withSession(['url.intended' => 'https://sitio-falso.example/cobro'])
            ->from(route('mi-cuenta.entrar'))
            ->post(route('mi-cuenta.entrar.post'), ['email' => $socio->email, 'password' => self::CLAVE])
            ->assertRedirect(route('mi-cuenta.index'));
    }
}

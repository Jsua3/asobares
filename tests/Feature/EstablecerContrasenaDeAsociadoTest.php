<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EstablecerContrasenaDeAsociadoTest extends TestCase
{
    use RefreshDatabase;

    private const string CLAVE = 'Cordillera-Quindio-2026!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->setLocale('es');
        $this->app->setFallbackLocale('es');
    }

    private function socio(): User
    {
        $usuario = User::factory()->create(['email' => 'socio@asobares.test']);
        $usuario->contrasena_provisional = true;
        $usuario->save();

        return $usuario;
    }

    public function test_token_valido_establece_contrasena_y_termina_el_acceso_provisional(): void
    {
        $usuario = $this->socio();
        $token = Password::createToken($usuario);

        $this->post(route('mi-cuenta.password.update'), [
            'token' => $token,
            'email' => $usuario->email,
            'password' => self::CLAVE,
            'password_confirmation' => self::CLAVE,
        ])->assertRedirect(route('mi-cuenta.entrar'))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(self::CLAVE, $usuario->fresh()->password));
        $this->assertFalse($usuario->fresh()->contrasena_provisional);
    }

    public function test_enlace_invalido_no_cambia_la_clave_y_no_revela_si_existe_cuenta(): void
    {
        $usuario = $this->socio();
        $anterior = $usuario->password;

        foreach ([$usuario->email, 'nadie@asobares.test'] as $correo) {
            $this->post(route('mi-cuenta.password.update'), [
                'token' => 'token-inventado',
                'email' => $correo,
                'password' => self::CLAVE,
                'password_confirmation' => self::CLAVE,
            ])->assertSessionHasErrors('email');

            $this->assertSame(
                'Este enlace ya no es válido para ese correo. Si ya creaste tu contraseña, entra a Mi Cuenta; si no, solicita a ASOBARES un enlace nuevo.',
                session('errors')->first('email'),
            );
        }

        $this->assertSame($anterior, $usuario->fresh()->password);
        $this->assertTrue($usuario->contrasena_provisional);
    }

    public function test_enlace_vencido_responde_con_mensaje_legible(): void
    {
        $usuario = $this->socio();
        $token = Password::createToken($usuario);
        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();
        $url = route('mi-cuenta.password.reset', ['token' => $token, 'email' => $usuario->email]);

        $this->from($url)->post(route('mi-cuenta.password.update'), [
            'token' => $token,
            'email' => $usuario->email,
            'password' => self::CLAVE,
            'password_confirmation' => self::CLAVE,
        ])->assertRedirect($url)->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Este enlace ya no es válido', session('errors')->first('email'));
        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }

    public function test_el_error_de_token_se_muestra_en_el_formulario_sin_claves_crudas(): void
    {
        $usuario = $this->socio();
        $url = route('mi-cuenta.password.reset', ['token' => 'token-inventado', 'email' => $usuario->email]);

        $respuesta = $this->from($url)->post(route('mi-cuenta.password.update'), [
            'token' => 'token-inventado',
            'email' => $usuario->email,
            'password' => self::CLAVE,
            'password_confirmation' => self::CLAVE,
        ])->assertRedirect($url)->assertSessionHasErrors('email');

        $cookie = collect($respuesta->headers->getCookies())
            ->first(fn ($cookie): bool => $cookie->getName() === config('session.cookie'));
        $this->assertNotNull($cookie);
        $pagina = $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue())->get($url)->assertOk();
        $pagina->assertSeeText('Este enlace ya no es válido para ese correo.');
        $pagina->assertDontSee('passwords.token');
        $pagina->assertDontSee('validation.');
    }

    /** @return array<string, array{string, string, string}> */
    public static function clavesInvalidas(): array
    {
        return [
            'confirmación' => [self::CLAVE, 'Otra-Clave-Quindio-2026!', 'Las dos contraseñas no coinciden.'],
            'sin símbolos' => ['CordilleraQuindio2026', 'CordilleraQuindio2026', 'La contraseña necesita al menos un símbolo.'],
            'sin números' => ['Cordillera-Quindio!', 'Cordillera-Quindio!', 'La contraseña necesita al menos un número.'],
            'sin mayúsculas' => ['cordillera-quindio-2026!', 'cordillera-quindio-2026!', 'La contraseña necesita al menos una mayúscula y una minúscula.'],
        ];
    }

    #[DataProvider('clavesInvalidas')]
    public function test_los_errores_de_contrasena_son_espanoles(string $clave, string $confirmacion, string $mensaje): void
    {
        $usuario = $this->socio();
        $token = Password::createToken($usuario);

        $this->post(route('mi-cuenta.password.update'), [
            'token' => $token,
            'email' => $usuario->email,
            'password' => $clave,
            'password_confirmation' => $confirmacion,
        ])->assertSessionHasErrors('password');

        $this->assertSame($mensaje, session('errors')->first('password'));
    }
}

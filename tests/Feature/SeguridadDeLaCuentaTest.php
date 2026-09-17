<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * El titular cambia su contraseña desde /mi-cuenta/seguridad: es la única
 * puerta que apaga la marca de provisional.
 *
 * SOBRE `forgetGuards()`: como en `InvalidacionDeSesionTest`, el contenedor no
 * se reconstruye entre peticiones de una prueba y el guard conservaría el
 * usuario con el hash viejo. `forgetGuards()` reproduce lo que en producción
 * hace cada petición nueva; sin él, las pruebas de sesión darían falsos verdes.
 */
class SeguridadDeLaCuentaTest extends TestCase
{
    use RefreshDatabase;

    private const string ACTUAL = 'Provisional-Quindio-2026!';

    private const string NUEVA = 'Cordillera-Propia-2026#';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function afiliado(bool $provisional = true): User
    {
        $usuario = User::factory()->create([
            'email' => 'duena@merlin.test',
            'password' => self::ACTUAL,
            'asociado_id' => Asociado::factory()->publicado()->create()->id,
        ]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        return $usuario->fresh();
    }

    /** Por la puerta de verdad: el hash solo queda en la sesión si la petición pasa por el middleware. */
    private function entrar(User $usuario): void
    {
        $this->post(route('mi-cuenta.entrar.post'), [
            'email' => $usuario->email,
            'password' => self::ACTUAL,
        ])->assertRedirect(route('mi-cuenta.index'));

        $this->get(route('mi-cuenta.index'))->assertOk();
    }

    /** @return array<string, string> */
    private function datosValidos(): array
    {
        return [
            'current_password' => self::ACTUAL,
            'password' => self::NUEVA,
            'password_confirmation' => self::NUEVA,
        ];
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    public function test_un_invitado_va_a_la_puerta_de_mi_cuenta(): void
    {
        $this->get(route('mi-cuenta.seguridad'))->assertRedirect(route('mi-cuenta.entrar'));
    }

    public function test_el_equipo_del_gremio_no_usa_esta_pantalla(): void
    {
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($direccion)->get(route('mi-cuenta.seguridad'))->assertForbidden();
    }

    /**
     * Los nombres no son de estilo: son los que Laravel no devuelve a la
     * sesión al fallar la validación (`$dontFlash`).
     */
    public function test_la_pantalla_pide_la_actual_la_nueva_y_su_confirmacion(): void
    {
        $html = $this->actingAs($this->afiliado())->get(route('mi-cuenta.seguridad'))->assertOk()->getContent();
        $xpath = $this->xpathDe($html);

        $formulario = '//form[@method="POST"][@action="'.route('mi-cuenta.seguridad.actualizar').'"]';

        $this->assertSame(1, $xpath->query($formulario)->length);
        $this->assertSame(1, $xpath->query($formulario.'//input[@name="_method"][@value="PUT"]')->length);

        foreach (['current_password', 'password', 'password_confirmation'] as $campo) {
            $this->assertSame(1, $xpath->query($formulario.'//input[@type="password"][@name="'.$campo.'"]')->length, "Falta el campo {$campo}.");
        }
    }

    public function test_con_la_marca_explica_por_que_hay_que_cambiarla(): void
    {
        $this->actingAs($this->afiliado(provisional: true))
            ->get(route('mi-cuenta.seguridad'))
            ->assertSeeText('Entraste con la contraseña provisional');
    }

    public function test_sin_la_marca_no_habla_de_contrasena_provisional(): void
    {
        $this->actingAs($this->afiliado(provisional: false))
            ->get(route('mi-cuenta.seguridad'))
            ->assertDontSeeText('Entraste con la contraseña provisional');
    }

    /** @return array<string, array{array<string, string>, string, string}> */
    public static function cambiosQueNoSirven(): array
    {
        return [
            'actual equivocada' => [['current_password' => 'Otra-Clave-2026!', 'password' => self::NUEVA, 'password_confirmation' => self::NUEVA], 'current_password', 'Esa no es tu contraseña actual.'],
            'confirmación distinta' => [['current_password' => self::ACTUAL, 'password' => self::NUEVA, 'password_confirmation' => 'Distinta-2026#xy'], 'password', 'La confirmación no coincide con la contraseña nueva.'],
            'sin símbolo' => [['current_password' => self::ACTUAL, 'password' => 'CordilleraPropia2026', 'password_confirmation' => 'CordilleraPropia2026'], 'password', 'La contraseña nueva necesita al menos un símbolo.'],
            'sin mayúscula' => [['current_password' => self::ACTUAL, 'password' => 'cordillera-propia-2026#', 'password_confirmation' => 'cordillera-propia-2026#'], 'password', 'La contraseña nueva necesita al menos una mayúscula y una minúscula.'],
            'sin número' => [['current_password' => self::ACTUAL, 'password' => 'Cordillera-Propia-Quindio#', 'password_confirmation' => 'Cordillera-Propia-Quindio#'], 'password', 'La contraseña nueva necesita al menos un número.'],
            'corta' => [['current_password' => self::ACTUAL, 'password' => 'Corta-1#a', 'password_confirmation' => 'Corta-1#a'], 'password', 'La contraseña nueva necesita al menos 12 caracteres.'],
            'igual a la actual' => [['current_password' => self::ACTUAL, 'password' => self::ACTUAL, 'password_confirmation' => self::ACTUAL], 'password', 'La contraseña nueva tiene que ser distinta de la actual.'],
        ];
    }

    /** @param  array<string, string>  $datos */
    #[DataProvider('cambiosQueNoSirven')]
    public function test_un_cambio_que_no_sirve_se_explica_en_espanol_y_no_cambia_nada(array $datos, string $campo, string $mensaje): void
    {
        $usuario = $this->afiliado();
        $hashAntes = $usuario->password;

        $this->actingAs($usuario)
            ->from(route('mi-cuenta.seguridad'))
            ->put(route('mi-cuenta.seguridad.actualizar'), $datos)
            ->assertRedirect(route('mi-cuenta.seguridad'))
            ->assertSessionHasErrors([$campo => $mensaje]);

        $usuario->refresh();

        $this->assertSame($hashAntes, $usuario->password);
        $this->assertTrue($usuario->contrasena_provisional);
    }

    public function test_un_error_no_devuelve_ninguna_contrasena_a_la_sesion(): void
    {
        $this->actingAs($this->afiliado())
            ->from(route('mi-cuenta.seguridad'))
            ->put(route('mi-cuenta.seguridad.actualizar'), [
                'current_password' => 'Otra-Clave-2026!',
                'password' => self::NUEVA,
                'password_confirmation' => self::NUEVA,
            ]);

        $viajaron = array_intersect_key(
            session()->getOldInput(),
            array_flip(['current_password', 'password', 'password_confirmation'])
        );

        $this->assertSame([], $viajaron);
    }

    public function test_cambiarla_guarda_la_nueva_y_apaga_la_marca(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos())
            ->assertRedirect(route('mi-cuenta.index'))
            ->assertSessionHas('exito');

        $usuario->refresh();

        $this->assertTrue(Hash::check(self::NUEVA, $usuario->password));
        $this->assertFalse($usuario->contrasena_provisional);
    }

    public function test_la_bitacora_anota_el_cambio_sin_la_contrasena(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());

        $registro = Activity::query()
            ->where('log_name', 'sesion')
            ->where('description', 'cambió su contraseña')
            ->first();

        $this->assertNotNull($registro);
        $this->assertSame($usuario->id, (int) $registro->causer_id);
        $this->assertStringNotContainsString(self::NUEVA, (string) json_encode($registro->properties));
        $this->assertStringNotContainsString(self::ACTUAL, (string) json_encode($registro->properties));
    }

    /** La contraprueba de la siguiente: quien la cambió sigue dentro. */
    public function test_quien_la_cambio_sigue_dentro(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());
        $this->app['auth']->forgetGuards();

        $this->get(route('mi-cuenta.index'))->assertOk();
        $this->assertAuthenticatedAs($usuario->fresh());
    }

    /**
     * Otra sesión abierta con la contraseña vieja es una sesión que guarda el
     * hash viejo: se le devuelve ese hash y tiene que caer. Es el caso de
     * quien entró con la genérica antes que el dueño.
     */
    public function test_una_sesion_abierta_con_la_contrasena_vieja_se_cierra(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);
        $hashViejo = session('password_hash_web');
        $this->assertNotNull($hashViejo);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());
        $this->app['auth']->forgetGuards();

        $this->withSession(['password_hash_web' => $hashViejo])
            ->get(route('mi-cuenta.index'))
            ->assertRedirect(route('mi-cuenta.entrar'));

        $this->assertGuest();
    }
}

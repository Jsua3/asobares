<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Rotar la contraseña tiene que echar a quien ya estuviera dentro.
 *
 * `bootstrap/app.php` añade `AuthenticateSession` al grupo `web`: el middleware
 * guarda en la sesión el hash de la contraseña con la que se entró y lo compara
 * en cada petición. Si la dirección le cambia la clave a un afiliado desde el
 * panel —el único camino que hoy existe para cambiarla, en `UserForm`—, la
 * sesión que alguien tuviera abierta con la clave vieja deja de valer.
 *
 * Sin estas dos pruebas el middleware era un cambio de sesión sin cobertura.
 *
 * SOBRE `forgetGuards()`, que no es un truco para que pase: en una prueba HTTP
 * el contenedor NO se reconstruye entre `$this->get()` y `$this->get()`, así
 * que `SessionGuard` conserva en memoria el modelo que resolvió en la primera
 * petición —con el hash viejo— y `AuthenticateSession` compara ese hash contra
 * sí mismo. En producción cada petición levanta un proceso nuevo y el guard
 * relee al usuario de la base. `forgetGuards()` es lo que reproduce eso; sin
 * él, la prueba pasaría en verde con el middleware quitado, que es exactamente
 * el falso verde que este proyecto se ha propuesto no volver a escribir.
 */
class InvalidacionDeSesionTest extends TestCase
{
    use RefreshDatabase;

    private const string CLAVE = 'Asobares2026*Quindio';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearAfiliado(): User
    {
        $usuario = User::factory()->create([
            'email' => 'socio@asobares.test',
            'password' => Hash::make(self::CLAVE),
            'asociado_id' => Asociado::factory()->publicado()->create()->id,
        ]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    /**
     * Entra por la puerta de verdad, no con `actingAs`: el hash sólo queda
     * guardado en la sesión cuando la petición pasa por el middleware con el
     * usuario ya autenticado.
     */
    private function entrar(User $socio): void
    {
        $this->post(route('mi-cuenta.entrar.post'), [
            'email' => $socio->email,
            'password' => self::CLAVE,
        ])->assertRedirect(route('mi-cuenta.index'));

        $this->get(route('mi-cuenta.index'))->assertOk();
    }

    public function test_rotar_la_contrasena_cierra_la_sesion_que_ya_estaba_abierta(): void
    {
        $socio = $this->crearAfiliado();
        $this->entrar($socio);

        // Lo que hace la dirección desde el panel: `UserForm` escribe `password`
        // en el mismo modelo, y el cast `hashed` lo convierte.
        $socio->update(['password' => 'ClaveRotada2026*Quindio']);
        $this->app['auth']->forgetGuards();

        // El destino es el login del afiliado y no el de Filament: lo decide
        // `redirectGuestsTo` en bootstrap/app.php.
        $this->get(route('mi-cuenta.index'))
            ->assertRedirect(route('mi-cuenta.entrar'));

        $this->assertGuest();
    }

    /**
     * La contraprueba, en las mismas condiciones que la de arriba —incluido el
     * `forgetGuards()`—: si esta se pusiera roja, la otra estaría midiendo la
     * reconstrucción del guard y no la rotación de la clave.
     */
    public function test_sin_rotar_la_contrasena_la_sesion_sigue_viva(): void
    {
        $socio = $this->crearAfiliado();
        $this->entrar($socio);

        // Se toca el modelo, pero no la contraseña.
        $socio->update(['name' => 'Nombre Corregido']);
        $this->app['auth']->forgetGuards();

        $this->get(route('mi-cuenta.index'))->assertOk();

        $this->assertAuthenticatedAs($socio->fresh());
    }
}

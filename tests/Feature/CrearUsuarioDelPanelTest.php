<?php

namespace Tests\Feature;

use App\Console\Commands\CrearUsuarioDelPanel;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El alta de cuentas del panel fuera del sembrador del demo.
 *
 * Existe porque el sitio quedó desplegado sin ninguna forma legítima de entrar
 * a `/admin`: `UsuarioSeeder` se niega a correr en producción --y hace bien,
 * porque publica `Asobares2026*`, que está en el README de un repositorio
 * **público**-- y no había alternativa.
 */
class CrearUsuarioDelPanelTest extends TestCase
{
    use RefreshDatabase;

    private const string CLAVE_BUENA = 'Cordillera-Quindio-2026!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    public function test_crea_la_cuenta_con_su_rol(): void
    {
        $this->artisan('asobares:crear-usuario', [
            'email' => 'direccion@asobaresquindio.test',
            '--nombre' => 'Natalia Gutiérrez',
        ])
            ->expectsQuestion('Contraseña para la cuenta', self::CLAVE_BUENA)
            ->assertSuccessful();

        $usuario = User::query()->where('email', 'direccion@asobaresquindio.test')->firstOrFail();

        $this->assertSame('Natalia Gutiérrez', $usuario->name);
        $this->assertTrue($usuario->hasRole(User::ROL_SUPER_ADMIN));
        $this->assertTrue(Hash::check(self::CLAVE_BUENA, $usuario->password));
        $this->assertNotNull($usuario->email_verified_at);
    }

    /**
     * El error que de verdad va a cometer alguien con prisa: copiar la del
     * README «porque es la que ya conozco». En un repositorio público eso es
     * entregarle el panel --pagos, cartera y datos personales de los
     * afiliados-- a cualquiera que sepa leer.
     */
    public function test_rechaza_la_contrasena_publicada_en_el_readme(): void
    {
        $this->artisan('asobares:crear-usuario', ['email' => 'quien.sea@asobaresquindio.test'])
            ->expectsQuestion('Contraseña para la cuenta', CrearUsuarioDelPanel::CLAVE_PUBLICADA)
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'quien.sea@asobaresquindio.test']);
    }

    /** @return list<array{string}> */
    public static function clavesDebiles(): array
    {
        return [
            'corta' => ['Corta-1!'],
            'sin números' => ['Cordillera-Quindio!'],
            'sin símbolos' => ['CordilleraQuindio2026'],
            'sin mayúsculas' => ['cordillera-quindio-2026!'],
        ];
    }

    #[DataProvider('clavesDebiles')]
    public function test_rechaza_contrasenas_debiles(string $clave): void
    {
        $this->artisan('asobares:crear-usuario', ['email' => 'debil@asobaresquindio.test'])
            ->expectsQuestion('Contraseña para la cuenta', $clave)
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'debil@asobaresquindio.test']);
    }

    /**
     * El fallo que dejaría la cuenta encerrada. Las direcciones del demo son
     * `.test` --dominio reservado que por definición no recibe correo-- y no
     * hay proveedor SMTP contratado (§29.1): con el segundo factor por correo
     * encendido, Filament pide un código que no va a llegar nunca y no hay
     * forma de entrar aunque la contraseña sea correcta.
     */
    public function test_nunca_deja_encendido_el_segundo_factor_por_correo(): void
    {
        $encerrado = User::factory()->create([
            'email' => 'oficina@asobaresquindio.test',
            'has_email_authentication' => true,
        ]);

        $this->assertTrue($encerrado->fresh()->hasEmailAuthentication(), 'Partimos de la cuenta encerrada.');

        $this->artisan('asobares:crear-usuario', [
            'email' => 'oficina@asobaresquindio.test',
            '--rol' => User::ROL_SUBADMIN,
        ])
            ->expectsQuestion('Contraseña para la cuenta', self::CLAVE_BUENA)
            ->assertSuccessful();

        $this->assertFalse(
            $encerrado->fresh()->hasEmailAuthentication(),
            'Con el factor de correo encendido y sin SMTP, la cuenta queda inaccesible.'
        );
    }

    /** Actualizar no duplica: la misma dirección es la misma persona. */
    public function test_una_segunda_llamada_actualiza_en_vez_de_duplicar(): void
    {
        foreach (['Nombre viejo', 'Nombre nuevo'] as $nombre) {
            $this->artisan('asobares:crear-usuario', [
                'email' => 'direccion@asobaresquindio.test',
                '--nombre' => $nombre,
            ])
                ->expectsQuestion('Contraseña para la cuenta', self::CLAVE_BUENA)
                ->assertSuccessful();
        }

        $this->assertSame(1, User::query()->where('email', 'direccion@asobaresquindio.test')->count());
        $this->assertSame('Nombre nuevo', User::query()->where('email', 'direccion@asobaresquindio.test')->value('name'));
    }

    public function test_un_rol_desconocido_no_crea_nada(): void
    {
        $this->artisan('asobares:crear-usuario', [
            'email' => 'inventado@asobaresquindio.test',
            '--rol' => 'jefe_supremo',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'inventado@asobaresquindio.test']);
    }

    /**
     * La contraseña no puede ir en un argumento: queda en el historial del
     * shell y en los registros de quien lo ejecute en remoto. Si alguien
     * añade `{clave?}` a la firma, esto se pone rojo.
     */
    public function test_la_contrasena_no_es_un_argumento_de_la_orden(): void
    {
        $firma = (new \ReflectionClass(CrearUsuarioDelPanel::class))
            ->getDefaultProperties()['signature'] ?? '';

        $this->assertDoesNotMatchRegularExpression(
            '/\{-{0,2}(clave|password|contrasena|contraseña)/i',
            (string) $firma,
            'La contraseña volvió a la línea de órdenes: ahí la lee el historial del shell.'
        );
    }
}

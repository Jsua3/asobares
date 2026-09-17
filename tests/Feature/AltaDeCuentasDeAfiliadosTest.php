<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use App\Services\AltaDeCuentasDeAfiliados;
use App\Services\ResultadoDeAltaDeCuentas;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Las cuentas de /mi-cuenta que nacen de la base del gremio.
 *
 * Lo que importa aquí es la contención: una cuenta que ya existe no se toca,
 * un correo ambiguo no produce cuenta y ninguna cuenta del equipo cambia.
 */
class AltaDeCuentasDeAfiliadosTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function ficha(string $nombre, ?string $correo, ?string $representante = 'Duena del Local'): Asociado
    {
        return Asociado::factory()->create([
            'nombre' => $nombre,
            'correo_interno' => $correo,
            'representante' => $representante,
        ]);
    }

    /** @param  list<Asociado>  $fichas */
    private function crear(array $fichas): ResultadoDeAltaDeCuentas
    {
        return app(AltaDeCuentasDeAfiliados::class)->crear(collect($fichas), self::GENERICA);
    }

    public function test_crea_la_cuenta_vinculada_con_su_rol_y_la_marca(): void
    {
        $ficha = $this->ficha('Bar Merlin', 'duena@merlin.test');

        $resultado = $this->crear([$ficha]);

        $usuario = User::query()->where('email', 'duena@merlin.test')->firstOrFail();

        $this->assertSame(1, $resultado->creadas());
        $this->assertSame($ficha->id, $usuario->asociado_id);
        $this->assertTrue($usuario->hasRole(User::ROL_ASOCIADO));
        $this->assertTrue($usuario->contrasena_provisional);
        $this->assertNotNull($usuario->email_verified_at);
        $this->assertSame('Duena del Local', $usuario->name);
        $this->assertTrue(Hash::check(self::GENERICA, $usuario->password));
    }

    public function test_el_correo_se_guarda_en_minusculas_y_sin_espacios(): void
    {
        $this->crear([$this->ficha('Bar Merlin', '  Duena@Merlin.TEST ')]);

        $this->assertSame(1, User::query()->where('email', 'duena@merlin.test')->count());
    }

    public function test_sin_representante_la_cuenta_lleva_el_nombre_del_establecimiento(): void
    {
        $this->crear([$this->ficha('Bar Merlin', 'duena@merlin.test', representante: null)]);

        $this->assertSame('Bar Merlin', User::query()->where('email', 'duena@merlin.test')->value('name'));
    }

    /** @return array<string, array{string|null, string}> */
    public static function correosQueNoDanCuenta(): array
    {
        return [
            'vacío' => [null, AltaDeCuentasDeAfiliados::SIN_CORREO],
            'sin dominio completo' => ['fernanda@hotmail', AltaDeCuentasDeAfiliados::CORREO_INVALIDO],
            'sin arroba' => ['diana.gomez.bar', AltaDeCuentasDeAfiliados::CORREO_INVALIDO],
        ];
    }

    #[DataProvider('correosQueNoDanCuenta')]
    public function test_un_correo_vacio_o_invalido_no_da_cuenta_y_lo_dice(?string $correo, string $motivo): void
    {
        $resultado = $this->crear([$this->ficha('Bar Merlin', $correo)]);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(["«Bar Merlin»: {$motivo}"], $resultado->sinCuenta());
    }

    public function test_un_correo_repetido_en_dos_fichas_no_crea_ninguna_de_las_dos(): void
    {
        $resultado = $this->crear([
            $this->ficha('Coffee Azul', 'dueno@azul.test'),
            $this->ficha('Terraza Azul', 'DUENO@azul.test'),
        ]);

        $this->assertSame(0, User::query()->count());
        $this->assertSame([
            '«Coffee Azul»: '.AltaDeCuentasDeAfiliados::CORREO_COMPARTIDO.' «Terraza Azul»',
            '«Terraza Azul»: '.AltaDeCuentasDeAfiliados::CORREO_COMPARTIDO.' «Coffee Azul»',
        ], $resultado->sinCuenta());
    }

    /**
     * Se cuenta aparte y no como ficha sin cuenta: al volver a importar el
     * archivo corregido casi todas la tienen ya, y esas líneas empujarían fuera
     * del aviso las que sí piden algo al gremio.
     */
    public function test_una_ficha_que_ya_tiene_cuenta_no_recibe_otra_y_se_cuenta_aparte(): void
    {
        $ficha = $this->ficha('Bar Merlin', 'nuevo@merlin.test');
        $dueno = User::factory()->create(['email' => 'anterior@merlin.test', 'asociado_id' => $ficha->id]);
        $dueno->syncRoles([User::ROL_ASOCIADO]);

        $resultado = $this->crear([$ficha]);

        $this->assertSame(0, User::query()->where('email', 'nuevo@merlin.test')->count());
        $this->assertSame(1, $resultado->yaTenianCuenta());
        $this->assertSame([], $resultado->sinCuenta());
    }

    /** @return array<string, array{string}> */
    public static function rolesDelEquipo(): array
    {
        return [
            'dirección' => [User::ROL_SUPER_ADMIN],
            'secretaría' => [User::ROL_SUBADMIN],
        ];
    }

    #[DataProvider('rolesDelEquipo')]
    public function test_el_correo_de_alguien_del_equipo_no_se_toca(string $rol): void
    {
        $equipo = User::factory()->create(['email' => 'oficina@gremio.test', 'password' => 'Clave-Del-Equipo-2026!']);
        $equipo->syncRoles([$rol]);
        $hashAntes = $equipo->fresh()->password;

        $resultado = $this->crear([$this->ficha('Bar Merlin', 'OFICINA@gremio.test')]);

        $equipo->refresh();

        $this->assertSame($hashAntes, $equipo->password);
        $this->assertTrue($equipo->hasRole($rol));
        $this->assertNull($equipo->asociado_id);
        $this->assertFalse($equipo->contrasena_provisional);
        $this->assertSame(['«Bar Merlin»: '.AltaDeCuentasDeAfiliados::CORREO_DEL_EQUIPO], $resultado->sinCuenta());
    }

    /**
     * Reimportar el archivo corregido no puede devolverle la genérica a quien
     * ya la cambió, aunque su correo aparezca en otra ficha.
     */
    public function test_una_cuenta_existente_nunca_recupera_la_generica(): void
    {
        $otraFicha = Asociado::factory()->create();
        $dueno = User::factory()->create(['email' => 'duena@merlin.test', 'password' => 'Su-Clave-Propia-2026!', 'asociado_id' => $otraFicha->id]);
        $dueno->syncRoles([User::ROL_ASOCIADO]);
        $hashAntes = $dueno->fresh()->password;

        $resultado = $this->crear([$this->ficha('Bar Merlin', 'duena@merlin.test')]);

        $dueno->refresh();

        $this->assertSame($hashAntes, $dueno->password);
        $this->assertSame($otraFicha->id, $dueno->asociado_id);
        $this->assertFalse($dueno->contrasena_provisional);
        $this->assertSame(['«Bar Merlin»: '.AltaDeCuentasDeAfiliados::CORREO_CON_CUENTA], $resultado->sinCuenta());
    }

    public function test_las_cuentas_nuevas_comparten_un_solo_hash(): void
    {
        $this->crear([$this->ficha('Bar Uno', 'uno@bar.test'), $this->ficha('Bar Dos', 'dos@bar.test')]);

        $hashes = User::query()->pluck('password')->unique();

        $this->assertCount(1, $hashes);
        $this->assertTrue(Hash::check(self::GENERICA, $hashes->first()));
    }

    public function test_el_resumen_cuenta_las_creadas_y_las_que_no(): void
    {
        $resultado = $this->crear([$this->ficha('Bar Uno', 'uno@bar.test'), $this->ficha('Bar Dos', null)]);

        $this->assertSame('1 cuenta creada · 1 ficha sin cuenta.', $resultado->resumen());
    }

    /**
     * Cuentas creadas, fichas que ya tenían cuenta y fichas sin cuenta.
     *
     * @return array<string, array{int, int, int, string}>
     */
    public static function resumenes(): array
    {
        return [
            'todas creadas' => [2, 0, 0, '2 cuentas creadas.'],
            'una creada' => [1, 0, 0, '1 cuenta creada.'],
            'ninguna creada y varias sin cuenta' => [0, 0, 2, '0 cuentas creadas · 2 fichas sin cuenta.'],
            'una que ya tenía cuenta' => [0, 1, 0, '0 cuentas creadas · 1 ficha ya tenía cuenta.'],
            'la reimportación del archivo corregido' => [1, 35, 2, '1 cuenta creada · 35 fichas ya tenían cuenta · 2 fichas sin cuenta.'],
        ];
    }

    #[DataProvider('resumenes')]
    public function test_el_resumen_nombra_cada_tramo_con_su_numero(int $creadas, int $yaTenian, int $sinCuenta, string $esperado): void
    {
        $resultado = new ResultadoDeAltaDeCuentas;

        for ($i = 1; $i <= $creadas; $i++) {
            $resultado->contarCreada();
        }

        for ($i = 1; $i <= $yaTenian; $i++) {
            $resultado->contarYaTenia();
        }

        for ($i = 1; $i <= $sinCuenta; $i++) {
            $resultado->agregarSinCuenta("Bar {$i}", AltaDeCuentasDeAfiliados::SIN_CORREO);
        }

        $this->assertSame($esperado, $resultado->resumen());
    }
}

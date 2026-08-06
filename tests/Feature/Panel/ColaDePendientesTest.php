<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\User;
use App\Models\Vacante;
use App\Panel\ColaDePendientes;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * La cola de pendientes es lo primero que ve quien entra al panel.
 *
 * Pregunta a la policy y no al rol, igual que las notificaciones RF-37: así
 * la secretaría ve las bolsas, la dirección ve lo que redacta la secretaría,
 * y un cambio de policy mueve la cola sin tocar este servicio.
 */
class ColaDePendientesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_ve_pendiente_el_contenido_que_solo_ella_aprueba(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $cola = app(ColaDePendientes::class)->para($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $etiquetas = array_column($cola, 'etiqueta');
        $this->assertNotEmpty(
            array_filter($etiquetas, fn (string $e): bool => str_contains($e, 'asociado')),
            'La dirección aprueba asociados: debe verlos en su cola.'
        );
    }

    /**
     * La frontera negativa, que es la que prueba algo: la secretaría NO
     * aprueba asociados, así que un asociado pendiente no es su trabajo.
     */
    public function test_la_secretaria_no_ve_pendiente_lo_que_no_puede_aprobar(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $cola = app(ColaDePendientes::class)->para($this->usuarioCon(User::ROL_SUBADMIN));

        $etiquetas = array_column($cola, 'etiqueta');
        $this->assertEmpty(
            array_filter($etiquetas, fn (string $e): bool => str_contains($e, 'asociado')),
            'La secretaría no publica asociados: no debe verlos como pendientes.'
        );
    }

    public function test_la_secretaria_si_ve_pendientes_las_bolsas(): void
    {
        $asociado = Asociado::factory()->create();
        Vacante::factory()->for($asociado)->create([
            'estado' => EstadoPublicacion::PendienteAprobacion,
        ]);

        $cola = app(ColaDePendientes::class)->para($this->usuarioCon(User::ROL_SUBADMIN));

        $etiquetas = array_column($cola, 'etiqueta');
        $this->assertNotEmpty(
            array_filter($etiquetas, fn (string $e): bool => str_contains($e, 'vacante')),
            'La secretaría aprueba las tres bolsas: la vacante es su trabajo.'
        );
    }

    public function test_la_cola_esta_vacia_cuando_no_hay_nada_pendiente(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $usuario = $this->usuarioCon(User::ROL_SUPER_ADMIN);

        $this->assertSame([], app(ColaDePendientes::class)->para($usuario));
        $this->assertSame(0, app(ColaDePendientes::class)->total($usuario));
    }

    public function test_cuenta_cuantos_hay_y_marca_urgente_lo_viejo(): void
    {
        Asociado::factory()->count(2)->create([
            'estado' => EstadoPublicacion::PendienteAprobacion,
            'updated_at' => now()->subDays(9),
        ]);

        $cola = app(ColaDePendientes::class)->para($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $fila = collect($cola)->firstWhere(fn (array $f): bool => str_contains($f['etiqueta'], 'asociado'));

        $this->assertNotNull($fila);
        $this->assertSame(2, $fila['conteo']);
        $this->assertTrue($fila['urgente'], 'Nueve días esperando es urgente.');
        $this->assertNotNull($fila['antiguedad']);
    }

    public function test_el_total_suma_todas_las_filas(): void
    {
        $asociado = Asociado::factory()->create();
        Asociado::factory()->count(2)->create(['estado' => EstadoPublicacion::PendienteAprobacion]);
        Vacante::factory()->for($asociado)->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $total = app(ColaDePendientes::class)->total($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->assertSame(3, $total);
    }

    /**
     * El tablero pide esta cola varias veces por render (widget, canView(),
     * tarjeta de KPIs): la segunda llamada con el mismo usuario debe salir
     * de la caché de instancia, no repetir las consultas.
     */
    public function test_la_segunda_llamada_con_el_mismo_usuario_no_repite_consultas(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $servicio = app(ColaDePendientes::class);
        $usuario = $this->usuarioCon(User::ROL_SUPER_ADMIN);

        $servicio->para($usuario);

        $consultas = 0;
        DB::listen(function () use (&$consultas): void {
            $consultas++;
        });

        $servicio->para($usuario);

        $this->assertSame(0, $consultas, 'La segunda llamada con el mismo usuario debe usar la cache de instancia.');
    }

    /**
     * La cache se indexa por usuario: dos usuarios distintos en la misma
     * peticion no pueden terminar compartiendo el resultado del otro.
     */
    public function test_dos_usuarios_distintos_no_comparten_la_cache(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $servicio = app(ColaDePendientes::class);

        $colaDireccion = $servicio->para($this->usuarioCon(User::ROL_SUPER_ADMIN));
        $colaSecretaria = $servicio->para($this->usuarioCon(User::ROL_SUBADMIN));

        $etiquetasDireccion = array_column($colaDireccion, 'etiqueta');
        $etiquetasSecretaria = array_column($colaSecretaria, 'etiqueta');

        $this->assertNotEmpty(
            array_filter($etiquetasDireccion, fn (string $e): bool => str_contains($e, 'asociado')),
            'La dirección debe ver el asociado pendiente en su propia cola.'
        );
        $this->assertEmpty(
            array_filter($etiquetasSecretaria, fn (string $e): bool => str_contains($e, 'asociado')),
            'La secretaría no debe heredar el asociado de la cola de la dirección.'
        );
    }
}

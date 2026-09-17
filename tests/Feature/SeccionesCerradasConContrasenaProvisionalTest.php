<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Mientras la contraseña sea provisional, las secciones con datos de otras
 * personas mandan a /mi-cuenta/seguridad. Una prueba por ruta: sacar una sola
 * del grupo cerrado tiene que poner roja una prueba.
 */
class SeccionesCerradasConContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    /** @return array{0: User, 1: Vacante, 2: Postulacion} */
    private function escenario(bool $provisional): array
    {
        $asociado = Asociado::factory()->publicado()->create();
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        $vacante = Vacante::factory()->for($asociado)->publicado()->create();
        $postulacion = Postulacion::factory()->for($vacante)->create();

        return [$usuario->fresh(), $vacante, $postulacion];
    }

    /**
     * Método, nombre de ruta y qué parámetro lleva. Los parámetros apuntan a
     * registros reales del afiliado: así la prueba no depende de si el
     * middleware corre antes o después de resolver la ruta.
     *
     * @return array<string, array{string, string, string}>
     */
    public static function seccionesCerradas(): array
    {
        return [
            'proveedores' => ['GET', 'mi-cuenta.proveedores.index', ''],
            'artistas' => ['GET', 'mi-cuenta.artistas.index', ''],
            'banco de talento' => ['GET', 'mi-cuenta.aspirantes.index', ''],
            'mis vacantes' => ['GET', 'mi-cuenta.vacantes.index', ''],
            'crear vacante' => ['GET', 'mi-cuenta.vacantes.crear', ''],
            'guardar vacante' => ['POST', 'mi-cuenta.vacantes.store', ''],
            'editar vacante' => ['GET', 'mi-cuenta.vacantes.editar', 'vacante'],
            'actualizar vacante' => ['PUT', 'mi-cuenta.vacantes.update', 'vacante'],
            'cerrar vacante' => ['POST', 'mi-cuenta.vacantes.cerrar', 'vacante'],
            'reabrir vacante' => ['POST', 'mi-cuenta.vacantes.reabrir', 'vacante'],
            'ver vacante y sus postulaciones' => ['GET', 'mi-cuenta.vacantes.show', 'vacante'],
            'gestionar una postulación' => ['PATCH', 'mi-cuenta.postulaciones.gestionar', 'postulacion'],
        ];
    }

    private function url(string $ruta, string $parametro, Vacante $vacante, Postulacion $postulacion): string
    {
        return match ($parametro) {
            'vacante' => route($ruta, $vacante),
            'postulacion' => route($ruta, $postulacion),
            default => route($ruta),
        };
    }

    #[DataProvider('seccionesCerradas')]
    public function test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad(string $metodo, string $ruta, string $parametro): void
    {
        [$usuario, $vacante, $postulacion] = $this->escenario(provisional: true);

        $this->actingAs($usuario)
            ->call($metodo, $this->url($ruta, $parametro, $vacante, $postulacion))
            ->assertRedirect(route('mi-cuenta.seguridad'))
            ->assertSessionHas('aviso');
    }

    #[DataProvider('seccionesCerradas')]
    public function test_sin_la_marca_la_seccion_no_manda_a_seguridad(string $metodo, string $ruta, string $parametro): void
    {
        [$usuario, $vacante, $postulacion] = $this->escenario(provisional: false);

        $respuesta = $this->actingAs($usuario)->call($metodo, $this->url($ruta, $parametro, $vacante, $postulacion));

        $this->assertNotSame(route('mi-cuenta.seguridad'), $respuesta->headers->get('Location'));
    }

    /** @return array<string, array{string}> */
    public static function seccionesAbiertas(): array
    {
        return [
            'estado de cuenta y convenios' => ['mi-cuenta.index'],
            'mis fotos' => ['mi-cuenta.fotos.index'],
            'seguridad' => ['mi-cuenta.seguridad'],
        ];
    }

    #[DataProvider('seccionesAbiertas')]
    public function test_con_la_contrasena_provisional_siguen_abiertas(string $ruta): void
    {
        [$usuario] = $this->escenario(provisional: true);

        $this->actingAs($usuario)->get(route($ruta))->assertOk();
    }

    public function test_pagar_sigue_abierto_con_la_contrasena_provisional(): void
    {
        [$usuario] = $this->escenario(provisional: true);

        $this->actingAs($usuario)->post(route('mi-cuenta.pagar'))->assertRedirect(route('mi-cuenta.index'));
    }
}

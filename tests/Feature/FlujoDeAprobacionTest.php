<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Noticia;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * RF-37. El requisito que se está evaluando: un subadmin no publica, ni
 * siquiera manipulando el formulario, porque la regla vive en el modelo.
 */
class FlujoDeAprobacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_un_subadmin_no_puede_publicar_aunque_mande_el_estado_publicado(): void
    {
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);
        Auth::login($subadmin);

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(
            EstadoPublicacion::PendienteAprobacion,
            $asociado->fresh()->estado,
            'El contenido de un subadmin debe quedar pendiente de aprobación.'
        );
    }

    public function test_un_subadmin_tampoco_puede_publicar_editando_un_borrador(): void
    {
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Borrador]);

        Auth::login($subadmin);
        $asociado->update(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $asociado->fresh()->estado);
    }

    /**
     * La frontera de publicación se cruza en los dos sentidos. Vigilar sólo la
     * entrada dejaba que la secretaría bajara del sitio, en silencio y sin
     * dejar rastro en la cola de revisión, contenido que la dirección ya
     * había aprobado.
     */
    public function test_un_subadmin_no_puede_despublicar_lo_que_la_direccion_aprobo(): void
    {
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        Auth::login($this->crearUsuario(User::ROL_SUBADMIN));
        $asociado->update(['estado' => EstadoPublicacion::Borrador]);

        $this->assertSame(
            EstadoPublicacion::PendienteAprobacion,
            $asociado->fresh()->estado,
            'Bajarlo a borrador lo sacaría del sitio sin que la dirección se entere.'
        );
    }

    public function test_la_direccion_si_puede_despublicar(): void
    {
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        Auth::login($this->crearUsuario(User::ROL_SUPER_ADMIN));
        $asociado->update(['estado' => EstadoPublicacion::Borrador]);

        $this->assertSame(EstadoPublicacion::Borrador, $asociado->fresh()->estado);
    }

    public function test_el_super_admin_si_publica(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        Auth::login($direccion);

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }

    public function test_enviar_a_revision_notifica_a_la_direccion(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);

        Auth::login($subadmin);
        Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(
            1,
            $direccion->notifications()->count(),
            'La dirección debe recibir una notificación de base de datos por cada envío a revisión.'
        );
    }

    public function test_las_semillas_y_la_consola_no_pasan_por_el_flujo(): void
    {
        // Sin sesión iniciada (seeders, comandos, jobs) el estado se respeta tal cual.
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }

    public function test_una_vacante_pendiente_avisa_a_la_secretaria_y_a_la_direccion(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        $asociado = Asociado::factory()->publicado()->create();
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        Auth::login($duenio->fresh());
        Vacante::factory()->for($asociado)->pendiente()->create();

        $this->assertSame(1, $secretaria->notifications()->count(), 'La secretaría modera las bolsas: tiene que enterarse.');
        $this->assertSame(1, $direccion->notifications()->count());
    }

    public function test_una_noticia_pendiente_solo_avisa_a_la_direccion(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        Auth::login($secretaria);
        Noticia::create([
            'titulo' => 'Nota de prueba del gremio',
            'slug' => 'nota-de-prueba-del-gremio',
            'contenido' => 'Cuerpo suficiente para la nota.',
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $this->assertSame(1, $direccion->notifications()->count());
        $this->assertSame(0, $secretaria->notifications()->count(), 'Nadie se avisa a sí mismo de lo que acaba de redactar.');
    }
}

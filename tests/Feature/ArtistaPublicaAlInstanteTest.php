<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Filament\Resources\Artistas\ArtistaResource;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * La bolsa de artistas publica al instante y se modera después (encargo §13,
 * 18 sep, aprobado por Natalia): quien la modera recibe un aviso en la campana
 * del panel con acceso a la ficha, y puede editarla, despublicarla o borrarla.
 */
class ArtistaPublicaAlInstanteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    /** @return array<string, mixed> */
    private function inscripcion(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'DJ Tornamesa',
            'tipo' => TipoArtista::Dj->value,
            'genero_musical' => 'Crossover',
            'municipio_id' => Municipio::factory()->create()->id,
            'whatsapp' => '3151189203',
            'correo' => 'dj@ejemplo.test',
            'acepta_datos' => '1',
        ], $cambios);
    }

    private function conRol(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /**
     * Un afiliado con la sesión abierta también publica al inscribirse: el
     * observador del flujo de aprobación bajaría a pendiente a cualquier
     * usuario sin permiso de publicar.
     */
    public function test_con_sesion_de_afiliado_la_inscripcion_tambien_sale_publicada(): void
    {
        $afiliado = User::factory()->create(['asociado_id' => Asociado::factory()->publicado()->create()->id]);
        $afiliado->syncRoles([User::ROL_ASOCIADO]);
        $this->actingAs($afiliado->fresh());

        $this->post(route('artistas.inscripcion.store'), $this->inscripcion())->assertSessionHas('exito');

        $this->assertSame(EstadoPublicacion::Publicado, Artista::firstOrFail()->estado);
        $this->assertFalse($afiliado->can('publicar_artista'));
    }

    /**
     * La salida del observador es solo para el alta: editar después sigue
     * pasando por el flujo de aprobación.
     */
    public function test_la_salida_del_flujo_es_solo_para_el_alta(): void
    {
        // La misma instancia que se dio de alta, con la bandera todavía puesta.
        $artista = new Artista(Artista::factory()->make(['estado' => EstadoPublicacion::Publicado])->getAttributes());
        $artista->marcarInscripcionPublicaValidada();
        $artista->save();

        $this->actingAs($this->conRol(User::ROL_ASOCIADO));
        $artista->update(['estado' => EstadoPublicacion::Borrador]);

        // Quien no puede publicar tampoco saca del sitio lo publicado: queda
        // pendiente para la secretaría.
        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $artista->fresh()->estado);
    }

    public function test_quien_modera_la_bolsa_recibe_un_aviso_con_acceso_a_la_ficha(): void
    {
        $direccion = $this->conRol(User::ROL_SUPER_ADMIN);
        $secretaria = $this->conRol(User::ROL_SUBADMIN);
        $afiliado = $this->conRol(User::ROL_ASOCIADO);

        $this->post(route('artistas.inscripcion.store'), $this->inscripcion())->assertSessionHas('exito');
        $artista = Artista::firstOrFail();

        $this->assertSame(1, $direccion->notifications()->count());
        $this->assertSame(1, $secretaria->notifications()->count());
        $this->assertSame(0, $afiliado->notifications()->count());
        $this->assertSame(2, DatabaseNotification::count());

        $aviso = $secretaria->notifications()->firstOrFail();
        $this->assertNull($aviso->read_at);
        $this->assertSame('Nuevo artista publicado: DJ Tornamesa', $aviso->data['title']);
        $this->assertSame('Revisar artista', $aviso->data['actions'][0]['label']);
        $this->assertSame(ArtistaResource::getUrl('edit', ['record' => $artista], panel: 'admin'), $aviso->data['actions'][0]['url']);
        $this->assertTrue($aviso->data['actions'][0]['shouldMarkAsRead']);
    }

    public function test_el_nombre_del_artista_no_inyecta_html_en_el_aviso(): void
    {
        $direccion = $this->conRol(User::ROL_SUPER_ADMIN);

        $this->post(route('artistas.inscripcion.store'), $this->inscripcion(['nombre' => 'DJ <b>Negrita</b>']))
            ->assertSessionHas('exito');

        $titulo = $direccion->notifications()->firstOrFail()->data['title'];
        $this->assertStringNotContainsString('<b>', $titulo);
        $this->assertStringContainsString('&lt;b&gt;', $titulo);
    }

    /**
     * Un doble clic o un reenvío del navegador no publica dos fichas: el
     * mismo nombre y el mismo contacto en diez minutos es la misma inscripción.
     */
    public function test_reenviar_la_misma_inscripcion_no_publica_dos_fichas(): void
    {
        $direccion = $this->conRol(User::ROL_SUPER_ADMIN);
        $datos = $this->inscripcion();

        $this->post(route('artistas.inscripcion.store'), $datos)->assertSessionHas('exito');
        $this->post(route('artistas.inscripcion.store'), $datos)->assertSessionHas('exito');

        $this->assertSame(1, Artista::count());
        $this->assertSame(1, $direccion->notifications()->count());
    }

    public function test_pasados_diez_minutos_la_misma_inscripcion_si_crea_otra_ficha(): void
    {
        $datos = $this->inscripcion();
        $this->post(route('artistas.inscripcion.store'), $datos)->assertSessionHas('exito');

        Carbon::setTestNow(now()->addMinutes(11));
        $this->post(route('artistas.inscripcion.store'), $datos)->assertSessionHas('exito');
        Carbon::setTestNow();

        $this->assertSame(2, Artista::count());
    }

    public function test_la_moderacion_posterior_puede_despublicar_y_borrar(): void
    {
        $this->post(route('artistas.inscripcion.store'), $this->inscripcion())->assertSessionHas('exito');
        $artista = Artista::firstOrFail();

        $this->actingAs($this->conRol(User::ROL_SUBADMIN));
        $artista->update(['estado' => EstadoPublicacion::Borrador]);
        $this->assertSame(EstadoPublicacion::Borrador, $artista->fresh()->estado);
        $this->get(route('artistas.show', $artista))->assertNotFound();

        $artista->delete();
        $this->assertSame(0, Artista::count());
    }

    /** Ningún texto público promete una revisión que ya no ocurre. */
    public function test_los_textos_publicos_dicen_que_la_ficha_sale_al_instante(): void
    {
        $this->get(route('artistas.inscripcion'))
            ->assertOk()
            ->assertDontSee('revisa cada inscripción antes de publicarla')
            ->assertSee('Tu ficha sale publicada en cuanto la envías');

        $this->get(route('artistas.index'))
            ->assertOk()
            ->assertDontSee('la secretaría revisa tu ficha');

        $this->post(route('artistas.inscripcion.store'), $this->inscripcion())
            ->assertSessionHas('exito', fn (string $mensaje): bool => str_contains($mensaje, 'ya está publicada'));
    }
}

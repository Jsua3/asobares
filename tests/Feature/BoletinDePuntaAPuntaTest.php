<?php

namespace Tests\Feature;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Noticias\Pages\CreateNoticia;
use App\Models\Noticia;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El boletín de punta a punta: se escribe en el panel, sale en el sitio con su
 * categoría y su fecha, se puede programar y se puede despublicar.
 *
 * Producción no tiene ninguna entrada, y eso es falta de contenido, no del
 * módulo: estas pruebas lo recorren entero sin inventar ninguna noticia real.
 */
class BoletinDePuntaAPuntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function conRol(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /** @return array<string, mixed> */
    private function entrada(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Asamblea anual del capítulo',
            'slug' => 'asamblea-anual-del-capitulo',
            'categoria' => CategoriaNoticia::Noticia->value,
            'publicado_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'extracto' => 'El gremio se reúne para revisar el año.',
            'contenido' => '<p>Texto de la entrada.</p>',
            'imagen' => null,
            'estado' => EstadoPublicacion::Publicado->value,
        ], $cambios);
    }

    public function test_la_direccion_publica_desde_el_panel_y_la_entrada_sale_en_el_sitio(): void
    {
        $this->actingAs($this->conRol(User::ROL_SUPER_ADMIN));

        Livewire::test(CreateNoticia::class)->fillForm($this->entrada())->call('create')->assertHasNoFormErrors();

        $noticia = Noticia::firstOrFail();
        $this->assertSame(EstadoPublicacion::Publicado, $noticia->estado);

        $this->get(route('boletin.index'))->assertOk()->assertSee('Asamblea anual del capítulo');
        $this->get(route('boletin.index', ['categoria' => CategoriaNoticia::Noticia->value]))->assertSee('Asamblea anual del capítulo');
        $this->get(route('boletin.index', ['categoria' => CategoriaNoticia::Proyecto->value]))->assertDontSee('Asamblea anual del capítulo');
        $this->get(route('boletin.show', $noticia))->assertOk()->assertSee('Texto de la entrada.');
    }

    /** Lo que redacta la secretaría espera la aprobación de la dirección. */
    public function test_la_secretaria_redacta_y_su_entrada_espera_la_aprobacion(): void
    {
        $this->actingAs($this->conRol(User::ROL_SUBADMIN));

        Livewire::test(CreateNoticia::class)->fillForm($this->entrada())->call('create')->assertHasNoFormErrors();

        $noticia = Noticia::firstOrFail();
        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $noticia->estado);
        $this->get(route('boletin.show', $noticia))->assertNotFound();
    }

    public function test_una_entrada_programada_no_sale_antes_de_su_fecha(): void
    {
        $this->actingAs($this->conRol(User::ROL_SUPER_ADMIN));

        Livewire::test(CreateNoticia::class)
            ->fillForm($this->entrada(['publicado_at' => now()->addDay()->format('Y-m-d H:i:s')]))
            ->call('create')
            ->assertHasNoFormErrors();

        $noticia = Noticia::firstOrFail();
        $this->get(route('boletin.index'))->assertDontSee('Asamblea anual del capítulo');
        $this->get(route('boletin.show', $noticia))->assertNotFound();

        $this->travel(2)->days();
        $this->get(route('boletin.show', $noticia))->assertOk();
    }

    public function test_despublicar_saca_la_entrada_del_sitio(): void
    {
        $this->actingAs($this->conRol(User::ROL_SUPER_ADMIN));
        Livewire::test(CreateNoticia::class)->fillForm($this->entrada())->call('create')->assertHasNoFormErrors();
        $noticia = Noticia::firstOrFail();

        $noticia->update(['estado' => EstadoPublicacion::Borrador]);

        $this->get(route('boletin.index'))->assertDontSee('Asamblea anual del capítulo');
        $this->get(route('boletin.show', $noticia))->assertNotFound();
    }
}

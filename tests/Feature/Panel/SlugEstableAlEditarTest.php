<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Artistas\Pages\CreateArtista;
use App\Filament\Resources\Artistas\Pages\EditArtista;
use App\Filament\Resources\Asociados\Pages\CreateAsociado;
use App\Filament\Resources\Asociados\Pages\EditAsociado;
use App\Filament\Resources\Categorias\Pages\CreateCategoria;
use App\Filament\Resources\Categorias\Pages\EditCategoria;
use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Filament\Resources\Eventos\Pages\EditEvento;
use App\Filament\Resources\Iniciativas\Pages\CreateIniciativa;
use App\Filament\Resources\Iniciativas\Pages\EditIniciativa;
use App\Filament\Resources\Municipios\Pages\CreateMunicipio;
use App\Filament\Resources\Municipios\Pages\EditMunicipio;
use App\Filament\Resources\Noticias\Pages\CreateNoticia;
use App\Filament\Resources\Noticias\Pages\EditNoticia;
use App\Filament\Resources\Proveedors\Pages\CreateProveedor;
use App\Filament\Resources\Proveedors\Pages\EditProveedor;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Municipio;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El slug es la dirección pública del registro. Corregir una tilde en el
 * nombre de algo ya publicado no puede mover su URL: los enlaces compartidos
 * o indexados quedarían en 404, y en municipios los filtros guardados dejarían
 * de filtrar. Solo al crear se propone el slug a partir del nombre.
 */
class SlugEstableAlEditarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());
    }

    /**
     * @return array<string, array{0: class-string<Model>, 1: class-string, 2: string}>
     */
    public static function formulariosDeEdicion(): array
    {
        return [
            'artista' => [Artista::class, EditArtista::class, 'nombre'],
            'asociado' => [Asociado::class, EditAsociado::class, 'nombre'],
            'categoria' => [Categoria::class, EditCategoria::class, 'nombre'],
            'evento' => [Evento::class, EditEvento::class, 'titulo'],
            'iniciativa' => [Iniciativa::class, EditIniciativa::class, 'nombre'],
            'municipio' => [Municipio::class, EditMunicipio::class, 'nombre'],
            'noticia' => [Noticia::class, EditNoticia::class, 'titulo'],
            'proveedor' => [Proveedor::class, EditProveedor::class, 'nombre'],
        ];
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function formulariosDeCreacion(): array
    {
        return [
            'artista' => [CreateArtista::class, 'nombre'],
            'asociado' => [CreateAsociado::class, 'nombre'],
            'categoria' => [CreateCategoria::class, 'nombre'],
            'evento' => [CreateEvento::class, 'titulo'],
            'iniciativa' => [CreateIniciativa::class, 'nombre'],
            'municipio' => [CreateMunicipio::class, 'nombre'],
            'noticia' => [CreateNoticia::class, 'titulo'],
            'proveedor' => [CreateProveedor::class, 'nombre'],
        ];
    }

    /**
     * @param  class-string<Model>  $modelo
     */
    #[DataProvider('formulariosDeEdicion')]
    public function test_editar_el_nombre_no_reescribe_el_slug(string $modelo, string $pagina, string $campo): void
    {
        $registro = $modelo::factory()->create(['slug' => 'direccion-que-ya-circula']);

        Livewire::test($pagina, ['record' => $registro->getRouteKey()])
            ->fillForm([$campo => 'Un nombre corregido por la oficina'])
            ->assertSchemaStateSet(['slug' => 'direccion-que-ya-circula']);
    }

    #[DataProvider('formulariosDeCreacion')]
    public function test_al_crear_el_slug_se_propone_desde_el_nombre(string $pagina, string $campo): void
    {
        Livewire::test($pagina)
            ->fillForm([$campo => 'Un Registro Nuevo del Gremio'])
            ->assertSchemaStateSet(['slug' => 'un-registro-nuevo-del-gremio']);
    }

    public function test_guardar_un_evento_publicado_con_otro_titulo_conserva_su_url_publica(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Congreso del gremio',
            'slug' => 'congreso-del-gremio',
        ]);

        Livewire::test(EditEvento::class, ['record' => $evento->getRouteKey()])
            ->fillForm(['titulo' => 'Congreso Nacional del gremio'])
            ->call('save')
            ->assertHasNoFormErrors();

        $evento->refresh();

        $this->assertSame('Congreso Nacional del gremio', $evento->titulo);
        $this->assertSame('congreso-del-gremio', $evento->slug);
        $this->get(route('eventos.show', 'congreso-del-gremio'))
            ->assertOk()
            ->assertSee('Congreso Nacional del gremio');
    }

    public function test_guardar_un_asociado_publicado_con_otro_nombre_conserva_su_ficha_en_el_directorio(): void
    {
        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => 'Bar La Terraza',
            'slug' => 'bar-la-terraza',
        ]);

        Livewire::test(EditAsociado::class, ['record' => $asociado->getRouteKey()])
            ->fillForm(['nombre' => 'Bar La Terraza de Armenia'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('bar-la-terraza', $asociado->fresh()->slug);
        $this->get(route('directorio.show', 'bar-la-terraza'))
            ->assertOk()
            ->assertSee('Bar La Terraza de Armenia');
    }
}

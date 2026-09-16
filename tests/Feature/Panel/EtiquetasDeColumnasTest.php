<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Beneficios\Pages\ListBeneficios;
use App\Filament\Resources\Categorias\Pages\ListCategorias;
use App\Filament\Resources\Municipios\Pages\ListMunicipios;
use App\Filament\Resources\Noticias\Pages\ListNoticias;
use App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Tables\Columns\Column;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Sin `->label()`, Filament rotula la columna con el nombre del atributo
 * («Created at», «Publicado at», «Adjunto nombre»). En un panel en español
 * eso se lee como un descuido, así que cada columna declara su rótulo con el
 * mismo vocabulario que las tablas hermanas.
 */
class EtiquetasDeColumnasTest extends TestCase
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
     * @return array<string, array{0: class-string, 1: string, 2: string}>
     */
    public static function columnas(): array
    {
        return [
            'beneficios · titulo' => [ListBeneficios::class, 'titulo', 'Título'],
            'beneficios · icono' => [ListBeneficios::class, 'icono', 'Icono'],
            'beneficios · orden' => [ListBeneficios::class, 'orden', 'Orden'],
            'beneficios · created_at' => [ListBeneficios::class, 'created_at', 'Creado'],
            'beneficios · updated_at' => [ListBeneficios::class, 'updated_at', 'Actualizado'],

            'categorias · nombre' => [ListCategorias::class, 'nombre', 'Nombre'],
            'categorias · slug' => [ListCategorias::class, 'slug', 'Slug (URL)'],
            'categorias · created_at' => [ListCategorias::class, 'created_at', 'Creado'],
            'categorias · updated_at' => [ListCategorias::class, 'updated_at', 'Actualizado'],

            'municipios · nombre' => [ListMunicipios::class, 'nombre', 'Nombre'],
            'municipios · slug' => [ListMunicipios::class, 'slug', 'Slug (URL)'],
            'municipios · orden' => [ListMunicipios::class, 'orden', 'Orden'],
            'municipios · activo' => [ListMunicipios::class, 'activo', 'Activo'],
            'municipios · created_at' => [ListMunicipios::class, 'created_at', 'Creado'],
            'municipios · updated_at' => [ListMunicipios::class, 'updated_at', 'Actualizado'],

            'noticias · titulo' => [ListNoticias::class, 'titulo', 'Título'],
            'noticias · slug' => [ListNoticias::class, 'slug', 'Slug (URL)'],
            'noticias · imagen' => [ListNoticias::class, 'imagen', 'Imagen'],
            'noticias · categoria' => [ListNoticias::class, 'categoria', 'Categoría'],
            'noticias · publicado_at' => [ListNoticias::class, 'publicado_at', 'Fecha de publicación'],
            'noticias · estado' => [ListNoticias::class, 'estado', 'Estado'],
            'noticias · created_at' => [ListNoticias::class, 'created_at', 'Creado'],
            'noticias · updated_at' => [ListNoticias::class, 'updated_at', 'Actualizado'],

            'requisitos · entidad' => [ListRequisitoAperturas::class, 'entidad', 'Entidad'],
            'requisitos · enlace_externo' => [ListRequisitoAperturas::class, 'enlace_externo', 'Enlace externo'],
            'requisitos · adjunto' => [ListRequisitoAperturas::class, 'adjunto', 'Formato oficial (PDF)'],
            'requisitos · adjunto_nombre' => [ListRequisitoAperturas::class, 'adjunto_nombre', 'Nombre del formato'],
            'requisitos · costo_aproximado' => [ListRequisitoAperturas::class, 'costo_aproximado', 'Costo aproximado'],
            'requisitos · orden' => [ListRequisitoAperturas::class, 'orden', 'Orden'],
            'requisitos · estado' => [ListRequisitoAperturas::class, 'estado', 'Estado'],
            'requisitos · created_at' => [ListRequisitoAperturas::class, 'created_at', 'Creado'],
            'requisitos · updated_at' => [ListRequisitoAperturas::class, 'updated_at', 'Actualizado'],
        ];
    }

    #[DataProvider('columnas')]
    public function test_la_columna_lleva_su_rotulo_en_espanol(string $pagina, string $columna, string $rotulo): void
    {
        $leido = null;

        Livewire::test($pagina)
            ->assertTableColumnExists($columna, function (Column $encontrada) use (&$leido): bool {
                $leido = $encontrada->getLabel();

                return true;
            });

        $this->assertSame($rotulo, $leido);
    }
}

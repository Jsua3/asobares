<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Asociados\Pages\CreateAsociado;
use App\Filament\Resources\Asociados\Pages\EditAsociado;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\MunicipioSeeder;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La subida de archivos era el hueco que quedaba: el formulario renderizaba
 * en las pruebas, pero nunca se le había subido una imagen de verdad.
 */
class SubidaDeImagenesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        $this->seed(MunicipioSeeder::class);
        $this->seed(CategoriaSeeder::class);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($usuario->fresh());
    }

    public function test_subir_una_foto_de_portada_la_guarda_en_el_disco_publico(): void
    {
        Storage::fake('public');

        Livewire::test(CreateAsociado::class)
            ->fillForm([
                'nombre' => 'Bar con Portada',
                'slug' => 'bar-con-portada',
                'categoria_id' => Categoria::first()->id,
                'municipio_id' => Municipio::first()->id,
                'foto_portada' => UploadedFile::fake()->image('portada.png', 1200, 900),
                'estado' => EstadoPublicacion::Publicado->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $asociado = Asociado::where('slug', 'bar-con-portada')->firstOrFail();

        $this->assertNotNull($asociado->foto_portada, 'La portada debería haberse guardado.');
        $this->assertStringStartsWith('asociados/', $asociado->foto_portada);
        Storage::disk('public')->assertExists($asociado->foto_portada);
    }

    public function test_la_portada_se_sirve_con_una_url_relativa(): void
    {
        // Atarla a APP_URL rompe las imágenes en cuanto el servidor levanta en
        // otro puerto: la URL debe funcionar en cualquier host.
        $url = Storage::disk('public')->url('asociados/ejemplo.png');

        $this->assertSame('/storage/asociados/ejemplo.png', $url);
    }

    public function test_subir_la_galeria_crea_las_conversiones_webp(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        Livewire::test(EditAsociado::class, ['record' => $asociado->getRouteKey()])
            ->fillForm([
                'galeria' => [UploadedFile::fake()->image('foto.png', 1200, 900)],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $media = $asociado->fresh()->getMedia('galeria');

        $this->assertCount(1, $media, 'La imagen debería quedar en la colección `galeria`.');
        $this->assertTrue(
            $media->first()->hasGeneratedConversion('thumb'),
            'RNF-02: la galería se sirve en webp, así que la conversión debe generarse.'
        );
    }

    public function test_se_rechaza_un_archivo_que_no_es_imagen(): void
    {
        Storage::fake('public');

        Livewire::test(CreateAsociado::class)
            ->fillForm([
                'nombre' => 'Bar con Archivo Raro',
                'slug' => 'bar-con-archivo-raro',
                'categoria_id' => Categoria::first()->id,
                'municipio_id' => Municipio::first()->id,
                'foto_portada' => UploadedFile::fake()->create('malicioso.php', 20, 'application/x-php'),
            ])
            ->call('create')
            ->assertHasFormErrors(['foto_portada']);

        $this->assertDatabaseMissing('asociados', ['slug' => 'bar-con-archivo-raro']);
    }
}

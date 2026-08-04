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

    /**
     * La extensión no puede elegirla quien sube. Un JPEG legítimo llamado
     * «payload.html» pasa la validación de tipo —su MIME es image/jpeg— pero
     * si se guarda con ese nombre, el servidor lo entrega como HTML desde
     * /storage y se ejecuta en el navegador de cualquier visitante.
     */
    public function test_la_extension_guardada_se_deriva_del_tipo_real_y_no_del_nombre(): void
    {
        Storage::fake('public');

        Livewire::test(CreateAsociado::class)
            ->fillForm([
                'nombre' => 'Bar Polyglot',
                'slug' => 'bar-polyglot',
                'categoria_id' => Categoria::first()->id,
                'municipio_id' => Municipio::first()->id,
                'foto_portada' => UploadedFile::fake()->create('payload.html', 40, 'image/jpeg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $portada = Asociado::where('slug', 'bar-polyglot')->value('foto_portada');

        $this->assertNotNull($portada);
        $this->assertStringEndsWith('.jpg', $portada, "Se guardó como «{$portada}».");
        $this->assertStringNotContainsString('.html', $portada);
    }

    /**
     * Se prueba sobre el modelo y no sobre el formulario porque la limpieza
     * vive en un observer: cualquier cambio del campo la dispara, venga del
     * panel, de un comando o de una importación.
     */
    public function test_cambiar_la_portada_borra_la_anterior_del_disco(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('asociados/vieja.png', 'contenido');
        Storage::disk('public')->put('asociados/nueva.png', 'contenido');

        $asociado = Asociado::factory()->publicado()->create(['foto_portada' => 'asociados/vieja.png']);

        $asociado->update(['foto_portada' => 'asociados/nueva.png']);

        Storage::disk('public')->assertMissing('asociados/vieja.png');
        Storage::disk('public')->assertExists('asociados/nueva.png');
    }

    public function test_quitar_la_portada_tambien_borra_el_archivo(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('asociados/sola.png', 'contenido');
        $asociado = Asociado::factory()->publicado()->create(['foto_portada' => 'asociados/sola.png']);

        $asociado->update(['foto_portada' => null]);

        Storage::disk('public')->assertMissing('asociados/sola.png');
    }

    public function test_eliminar_un_asociado_borra_su_portada_del_disco(): void
    {
        Storage::fake('public');

        $asociado = Asociado::factory()->publicado()->create();

        Livewire::test(EditAsociado::class, ['record' => $asociado->getRouteKey()])
            ->fillForm(['foto_portada' => UploadedFile::fake()->image('portada.png', 400, 300)])
            ->call('save');

        $ruta = $asociado->fresh()->foto_portada;
        Storage::disk('public')->assertExists($ruta);

        $asociado->fresh()->delete();

        Storage::disk('public')->assertMissing($ruta);
    }

    /**
     * Las semillas comparten el mismo formato entre varios municipios. Borrar
     * uno no puede dejar sin adjunto a los demás.
     */
    public function test_no_se_borra_un_archivo_que_otro_registro_sigue_usando(): void
    {
        Storage::fake('public');

        $compartida = 'asociados/compartida.png';
        Storage::disk('public')->put($compartida, 'contenido');

        $uno = Asociado::factory()->publicado()->create(['foto_portada' => $compartida]);
        Asociado::factory()->publicado()->create(['foto_portada' => $compartida]);

        $uno->delete();

        Storage::disk('public')->assertExists($compartida);
    }

    public function test_los_temporales_de_livewire_no_van_al_disco_publico(): void
    {
        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
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

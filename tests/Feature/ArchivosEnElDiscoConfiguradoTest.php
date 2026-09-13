<?php

namespace Tests\Feature;

use App\Enums\TipoArtista;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Los archivos se guardan y se borran en el disco que dice la configuración.
 *
 * En el servidor `DISCO_PUBLICO` y `DISCO_PRIVADO` apuntan al almacenamiento de
 * objetos. Un disco escrito a mano coincide con el de desarrollo y ninguna
 * prueba lo nota: por eso aquí los dos discos se llaman distinto de `public` y
 * `local`, y los de siempre se falsean también para que un acierto por
 * casualidad no pase y un fallo no ensucie el disco real.
 */
class ArchivosEnElDiscoConfiguradoTest extends TestCase
{
    use RefreshDatabase;

    private const string DISCO_PUBLICO = 'bucket-publico';

    private const string DISCO_PRIVADO = 'bucket-privado';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'almacenamiento.publico' => self::DISCO_PUBLICO,
            'almacenamiento.privado' => self::DISCO_PRIVADO,
        ]);

        Storage::fake(self::DISCO_PUBLICO);
        Storage::fake(self::DISCO_PRIVADO);
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_cambiar_una_portada_borra_la_anterior_del_disco_publico_configurado(): void
    {
        Storage::disk(self::DISCO_PUBLICO)->put('asociados/vieja.png', 'contenido');
        Storage::disk(self::DISCO_PUBLICO)->put('asociados/nueva.png', 'contenido');

        $asociado = Asociado::factory()->publicado()->create(['foto_portada' => 'asociados/vieja.png']);

        $asociado->update(['foto_portada' => 'asociados/nueva.png']);

        Storage::disk(self::DISCO_PUBLICO)->assertMissing('asociados/vieja.png');
        Storage::disk(self::DISCO_PUBLICO)->assertExists('asociados/nueva.png');
    }

    public function test_reemplazar_un_formato_de_la_guia_borra_el_anterior_del_disco_privado_configurado(): void
    {
        Storage::disk(self::DISCO_PRIVADO)->put('formatos/viejo.pdf', '%PDF-1.4');
        Storage::disk(self::DISCO_PRIVADO)->put('formatos/nuevo.pdf', '%PDF-1.4');

        $requisito = RequisitoApertura::factory()->create(['adjunto' => 'formatos/viejo.pdf']);

        $requisito->update(['adjunto' => 'formatos/nuevo.pdf']);

        Storage::disk(self::DISCO_PRIVADO)->assertMissing('formatos/viejo.pdf');
        Storage::disk(self::DISCO_PRIVADO)->assertExists('formatos/nuevo.pdf');
    }

    public function test_la_foto_del_artista_inscrito_desde_el_sitio_va_al_disco_publico_configurado(): void
    {
        $municipio = Municipio::factory()->create();

        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Tornamesa',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => $municipio->id,
            'foto' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $foto = Artista::firstOrFail()->foto;

        $this->assertNotNull($foto);
        Storage::disk(self::DISCO_PUBLICO)->assertExists($foto);
        Storage::disk('public')->assertMissing($foto);
    }
}

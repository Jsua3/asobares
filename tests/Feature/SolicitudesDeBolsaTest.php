<?php

namespace Tests\Feature;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Models\Artista;
use App\Models\Municipio;
use App\Models\Proveedor;
use App\Support\Formulario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SolicitudesDeBolsaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_inscripcion_de_artista_crea_una_ficha_pendiente(): void
    {
        $municipio = Municipio::factory()->create();

        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Tornamesa',
            'tipo' => TipoArtista::Dj->value,
            'genero_musical' => 'Crossover',
            'descripcion' => 'Diez años animando fiestas en el Quindío.',
            'municipio_id' => $municipio->id,
            'whatsapp' => '3151189203',
            'correo' => 'dj@ejemplo.test',
            'tarifa_desde' => 600000,
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $artista = Artista::firstOrFail();

        $this->assertSame('DJ Tornamesa', $artista->nombre);
        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $artista->estado);
        $this->assertNotNull($artista->slug);
        $this->assertTrue($artista->acepta_datos);
        $this->assertNotNull($artista->consentimiento_at);
    }

    public function test_la_ficha_recien_inscrita_no_sale_en_el_directorio_publico(): void
    {
        $municipio = Municipio::factory()->create();

        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Sin Aprobar',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => $municipio->id,
            'whatsapp' => '3151189203',
            'acepta_datos' => '1',
        ]);

        $this->get(route('artistas.index'))->assertDontSee('DJ Sin Aprobar');
        $this->get(route('artistas.show', Artista::firstOrFail()))->assertNotFound();
    }

    public function test_la_inscripcion_de_artista_exige_autorizacion_de_datos(): void
    {
        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Sin Permiso',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => Municipio::factory()->create()->id,
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Artista::count());
    }

    public function test_el_honeypot_descarta_la_inscripcion_de_artista(): void
    {
        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'Bot Artista',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'acepta_datos' => '1',
            Formulario::CAMPO_TRAMPA => 'soy-un-bot',
        ])->assertStatus(422);

        $this->assertSame(0, Artista::count());
    }

    public function test_dos_artistas_con_el_mismo_nombre_no_chocan_de_slug(): void
    {
        $municipio = Municipio::factory()->create();
        $datos = [
            'nombre' => 'DJ Repetido',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => $municipio->id,
            'acepta_datos' => '1',
        ];

        $this->post(route('artistas.inscripcion.store'), $datos);
        $this->post(route('artistas.inscripcion.store'), $datos)->assertSessionHas('exito');

        $this->assertSame(2, Artista::count());
        $this->assertSame(2, Artista::distinct()->count('slug'));
    }

    public function test_la_pagina_de_inscripcion_de_artistas_responde(): void
    {
        $this->get(route('artistas.inscripcion'))->assertSuccessful();
    }

    public function test_la_foto_del_artista_se_guarda_en_el_disco_publico(): void
    {
        Storage::fake('public');

        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Con Foto',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'acepta_datos' => '1',
            'foto' => UploadedFile::fake()->image('perfil.jpg', 800, 600),
        ])->assertSessionHas('exito');

        $artista = Artista::firstOrFail();

        $this->assertNotNull($artista->foto);
        Storage::disk('public')->assertExists($artista->foto);
    }

    public function test_un_archivo_que_no_es_imagen_no_se_acepta(): void
    {
        Storage::fake('public');

        $this->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Con Payload',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'acepta_datos' => '1',
            'foto' => UploadedFile::fake()->create('malicioso.php', 40, 'application/x-php'),
        ])->assertSessionHasErrors('foto');

        $this->assertSame(0, Artista::count());
    }

    public function test_la_inscripcion_de_proveedor_crea_una_ficha_pendiente(): void
    {
        $this->post(route('proveedores.inscripcion.store'), [
            'nombre' => 'Hielos del Quindío',
            'categoria_proveedor' => CategoriaProveedor::Hielo->value,
            'descripcion' => 'Despacho de hielo en bloque y en cubo a bares de Armenia.',
            'municipio_id' => Municipio::factory()->create()->id,
            'whatsapp' => '3151189203',
            'correo' => 'ventas@hielos.test',
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $proveedor = Proveedor::firstOrFail();

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $proveedor->estado);
        $this->assertTrue($proveedor->acepta_datos);
        $this->assertNotNull($proveedor->slug);
    }

    public function test_el_proveedor_pendiente_no_sale_en_el_listado_publico(): void
    {
        $this->post(route('proveedores.inscripcion.store'), [
            'nombre' => 'Proveedor Sin Aprobar',
            'categoria_proveedor' => CategoriaProveedor::Licores->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'acepta_datos' => '1',
        ]);

        $this->get(route('proveedores.index'))->assertDontSee('Proveedor Sin Aprobar');
    }

    public function test_un_proveedor_vencido_tampoco_sale_aunque_este_publicado(): void
    {
        Proveedor::factory()->publicado()->vencido()->create(['nombre' => 'Proveedor Vencido']);
        Proveedor::factory()->publicado()->create(['nombre' => 'Proveedor Vigente']);

        $respuesta = $this->get(route('proveedores.index'));

        $respuesta->assertSee('Proveedor Vigente');
        $respuesta->assertDontSee('Proveedor Vencido');
    }

    public function test_la_inscripcion_de_proveedor_exige_autorizacion_de_datos(): void
    {
        $this->post(route('proveedores.inscripcion.store'), [
            'nombre' => 'Sin Permiso',
            'categoria_proveedor' => CategoriaProveedor::Aseo->value,
            'municipio_id' => Municipio::factory()->create()->id,
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Proveedor::count());
    }

    public function test_la_pagina_de_inscripcion_de_proveedores_responde(): void
    {
        $this->get(route('proveedores.inscripcion'))->assertSuccessful();
    }
}

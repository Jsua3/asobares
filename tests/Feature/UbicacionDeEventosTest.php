<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Models\Evento;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Dónde se hace un evento, en datos (bloque C, aprobado por Natalia el
 * 18 sep): dirección, municipio, coordenadas y enlace de mapa, todo opcional.
 * La presentación pública la hace Ingrid con este contrato: `tieneUbicacion()`
 * para decidir si pinta el mapa y `urlDelMapa()` para el enlace.
 */
class UbicacionDeEventosTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function formularioDelPanel(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Asamblea del gremio',
            'slug' => 'asamblea-del-gremio',
            'tipo' => TipoEvento::Evento->value,
            'origen' => OrigenEvento::Asobares->value,
            'aliado_id' => null,
            'lugar' => 'Cámara de Comercio',
            'fecha_inicio' => '2026-10-02 18:00:00',
            'fecha_fin' => null,
            'descripcion' => 'Asamblea ordinaria.',
            'permite_inscripcion' => false,
            'enlace_externo' => null,
            'cupos' => null,
            'precio' => 0,
            'imagen' => null,
            'estado' => EstadoPublicacion::Borrador->value,
        ], $cambios);
    }

    private function comoDireccion(): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());
    }

    public function test_el_panel_guarda_la_ubicacion_del_evento(): void
    {
        $this->comoDireccion();
        $armenia = Municipio::factory()->create(['nombre' => 'Armenia']);

        Livewire::test(CreateEvento::class)->fillForm($this->formularioDelPanel([
            'direccion' => 'Carrera 14 # 23-15, piso 3',
            'municipio_id' => $armenia->id,
            'lat' => 4.5338900,
            'lng' => -75.6811100,
            'mapa_url' => 'https://maps.google.com/?q=Camara+de+Comercio+Armenia',
        ]))->call('create')->assertHasNoFormErrors();

        $evento = Evento::firstOrFail();
        $this->assertSame('Carrera 14 # 23-15, piso 3', $evento->direccion);
        $this->assertTrue($evento->municipio->is($armenia));
        $this->assertEqualsWithDelta(4.53389, $evento->lat, 0.0000001);
        $this->assertEqualsWithDelta(-75.68111, $evento->lng, 0.0000001);
        $this->assertTrue($evento->tieneUbicacion());

    }

    /**
     * PostgreSQL entrega una columna decimal como texto ('4.5338900'); SQLite,
     * que es la base de las pruebas, ya como número. Se hidrata el modelo como
     * lo haría producción: las coordenadas tienen que salir como números, que
     * es lo que el mapa recibe en JSON.
     */
    public function test_las_coordenadas_salen_como_numeros_aunque_la_base_las_de_como_texto(): void
    {
        $evento = (new Evento)->setRawAttributes(['lat' => '4.5338900', 'lng' => '-75.6811100'], sync: true);

        $this->assertSame(4.53389, $evento->lat);
        $this->assertSame(-75.68111, $evento->lng);
        $this->assertSame('https://www.openstreetmap.org/?mlat=4.53389&mlon=-75.68111#map=17/4.53389/-75.68111', $evento->urlDelMapa());
    }

    public function test_la_ubicacion_es_opcional(): void
    {
        $this->comoDireccion();

        Livewire::test(CreateEvento::class)->fillForm($this->formularioDelPanel())->call('create')->assertHasNoFormErrors();

        $evento = Evento::firstOrFail();
        $this->assertNull($evento->municipio_id);
        $this->assertFalse($evento->tieneUbicacion());
        $this->assertNull($evento->urlDelMapa());
    }

    public function test_el_panel_rechaza_coordenadas_fuera_de_rango_y_enlaces_que_no_son_web(): void
    {
        $this->comoDireccion();

        // `ftp://` es una URL válida que no es web: solo la regla con esquema
        // la rechaza. `javascript:` ya lo rechaza cualquier regla de URL.
        Livewire::test(CreateEvento::class)->fillForm($this->formularioDelPanel([
            'lat' => 120,
            'lng' => -200,
            'mapa_url' => 'ftp://mapas.example.com/lugar',
        ]))->call('create')->assertHasFormErrors(['lat', 'lng', 'mapa_url']);

        $this->assertSame(0, Evento::count());
    }

    /** El enlace escrito a mano manda; sin él, las coordenadas abren OpenStreetMap. */
    public function test_la_url_del_mapa_usa_el_enlace_o_las_coordenadas(): void
    {
        $conEnlace = Evento::factory()->create(['lat' => 4.53389, 'lng' => -75.68111, 'mapa_url' => 'https://maps.google.com/?q=Armenia']);
        $soloCoordenadas = Evento::factory()->create(['lat' => 4.53389, 'lng' => -75.68111, 'mapa_url' => null]);

        $this->assertSame('https://maps.google.com/?q=Armenia', $conEnlace->urlDelMapa());
        $this->assertSame(
            'https://www.openstreetmap.org/?mlat=4.53389&mlon=-75.68111#map=17/4.53389/-75.68111',
            $soloCoordenadas->urlDelMapa()
        );
    }

    /**
     * El formulario del calendario comunitario puede mandar municipio,
     * dirección y enlace de mapa. Las coordenadas no: son del panel.
     */
    public function test_el_formulario_comunitario_acepta_la_ubicacion_pero_no_las_coordenadas(): void
    {
        $calarca = Municipio::factory()->create(['nombre' => 'Calarcá']);

        $this->post(route('eventos.comunidad.store'), [
            'titulo' => 'Tarde de trova',
            'fecha' => '2026-10-03',
            'hora_inicio' => '17:00',
            'lugar' => 'Parque principal',
            'descripcion' => 'Trova al aire libre.',
            'municipio_id' => $calarca->id,
            'direccion' => 'Calle 39 con carrera 25',
            'mapa_url' => 'https://maps.google.com/?q=Parque+Calarca',
            'lat' => 1,
            'lng' => 1,
        ])->assertRedirect();

        $evento = Evento::firstOrFail();
        $this->assertTrue($evento->municipio->is($calarca));
        $this->assertSame('Calle 39 con carrera 25', $evento->direccion);
        $this->assertSame('https://maps.google.com/?q=Parque+Calarca', $evento->mapa_url);
        $this->assertNull($evento->lat);
        $this->assertNull($evento->lng);
    }

    public function test_el_formulario_comunitario_rechaza_un_municipio_inventado_y_un_enlace_que_no_es_web(): void
    {
        $this->post(route('eventos.comunidad.store'), [
            'titulo' => 'Tarde de trova',
            'fecha' => '2026-10-03',
            'hora_inicio' => '17:00',
            'lugar' => 'Parque principal',
            'descripcion' => 'Trova al aire libre.',
            'municipio_id' => 9999,
            'mapa_url' => 'ftp://mapas.example.com/lugar',
        ])->assertSessionHasErrors(['municipio_id', 'mapa_url']);

        $this->assertSame(0, Evento::count());
    }
}

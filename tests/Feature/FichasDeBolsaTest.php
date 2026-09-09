<?php

namespace Tests\Feature;

use App\Enums\TipoArtista;
use App\Models\Artista;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FichasDeBolsaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_artista_guarda_el_consentimiento_de_quien_lo_inscribio(): void
    {
        $artista = Artista::factory()->create([
            'correo' => 'dj@ejemplo.test',
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ]);

        $this->assertTrue($artista->acepta_datos);
        $this->assertNotNull($artista->consentimiento_at);
        $this->assertSame('dj@ejemplo.test', $artista->correo);
    }

    public function test_las_fichas_nacen_sin_dueno_hasta_que_tengan_cuenta_propia(): void
    {
        $this->assertNull(Artista::factory()->create()->user_id);
        $this->assertNull(Proveedor::factory()->create()->user_id);
    }

    public function test_solo_las_fichas_publicadas_salen_en_las_consultas_publicas(): void
    {
        Artista::factory()->publicado()->create(['nombre' => 'DJ Aprobado']);
        Artista::factory()->pendiente()->create(['nombre' => 'DJ En Revision']);

        $publicados = Artista::publicado()->pluck('nombre');

        $this->assertTrue($publicados->contains('DJ Aprobado'));
        $this->assertFalse($publicados->contains('DJ En Revision'));
    }

    // --- El escaparate de artistas (SUA-12) ---

    /**
     * El aviso de vacío decía SIEMPRE «No hay artistas con ese filtro. Prueba
     * con otro género o tipo», hubiera filtro o no.
     *
     * Medido contra producción el 9 de septiembre de 2026: cero fichas
     * publicadas, así que quien entraba a `/artistas` sin tocar nada leía que su
     * filtro no daba resultados —no había filtro— y que probara otro género
     * —no hay ninguno que funcione—. Un mensaje que manda a buscar donde no hay
     * nada es peor que no decir nada.
     *
     * La bolsa de empleo ya distinguía los dos casos; este escaparate no.
     */
    public function test_sin_artistas_el_aviso_no_habla_de_filtros(): void
    {
        $respuesta = $this->get(route('artistas.index'))->assertOk();

        $respuesta->assertDontSee('con ese filtro');
        $respuesta->assertDontSee('Prueba con otro género o tipo');
    }

    public function test_un_filtro_sin_resultados_si_lo_dice(): void
    {
        Artista::factory()->publicado()->create(['tipo' => TipoArtista::Dj]);

        $this->get(route('artistas.index', ['tipo' => TipoArtista::Banda->value]))
            ->assertOk()
            ->assertSee('con ese filtro');
    }

    /**
     * Mismo defecto que tenía la bolsa de empleo, y aquí se veía dentro del
     * MISMO formulario: «Género musical» ya salía de las fichas publicadas
     —en producción se quedaba en «Todos los géneros», que es correcto— y
     * «Tipo» seguía ofreciendo las cuatro opciones del enum, todas muertas.
     */
    public function test_el_selector_de_tipo_solo_ofrece_los_que_tienen_ficha_publicada(): void
    {
        Artista::factory()->publicado()->create(['tipo' => TipoArtista::Dj]);
        Artista::factory()->pendiente()->create(['tipo' => TipoArtista::Banda]);

        $tipos = $this->get(route('artistas.index'))->assertOk()->viewData('tipos');

        $this->assertSame([TipoArtista::Dj], $tipos, 'Solo los tipos que de verdad tienen ficha publicada.');
    }

    /** Lo elegido no puede desaparecer del desplegable que lo muestra. */
    public function test_el_tipo_elegido_sigue_en_el_selector_aunque_no_tenga_fichas(): void
    {
        Artista::factory()->publicado()->create(['tipo' => TipoArtista::Dj]);

        $tipos = $this->get(route('artistas.index', ['tipo' => TipoArtista::Solista->value]))
            ->assertOk()
            ->viewData('tipos');

        $this->assertContains(TipoArtista::Solista, $tipos);
    }

    /** Sin fichas publicadas no hay nada que filtrar, así que el filtro sobra. */
    public function test_sin_artistas_no_se_pinta_la_caja_de_filtros(): void
    {
        $this->get(route('artistas.index'))
            ->assertOk()
            ->assertDontSee('Todos los géneros');
    }

    public function test_con_artistas_la_caja_de_filtros_sigue_estando(): void
    {
        Artista::factory()->publicado()->create();

        $this->get(route('artistas.index'))
            ->assertOk()
            ->assertSee('Todos los géneros');
    }
}

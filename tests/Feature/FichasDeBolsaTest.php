<?php

namespace Tests\Feature;

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
}

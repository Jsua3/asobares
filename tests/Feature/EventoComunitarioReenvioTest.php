<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * El calendario comunitario publica al instante, así que un doble clic o un
 * reenvío del navegador publicaría el mismo evento dos veces. El mismo
 * título, fecha, hora y lugar en diez minutos es el mismo envío.
 */
class EventoComunitarioReenvioTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Encuentro comunitario de música',
            'fecha' => '2026-09-20',
            'hora_inicio' => '19:00',
            'hora_fin' => '22:00',
            'lugar' => 'Armenia',
            'descripcion' => 'Una noche de música local.',
        ], $cambios);
    }

    public function test_reenviar_el_mismo_evento_no_lo_publica_dos_veces_ni_avisa_dos_veces(): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect(route('eventos.calendario', [2026, '09']));
        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect(route('eventos.calendario', [2026, '09']))
            ->assertSessionHas('exito');

        $this->assertSame(1, Evento::count());
        $this->assertSame(1, $direccion->notifications()->count());
    }

    /** El reenvío tampoco deja una imagen huérfana en el disco. */
    public function test_el_reenvio_no_guarda_otra_imagen(): void
    {
        Storage::fake(config('almacenamiento.publico'));

        $this->post(route('eventos.comunidad.store'), $this->datos(['imagen' => UploadedFile::fake()->image('cartel.jpg', 800, 600)]));
        $this->post(route('eventos.comunidad.store'), $this->datos(['imagen' => UploadedFile::fake()->image('cartel.jpg', 800, 600)]));

        $this->assertSame(1, Evento::count());
        $this->assertCount(1, Storage::disk(config('almacenamiento.publico'))->allFiles('eventos'));
    }

    public function test_otro_horario_u_otro_lugar_es_otro_evento(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos());
        $this->post(route('eventos.comunidad.store'), $this->datos(['hora_inicio' => '20:00']));
        $this->post(route('eventos.comunidad.store'), $this->datos(['lugar' => 'Calarcá']));

        $this->assertSame(3, Evento::count());
    }

    public function test_pasados_diez_minutos_el_mismo_evento_si_se_publica_otra_vez(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos());

        Carbon::setTestNow(now()->addMinutes(11));
        $this->post(route('eventos.comunidad.store'), $this->datos());
        Carbon::setTestNow();

        $this->assertSame(2, Evento::count());
    }
}

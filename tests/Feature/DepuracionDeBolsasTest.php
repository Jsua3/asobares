<?php

namespace Tests\Feature;

use App\Models\Aspirante;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepuracionDeBolsasTest extends TestCase
{
    use RefreshDatabase;

    public function test_borra_las_postulaciones_de_vacantes_cerradas_hace_mucho(): void
    {
        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        $reciente = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonth()]);

        Postulacion::factory()->for($vieja)->create();
        $sobreviviente = Postulacion::factory()->for($reciente)->create();

        $this->artisan('bolsas:depurar')->assertExitCode(0);

        $this->assertSame(1, Postulacion::count());
        $this->assertNotNull($sobreviviente->fresh());
    }

    public function test_borra_las_postulaciones_de_vacantes_vencidas_hace_mucho(): void
    {
        $vencidaHaceRato = Vacante::factory()->publicado()->create([
            'fecha_limite' => now()->subMonths(9)->toDateString(),
        ]);

        Postulacion::factory()->for($vencidaHaceRato)->create();

        $this->artisan('bolsas:depurar');

        $this->assertSame(0, Postulacion::count());
    }

    public function test_no_toca_las_postulaciones_de_vacantes_abiertas(): void
    {
        $abierta = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($abierta)->count(3)->create();

        $this->artisan('bolsas:depurar');

        $this->assertSame(3, Postulacion::count());
    }

    public function test_borra_los_perfiles_del_banco_que_llevan_mas_de_un_ano_quietos(): void
    {
        Aspirante::factory()->abandonado()->create(['correo' => 'viejo@ejemplo.test']);
        Aspirante::factory()->create(['correo' => 'activo@ejemplo.test']);

        $this->artisan('bolsas:depurar');

        $this->assertSame(1, Aspirante::count());
        $this->assertSame('activo@ejemplo.test', Aspirante::firstOrFail()->correo);
    }

    public function test_con_pretend_no_borra_nada(): void
    {
        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar', ['--pretend' => true])->assertExitCode(0);

        $this->assertSame(1, Postulacion::count());
        $this->assertSame(1, Aspirante::count());
    }

    public function test_los_plazos_salen_de_la_configuracion(): void
    {
        config(['bolsas.retencion_postulaciones_meses' => 24]);

        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();

        $this->artisan('bolsas:depurar');

        $this->assertSame(1, Postulacion::count(), 'Con 24 meses de plazo, una vacante cerrada hace 8 aún no vence.');
    }
}

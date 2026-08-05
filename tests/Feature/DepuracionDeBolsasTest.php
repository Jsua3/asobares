<?php

namespace Tests\Feature;

use App\Enums\EstadoDeGestion;
use App\Models\Aspirante;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
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

    /**
     * Con una sola vacante en la base, una consulta con la correlación rota
     * también «pasaría» este test por pura casualidad. Aquí conviven a
     * propósito una vacante abierta con otras dos ya caducadas para que una
     * fuga de correlación en el `whereHas` (el `orWhere` mirando toda la
     * tabla en vez de solo la vacante de cada postulación) sí se note.
     */
    public function test_no_toca_las_postulaciones_de_vacantes_abiertas_aunque_convivan_con_vacantes_caducadas(): void
    {
        $abierta = Vacante::factory()->publicado()->create();
        $cerrada = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(9)]);
        $vencida = Vacante::factory()->publicado()->create(['fecha_limite' => now()->subMonths(9)->toDateString()]);

        $sobreviviente = Postulacion::factory()->for($abierta)->create();
        Postulacion::factory()->for($cerrada)->create();
        Postulacion::factory()->for($vencida)->create();

        $this->artisan('bolsas:depurar');

        $this->assertSame(1, Postulacion::count());
        $this->assertNotNull($sobreviviente->fresh());
    }

    public function test_borra_los_perfiles_del_banco_cuyo_consentimiento_vencio(): void
    {
        Aspirante::factory()->abandonado()->create(['correo' => 'viejo@ejemplo.test']);
        Aspirante::factory()->create(['correo' => 'activo@ejemplo.test']);

        $this->artisan('bolsas:depurar');

        $this->assertSame(1, Aspirante::count());
        $this->assertSame('activo@ejemplo.test', Aspirante::firstOrFail()->correo);
    }

    /**
     * Antes la purga se anclaba a `updated_at`: cualquier edición desde el
     * panel —incluido que la secretaría cambie el estado de gestión— regalaba
     * doce meses más sin que la persona hubiera renovado nada. Ahora cuelga
     * de `consentimiento_at`, que una edición cualquiera no toca.
     */
    public function test_una_edicion_desde_el_panel_no_salva_a_un_aspirante_con_el_consentimiento_vencido(): void
    {
        $aspirante = Aspirante::factory()->abandonado()->create();
        $consentimientoOriginal = $aspirante->consentimiento_at;

        // La secretaría gestiona el perfil: esto toca `updated_at`, no `consentimiento_at`.
        $aspirante->update(['estado' => EstadoDeGestion::Contactado]);

        $this->assertTrue($aspirante->fresh()->consentimiento_at->equalTo($consentimientoOriginal));

        $this->artisan('bolsas:depurar');

        $this->assertSame(0, Aspirante::count(), 'El consentimiento sigue vencido: la edición no lo renovó.');
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

    public function test_el_plazo_de_aspirantes_sale_de_la_configuracion(): void
    {
        config(['bolsas.retencion_aspirantes_meses' => 24]);

        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar');

        $this->assertSame(1, Aspirante::count(), 'Con 24 meses de plazo, un consentimiento de hace 18 aún no vence.');
    }

    public function test_registra_en_la_bitacora_cuando_borra_datos(): void
    {
        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar');

        $registro = Activity::where('log_name', 'bolsas')->first();

        $this->assertNotNull($registro);
        $this->assertSame('deleted', $registro->event);
        $this->assertStringContainsString('1 postulaciones', $registro->description);
        $this->assertStringContainsString('1 perfiles', $registro->description);
    }

    public function test_con_pretend_no_escribe_bitacora(): void
    {
        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar', ['--pretend' => true]);

        $this->assertSame(0, Activity::where('log_name', 'bolsas')->count());
    }

    public function test_aborta_si_el_plazo_de_postulaciones_es_cero(): void
    {
        config(['bolsas.retencion_postulaciones_meses' => 0]);

        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar')->assertExitCode(1);

        $this->assertSame(1, Postulacion::count());
        $this->assertSame(1, Aspirante::count());
        $this->assertSame(0, Activity::where('log_name', 'bolsas')->count());
    }

    public function test_aborta_si_el_plazo_de_postulaciones_es_negativo(): void
    {
        config(['bolsas.retencion_postulaciones_meses' => -1]);

        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar')->assertExitCode(1);

        $this->assertSame(1, Postulacion::count());
        $this->assertSame(1, Aspirante::count());
        $this->assertSame(0, Activity::where('log_name', 'bolsas')->count());
    }

    public function test_aborta_si_el_plazo_de_aspirantes_es_cero(): void
    {
        config(['bolsas.retencion_aspirantes_meses' => 0]);

        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar')->assertExitCode(1);

        $this->assertSame(1, Postulacion::count());
        $this->assertSame(1, Aspirante::count());
        $this->assertSame(0, Activity::where('log_name', 'bolsas')->count());
    }

    public function test_aborta_si_el_plazo_de_aspirantes_es_negativo(): void
    {
        config(['bolsas.retencion_aspirantes_meses' => -1]);

        $vieja = Vacante::factory()->publicado()->create(['cerrada_at' => now()->subMonths(8)]);
        Postulacion::factory()->for($vieja)->create();
        Aspirante::factory()->abandonado()->create();

        $this->artisan('bolsas:depurar')->assertExitCode(1);

        $this->assertSame(1, Postulacion::count());
        $this->assertSame(1, Aspirante::count());
        $this->assertSame(0, Activity::where('log_name', 'bolsas')->count());
    }
}

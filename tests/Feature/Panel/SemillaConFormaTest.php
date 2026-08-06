<?php

namespace Tests\Feature\Panel;

use App\Models\ConsultaGuia;
use Database\Seeders\ConsultaGuiaSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Las gráficas del tablero y del observatorio se diseñan contra estos datos.
 * Con la semilla plana anterior salían líneas en cero, y una gráfica vacía
 * enseña que el tablero no sirve.
 */
class SemillaConFormaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_hay_consultas_de_guia_suficientes_para_un_mapa_de_calor(): void
    {
        $this->assertGreaterThan(200, ConsultaGuia::count());
    }

    public function test_las_consultas_cubren_al_menos_un_ano_y_medio(): void
    {
        $masAntigua = ConsultaGuia::min('created_at');

        $this->assertLessThan(
            now()->subMonths(15),
            Carbon::parse($masAntigua),
            'La serie debe abarcar ~18 meses para que el observatorio tenga historia.'
        );
    }

    public function test_armenia_concentra_las_consultas_como_en_la_realidad(): void
    {
        $porMunicipio = ConsultaGuia::query()
            ->join('municipios', 'municipios.id', '=', 'consultas_guia.municipio_id')
            ->selectRaw('municipios.nombre, count(*) as total')
            ->groupBy('municipios.nombre')
            ->orderByDesc('total')
            ->pluck('total', 'nombre');

        $this->assertSame('Armenia', $porMunicipio->keys()->first());
        // Y no es un monocultivo: los municipios pequeños también aparecen.
        $this->assertGreaterThanOrEqual(4, $porMunicipio->count());
    }

    public function test_el_seeder_no_duplica_al_correr_dos_veces(): void
    {
        $antes = ConsultaGuia::count();

        $this->seed(ConsultaGuiaSeeder::class);

        $this->assertSame($antes, ConsultaGuia::count());
    }
}

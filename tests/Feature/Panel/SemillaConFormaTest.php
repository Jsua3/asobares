<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoTransaccion;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\ConsultaGuia;
use App\Models\Transaccion;
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

    public function test_hasta_el_municipio_mas_pequeno_muestra_crecimiento(): void
    {
        $masPequeno = ConsultaGuia::query()
            ->selectRaw('municipio_id, count(*) as total')
            ->groupBy('municipio_id')
            ->orderBy('total')
            ->first();

        // Contar filas por mes exacto: meses 0-8 (ultimos 9) vs meses 9-17 (primeros 9).
        $primerSemestre = 0;
        $ultimoSemestre = 0;

        foreach (range(0, 8) as $mes) {
            $inicio = now()->subMonths($mes)->startOfMonth();
            $fin = now()->subMonths($mes)->endOfMonth();
            $ultimoSemestre += ConsultaGuia::where('municipio_id', $masPequeno->municipio_id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();
        }

        foreach (range(9, 17) as $mes) {
            $inicio = now()->subMonths($mes)->startOfMonth();
            $fin = now()->subMonths($mes)->endOfMonth();
            $primerSemestre += ConsultaGuia::where('municipio_id', $masPequeno->municipio_id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();
        }

        $this->assertGreaterThan(
            $primerSemestre,
            $ultimoSemestre,
            'La serie debe crecer tambien en los municipios pequenos: una linea plana es el artefacto que esta semilla existe para evitar.'
        );
    }

    public function test_hay_recaudo_en_al_menos_doce_meses_distintos(): void
    {
        $meses = Transaccion::query()
            ->where('estado', EstadoTransaccion::Aprobada)
            ->get()
            ->map(fn ($t): string => $t->created_at->format('Y-m'))
            ->unique();

        $this->assertGreaterThanOrEqual(
            12,
            $meses->count(),
            'Sin serie mensual el gráfico de recaudo es una línea plana.'
        );
    }

    public function test_diciembre_recauda_mas_que_enero(): void
    {
        $porMes = Transaccion::query()
            ->where('estado', EstadoTransaccion::Aprobada)
            ->get()
            ->groupBy(fn ($t): string => $t->created_at->format('m'))
            ->map(fn ($grupo): float => (float) $grupo->sum('monto'));

        $this->assertArrayHasKey('12', $porMes->all());
        $this->assertArrayHasKey('01', $porMes->all());
        $this->assertGreaterThan(
            $porMes['01'],
            $porMes['12'],
            'La vida nocturna factura en diciembre; la semilla debe parecerlo.'
        );
    }

    public function test_el_mes_en_curso_tiene_recaudo_mayor_que_cero(): void
    {
        $recaudoDelMes = Transaccion::query()
            ->where('estado', EstadoTransaccion::Aprobada)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('monto');

        $this->assertGreaterThan(
            0,
            (float) $recaudoDelMes,
            'El mes en curso no puede quedar vacío hasta el cierre: el KPI de recaudo mostraría una caída falsa.'
        );
    }

    public function test_ninguna_transaccion_tiene_fecha_futura(): void
    {
        $masReciente = Transaccion::max('created_at');

        $this->assertLessThanOrEqual(
            now(),
            Carbon::parse($masReciente),
            'La semilla no puede fechar transacciones en el futuro.'
        );
    }

    public function test_todo_asociado_con_cartera_aparece_como_pagador(): void
    {
        $conCartera = Asociado::query()->has('cartera')->pluck('id');

        $pagaron = Transaccion::query()
            ->where('estado', EstadoTransaccion::Aprobada)
            ->whereNotNull('asociado_id')
            ->distinct()
            ->pluck('asociado_id');

        $nuncaPagaron = $conCartera->diff($pagaron);

        $this->assertCount(
            0,
            $nuncaPagaron,
            'Todo asociado con cartera debe aparecer al menos una vez en el historial de dieciocho meses.'
        );
    }

    public function test_ningun_asociado_en_mora_tiene_pagos_en_su_ventana_de_mora(): void
    {
        $enMora = Cartera::where('meses_mora', '>', 0)->get(['asociado_id', 'meses_mora']);

        foreach ($enMora as $cartera) {
            $pagosEnVentana = Transaccion::query()
                ->where('estado', EstadoTransaccion::Aprobada)
                ->where('asociado_id', $cartera->asociado_id)
                ->where('created_at', '>=', now()->subMonths($cartera->meses_mora)->startOfMonth())
                ->count();

            $this->assertSame(
                0,
                $pagosEnVentana,
                "El asociado {$cartera->asociado_id} debe {$cartera->meses_mora} meses de mora y no puede tener pagos dentro de esa ventana."
            );
        }
    }

    /**
     * El gremio crece mes a mes (prompt maestro): si ningún asociado se
     * afilió en los últimos treinta días, la tarjeta «altas este mes» del
     * tablero muestra siempre cero, un artefacto tan falso como el que
     * corrige `ResumenDelGremio` al dejar de leer `created_at`.
     */
    public function test_hay_al_menos_una_afiliacion_en_los_ultimos_treinta_dias(): void
    {
        $this->assertTrue(
            Asociado::where('fecha_afiliacion', '>=', now()->subDays(30)->toDateString())->exists(),
            'La semilla debe dejar al menos un asociado afiliado en el último mes.'
        );
    }
}

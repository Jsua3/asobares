<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoTransaccion;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\ConsultaGuia;
use App\Models\Transaccion;
use App\Models\User;
use App\Panel\ColaDePendientes;
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
            $inicio = now()->startOfMonth()->subMonths($mes);
            $fin = $inicio->copy()->endOfMonth();
            $ultimoSemestre += ConsultaGuia::where('municipio_id', $masPequeno->municipio_id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->count();
        }

        foreach (range(9, 17) as $mes) {
            $inicio = now()->startOfMonth()->subMonths($mes);
            $fin = $inicio->copy()->endOfMonth();
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
                ->where('created_at', '>=', now()->startOfMonth()->subMonths($cartera->meses_mora))
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

    /**
     * Un establecimiento no paga mensualidades antes de afiliarse. Es la
     * prueba que habría atrapado el caso real: `la-cava-del-yipao` afiliado
     * el 2026-07-30 con pagos desde 2025-05-18, catorce meses antes de
     * existir como afiliado.
     */
    public function test_ningun_asociado_tiene_pagos_aprobados_antes_de_su_afiliacion(): void
    {
        $asociados = Asociado::whereNotNull('fecha_afiliacion')->get(['id', 'fecha_afiliacion']);

        foreach ($asociados as $asociado) {
            $pagoMasAntiguo = Transaccion::query()
                ->where('estado', EstadoTransaccion::Aprobada)
                ->where('asociado_id', $asociado->id)
                ->min('created_at');

            if ($pagoMasAntiguo === null) {
                continue;
            }

            $this->assertGreaterThanOrEqual(
                $asociado->fecha_afiliacion->startOfDay(),
                Carbon::parse($pagoMasAntiguo),
                "El asociado {$asociado->id} tiene un pago aprobado anterior a su fecha de afiliación."
            );
        }
    }

    /**
     * `bruma-gastrobar` es `CarteraSeeder::ASOCIADO_DEMO`, el establecimiento
     * de `/mi-cuenta` en el guion de la demo. Que alguien afiliado hace nueve
     * días deba tres meses de mora es la primera pantalla que cualquiera
     * cuestionaría al abrir la demo.
     */
    public function test_ningun_asociado_en_mora_se_afilio_en_los_ultimos_treinta_dias(): void
    {
        $enMoraYReciente = Cartera::query()
            ->where('meses_mora', '>', 0)
            ->whereHas('asociado', fn ($q) => $q->where('fecha_afiliacion', '>=', now()->subDays(30)->toDateString()))
            ->count();

        $this->assertSame(
            0,
            $enMoraYReciente,
            'Quien acaba de afiliarse no puede deber meses de mensualidad.'
        );
    }

    /**
     * Antes la semilla dejaba un único pendiente en todo el sistema, con
     * `updated_at = now()`: la banda de pendientes del tablero se enseñaba
     * en la demo con un solo renglón y el estado "urgente" nunca aparecía.
     * Ahora hay pendientes repartidos entre modelos distintos (vacante,
     * artista, proveedor, noticia) con antigüedades escalonadas.
     */
    public function test_la_semilla_reparte_pendientes_entre_modelos_con_al_menos_uno_urgente(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        $cola = app(ColaDePendientes::class)->para($usuario->fresh());

        $this->assertGreaterThan(
            1,
            count($cola),
            'La banda de pendientes no puede depender de un único renglón: hace falta variedad para que la demo la enseñe bien.'
        );

        $this->assertTrue(
            collect($cola)->contains(fn (array $fila): bool => $fila['urgente'] === true),
            'Al menos uno de los pendientes sembrados debe superar el umbral de "urgente" (más de cinco días).'
        );
    }
}

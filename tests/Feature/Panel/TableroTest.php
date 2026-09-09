<?php

namespace Tests\Feature\Panel;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoMensaje;
use App\Enums\EstadoPublicacion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Enums\TipoMensaje;
use App\Filament\Widgets\AsociadosPorMunicipio;
use App\Filament\Widgets\EntradasAlSitio;
use App\Filament\Widgets\InscripcionesDelMes;
use App\Filament\Widgets\PaginasMasVisitadas;
use App\Filament\Widgets\PendientesDeAprobacion;
use App\Filament\Widgets\PorDondeEntranAlSitio;
use App\Filament\Widgets\RecaudoMensual;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\UltimasTransacciones;
use App\Filament\Widgets\VisitasDelSitio;
use App\Models\Asociado;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Transaccion;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Support\View\ComponentAttributeBag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El tablero deja de ser un marcador y pasa a ser una cola de trabajo.
 */
class TableroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_banda_de_accion_lista_lo_pendiente_con_enlace(): void
    {
        Asociado::factory()->count(3)->create([
            'estado' => EstadoPublicacion::PendienteAprobacion,
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(PendientesDeAprobacion::class)
            ->assertSee('3 asociados esperando tu aprobación')
            ->assertSee('Revisar');
    }

    public function test_la_banda_se_esconde_cuando_no_hay_nada_pendiente(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->assertFalse(PendientesDeAprobacion::canView());
    }

    /** Un tablero con una cola vacía enseña que el tablero no sirve. */
    public function test_la_banda_se_muestra_cuando_si_hay_pendientes(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->assertTrue(PendientesDeAprobacion::canView());
    }

    /**
     * Filament monta el widget llamando primero a `canView()` y luego
     * renderizándolo. Si `ColaDePendientes` no está registrado como
     * singleton, cada llamada resuelve una instancia nueva y su memoización
     * interna no sirve de nada: el cálculo completo (una consulta COUNT y
     * una MIN contra `asociados`) se repite dos veces en vez de una.
     */
    public function test_canview_y_el_render_comparten_la_memoizacion_del_singleton(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $consultasAAsociados = 0;
        DB::listen(function ($consulta) use (&$consultasAAsociados): void {
            if (str_contains($consulta->sql, 'asociados')) {
                $consultasAAsociados++;
            }
        });

        PendientesDeAprobacion::canView();
        Livewire::test(PendientesDeAprobacion::class);

        $this->assertSame(
            2,
            $consultasAAsociados,
            'canView() y el render deben compartir la misma instancia memoizada: un solo calculo, dos consultas (COUNT y MIN) contra asociados.'
        );
    }

    public function test_la_direccion_ve_cuatro_tarjetas_con_la_plata(): void
    {
        Municipio::factory()->count(3)->create();

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('Recaudado este mes')
            ->assertSee('Cartera en mora')
            ->assertSee('Cobertura territorial');
    }

    /** La secretaría no ve plata: no tiene `ver_transaccion` ni `ver_cartera`. */
    public function test_la_secretaria_ve_su_propio_juego_de_tarjetas(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('Pendientes de moderación')
            ->assertSee('Bandeja sin responder')
            ->assertDontSee('Recaudado este mes')
            ->assertDontSee('Cartera en mora');
    }

    public function test_el_resumen_muestra_exactamente_cuatro_tarjetas(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $widget = new ResumenDelGremio;
        $stats = (fn (): array => $this->getStats())->call($widget);

        $this->assertCount(4, $stats, 'Cuatro tarjetas, no seis: seis es un marcador, no un tablero.');
    }

    public function test_el_recaudo_mensual_reemplazo_a_las_inscripciones_de_30_dias(): void
    {
        $this->assertFalse(
            class_exists(InscripcionesDelMes::class),
            'La gráfica de 30 días era una línea plana en cero con eventos mensuales.'
        );
        $this->assertTrue(class_exists(RecaudoMensual::class));
    }

    /**
     * El widget anterior traía todos los modelos a memoria para agruparlos con
     * `groupBy` de Collection. Se mide el comportamiento —cuántas consultas
     * salen— y no cómo está escrito el archivo: una aserción sobre el texto
     * fuente se rompe con cualquier `->get()` legítimo en otro método.
     *
     * ⚠️ No hay `TransaccionFactory` en el proyecto: las transacciones se
     * crean a mano con la misma forma que usa `TransaccionSeeder`.
     */
    public function test_el_recaudo_mensual_agrega_en_una_sola_consulta(): void
    {
        foreach (range(1, (int) now()->format('n')) as $mes) {
            Transaccion::create([
                'referencia' => Transaccion::generarReferencia(),
                'concepto' => ConceptoTransaccion::Mensualidad,
                'monto' => 50000,
                'moneda' => 'COP',
                'estado' => EstadoTransaccion::Aprobada,
                'metodo' => MetodoPago::Pse,
                'payload' => ['origen' => 'prueba'],
                'created_at' => now()->startOfYear()->addMonths($mes - 1)->addDay(),
            ]);
        }

        $consultas = [];
        DB::listen(function ($evento) use (&$consultas): void {
            $consultas[] = $evento->sql;
        });

        $widget = new RecaudoMensual;
        $datos = (fn (): array => $this->getData())->call($widget);

        $this->assertCount(
            1,
            $consultas,
            'La serie se agrega en SQL: una consulta, no una por mes ni una por fila.'
        );
        $this->assertStringContainsStringIgnoringCase('sum(monto)', $consultas[0]);
        $this->assertCount((int) now()->format('n'), $datos['labels']);
    }

    /**
     * El delta de «Recaudado este mes» comparaba el mes en curso incompleto
     * contra el mes anterior COMPLETO: al principio de mes eso siempre pinta
     * mal (el día 6, $150.000 contra $930.000 del mes pasado entero sería un
     * −84 % que no dice nada). Se siembra marzo cortado al día 15 y febrero
     * con la mitad del dinero antes del día 15 y la otra mitad después, de
     * forma que la ventana equivalente y el mes completo den signos
     * contrarios: si el widget usara el mes anterior entero, o si arrastrara
     * un pago futuro dentro del mes en curso, el resultado sería negativo.
     */
    public function test_el_delta_de_recaudo_compara_el_mismo_tramo_de_dias_no_el_mes_completo(): void
    {
        $this->travelTo(Carbon::create(2026, 3, 15, 12, 0, 0));

        // Marzo, dentro de la ventana comparable (1 al 15): sí cuenta.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 10), 200_000);
        // Marzo, después de «hoy»: un pago que aún no ha ocurrido no puede
        // contar en el recaudo del mes en curso.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 20), 500_000);

        // Febrero, dentro de la ventana equivalente (1 al 15): sí cuenta.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 2, 10), 100_000);
        // Febrero, después del día 15: solo entraría si el cálculo comparara
        // contra el mes anterior completo en vez de la ventana equivalente.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 2, 20), 900_000);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        // $200.000 (marzo 1-15) vs. $100.000 (febrero 1-15) = +100,0 %. Con
        // el mes anterior completo ($1.000.000) o el pago futuro de marzo
        // sumado ($700.000) el delta habría salido negativo.
        Livewire::test(ResumenDelGremio::class)
            ->assertSee('$200.000')
            ->assertSee('+100,0 % vs. mismo tramo del mes anterior')
            ->assertDontSee('700.000')
            ->assertDontSee('1.000.000');
    }

    /**
     * «+300,0 %» sobre cuatro pagos contra uno es ruido de muestra chica,
     * no una tendencia: el titular no aguanta la pregunta «¿de qué a qué?».
     * `Stat` no tiene un hueco propio para la n, así que va en la misma
     * description() que el porcentaje.
     */
    public function test_el_recaudo_muestra_la_n_de_cada_tramo_junto_al_porcentaje(): void
    {
        $this->travelTo(Carbon::create(2026, 3, 15, 12, 0, 0));

        // Cuatro pagos en el tramo actual (marzo 1-15) contra uno en el
        // mismo tramo del mes anterior (febrero 1-15): +300,0 %.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 5), 50_000);
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 8), 50_000);
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 10), 50_000);
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 12), 50_000);
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 2, 10), 50_000);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('+300,0 % vs. mismo tramo del mes anterior')
            ->assertSee('n = 4 vs. n = 1');
    }

    /**
     * `created_at` es cuándo Eloquent insertó la fila, no cuándo el
     * establecimiento se afilió al gremio. Este asociado se inserta HOY pero
     * se afilió hace seis meses: con la implementación vieja, que leía
     * `created_at`, esta prueba habría fallado, porque hoy es exactamente
     * cuando se creó la fila.
     */
    public function test_las_altas_del_mes_cuentan_por_fecha_de_afiliacion_no_por_insercion(): void
    {
        Asociado::factory()->publicado()->create([
            'fecha_afiliacion' => now()->subMonths(6)->toDateString(),
            'created_at' => now(),
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('0 altas este mes')
            ->assertDontSee('1 altas este mes');
    }

    /**
     * «1 altas este mes» era el texto real que producía la base: la
     * descripción concatenaba el conteo sin pasar por `Str::plural`, la misma
     * convención que ya usan `directorio/index.blade.php`,
     * `mi-cuenta/index.blade.php` y el paginador.
     */
    public function test_las_altas_del_mes_singularizan_con_una_sola_alta(): void
    {
        Asociado::factory()->publicado()->create([
            'fecha_afiliacion' => now()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('1 alta este mes')
            ->assertDontSee('1 altas este mes');
    }

    public function test_las_altas_del_mes_pluralizan_con_dos_altas(): void
    {
        Asociado::factory()->publicado()->count(2)->create([
            'fecha_afiliacion' => now()->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)->assertSee('2 altas este mes');
    }

    /**
     * Mismo defecto de concordancia en la tarjeta de secretaría: «1 PQR
     * abiertos» tenía el adjetivo en plural fijo, sin importar el conteo.
     */
    public function test_los_pqr_abiertos_singularizan_con_uno_solo(): void
    {
        $this->crearPqr('PQR-2026-0001');

        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('1 PQR abierto')
            ->assertDontSee('1 PQR abiertos');
    }

    public function test_los_pqr_abiertos_pluralizan_con_dos(): void
    {
        $this->crearPqr('PQR-2026-0001');
        $this->crearPqr('PQR-2026-0002');

        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        Livewire::test(ResumenDelGremio::class)->assertSee('2 PQR abiertos');
    }

    private function crearPqr(string $radicado): Mensaje
    {
        return Mensaje::create([
            'tipo' => TipoMensaje::Pqr,
            'nombre' => 'Carlos Muñoz',
            'correo' => 'carlos@ejemplo.test',
            'mensaje' => 'No me ha llegado el carné de afiliado.',
            'radicado' => $radicado,
            'estado' => EstadoMensaje::Nuevo,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ]);
    }

    private function sembrarTransaccionAprobada(Carbon $fecha, float $monto): void
    {
        Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Aprobada,
            'metodo' => MetodoPago::Pse,
            'payload' => ['origen' => 'prueba'],
            'created_at' => $fecha,
        ]);
    }

    public function test_el_tablero_es_una_pagina_propia_con_titulo_del_gremio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Tablero del gremio');
    }

    public function test_el_tablero_de_fabrica_ya_no_esta_registrado(): void
    {
        $panel = (new AdminPanelProvider($this->app))
            ->panel(Panel::make());

        $paginas = $panel->getPages();

        $this->assertNotContains(Dashboard::class, $paginas);
    }

    /**
     * `discoverWidgets(in: app_path('Filament/Widgets'))` recorre
     * subdirectorios: sin que las gráficas de `Observatorio/` opten por
     * quedar fuera (`GraficaDelObservatorio::$isDiscovered = false`), las
     * seis se colaban en el tablero con sus `$sort` (1–6) intercalados entre
     * los del tablero (0–4) — Pendientes → Presencia → Resumen → Composición
     * → Recaudo → …, rompiendo las tres bandas que el tablero documenta como
     * su razón de existir y doblando su coste de consultas.
     *
     * Se afirma el conjunto exacto, no solo la ausencia del observatorio: un
     * tablero al que le falte uno de los cinco widgets propios también sería
     * un defecto, y `assertEqualsCanonicalizing` lo atrapa igual que atrapa
     * una fuga del observatorio.
     */
    public function test_el_tablero_trae_exactamente_sus_widgets_y_ninguno_del_observatorio(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $widgets = array_values($panel->getWidgets());

        $this->assertEqualsCanonicalizing(
            [
                PendientesDeAprobacion::class,
                ResumenDelGremio::class,
                RecaudoMensual::class,
                AsociadosPorMunicipio::class,
                UltimasTransacciones::class,
                // Las tres del flujo del sitio (Acta 08, A-03, 9 sep 2026):
                // los números, la curva y por dónde entra la gente.
                EntradasAlSitio::class,
                VisitasDelSitio::class,
                PorDondeEntranAlSitio::class,
                PaginasMasVisitadas::class,
            ],
            $widgets,
            'El tablero debe traer exactamente estos nueve widgets: ni de menos, ni con ninguna gráfica del observatorio colada por discoverWidgets().'
        );

        foreach ($widgets as $widget) {
            $this->assertFalse(
                str_starts_with($widget, 'App\\Filament\\Widgets\\Observatorio\\'),
                "{$widget} es una gráfica del observatorio y no debería estar registrada en el tablero."
            );
        }
    }

    public function test_la_secretaria_tambien_entra_al_tablero(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->get('/admin')->assertOk()->assertSee('Tablero del gremio');
    }

    /**
     * Composición visual del tablero: en `xl` (6 columnas) recaudo ocupa 4
     * y municipios 2, en la misma fila. El resto va a todo el ancho, y lo
     * declara desglosado porque `'full'` a secas solo llega a `lg`. En
     * `md` y móvil las dos gráficas siguen en `full` para que doce
     * municipios no queden ilegibles. Esto no cubre datos ni permisos.
     *
     * @return array<class-string, int|string|array<string, int|string>>
     */
    private function anchosAprobadosDelTablero(): array
    {
        return [
            PendientesDeAprobacion::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 'full',
            ],
            ResumenDelGremio::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 'full',
            ],
            RecaudoMensual::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 4,
            ],
            AsociadosPorMunicipio::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 2,
            ],
            UltimasTransacciones::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 'full',
            ],
            /*
             * La banda del flujo del sitio, rehecha el 9 de septiembre de 2026
             * con el Acta 08 (A-03): tres números a lo ancho, debajo la curva de
             * treinta días también a lo ancho --dos series no caben legibles en
             * cuatro columnas-- y al pie las dos listas ordenadas, 3 + 2 = 6.
             *
             * Las dos van juntas a propósito: una dice por dónde ENTRA la gente y
             * la otra qué MIRA una vez dentro, y leerlas al lado es la mitad de
             * lo que hace útil la cifra.
             */
            EntradasAlSitio::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 'full',
            ],
            VisitasDelSitio::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 'full',
            ],
            PorDondeEntranAlSitio::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 3,
            ],
            PaginasMasVisitadas::class => [
                'default' => 'full',
                'md' => 'full',
                'xl' => 3,
            ],
        ];
    }

    /**
     * Un `columnSpan` sin desglosar no se aplica en todos los anchos: Filament
     * lo guarda como `['lg' => …]`, y la regla base de la rejilla solo lee
     * `--col-span-default`. Con el tablero a una columna daba igual; con la
     * rejilla de 2 en `md` y 6 en `xl` (7 sep) el widget cae a una sola pista
     * y queda a un sexto de fila con el resto vacío.
     * Rotura: devolver `'full'` a secas a cualquiera de los tres.
     */
    public function test_cada_widget_del_tablero_declara_su_ancho_en_todos_los_anchos(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        foreach ($panel->getWidgets() as $claseWidget) {
            $widget = new $claseWidget;
            $atributos = (new ComponentAttributeBag)->gridColumn((fn () => $this->columnSpan)->call($widget));

            $this->assertStringContainsString(
                '--col-span-default',
                (string) $atributos,
                "{$claseWidget} no declara `--col-span-default`: la regla base de la rejilla solo lee esa variable, así que bajo `lg` el widget cae a una sola pista."
            );
        }
    }

    /**
     * La vista propia del widget de pendientes es la única del tablero que no
     * sale de una plantilla de Filament. Quien coloca cada widget en la rejilla
     * es `x-filament-widgets::widget`, que llama a `gridColumn()` con el tramo
     * del widget: sin ese envoltorio la vista se salta el tramo y, con la
     * rejilla de 6 columnas del 7 sep, quedaba a un sexto de fila con el texto
     * y el botón montados uno sobre otro.
     * Rotura: quitar el envoltorio y dejar la tarjeta de vidrio como raíz.
     */
    public function test_la_vista_de_pendientes_conserva_su_sitio_en_la_rejilla(): void
    {
        $vista = File::get(resource_path('views/filament/widgets/pendientes-de-aprobacion.blade.php'));

        $this->assertStringContainsString(
            '<x-filament-widgets::widget>',
            $vista,
            'La vista no usa el envoltorio que coloca el widget en la rejilla del tablero.'
        );

        $this->assertStringContainsString(
            'gridColumn',
            File::get(base_path('vendor/filament/widgets/resources/views/components/widget.blade.php')),
            'El envoltorio de Filament dejó de colocar el widget en la rejilla: revisa esta guardia contra la versión nueva.'
        );
    }

    public function test_el_tablero_reparte_recaudo_y_municipios_en_escritorio(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());
        $esperados = $this->anchosAprobadosDelTablero();

        $this->assertEqualsCanonicalizing(
            array_keys($esperados),
            array_values($panel->getWidgets()),
        );

        foreach ($panel->getWidgets() as $claseWidget) {
            $widget = new $claseWidget;
            $columnSpan = (fn () => $this->columnSpan)->call($widget);

            $this->assertSame(
                $esperados[$claseWidget],
                $columnSpan,
                "{$claseWidget} no declara el columnSpan de la composición aprobada."
            );
        }
    }
}

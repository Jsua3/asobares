<?php

namespace Tests\Feature;

use App\Filament\Widgets\EntradasAlSitio;
use App\Filament\Widgets\PorDondeEntranAlSitio;
use App\Filament\Widgets\VisitasDelSitio;
use App\Models\User;
use App\Models\VisitaDiaria;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * «Métricas de la página: flujo de personas que entran a la página»
 * (petición de la dirección · Acta 08, A-03).
 *
 * El Acta 07 dejó un contador de **páginas servidas**: quien abre cuatro fichas
 * cuenta cuatro. Eso responde «qué se mira y cuánto», y no responde lo que se
 * pidió, que es cuánta gente entra. Lo que falta es distinguir la **llegada**
 * del resto de la navegación, y se distingue mirando el `Referer`: si no viene
 * de nuestro propio dominio, es alguien que entra.
 *
 * ⚠️ **Entradas no es «visitantes únicos», y la diferencia no es un matiz.** Dos
 * visitas de la misma persona en dos días cuentan dos. Contar personas exige
 * guardar IP, cookie o sesión --lo que el Acta 07 descartó a propósito para
 * quedar fuera de la Ley 1581-- y sigue necesitando la política de tratamiento
 * publicada (D-19). Aquí no se guarda nada de quien visita: el encabezado se
 * MIRA para decidir y no se escribe, igual que ya se hace con el navegador para
 * descartar rastreadores.
 */
class FlujoDeEntradasAlSitioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function direccion(): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        return $usuario->fresh();
    }

    // --- Qué es una entrada ---

    public function test_llegar_sin_procedencia_cuenta_como_entrada(): void
    {
        $this->get(route('inicio'))->assertOk();

        $fila = VisitaDiaria::where('ruta', 'inicio')->first();

        $this->assertSame(1, $fila->total);
        $this->assertSame(1, $fila->entradas, 'Quien llega escribiendo la dirección está entrando.');
    }

    public function test_llegar_desde_google_cuenta_como_entrada(): void
    {
        $this->get(route('empleo.index'), ['referer' => 'https://www.google.com/'])->assertOk();

        $this->assertSame(1, VisitaDiaria::where('ruta', 'empleo.index')->value('entradas'));
    }

    /**
     * Lo que separa esta cifra de la anterior: navegar por dentro NO es entrar.
     * Sin esto, «entradas» sería otra vez «páginas servidas» con otro nombre.
     */
    public function test_navegar_por_dentro_del_sitio_no_es_una_entrada(): void
    {
        $this->get(route('directorio.index'), ['referer' => route('inicio')])->assertOk();

        $fila = VisitaDiaria::where('ruta', 'directorio.index')->first();

        $this->assertSame(1, $fila->total, 'Sigue siendo una página servida...');
        $this->assertSame(0, $fila->entradas, '...pero no una llegada al sitio.');
    }

    public function test_una_visita_y_dos_paginas_dejan_una_entrada_y_dos_paginas(): void
    {
        $this->get(route('inicio'))->assertOk();
        $this->get(route('directorio.index'), ['referer' => route('inicio')])->assertOk();

        $this->assertSame(2, (int) VisitaDiaria::sum('total'));
        $this->assertSame(1, (int) VisitaDiaria::sum('entradas'));
    }

    /** El rastreador no entra, igual que no sirve páginas. */
    public function test_un_rastreador_no_deja_entrada(): void
    {
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get(route('inicio'))->assertOk();

        $this->assertSame(0, VisitaDiaria::count());
    }

    /** Lo que no es una página tampoco es una entrada. */
    public function test_el_sitemap_no_deja_entrada(): void
    {
        $this->get('/sitemap.xml')->assertOk();

        $this->assertSame(0, VisitaDiaria::count());
    }

    // --- Lo que no se guarda ---

    /**
     * El `Referer` se mira y no se guarda, igual que el navegador. Si esta
     * columna apareciera, la tabla dejaría de ser un agregado anónimo y entraría
     * en el alcance de la Ley 1581 --una URL de procedencia puede traer términos
     * de búsqueda, identificadores de campaña o el perfil desde el que se hizo
     * clic--.
     */
    public function test_la_tabla_sigue_sin_guardar_nada_de_quien_visita(): void
    {
        $this->get(route('inicio'), ['referer' => 'https://facebook.com/perfil-de-alguien'])->assertOk();

        // Ordenadas, porque el ORDEN no es lo que se afirma y además no es el
        // mismo en los dos motores: SQLite ignora `after()` y añade al final
        // (runbook §14.2). Lo que se afirma es el CONJUNTO: qué columnas hay.
        $columnas = Schema::getColumnListing('visitas_diarias');
        sort($columnas);

        $this->assertSame(
            ['created_at', 'dia', 'entradas', 'id', 'ruta', 'total', 'updated_at'],
            $columnas,
            'Ni procedencia, ni IP, ni navegador, ni sesión.'
        );
    }

    // --- Lo que ve la dirección ---

    public function test_la_grafica_de_visitas_dibuja_las_dos_series(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel('admin');

        $this->get(route('inicio'))->assertOk();
        $this->get(route('directorio.index'), ['referer' => route('inicio')])->assertOk();

        $datos = (fn (): array => $this->getData())->call(new VisitasDelSitio);

        $this->assertCount(2, $datos['datasets'], 'Entradas y páginas servidas, para poder compararlas.');
        $this->assertSame(1, end($datos['datasets'][0]['data']));
        $this->assertSame(2, end($datos['datasets'][1]['data']));
    }

    public function test_por_donde_entran_ordena_las_paginas_de_llegada(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel('admin');

        VisitaDiaria::create(['ruta' => 'guia.index', 'dia' => now()->toDateString(), 'total' => 40, 'entradas' => 30]);
        VisitaDiaria::create(['ruta' => 'inicio', 'dia' => now()->toDateString(), 'total' => 90, 'entradas' => 12]);

        $datos = (fn (): array => $this->getData())->call(new PorDondeEntranAlSitio);

        $this->assertSame([30, 12], $datos['datasets'][0]['data'], 'Ordena por entradas, no por páginas servidas.');
        $this->assertSame(['/abre-tu-negocio', '/'], $datos['labels']);
    }

    /**
     * Un número suelto no es una métrica. La dirección necesita saber si sube o
     * baja, que es lo que se pidió al decir «flujo».
     */
    public function test_la_tarjeta_compara_la_semana_con_la_anterior(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel('admin');

        VisitaDiaria::create(['ruta' => 'inicio', 'dia' => now()->subDays(2)->toDateString(), 'total' => 30, 'entradas' => 20]);
        VisitaDiaria::create(['ruta' => 'inicio', 'dia' => now()->subDays(9)->toDateString(), 'total' => 15, 'entradas' => 10]);

        $tarjetas = (new EntradasAlSitio)->obtenerTarjetas();

        $this->assertSame('20', $tarjetas[0]->getValue());
        $this->assertStringContainsString('100 %', $tarjetas[0]->getDescription());
    }

    /** Sin datos de la semana anterior no se inventa un porcentaje. */
    public function test_sin_semana_anterior_no_se_inventa_una_comparacion(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel('admin');

        VisitaDiaria::create(['ruta' => 'inicio', 'dia' => now()->toDateString(), 'total' => 5, 'entradas' => 5]);

        $tarjetas = (new EntradasAlSitio)->obtenerTarjetas();

        $this->assertStringNotContainsString('%', $tarjetas[0]->getDescription());
    }

    /**
     * La cifra tiene que decir lo que es. «Entradas» a secas se lee como
     * personas distintas, y no lo es.
     */
    public function test_las_dos_graficas_advierten_que_no_son_personas_distintas(): void
    {
        foreach ([new EntradasAlSitio, new PorDondeEntranAlSitio] as $widget) {
            $this->assertStringContainsString(
                'personas distintas',
                (string) $widget->obtenerAdvertencia(),
                'Cada cifra dice qué mide, o alguien la leerá como visitantes únicos.'
            );
        }
    }

    /**
     * Que los tres widgets se PINTEN, no solo que calculen bien.
     *
     * `getData()` prueba la aritmética y no toca la plantilla: un widget con los
     * números correctos y un error de Blade pasa esa prueba y revienta en el
     * tablero. Y mirarlo con ojos no es una opción aquí --el segundo factor
     * impide que una sesión automatizada abra el panel, y el navegador de esta
     * máquina no compone con la ventana detrás (estado.md, deuda del 7 y 8 sep)--,
     * así que este renderizado completo es el sustituto honesto: no prueba que se
     * vea bonito, prueba que se vea.
     */
    public function test_los_tres_widgets_del_flujo_se_pintan_sin_reventar(): void
    {
        $this->actingAs($this->direccion());
        Filament::setCurrentPanel('admin');

        VisitaDiaria::create(['ruta' => 'inicio', 'dia' => now()->toDateString(), 'total' => 90, 'entradas' => 40]);
        VisitaDiaria::create(['ruta' => 'guia.index', 'dia' => now()->subDays(9)->toDateString(), 'total' => 30, 'entradas' => 25]);

        Livewire::test(EntradasAlSitio::class)
            ->assertOk()
            ->assertSee('Entradas esta semana')
            ->assertSee('Páginas por visita')
            ->assertSee('no personas distintas');

        Livewire::test(VisitasDelSitio::class)
            ->assertOk()
            ->assertSee('Flujo del sitio');

        Livewire::test(PorDondeEntranAlSitio::class)
            ->assertOk()
            ->assertSee('Por dónde entran');
    }

    /**
     * El tablero tiene que traerlos puestos. Un widget que existe, se prueba y no
     * está registrado es trabajo que nadie ve: exactamente el modo de fallo que
     * este proyecto ya pagó con la analítica del Acta 07.
     */
    public function test_los_tres_widgets_estan_registrados_en_el_tablero(): void
    {
        $registrados = Filament::getPanel('admin')->getWidgets();

        foreach ([EntradasAlSitio::class, VisitasDelSitio::class, PorDondeEntranAlSitio::class] as $widget) {
            $this->assertContains($widget, $registrados, "«{$widget}» no está en el tablero.");
        }
    }

    // --- La misma puerta que el resto del observatorio ---

    public function test_solo_quien_ve_el_observatorio_ve_el_flujo(): void
    {
        $asociado = User::factory()->create();
        $asociado->syncRoles([User::ROL_ASOCIADO]);
        $this->actingAs($asociado->fresh());

        $this->assertFalse(EntradasAlSitio::canView());
        $this->assertFalse(PorDondeEntranAlSitio::canView());

        $this->actingAs($this->direccion());

        $this->assertTrue(EntradasAlSitio::canView());
        $this->assertTrue(PorDondeEntranAlSitio::canView());
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Filament\Widgets\PaginasMasVisitadas;
use App\Filament\Widgets\VisitasDelSitio;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\VisitaDiaria;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Analítica del sitio (Acta 07, A-02, con la contrapropuesta aceptada).
 *
 * Sin Google Analytics ni paquetes de terceros: se cuenta con el mismo molde
 * que ya cuenta las consultas de la guía normativa, que no guarda IP, ni
 * navegador, ni sesión --así es un agregado y no un dato personal, y queda
 * fuera del alcance de la Ley 1581--.
 *
 * Y una vuelta de tuerca más: no se guarda una fila por visita sino un contador
 * por ruta y día. Además de dejar la tabla acotada --rutas por días, no visitas--
 * quita del medio la última traza que quedaba, que era la hora exacta de cada
 * visita.
 */
class AnaliticaDelSitioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    // --- Lo que se cuenta ---

    public function test_visitar_una_pagina_publica_suma_una_visita(): void
    {
        $this->get(route('inicio'))->assertOk();

        $this->assertDatabaseHas('visitas_diarias', [
            'ruta' => 'inicio',
            'dia' => now()->toDateString(),
            'total' => 1,
        ]);
    }

    /**
     * Un contador, no un registro: dos visitas el mismo día suman en la misma
     * fila. Si esto se rompiera, la tabla crecería con el tráfico en vez de con
     * el calendario.
     */
    public function test_dos_visitas_el_mismo_dia_suman_en_la_misma_fila(): void
    {
        $this->get(route('inicio'))->assertOk();
        $this->get(route('inicio'))->assertOk();
        $this->get(route('contacto'))->assertOk();

        $this->assertSame(2, VisitaDiaria::count(), 'Cada ruta y día es una sola fila.');
        $this->assertSame(2, (int) VisitaDiaria::where('ruta', 'inicio')->value('total'));
    }

    // --- Lo que NO se cuenta ---

    /**
     * ⚠️ Esta prueba pasa por partida doble y conviene saberlo: el panel arma su
     * propia pila de middleware y este contador ni siquiera llega ahí. Lo que
     * fija es el resultado --la analítica del sitio no vigila el trabajo de la
     * oficina--, no el filtro. Del filtro se ocupa la prueba de abajo.
     */
    public function test_el_panel_de_administracion_no_cuenta_como_visita_del_sitio(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($usuario->fresh())->get('/admin/beneficios');

        $this->assertSame(0, VisitaDiaria::count(), 'La analítica del sitio no vigila el trabajo de la oficina.');
    }

    /**
     * El portal del afiliado SÍ pasa por el grupo `web`, así que es esta ruta
     * --y no la del panel-- la que ejerce de verdad el filtro de lo que no es
     * sitio público.
     */
    public function test_el_portal_del_afiliado_no_cuenta_como_visita_del_sitio(): void
    {
        $asociado = User::factory()->create(['asociado_id' => Asociado::factory()->publicado()->create()->id]);
        $asociado->syncRoles([User::ROL_ASOCIADO]);

        $this->actingAs($asociado->fresh())->get(route('mi-cuenta.index'))->assertOk();

        $this->assertSame(0, VisitaDiaria::count());
    }

    /**
     * El contrato de este middleware, escrito en `bootstrap/app.php`, dice
     * «cuenta páginas servidas, **no descargas** ni webhooks». Descargar un
     * formato de la guía es una descarga, va por el grupo `web`, tiene nombre de
     * ruta y responde 200: cumplía las cuatro condiciones del filtro viejo y se
     * contaba como si fuera una página. Encima ya se registra aparte en
     * `consultas_guia`, así que quedaba contada dos veces en dos sistemas.
     */
    public function test_descargar_un_formato_de_la_guia_no_cuenta_como_pagina_servida(): void
    {
        $requisito = $this->requisitoConFormato();

        $this->get(route('guia.formato', $requisito))->assertOk();

        $this->assertSame(
            0,
            VisitaDiaria::count(),
            'Una descarga no es una página servida: lo dice el contrato del middleware.'
        );
    }

    /**
     * `robots.txt` y `sitemap.xml` son rutas con nombre, en el grupo `web`, que
     * responden 200 a un GET. Los pedían casi solo rastreadores, y el filtro por
     * agente de usuario solo atrapa a los conocidos: los demás acababan entre las
     * ocho barras de «Secciones más visitadas» que lee la dirección.
     *
     * @param  string  $ruta  nombre de la ruta que no es una página
     */
    #[DataProvider('rutasQueNoSonPaginas')]
    public function test_lo_que_no_es_una_pagina_no_cuenta(string $ruta): void
    {
        $this->get(route($ruta))->assertOk();

        $this->assertSame(0, VisitaDiaria::count(), "«{$ruta}» no es una página del sitio.");
    }

    /** @return array<string, array{0: string}> */
    public static function rutasQueNoSonPaginas(): array
    {
        return [
            'robots.txt' => ['robots'],
            'sitemap.xml' => ['sitemap'],
        ];
    }

    /** Un requisito publicado con su formato puesto en el disco privado. */
    private function requisitoConFormato(): RequisitoApertura
    {
        Storage::fake('local');
        Storage::disk('local')->put('formatos/formato.pdf', 'PDF');

        return RequisitoApertura::factory()
            ->for(Municipio::factory())
            ->publicado()
            ->create(['adjunto' => 'formatos/formato.pdf', 'adjunto_nombre' => 'Formato']);
    }

    /**
     * Las páginas de la pasarela no son el sitio público, y el propio sitio ya lo
     * dice: `robots.txt` lista `/pago/` y `/pago-simulado` entre las zonas
     * privadas, junto al panel y al portal del afiliado. Contarlas mezcla el
     * tráfico de un cobro con el interés por el contenido.
     */
    public function test_las_paginas_de_la_pasarela_no_cuentan_como_visita_del_sitio(): void
    {
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'asociado_id' => Asociado::factory()->create()->id,
            'monto' => 50000,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
        ]);

        // La página firmada del detalle del cobro, que es la que responde 200:
        // `pago.retorno` solo redirige hacia ella y una redirección no se cuenta.
        $this->get($transaccion->urlDeEstado())->assertOk();

        $this->assertSame(0, VisitaDiaria::count(), 'La pasarela es zona privada, y robots.txt ya lo declara.');
    }

    public function test_una_peticion_que_no_es_get_no_cuenta(): void
    {
        $this->post(route('contacto.store'), []);

        $this->assertSame(0, VisitaDiaria::count());
    }

    /**
     * Una dirección que no existe no llega ni a tener nombre de ruta, así que
     * la corta la rama de arriba. Se deja escrito para que nadie lea esta
     * prueba como si fuera la del código de respuesta: esa es la siguiente.
     */
    public function test_una_direccion_que_no_existe_no_cuenta(): void
    {
        $this->get('/una-ruta-que-no-existe')->assertNotFound();

        $this->assertSame(0, VisitaDiaria::count());
    }

    /**
     * Esta sí ejerce el código de respuesta: la ruta existe y tiene nombre
     * --`artistas.show`-- pero la ficha está en borrador y el controlador
     * responde 404. Sin la comprobación del 200, cada intento de ver algo no
     * publicado sumaría una visita a esa sección.
     */
    public function test_una_ruta_con_nombre_que_responde_404_no_cuenta(): void
    {
        $artista = Artista::factory()->create(['slug' => 'ficha-en-borrador']);

        $this->get(route('artistas.show', $artista))->assertNotFound();

        $this->assertSame(0, VisitaDiaria::count());
    }

    /**
     * Sin este filtro la cifra la escriben los rastreadores y no las personas,
     * y el gremio leería como interés lo que es indexación. El navegador se
     * mira para decidir; no se guarda.
     */
    public function test_un_rastreador_no_cuenta_como_visita(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get(route('inicio'))
            ->assertOk();

        $this->assertSame(0, VisitaDiaria::count());
    }

    // --- Datos personales ---

    public function test_la_tabla_no_guarda_ningun_dato_personal(): void
    {
        $columnas = Schema::getColumnListing('visitas_diarias');

        sort($columnas);

        $this->assertSame(
            // `entradas` entró el 9 sep 2026 (Acta 08, A-03) y es otro contador,
            // no un dato de nadie: el `Referer` que decide si suma se mira y no
            // se guarda, igual que el navegador.
            ['created_at', 'dia', 'entradas', 'id', 'ruta', 'total', 'updated_at'],
            $columnas,
            'La tabla de visitas ganó una columna. Si guarda IP, navegador o sesión deja de ser un agregado y entra en la Ley 1581.'
        );
    }

    /**
     * La ruta se guarda por su NOMBRE y no por su URL, y no es un detalle: una
     * URL trae la cadena de consulta, y ahí puede venir cualquier cosa que
     * alguien pegue en la barra del navegador.
     */
    public function test_se_guarda_el_nombre_de_la_ruta_y_no_la_url(): void
    {
        $this->get(route('directorio.index').'?q=bar&algo=personal@ejemplo.test')->assertOk();

        $this->assertSame('directorio.index', VisitaDiaria::firstOrFail()->ruta);
        $this->assertSame(0, VisitaDiaria::where('ruta', 'like', '%personal%')->count());
    }

    // --- Quién la ve ---

    public function test_solo_la_direccion_ve_la_analitica_del_sitio(): void
    {
        $secretaria = User::factory()->create();
        $secretaria->syncRoles([User::ROL_SUBADMIN]);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($secretaria->fresh());
        $this->assertFalse(VisitasDelSitio::canView());
        $this->assertFalse(PaginasMasVisitadas::canView());

        $this->actingAs($direccion->fresh());
        $this->assertTrue(VisitasDelSitio::canView());
        $this->assertTrue(PaginasMasVisitadas::canView());
    }

    // --- Lo que enseña ---

    public function test_la_grafica_de_visitas_lleva_la_cuenta_de_hoy(): void
    {
        $this->get(route('inicio'))->assertOk();
        $this->get(route('inicio'))->assertOk();
        $this->get(route('contacto'))->assertOk();

        $datos = (fn (): array => $this->getData())->call(new VisitasDelSitio);
        $serie = $datos['datasets'][0]['data'];

        $this->assertCount(30, $datos['labels'], 'La ventana son treinta días, con los vacíos en cero.');
        $this->assertSame(3, end($serie), 'El último punto de la serie es hoy: dos de inicio y una de contacto.');
    }

    /**
     * Se enseña la dirección y no el nombre interno de la ruta: «/contacto» lo
     * entiende quien mira el tablero y «contacto» a secas no dice si es la
     * página o el formulario.
     */
    public function test_las_secciones_mas_visitadas_se_enseñan_por_su_direccion(): void
    {
        $this->get(route('inicio'))->assertOk();
        $this->get(route('inicio'))->assertOk();
        $this->get(route('contacto'))->assertOk();

        $datos = (fn (): array => $this->getData())->call(new PaginasMasVisitadas);

        $this->assertSame(['/', '/contacto'], $datos['labels']);
        $this->assertSame([2, 1], $datos['datasets'][0]['data']);
    }

    /**
     * Que el widget exista no sirve de nada si el tablero no lo registra: es el
     * mismo modo de fallo silencioso de la puerta del banco de talento --la
     * pantalla responde 200 y no enseña a nadie--.
     */
    public function test_la_analitica_esta_registrada_en_el_tablero(): void
    {
        $widgets = Filament::getPanel('admin')->getWidgets();

        $this->assertContains(VisitasDelSitio::class, $widgets);
        $this->assertContains(PaginasMasVisitadas::class, $widgets);
    }
}

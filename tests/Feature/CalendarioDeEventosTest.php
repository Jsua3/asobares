<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * RF-19. El cronograma firmado dice «calendario + formularios» para el módulo
 * de eventos; los formularios existían y el calendario no.
 *
 * Casi todo lo que puede salir mal aquí sale mal EN SILENCIO, y por eso hay
 * tantos casos para una sola página:
 *
 *   - Registrar la ruta detrás de `/eventos/{evento:slug}` no da error: da un
 *     404 buscando un evento con slug «calendario».
 *   - Consultar dentro del bucle de celdas no da error: da 42 consultas por
 *     página, invisibles en local con seis eventos sembrados.
 *   - Repartir por `fecha_inicio` no da error: pinta el Congreso Nacional en
 *     la casilla del lunes y deja el martes y el miércoles en blanco.
 *   - Traer sólo el mes natural no da error: deja mudos los días colgantes de
 *     la primera y la última fila.
 *
 * Todas las fechas se anclan con `Carbon::create(2026, 9, …)` y no con
 * desplazamientos sobre `now()`: septiembre de 2026 empieza en MARTES, así que
 * su rejilla va del lunes 31 de agosto al domingo 4 de octubre —35 celdas— y
 * eso es lo que hace comprobables los casos de los días colgantes.
 */
class CalendarioDeEventosTest extends TestCase
{
    use RefreshDatabase;

    private const MES = '/eventos/calendario/2026/09';

    /** La rejilla de escritorio, recortada del resto del documento. */
    private function rejilla(string $html): string
    {
        $this->assertMatchesRegularExpression('/<table[^>]*>.*?<\/table>/s', $html, 'El calendario no pintó ninguna tabla.');
        preg_match('/<table[^>]*>.*?<\/table>/s', $html, $coincidencias);

        return $coincidencias[0];
    }

    /** La agenda vertical de móvil, recortada del resto del documento. */
    private function agenda(string $html): string
    {
        $this->assertMatchesRegularExpression('/<ol[^>]*sm:hidden[^>]*>.*?<\/ol>/s', $html, 'El calendario no pintó ninguna agenda móvil.');
        preg_match('/<ol[^>]*sm:hidden[^>]*>.*?<\/ol>/s', $html, $coincidencias);

        return $coincidencias[0];
    }

    /**
     * Cuántas consultas cuesta pintar una URL. Se mide con el registro de
     * consultas y no a ojo porque el defecto que persigue —una consulta por
     * casilla— no se ve ni se nota: sólo se cuenta.
     */
    private function consultasDe(string $url): int
    {
        // Se mide EN CALIENTE. La primera petición de cada proceso paga además
        // la consulta de `ajustes`, que se memoiza en caché para el resto: sin
        // este tiro de calentamiento la comparación mediría la caché fría
        // contra la caliente y saldría una consulta de diferencia que no tiene
        // nada que ver con el número de eventos.
        $this->get($url)->assertSuccessful();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get($url)->assertSuccessful();

        $consultas = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $consultas;
    }

    // --- El orden de las rutas ---

    /**
     * RIESGO 1. `/eventos/calendario` tiene dos segmentos igual que
     * `/eventos/{evento:slug}`: registrada después, la ruta del calendario no
     * se alcanza JAMÁS y Laravel devuelve 404 buscando un evento con ese slug.
     *
     * El caso crea el evento que de verdad la secuestraría, así que también
     * cae en rojo si alguien reordena el archivo de rutas en un futuro merge.
     */
    public function test_la_ficha_por_slug_no_captura_la_ruta_del_calendario(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 10));

        Evento::factory()->publicado()->create([
            'titulo' => 'Un evento llamado calendario',
            'slug' => 'calendario',
        ]);

        $this->get('/eventos/calendario')
            ->assertRedirect(route('eventos.calendario', [2026, '09']));
    }

    public function test_el_calendario_sin_fecha_redirige_al_mes_en_curso(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 3, 8));

        $this->get(route('eventos.calendario.hoy'))
            ->assertRedirect(route('eventos.calendario', [2026, '02']));
    }

    // --- El reparto por casillas ---

    /**
     * RIESGO 3, y es EL defecto de este módulo. ExpoBar dura dos días y el
     * Congreso Nacional tres: un `whereBetween` sobre `fecha_inicio` sola, o un
     * `groupBy('fecha_inicio')` de Eloquent, los pinta en la casilla del
     * arranque y deja las demás en blanco.
     */
    public function test_un_evento_de_varios_dias_aparece_en_todas_sus_casillas(): void
    {
        $congreso = Evento::factory()->publicado()
            ->deVariosDias(Carbon::create(2026, 9, 10), 3)
            ->create(['titulo' => 'Congreso de tres dias', 'slug' => 'congreso-de-tres-dias']);

        $html = $this->get(self::MES)->assertSuccessful()->getContent();

        $this->assertSame(
            3,
            substr_count($this->rejilla($html), route('eventos.show', $congreso)),
            'Un evento del 10 al 12 tiene que ocupar sus TRES casillas, no sólo la del arranque.'
        );

        $agenda = $this->agenda($html);

        foreach (['jueves 10 de septiembre', 'viernes 11 de septiembre', 'sábado 12 de septiembre'] as $dia) {
            $this->assertStringContainsString(Str::ucfirst($dia), $agenda, "La agenda móvil no lista el {$dia}.");
        }
    }

    /**
     * RIESGO 4. La rejilla de septiembre de 2026 arranca el lunes 31 de agosto,
     * porque el día 1 cae en martes. Con `startOfMonth`/`endOfMonth` en la
     * consulta esas celdas salen siempre vacías y el calendario afirma que el
     * 31 de agosto no pasa nada.
     */
    public function test_los_dias_colgantes_de_la_rejilla_traen_sus_eventos(): void
    {
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 8, 31))
            ->create(['titulo' => 'Foro del lunes colgante', 'slug' => 'foro-del-lunes-colgante']);

        // Y el otro extremo: la última fila llega hasta el domingo 4 de octubre.
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 10, 4))
            ->create(['titulo' => 'Foro del domingo colgante', 'slug' => 'foro-del-domingo-colgante']);

        $this->get(self::MES)
            ->assertSuccessful()
            ->assertSee('Foro del lunes colgante')
            ->assertSee('Foro del domingo colgante');
    }

    public function test_un_evento_de_otro_mes_no_se_cuela(): void
    {
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 10, 20))
            ->create(['titulo' => 'Foro de octubre', 'slug' => 'foro-de-octubre']);

        $this->get(self::MES)->assertSuccessful()->assertDontSee('Foro de octubre');
    }

    public function test_los_eventos_sin_publicar_no_aparecen_en_el_calendario(): void
    {
        Evento::factory()->elDia(Carbon::create(2026, 9, 12))
            ->create(['titulo' => 'Borrador de septiembre', 'slug' => 'borrador-de-septiembre']);

        Evento::factory()->elDia(Carbon::create(2026, 9, 13))
            ->create([
                'titulo' => 'Pendiente de septiembre',
                'slug' => 'pendiente-de-septiembre',
                'estado' => EstadoPublicacion::PendienteAprobacion,
            ]);

        $this->get(self::MES)
            ->assertSuccessful()
            ->assertDontSee('Borrador de septiembre')
            ->assertDontSee('Pendiente de septiembre');
    }

    // --- El presupuesto de consultas ---

    /**
     * RIESGO 2. Una rejilla mensual invita a consultar dentro del bucle de
     * celdas: 42 consultas por página, indetectables en local con seis eventos
     * sembrados.
     *
     * No se compara contra un número fijo a propósito: un número fijo envejece
     * con cualquier cambio del layout —una consulta más de la navbar y el caso
     * se vuelve rojo sin que haya ningún defecto—. Comparar un mes de UN evento
     * contra el mismo mes con VEINTICINCO es la firma exacta del N+1, y esa no
     * envejece nunca.
     */
    public function test_el_calendario_no_dispara_una_consulta_por_dia(): void
    {
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 9, 2))
            ->create(['titulo' => 'Foro solitario', 'slug' => 'foro-solitario']);

        $conUno = $this->consultasDe(self::MES);

        for ($dia = 1; $dia <= 25; $dia++) {
            Evento::factory()->publicado()
                ->elDia(Carbon::create(2026, 9, $dia))
                ->create(['titulo' => "Foro numero {$dia}", 'slug' => "foro-numero-{$dia}"]);
        }

        $conVeinticinco = $this->consultasDe(self::MES);

        $this->assertSame(
            $conUno,
            $conVeinticinco,
            "El calendario cuesta {$conUno} consultas con un evento y {$conVeinticinco} con veinticinco: "
            .'algo está consultando dentro del bucle de celdas.'
        );
    }

    // --- Sin JavaScript ---

    /**
     * El sitio es Blade servido entero: no hay estado de cliente que animar y
     * cambiar de mes es navegación, igual que filtrar o paginar. Si mañana
     * alguien cuelga la navegación de un `x-on:click`, el mes deja de tener URL
     * propia y con ella se van el enlace que se comparte y el que se indexa.
     */
    public function test_la_navegacion_entre_meses_funciona_sin_javascript(): void
    {
        $html = $this->get(self::MES)->assertSuccessful()->getContent();

        foreach (['prev' => [2026, '08'], 'next' => [2026, '10']] as $relacion => $destino) {
            $this->assertMatchesRegularExpression(
                '/<a[^>]*rel="'.$relacion.'"[^>]*>/',
                $html,
                "Falta el enlace rel=\"{$relacion}\"."
            );

            preg_match('/<a[^>]*rel="'.$relacion.'"[^>]*>/', $html, $etiqueta);

            $this->assertStringContainsString(
                route('eventos.calendario', $destino),
                $etiqueta[0],
                "El enlace rel=\"{$relacion}\" no apunta al mes que toca."
            );

            foreach (['x-on:', '@click', 'wire:', 'onclick'] as $prohibido) {
                $this->assertStringNotContainsString(
                    $prohibido,
                    $etiqueta[0],
                    "El enlace rel=\"{$relacion}\" cuelga de JavaScript: sin él el mes no tiene URL propia."
                );
            }
        }
    }

    // --- Los topes del rastreador ---

    public function test_el_mes_fuera_del_rango_admitido_responde_404(): void
    {
        // Lo corta la guarda del controlador: el año casa con la ruta.
        $this->get('/eventos/calendario/1899/01')->assertNotFound();
        $this->get('/eventos/calendario/2999/01')->assertNotFound();

        // Y esto lo corta la restricción de la propia ruta.
        $this->get('/eventos/calendario/2026/13')->assertNotFound();
        $this->get('/eventos/calendario/2026/00')->assertNotFound();
    }

    /**
     * Un mes sin eventos se navega igual —un calendario vacío sigue siendo
     * información— pero no se indexa: los enlaces de mes anterior y siguiente
     * no tienen tope y un rastreador pasearía por años enteros de páginas
     * vacías distintas.
     */
    public function test_un_mes_sin_datos_se_navega_pero_no_se_indexa(): void
    {
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 9, 9))
            ->create(['titulo' => 'Unico foro publicado', 'slug' => 'unico-foro-publicado']);

        $this->get(self::MES)
            ->assertSuccessful()
            ->assertDontSee('noindex', escape: false);

        $this->get('/eventos/calendario/2024/03')
            ->assertSuccessful()
            ->assertSee('No hay eventos del gremio en Marzo de 2024')
            ->assertSee('name="robots" content="noindex, follow"', escape: false);
    }

    // --- El día de hoy ---

    public function test_el_calendario_marca_el_dia_de_hoy_una_sola_vez(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 10));

        $delMes = $this->get(self::MES)->assertSuccessful()->getContent();

        $this->assertSame(
            1,
            substr_count($this->rejilla($delMes), 'aria-current="date"'),
            'Exactamente una casilla de la rejilla es hoy.'
        );

        $deOtroMes = $this->get('/eventos/calendario/2026/11')->assertSuccessful()->getContent();

        $this->assertStringNotContainsString(
            'aria-current="date"',
            $deOtroMes,
            'Noviembre no contiene el día de hoy: nada debe marcarse.'
        );
    }

    // --- El corte móvil / escritorio ---

    /**
     * Siete columnas en 375 px dan celdas de 53 px: ni el título del evento se
     * lee ni el objetivo táctil llega a 44. Móvil recibe la misma información
     * como agenda vertical, del mismo array y sin una consulta más.
     *
     * No hay viewport en una prueba de feature, así que lo que se fija aquí es
     * el contrato del marcado: que las dos formas existen, que se excluyen por
     * `sm:` y que el evento sale en las dos.
     */
    public function test_movil_recibe_la_agenda_y_escritorio_la_rejilla(): void
    {
        $evento = Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 9, 18), 19)
            ->create(['titulo' => 'Mercado nocturno de prueba', 'slug' => 'mercado-nocturno-de-prueba']);

        $html = $this->get(self::MES)->assertSuccessful()->getContent();

        $this->assertMatchesRegularExpression(
            '/<div class="[^"]*\bhidden\b[^"]*\bsm:block\b[^"]*"[^>]*>\s*<table/',
            $html,
            'La rejilla tiene que estar oculta por debajo de sm.'
        );

        $this->assertStringContainsString($evento->titulo, $this->rejilla($html));

        $agenda = $this->agenda($html);
        $this->assertStringContainsString($evento->titulo, $agenda);
        // La agenda añade lo que en la rejilla no cabe: hora, tipo y lugar.
        $this->assertStringContainsString('7:00 p. m.', $agenda);
        $this->assertStringContainsString('Armenia, Quindío', $agenda);
    }

    // --- El conmutador de tres segmentos ---

    public function test_el_conmutador_lleva_las_tres_vistas_y_no_pierde_el_estado(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 10));

        $enLaRejilla = $this->get('/eventos?cuando=pasados')->assertSuccessful();
        $enLaRejilla->assertSee(route('eventos.calendario', [2026, '09']), escape: false);
        $enLaRejilla->assertSee('Calendario');

        $enElCalendario = $this->get(self::MES)->assertSuccessful();
        $enElCalendario->assertSee(route('eventos.index', ['cuando' => 'proximos']), escape: false);
        $enElCalendario->assertSee(route('eventos.index', ['cuando' => 'pasados']), escape: false);

        // El segmento activo se marca en las dos páginas, y es lo que arrastra
        // el `view-transition-name` de la pastilla roja de una a otra.
        $this->assertStringContainsString('aria-current="true"', $enElCalendario->getContent());

        /*
         * Y la barra: el item «Eventos» se enciende con `eventos.*`, que casa
         * con `eventos.calendario` sin tocar nada. Si algún día el calendario
         * naciera fuera de ese prefijo, la barra se apagaría en esta página y
         * nadie lo notaría.
         */
        $cabecera = $this->recortarCabecera($enElCalendario->getContent());
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="[^"]*\/eventos"[^>]*aria-current="page"[^>]*text-acento/s',
            $cabecera,
            'El item «Eventos» de la barra tiene que seguir encendido dentro del calendario.'
        );
    }

    private function recortarCabecera(string $html): string
    {
        preg_match('/<header.*?<\/header>/s', $html, $coincidencias);

        return $coincidencias[0] ?? '';
    }

    // --- La regla de «próximo» y «pasado», que el calendario destapó ---

    /**
     * Defecto preexistente que el calendario deja a la vista. `proximo()` y
     * `pasado()` miraban SÓLO `fecha_inicio`, así que el Congreso Nacional
     * —tres días— se archivaba el minuto uno de su segundo día, con dos días
     * todavía por delante y con las inscripciones abiertas.
     */
    public function test_un_evento_en_curso_cuenta_como_proximo(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 11, 12));

        $enCurso = Evento::factory()->publicado()
            ->deVariosDias(Carbon::create(2026, 9, 10), 3)
            ->create(['titulo' => 'Congreso en curso', 'slug' => 'congreso-en-curso']);

        $this->assertTrue(Evento::publicado()->proximo()->whereKey($enCurso->getKey())->exists());
        $this->assertFalse(Evento::publicado()->pasado()->whereKey($enCurso->getKey())->exists());
    }

    /**
     * El invariante que impide corregir uno solo de los dos scopes: si
     * `proximo()` mirara `fecha_fin` y `pasado()` siguiera mirando
     * `fecha_inicio`, el evento en curso saldría en las DOS pestañas y los dos
     * contadores del conmutador sumarían más eventos de los que hay.
     */
    public function test_los_dos_contadores_reparten_todos_los_eventos_sin_solaparse(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 11, 12));

        Evento::factory()->publicado()->deVariosDias(Carbon::create(2026, 9, 10), 3)->create(['slug' => 'en-curso']);
        Evento::factory()->publicado()->elDia(Carbon::create(2026, 9, 30))->create(['slug' => 'futuro']);
        Evento::factory()->publicado()->elDia(Carbon::create(2026, 8, 1))->create(['slug' => 'terminado']);
        Evento::factory()->elDia(Carbon::create(2026, 9, 20))->create(['slug' => 'borrador']);

        $this->assertSame(
            Evento::publicado()->count(),
            Evento::publicado()->proximo()->count() + Evento::publicado()->pasado()->count(),
            'Los dos contadores tienen que repartirse los eventos publicados: ni solaparse ni dejarse ninguno.'
        );
    }

    /**
     * El otro extremo del mismo arreglo: la ficha con seis COUNT.
     * `loadCount('inscripciones')` no servía de nada porque
     * `cuposDisponibles()` re-consultaba siempre, y la vista lo encadena desde
     * tres puntos. Ninguna de las seis daba error ni tardaba.
     */
    public function test_la_ficha_de_un_evento_no_recuenta_las_inscripciones(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Taller con aforo medido',
            'slug' => 'taller-con-aforo-medido',
            'cupos' => 40,
        ]);

        $evento->inscripciones()->create([
            'nombre' => 'Persona Interesada',
            'correo' => 'persona@example.test',
            'telefono' => '3001234567',
            'acepto_habeas_data' => true,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('eventos.show', $evento))->assertSuccessful();

        $recuentos = array_filter(
            DB::getQueryLog(),
            fn (array $consulta): bool => str_contains($consulta['query'], 'count(*)')
                && str_contains($consulta['query'], 'inscripciones')
        );

        DB::disableQueryLog();

        $this->assertCount(
            1,
            $recuentos,
            'El único conteo de inscripciones tiene que ser el `loadCount` del controlador.'
        );
    }
}

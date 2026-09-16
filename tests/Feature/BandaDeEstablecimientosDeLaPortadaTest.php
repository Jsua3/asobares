<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Support\BandaDeEstablecimientos;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La franja de establecimientos es una banda, no tres tarjetas fijas, y el
 * punto de partida gira por sesión sin tocar la BD.
 *
 * Roturas: volver a take(3); meter ORDER BY RANDOM(); href="#"; pintar
 * una foto editorial como si fuera la del asociado.
 */
class BandaDeEstablecimientosDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Semilla del giro por sesión (40 alfanuméricos, lo que exige el
     * almacén de sesiones). Con una sesión al azar el giro cae en 0 una de
     * cada n veces y la portada sin `paraLaPortada` pasaría.
     */
    private const string SESION_FIJA = 'portadaSesionFijaParaLaBandaDeAsobares02';

    public function test_rotar_conserva_el_ciclo_alfabetico_y_no_baraja(): void
    {
        $base = collect(['Ámbar', 'Colina', 'Mirador', 'Zorba']);

        $this->assertSame(['Ámbar', 'Colina', 'Mirador', 'Zorba'], BandaDeEstablecimientos::rotar($base, 0)->all());
        $this->assertSame(['Colina', 'Mirador', 'Zorba', 'Ámbar'], BandaDeEstablecimientos::rotar($base, 1)->all());
        $this->assertSame(['Ámbar', 'Colina', 'Mirador', 'Zorba'], BandaDeEstablecimientos::rotar($base, 4)->all());
        $this->assertSame(['Único'], BandaDeEstablecimientos::rotar(collect(['Único']), 3)->all());
    }

    public function test_el_origen_es_estable_para_la_misma_semilla(): void
    {
        $this->assertSame(0, BandaDeEstablecimientos::origen('', 8));
        $this->assertSame(0, BandaDeEstablecimientos::origen('sesion-a', 1));
        $this->assertSame(
            BandaDeEstablecimientos::origen('sesion-a', 8),
            BandaDeEstablecimientos::origen('sesion-a', 8)
        );
        $this->assertNotSame(
            BandaDeEstablecimientos::origen('sesion-a', 8),
            BandaDeEstablecimientos::origen('sesion-b', 8)
        );
    }

    public function test_la_portada_pinta_una_banda_con_mas_de_tres_destacados(): void
    {
        $this->seed(DatabaseSeeder::class);
        Asociado::query()->update(['destacado' => false]);

        // «Érase» en medio del alfabeto: sin él, el orden de bytes de SQLite
        // (Colina… Zorba, Ámbar) es una rotación del español y la banda lo
        // daría por bueno aunque faltara `ordenarEnEspanol`.
        $nombres = ['Ámbar Gastrobar', 'Colina Nocturna', 'Érase una Vez', 'Mirador del Quindío', 'Sauce Club', 'Zorba Bar'];

        $porBytes = $nombres;
        sort($porBytes);

        $this->assertFalse(
            $this->esRotacionDe($nombres, $porBytes),
            'El caso no sirve si el orden de bytes es una rotación del español.'
        );

        foreach ($nombres as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre,
                'slug' => 'banda-'.$indice,
                'destacado' => true,
            ]);
        }

        $origen = BandaDeEstablecimientos::origen(self::SESION_FIJA, count($nombres));

        $this->assertNotSame(0, $origen, 'La sesión fija no sirve si no gira: la portada sin giro pasaría.');

        $html = $this->conSesionFija()->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeDescubre($html);
        $vistos = $this->nombresDeLaBanda($seccion);

        $this->assertSame(self::SESION_FIJA, session()->getId(), 'La cookie no fijó la sesión que siembra el giro.');
        $this->assertGreaterThan(3, count($vistos));
        $this->assertTrue($this->esRotacionDe($nombres, $vistos), 'La banda no es una rotación del alfabeto español.');
        $this->assertSame(
            $this->girar($nombres, $origen),
            $vistos,
            'La banda tiene que empezar en el origen de la sesión y seguir el alfabeto español.'
        );
        $this->assertStringContainsString('home-editorial-banda__pista', $seccion);
        $this->assertStringContainsString('Ver establecimientos anteriores', $seccion);
        $this->assertStringContainsString('Ver establecimientos siguientes', $seccion);

        // Control y nombre accesible en el mismo nodo, no dos cadenas dentro
        // del mismo tramo de texto: el `[^>]*` de un lookahead se para en el
        // primer `>`, así que basta un atributo de Alpine con una comparación
        // —`x-show="quedan > 1"`— para que la guardia denuncie un botón sin
        // nombre teniéndolo. Y se exige exactamente uno: un control montado
        // dos veces también es un defecto.
        $xpathDeLaSeccion = $this->xpathDe($seccion);

        $this->assertSame(
            1,
            $xpathDeLaSeccion->query('//button[contains(concat(" ", normalize-space(@class), " "), " home-editorial-banda__control--prev ")][@aria-label="Ver establecimientos anteriores"]')->length,
            'El control anterior de la banda solo lleva un icono: su nombre accesible es el aria-label.'
        );
        $this->assertSame(
            1,
            $xpathDeLaSeccion->query('//button[contains(concat(" ", normalize-space(@class), " "), " home-editorial-banda__control--next ")][@aria-label="Ver establecimientos siguientes"]')->length,
            'El control siguiente de la banda solo lleva un icono: su nombre accesible es el aria-label.'
        );

        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringContainsString('La noche del Quindío', $seccion);
        $this->assertStringContainsString('Lugares que dan vida a nuestra ciudad.', $seccion);
        $this->assertStringContainsString('Algunos de los establecimientos afiliados al gremio.', $seccion);
        $this->assertStringContainsString('Ver el directorio completo', $seccion);

        foreach ($vistos as $nombre) {
            $asociado = Asociado::query()->where('nombre', $nombre)->firstOrFail();
            $this->assertStringContainsString(route('directorio.show', $asociado), $seccion);
        }
    }

    public function test_sin_foto_real_se_pinta_el_fallback_y_no_una_foto_editorial(): void
    {
        $this->seed(DatabaseSeeder::class);
        Asociado::query()->update(['destacado' => false]);

        Asociado::factory()->publicado()->create([
            'nombre' => 'Áabar Sin Foto',
            'slug' => 'aabar-sin-foto',
            'destacado' => true,
            'foto_portada' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeDescubre($html);

        $this->assertStringContainsString('home-editorial-establecimiento__fallback', $seccion);
        $this->assertStringNotContainsString('img/home/establecimiento-', $seccion);
        $this->assertStringContainsString(route('directorio.show', Asociado::query()->where('slug', 'aabar-sin-foto')->firstOrFail()), $seccion);
    }

    public function test_el_controlador_no_pide_azar_y_respeta_el_tope(): void
    {
        $controlador = File::get(app_path('Http/Controllers/Publico/InicioController.php'));

        $this->assertStringContainsString('BandaDeEstablecimientos::TOPE', $controlador);
        $this->assertStringNotContainsString('inRandomOrder', $controlador);
        $this->assertDoesNotMatchRegularExpression('/->orderByRaw\(/', $controlador);
    }

    /**
     * Las fichas nacen en borrador y no se publican sin autorización del
     * titular: marcar «destacado» no basta para salir en la portada.
     */
    public function test_un_destacado_sin_publicar_no_entra_en_la_banda(): void
    {
        $this->seed(DatabaseSeeder::class);
        Asociado::query()->update(['destacado' => false]);

        $publicados = ['Colina Nocturna', 'Mirador del Quindío', 'Zorba Bar'];

        foreach ($publicados as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre,
                'slug' => 'publicado-'.$indice,
                'destacado' => true,
            ]);
        }

        $sinPublicar = collect(EstadoPublicacion::cases())
            ->reject(fn (EstadoPublicacion $estado): bool => $estado === EstadoPublicacion::Publicado)
            ->map(fn (EstadoPublicacion $estado): Asociado => Asociado::factory()->create([
                'nombre' => 'Bodega sin publicar '.$estado->value,
                'slug' => 'sin-publicar-'.$estado->value,
                'destacado' => true,
                'estado' => $estado,
            ]));

        $this->assertNotEmpty($sinPublicar);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeDescubre($html);

        $this->assertEqualsCanonicalizing($publicados, $this->nombresDeLaBanda($seccion));

        foreach ($sinPublicar as $asociado) {
            $this->assertStringNotContainsString($asociado->nombre, $html, 'Una ficha '.$asociado->estado->value.' salió en la portada.');
            $this->assertStringNotContainsString(route('directorio.show', $asociado), $seccion);
        }
    }

    public function test_la_banda_no_tiene_autoplay(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.data('bandaEstablecimientos'", $js);

        // Acotado al bloque de la banda: la regex sobre el archivo entero
        // casaría con el reduceMovimiento() de prepararCifras.
        $this->assertMatchesRegularExpression(
            '/behavior:\s*reduceMovimiento\(\)\s*\?\s*\'auto\'\s*:\s*\'smooth\'/',
            $this->bloqueDeAlpine($js, 'bandaEstablecimientos'),
            'La banda tiene que desplazarse sin animación si pidieron menos movimiento.'
        );
        // Acotado por el mismo motivo que la de arriba, y falla de las dos
        // maneras cuando no lo está: `[\s\S]*` desde el nombre del componente
        // hasta el final del archivo se pone roja por cualquier `setInterval`
        // posterior aunque sea de otro componente, y pasa en vacío si alguien
        // renombra la banda, porque entonces el patrón no casa con nada y
        // «no coincide» es justo lo que la aserción pide. `bloqueDeAlpine`
        // afirma que el bloque existe antes de devolverlo, así que ahí no hay
        // aprobado por ausencia.
        $this->assertStringNotContainsString(
            'setInterval',
            $this->bloqueDeAlpine($js, 'bandaEstablecimientos'),
            'La banda volvió a rotar sola: un `setInterval` dentro de su componente es autoplay, y la portada no lo tiene.'
        );
    }

    public function test_el_directorio_sigue_sin_usar_la_rotacion_de_la_portada(): void
    {
        $directorio = File::get(app_path('Http/Controllers/Publico/DirectorioController.php'));

        $this->assertStringNotContainsString('BandaDeEstablecimientos', $directorio);
    }

    /** La cookie de sesión va cifrada, como la manda el navegador. */
    private function conSesionFija(): static
    {
        return $this->withCookie((string) config('session.cookie'), self::SESION_FIJA);
    }

    /**
     * Gira la lista a mano, sin pasar por `BandaDeEstablecimientos::rotar`:
     * si la prueba usara el mismo código que vigila, lo daría por bueno.
     *
     * @param  list<string>  $lista
     * @return list<string>
     */
    private function girar(array $lista, int $origen): array
    {
        $desplazamiento = $origen % count($lista);

        return array_merge(array_slice($lista, $desplazamiento), array_slice($lista, 0, $desplazamiento));
    }

    /** Desde `Alpine.data('nombre'` hasta el `}));` que lo cierra. */
    private function bloqueDeAlpine(string $js, string $componente): string
    {
        $inicio = strpos($js, "Alpine.data('".$componente."'");

        $this->assertNotFalse($inicio, "No encontré Alpine.data('{$componente}').");

        $fin = strpos($js, "\n}));", $inicio);

        $this->assertNotFalse($fin, "No encontré el cierre de Alpine.data('{$componente}').");

        return substr($js, $inicio, $fin - $inicio);
    }

    /**
     * @param  list<string>  $base
     * @param  list<string>  $visto
     */
    private function esRotacionDe(array $base, array $visto): bool
    {
        if ($base === [] || count($base) !== count($visto)) {
            return false;
        }

        $doble = array_merge($base, $base);

        for ($indice = 0; $indice < count($base); $indice++) {
            if (array_slice($doble, $indice, count($visto)) === $visto) {
                return true;
            }
        }

        return false;
    }

    /** El documento servido, listo para consultar por XPath. */
    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    /**
     * La franja de establecimientos, serializada desde el árbol servido.
     *
     * Es la raíz del archivo —de aquí cuelgan casi todas las aserciones— y
     * por eso no se recorta por texto: `<section class="home-editorial-descubre`
     * exige que `class` sea el primer atributo, y `(.*?)</section>` corta en el
     * primer cierre, así que un `x-data` delante o una sección anidada dejan
     * mudas quince aserciones sin que falte nada de la franja.
     */
    private function seccionDeDescubre(string $html): string
    {
        $secciones = $this->xpathDe($html)->query('//section[contains(concat(" ", normalize-space(@class), " "), " home-editorial-descubre ")]');

        $this->assertSame(1, $secciones->length, 'La portada no pintó la franja de establecimientos.');

        $nodo = $secciones->item(0);

        return $nodo->ownerDocument->saveHTML($nodo);
    }

    /**
     * Los nombres de la banda, en orden de documento.
     *
     * Se pide el encabezado de cada tarjeta y no cualquier `<h3>` del tramo:
     * un h3 que no sea título de establecimiento mete un nombre de más, y
     * bajar el título a `<h4>` por jerarquía deja la lista vacía. Las dos
     * roturas avisan por el motivo equivocado.
     *
     * @return list<string>
     */
    private function nombresDeLaBanda(string $seccion): array
    {
        $titulos = $this->xpathDe($seccion)->query(
            '//article[contains(concat(" ", normalize-space(@class), " "), " home-editorial-establecimiento ")]//h3'
        );

        $nombres = [];

        foreach ($titulos as $titulo) {
            $nombres[] = trim(preg_replace('/\s+/u', ' ', $titulo->textContent) ?? '');
        }

        return $nombres;
    }
}

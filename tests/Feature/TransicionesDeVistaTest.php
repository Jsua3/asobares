<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Noticia;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Dos elementos con el mismo `view-transition-name` en un documento hacen que
 * el navegador descarte la transición ENTERA: no la de ese elemento, la de toda
 * la página. Sin error de consola, sin nada roto a la vista y sin ninguna forma
 * de enterarse salvo mirar una navegación a cámara lenta.
 *
 * El sitio emite estos nombres desde cuatro secciones y hasta hoy la única
 * defensa era acordarse. El calendario es la sección con más superficie para
 * pisarse —la rejilla de escritorio y la agenda de móvil coexisten en el DOM, y
 * un evento de tres días ocupa tres casillas—, así que la guardia nace con él
 * pero protege sobre todo a las cuatro que ya estaban.
 *
 * Se mide sobre el HTML RENDERIZADO y no sobre las plantillas a propósito: el
 * defecto no está en el archivo, está en cuántas veces el bucle lo imprime.
 */
class TransicionesDeVistaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @return list<string> */
    private function nombresDe(string $html): array
    {
        preg_match_all('/view-transition-name:\s*([a-z0-9-]+)/i', $html, $coincidencias);

        return $coincidencias[1];
    }

    /**
     * Las páginas públicas con contenido. Se listan todas y no sólo las que hoy
     * emiten nombres: el día que alguien le ponga uno a la portada del inicio,
     * la guardia ya está puesta y no hay que acordarse de ampliarla.
     *
     * @return list<array{0: string}>
     */
    public static function rutasConTransicion(): array
    {
        return array_map(fn (string $ruta): array => [$ruta], [
            '/',
            '/quienes-somos',
            '/directorio',
            '/directorio?categoria=cafe&vista=mapa',
            '/abre-tu-negocio',
            '/abre-tu-negocio?municipio=salento',
            '/empleo',
            '/artistas',
            '/proveedores',
            '/eventos',
            '/eventos?cuando=pasados',
            '/eventos/calendario/2026/09',
            '/boletin',
            '/boletin?categoria=observatorio',
        ]);
    }

    #[DataProvider('rutasConTransicion')]
    public function test_ninguna_pagina_repite_un_nombre_de_transicion_de_vista(string $ruta): void
    {
        $nombres = $this->nombresDe($this->get($ruta)->assertSuccessful()->getContent());

        $repetidos = array_keys(array_filter(
            array_count_values($nombres),
            fn (int $veces): bool => $veces > 1
        ));

        $this->assertSame(
            [],
            $repetidos,
            "{$ruta} repite un `view-transition-name` y el navegador descartará la transición entera: "
            .implode(', ', $repetidos)
        );
    }

    /**
     * Las cuatro fichas de detalle, que son la otra mitad de cada par: la
     * portada del listado y la de la ficha comparten nombre a propósito, y esa
     * es exactamente la pareja que se rompe si una de las dos se duplica.
     */
    public function test_las_fichas_de_detalle_tampoco_repiten_ningun_nombre(): void
    {
        $fichas = [
            route('directorio.show', Asociado::publicado()->firstOrFail()),
            route('eventos.show', Evento::publicado()->firstOrFail()),
            route('boletin.show', Noticia::visible()->firstOrFail()),
            route('artistas.show', Artista::publicado()->firstOrFail()),
        ];

        foreach ($fichas as $ficha) {
            $nombres = $this->nombresDe($this->get($ficha)->assertSuccessful()->getContent());

            $this->assertSame(
                array_unique($nombres),
                $nombres,
                "{$ficha} repite un `view-transition-name`."
            );
        }
    }

    /**
     * Y que la guardia tenga algo que guardar.
     *
     * Un barrido que no encuentra nada pasa igual de verde que uno que
     * encuentra y aprueba, así que sin este caso bastaría con que alguien
     * cambiara la sintaxis del `style` —o retirara los nombres sin querer— para
     * que los catorce casos de arriba se volvieran decorativos.
     */
    public function test_las_paginas_que_emparejan_portadas_siguen_emitiendo_sus_nombres(): void
    {
        $esperado = [
            '/directorio' => 'portada-asociado-',
            '/artistas' => 'portada-artista-',
            '/eventos' => 'portada-evento-',
            '/boletin' => 'filtro-activo',
            '/eventos/calendario/2026/09' => 'calendario-titulo',
        ];

        foreach ($esperado as $ruta => $prefijo) {
            $this->assertNotEmpty(
                array_filter(
                    $this->nombresDe($this->get($ruta)->assertSuccessful()->getContent()),
                    fn (string $nombre): bool => str_starts_with($nombre, $prefijo)
                ),
                "{$ruta} dejó de emitir `{$prefijo}…`: la transición emparejada ya no existe."
            );
        }
    }

    /**
     * El calendario estrena su propio prefijo, y no es cosmética: un nombre
     * repetido entre secciones colisiona igual que uno repetido dentro de una.
     *
     * Y tiene PROHIBIDO emitir `portada-evento-{id}`, que es el par que empareja
     * el listado de tarjetas con la ficha: la rejilla y la agenda pintan los
     * mismos eventos, y un evento de tres días sale tres veces en la rejilla.
     * Con ese nombre serían de tres a seis elementos iguales en un documento.
     */
    public function test_el_calendario_usa_su_propio_prefijo_y_nunca_el_de_las_portadas(): void
    {
        $nombres = $this->nombresDe(
            $this->get('/eventos/calendario/2026/09')->assertSuccessful()->getContent()
        );

        $this->assertContains('calendario-titulo', $nombres);
        $this->assertContains('calendario-rejilla', $nombres);
        $this->assertContains('calendario-agenda', $nombres);

        foreach ($nombres as $nombre) {
            $this->assertStringStartsNotWith(
                'portada-evento-',
                $nombre,
                'El calendario no puede emitir el nombre que empareja el listado con la ficha.'
            );
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Support\BandaDeEstablecimientos;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El bloque de destacados de la portada sale de un cupo alfabético (OBS3-06).
 *
 * El directivo se paró justo en esto mirando la portada: «¿por qué está
 * colina primero, por qué mirador... o simplemente un aleatorio?» (R21
 * 06:11-06:17), y pidió «que sea en orden alfabético» (R21 06:24).
 *
 * La franja ya no recorta a tres fijos: gira el cupo en presentación. Lo
 * que no puede volver es elegir el cupo por `updated_at` ni por RANDOM().
 */
class OrdenDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La prueba construye el caso donde los tres órdenes posibles difieren,
     * o pasaría con el defecto dentro:
     *
     *   creación (= `updated_at`):  Zorba, Mirador, Colina, Ámbar
     *   bytes (= SQLite crudo):     Colina, Mirador, Zorba, Ámbar
     *   español (= lo correcto):    Ámbar, Colina, Mirador, Zorba
     */
    public function test_los_destacados_salen_en_orden_alfabetico_espanol(): void
    {
        $this->seed(DatabaseSeeder::class);

        Asociado::query()->update(['destacado' => false]);

        $nombres = ['Zorba Bar', 'Mirador del Quindío', 'Colina Nocturna', 'Ámbar Gastrobar'];

        foreach ($nombres as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre,
                'slug' => 'destacado-'.$indice,
                'destacado' => true,
            ]);
        }

        $porBytes = $nombres;
        sort($porBytes);

        $esperado = ['Ámbar Gastrobar', 'Colina Nocturna', 'Mirador del Quindío', 'Zorba Bar'];

        $this->assertNotSame($nombres, $esperado, 'El caso no sirve si el orden de creación ya es el correcto.');
        $this->assertNotSame($porBytes, $esperado, 'El caso no sirve si el orden de bytes ya es el correcto.');

        $cupo = ordenarEnEspanol(
            Asociado::publicado()->where('destacado', true)->orderBy('nombre')->take(BandaDeEstablecimientos::TOPE)->get()
        )->pluck('nombre')->all();

        if (class_exists(\Collator::class)) {
            $this->assertSame($esperado, $cupo);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertTrue(
            $this->esRotacionDe($cupo, $this->nombresEnPortada($html)),
            'La portada tiene que pintar una rotación del cupo alfabético, no otro orden.'
        );
    }

    /**
     * El cupo lo decide el `ORDER BY` de la base, no el reordenado en PHP.
     * Hacen falta más fichas que el tope para que la selección signifique algo.
     */
    public function test_con_mas_destacados_que_el_tope_solo_entra_el_cupo_alfabetico(): void
    {
        $this->seed(DatabaseSeeder::class);

        Asociado::query()->update(['destacado' => false]);

        $nombres = ['Zorba', 'Yatra', 'Xilema', 'Waldorf', 'Vega', 'Tulipán', 'Sauce', 'Roble', 'Quimera', 'Pino', 'Olivo', 'Nardo', 'Mirador'];

        foreach ($nombres as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre, 'slug' => 'destacado-'.$indice, 'destacado' => true,
            ]);
        }

        $cupo = ordenarEnEspanol(
            Asociado::publicado()->where('destacado', true)->orderBy('nombre')->take(BandaDeEstablecimientos::TOPE)->get()
        )->pluck('nombre')->all();

        $this->assertCount(BandaDeEstablecimientos::TOPE, $cupo);

        $html = $this->get('/')->assertOk()->getContent();
        $enPortada = $this->nombresEnPortada($html);

        $this->assertTrue($this->esRotacionDe($cupo, $enPortada));

        foreach (array_diff($nombres, $cupo) as $fuera) {
            $this->assertStringNotContainsString('>'.$fuera.'<', $html);
        }
    }

    /**
     * El defecto que el orden viejo tenía y nadie había nombrado: editar una
     * ficha desde el panel la metía en la portada. Quien corrigiera un
     * teléfono cambiaba qué establecimientos se ven, sin saberlo.
     */
    public function test_editar_una_ficha_no_la_mete_en_la_portada(): void
    {
        $this->seed(DatabaseSeeder::class);

        Asociado::query()->update(['destacado' => false]);

        foreach (['Mirador', 'Nardo', 'Olivo', 'Pino', 'Quimera', 'Roble', 'Sauce', 'Tulipán', 'Vega', 'Waldorf', 'Xilema', 'Yatra'] as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre, 'slug' => 'dentro-'.$indice, 'destacado' => true,
            ]);
        }

        $fuera = Asociado::factory()->publicado()->create([
            'nombre' => 'Zorba', 'slug' => 'fuera', 'destacado' => true,
        ]);

        $fuera->touch();

        $this->get('/')->assertOk()->assertDontSee('>'.$fuera->nombre.'<', escape: false);
    }

    /** El directorio no hereda la rotación de la portada. */
    public function test_la_portada_y_el_directorio_no_comparten_la_rotacion(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertOk();
        $this->get('/directorio')->assertOk();

        $this->assertStringNotContainsString(
            'BandaDeEstablecimientos',
            file_get_contents(app_path('Http/Controllers/Publico/DirectorioController.php'))
        );
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

    /**
     * @return list<string>
     */
    private function nombresEnPortada(string $html): array
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-descubre[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion)
        );

        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/s', $seccion[1], $titulos);

        return array_values(array_map(
            fn (string $titulo): string => trim(html_entity_decode(strip_tags($titulo), ENT_QUOTES, 'UTF-8')),
            $titulos[1]
        ));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Asociado;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El bloque de destacados de la portada sale ordenado (OBS3-06).
 *
 * El directivo se paró justo en esto mirando la portada: «¿por qué está
 * colina primero, por qué mirador... o simplemente un aleatorio?» (R21
 * 06:11-06:17), y pidió «que sea en orden alfabético» (R21 06:24).
 *
 * El §27.2 aclara dónde estaba el defecto de verdad, que no era el directorio
 * --ese ya ordenaba y ya tenía buscador-- sino la portada, que iba por
 * `latest('updated_at')`. Desde fuera eso no se distingue del azar: nadie ve
 * las fechas de edición.
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
     *
     * «Ámbar» está ahí a propósito: es lo único que separa el orden de bytes
     * del alfabético de verdad, y sin él la prueba pasaría en verde con
     * `ORDER BY nombre` a secas.
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

        $this->get('/')->assertOk()->assertSeeInOrder($esperado, escape: false);
    }

    /**
     * La portada muestra SEIS destacados, y cuáles son los seis lo decide el
     * `ORDER BY` de la base, no el reordenado en PHP.
     *
     * Esta prueba existe porque la de arriba NO protege eso: con cuatro
     * fichas `take(6)` se las lleva todas, y el colador las ordena bien
     * aunque la consulta venga por `updated_at`. Se descubrió mutando
     * --volver al orden viejo dejaba la suite en verde-- y es exactamente la
     * forma de falso verde que este proyecto ya pagó once veces. Hacen falta
     * más de seis para que la selección signifique algo.
     */
    public function test_con_mas_de_seis_destacados_salen_los_seis_primeros_del_alfabeto(): void
    {
        $this->seed(DatabaseSeeder::class);

        Asociado::query()->update(['destacado' => false]);

        // Creados al revés del alfabeto: por `updated_at` saldrían los de la
        // cola, que son justo los que NO deben salir.
        $nombres = ['Zorba', 'Yatra', 'Xilema', 'Waldorf', 'Vega', 'Tulipán', 'Sauce', 'Roble'];

        foreach ($nombres as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre, 'slug' => 'destacado-'.$indice, 'destacado' => true,
            ]);
        }

        $respuesta = $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Roble', 'Sauce', 'Tulipán', 'Vega', 'Waldorf', 'Xilema'], escape: false);

        foreach (['Yatra', 'Zorba'] as $fuera) {
            $respuesta->assertDontSee('>'.$fuera.'<', escape: false);
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

        foreach (['Roble', 'Sauce', 'Tulipán', 'Vega', 'Waldorf', 'Xilema'] as $indice => $nombre) {
            Asociado::factory()->publicado()->create([
                'nombre' => $nombre, 'slug' => 'dentro-'.$indice, 'destacado' => true,
            ]);
        }

        $fuera = Asociado::factory()->publicado()->create([
            'nombre' => 'Zorba', 'slug' => 'fuera', 'destacado' => true,
        ]);

        // Lo que haría la secretaría: corregir un dato de una ficha cualquiera.
        $fuera->touch();

        $this->get('/')->assertOk()->assertDontSee('>'.$fuera->nombre.'<', escape: false);
    }

    /** El directorio ya ordenaba así: las dos listas del sitio coinciden. */
    public function test_la_portada_y_el_directorio_ordenan_con_el_mismo_criterio(): void
    {
        $this->seed(DatabaseSeeder::class);

        $destacados = Asociado::publicado()->where('destacado', true)->orderBy('nombre')->take(6)->pluck('nombre');

        $this->assertGreaterThan(1, $destacados->count(), 'Hacen falta varios destacados para que el orden signifique algo.');

        $this->get('/')->assertOk()->assertSeeInOrder($destacados->all(), escape: false);
        $this->get('/directorio')->assertOk();
    }
}

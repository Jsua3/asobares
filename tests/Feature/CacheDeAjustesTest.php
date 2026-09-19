<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Los ajustes se leen de la caché una vez por petición, no una vez por
 * `ajuste()`.
 *
 * En producción la caché vive en la base de datos: sin memoria por petición,
 * cada `ajuste()` de una vista era una consulta a la tabla `cache` y una
 * deserialización de los doscientos ajustes. La portada llama a `ajuste()`
 * unas ochenta veces.
 */
class CacheDeAjustesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'database']);
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_portada_lee_los_ajustes_de_la_cache_una_sola_vez(): void
    {
        $lecturas = 0;

        DB::listen(function (QueryExecuted $consulta) use (&$lecturas): void {
            if (preg_match('/^select .* from "cache"/i', $consulta->sql)) {
                $lecturas++;
            }
        });

        $this->get(route('inicio'))->assertOk();

        $this->assertLessThanOrEqual(2, $lecturas, "La portada leyó la tabla `cache` {$lecturas} veces.");
    }

    /**
     * Guardar desde el panel y ver el cambio en la misma petición: la memoria
     * por petición no puede servir el valor viejo.
     */
    public function test_un_ajuste_guardado_se_ve_en_la_misma_peticion(): void
    {
        // Dos lecturas: con la caché vacía la primera la llena y solo la
        // segunda queda en la memoria de la petición.
        ajuste('sitio_eslogan');
        $this->assertNotSame('Lema nuevo', ajuste('sitio_eslogan'));

        Setting::query()->where('clave', 'sitio_eslogan')->first()->update(['valor' => 'Lema nuevo']);

        $this->assertSame('Lema nuevo', ajuste('sitio_eslogan'));
    }

    /**
     * La página de ajustes guarda con una actualización masiva, que no dispara
     * eventos de modelo, y después llama a `olvidarCache()`.
     */
    public function test_olvidar_la_cache_tambien_olvida_la_memoria_de_la_peticion(): void
    {
        ajuste('sitio_eslogan');
        $this->assertNotSame('Lema masivo', ajuste('sitio_eslogan'));

        Setting::query()->where('clave', 'sitio_eslogan')->update(['valor' => 'Lema masivo']);
        Setting::olvidarCache();

        $this->assertSame('Lema masivo', ajuste('sitio_eslogan'));
    }
}

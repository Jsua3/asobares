<?php

namespace Tests\Feature;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Municipio;
use App\Support\ReglaDeAlcaldias;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «A todos o nada» (OBS3-05).
 *
 * El directivo cortó en seco la idea de darle un espacio a las alcaldías junto
 * a los otros aliados institucionales: «es para no abrir susceptibilidades...
 * no le toca nombrarlos a todos, porque uno no se nombre» (R21 03:41-03:44),
 * «a todos o nada» (R21 03:47). Es una instrucción política, no estética.
 *
 * El acta pide «documentarla para que nadie la rompa después». Documentar no
 * basta: quien la rompa lo hará sin haberla leído, cargando una alcaldía un
 * martes. Por eso la regla se aplica al pintar, y lo que estas pruebas fijan
 * es que un juego parcial no se pueda mostrar aunque esté publicado y activo.
 */
class ReglaDeAlcaldiasTest extends TestCase
{
    use RefreshDatabase;

    private ReglaDeAlcaldias $regla;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regla = app(ReglaDeAlcaldias::class);
    }

    /** Ninguna cargada es la mitad válida de «a todos o nada». */
    public function test_sin_ninguna_alcaldia_la_regla_se_cumple(): void
    {
        $this->seed(DatabaseSeeder::class);

        $visibles = Aliado::visible()->get();

        $this->assertTrue($this->regla->seCumple($visibles));
        $this->assertCount($visibles->count(), $this->regla->filtrar($visibles));
    }

    /**
     * El caso que la regla existe para impedir, y el que de verdad pasaría:
     * alguien carga la alcaldía de su municipio y la publica.
     */
    public function test_una_sola_alcaldia_no_sale_al_sitio(): void
    {
        $this->seed(DatabaseSeeder::class);

        $armenia = Municipio::query()->firstOrFail();
        $alcaldia = $this->alcaldiaDe($armenia);

        $visibles = Aliado::visible()->get();

        $this->assertFalse($this->regla->seCumple($visibles), 'Con una sola alcaldía la regla NO se cumple.');
        $this->assertNotContains(
            $alcaldia->getKey(),
            $this->regla->filtrar($visibles)->pluck('id')->all(),
            'Una alcaldía suelta no puede salir al sitio.'
        );

        $this->get('/')->assertOk()->assertDontSee($alcaldia->nombre, escape: false);
    }

    /** Con todas cargadas sí salen: la regla no es una prohibición, es un umbral. */
    public function test_con_todas_las_alcaldias_salen_todas(): void
    {
        $this->seed(DatabaseSeeder::class);

        $alcaldias = Municipio::query()->get()->map(fn (Municipio $m): Aliado => $this->alcaldiaDe($m));

        $visibles = Aliado::visible()->get();

        $this->assertTrue($this->regla->seCumple($visibles));
        $this->assertSame([], $this->regla->faltantes($visibles)->all());

        $respuesta = $this->get('/')->assertOk();

        foreach ($alcaldias as $alcaldia) {
            $respuesta->assertSee($alcaldia->nombre, escape: false);
        }
    }

    /** Quitar una del juego completo las tumba todas, no solo esa. */
    public function test_quitar_una_del_juego_completo_las_tumba_todas(): void
    {
        $this->seed(DatabaseSeeder::class);

        Municipio::query()->get()->each(fn (Municipio $m) => $this->alcaldiaDe($m));

        $unaCualquiera = Aliado::query()->whereNotNull('municipio_id')->firstOrFail();
        $unaCualquiera->update(['activo' => false]);

        $visibles = Aliado::visible()->get();
        $filtradas = $this->regla->filtrar($visibles)->filter(
            fn (Aliado $a): bool => $this->regla->esAlcaldia($a)
        );

        $this->assertFalse($this->regla->seCumple($visibles));
        $this->assertCount(0, $filtradas, 'Si falta una, no sale ninguna: ese es el punto de la regla.');
    }

    /**
     * La regla mira las dos cosas. Un patrocinador comercial con sede en
     * Salento no es la Alcaldía de Salento, y atarlo a su municipio no puede
     * activar la regla ni, peor, hacer creer que ya está cubierto.
     */
    public function test_un_comercial_atado_a_un_municipio_no_cuenta_como_alcaldia(): void
    {
        $this->seed(DatabaseSeeder::class);

        $municipio = Municipio::query()->firstOrFail();

        $comercial = Aliado::factory()->visible()->create([
            'tipo' => TipoAliado::Comercial,
            'municipio_id' => $municipio->getKey(),
        ]);

        $visibles = Aliado::visible()->get();

        $this->assertFalse($this->regla->esAlcaldia($comercial));
        $this->assertTrue($this->regla->seCumple($visibles), 'Un comercial atado a un municipio no activa la regla.');
        $this->assertContains(
            $comercial->getKey(),
            $this->regla->filtrar($visibles)->pluck('id')->all(),
            'Y tampoco puede quedar filtrado como si fuera una alcaldía.'
        );
    }

    /** Lo que falta se puede enumerar, que es lo que el panel le enseña a la oficina. */
    public function test_los_faltantes_se_pueden_enumerar_para_el_panel(): void
    {
        $this->seed(DatabaseSeeder::class);

        $total = Municipio::query()->count();
        $cubierto = Municipio::query()->firstOrFail();
        $this->alcaldiaDe($cubierto);

        $faltantes = $this->regla->faltantes(Aliado::visible()->get());

        $this->assertCount($total - 1, $faltantes);
        $this->assertNotContains($cubierto->nombre, $faltantes->pluck('nombre')->all());
    }

    /** Un borrador o un aliado apagado no cubre a su municipio. */
    public function test_una_alcaldia_sin_publicar_no_cubre_su_municipio(): void
    {
        $this->seed(DatabaseSeeder::class);

        $municipio = Municipio::query()->firstOrFail();

        Aliado::factory()->institucional()->create([
            'municipio_id' => $municipio->getKey(),
            'activo' => true,
        ]);

        $faltantes = $this->regla->faltantes(Aliado::visible()->get());

        $this->assertContains(
            $municipio->nombre,
            $faltantes->pluck('nombre')->all(),
            'Un borrador no cubre: la regla mira lo que sale al sitio, no lo que hay en la base.'
        );
    }

    private function alcaldiaDe(Municipio $municipio): Aliado
    {
        return Aliado::factory()->visible()->institucional()->create([
            'nombre' => "Alcaldía de {$municipio->nombre}",
            'municipio_id' => $municipio->getKey(),
        ]);
    }
}

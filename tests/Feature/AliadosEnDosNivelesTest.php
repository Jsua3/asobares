<?php

namespace Tests\Feature;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los dos niveles de aliado en la portada (OBS3-04).
 *
 * El directivo pidió un bloque de aliados institucionales --Asobares Colombia,
 * la Cámara de Comercio, el Comité Intergremial y la Gobernación-- separado de
 * las marcas con convenio (`R21 02:19–03:26`), y el §27.5 lo eleva a regla de
 * contenido: «institucionales por encima de los comerciales, y con tratamiento
 * visual distinto». Hasta hoy la tabla no distinguía y todo caía en una sola
 * tira de logos, que dice que una licorera y la Gobernación son lo mismo.
 *
 * «Por encima» es la parte que se puede romper sin que se note leyendo, así
 * que es la que más se prueba aquí.
 */
class AliadosEnDosNivelesTest extends TestCase
{
    use RefreshDatabase;

    /** Los que nombró el directivo, en el orden en que los nombró. */
    private const array INSTITUCIONALES = [
        'Asobares Colombia',
        'Cámara de Comercio de Armenia y del Quindío',
        'Comité Intergremial del Quindío',
        'Gobernación del Quindío',
    ];

    /**
     * Lo que el acta pide y lo único que no se ve leyendo el diff: que la
     * banda institucional salga ANTES en el documento, no solo que exista.
     */
    public function test_los_institucionales_salen_por_encima_de_los_comerciales(): void
    {
        $this->seed(DatabaseSeeder::class);

        $institucional = Aliado::query()->where('tipo', TipoAliado::Institucional)->visible()->firstOrFail();
        $comercial = Aliado::query()->where('tipo', TipoAliado::Comercial)->visible()->firstOrFail();

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([$institucional->nombre, $comercial->nombre], escape: false);
    }

    /** Los cuatro que nombró el directivo están, y están arriba. */
    public function test_los_cuatro_institucionales_del_acta_estan_sembrados(): void
    {
        $this->seed(DatabaseSeeder::class);

        $sembrados = Aliado::query()
            ->where('tipo', TipoAliado::Institucional)
            ->pluck('nombre')
            ->all();

        foreach (self::INSTITUCIONALES as $nombre) {
            $this->assertContains($nombre, $sembrados, "Falta el aliado institucional «{$nombre}» del acta.");
        }

        $this->get('/')->assertOk()->assertSeeInOrder(self::INSTITUCIONALES, escape: false);
    }

    /**
     * Institucional se declara; no se hereda por descuido. Si el valor por
     * defecto fuera el de arriba, cualquier marca nueva que cargue la
     * secretaría acabaría junto a la Gobernación.
     */
    public function test_un_aliado_nuevo_nace_comercial(): void
    {
        $aliado = Aliado::factory()->create();

        $this->assertSame(TipoAliado::Comercial, $aliado->tipo);
        $this->assertFalse($aliado->esInstitucional());
    }

    /** La bandera de conveniencia dice lo mismo que el enum. */
    public function test_es_institucional_responde_al_tipo(): void
    {
        $this->assertTrue(Aliado::factory()->institucional()->create()->esInstitucional());
    }

    /**
     * La portada no puede caerse porque falte un nivel: al principio no habrá
     * institucionales cargados, y si el gremio retira los comerciales tampoco
     * debe quedar un rótulo huérfano encabezando una fila vacía.
     */
    public function test_la_portada_aguanta_que_falte_cualquiera_de_los_dos_niveles(): void
    {
        $this->seed(DatabaseSeeder::class);

        Aliado::query()->where('tipo', TipoAliado::Institucional)->delete();
        $this->get('/')->assertOk()->assertDontSee(ajuste('portada_aliados_institucionales'), escape: false);

        Aliado::query()->where('tipo', TipoAliado::Comercial)->delete();
        $this->get('/')
            ->assertOk()
            ->assertDontSee(ajuste('portada_aliados_comerciales'), escape: false)
            ->assertDontSee(ajuste('portada_aliados_titulo'), escape: false);
    }

    /**
     * El detalle del convenio es lo que distingue a una marca de una entidad:
     * la Gobernación no le hace descuento a nadie. Si un institucional trae
     * detalle privado, o alguien se equivocó de nivel o hay un convenio que
     * clasificar, y en los dos casos conviene enterarse.
     */
    public function test_los_institucionales_sembrados_no_traen_convenio_privado(): void
    {
        $this->seed(DatabaseSeeder::class);

        $conConvenio = Aliado::query()
            ->where('tipo', TipoAliado::Institucional)
            ->whereNotNull('detalle_convenio')
            ->pluck('nombre')
            ->all();

        $this->assertSame([], $conConvenio, 'Un aliado institucional no debería tener convenio privado sembrado.');
    }
}

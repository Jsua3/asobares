<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Database\Seeders\MunicipioSeeder;
use Database\Seeders\RequisitoAperturaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La guía deja de ser de un municipio y pasa a ser de los doce (D-21).
 *
 * El 15 de septiembre de 2026 el gremio entregó «Guía normativa Abre tu
 * negocio – 12 municipios del Quindío». Trae dos cosas de naturaleza distinta,
 * y la diferencia entre ellas es todo lo que vigila esta clase:
 *
 * - **El marco general**: nueve trámites de norma nacional, iguales en los
 *   doce municipios, cada uno con su ley citada. Eso se puede fechar, porque
 *   la ley se puede ir a mirar.
 * - **Los bloques por municipio**: qué pide Planeación, Bomberos, Salud y
 *   Rentas en cada sitio. El archivo trae **las doce filas en «Pendiente»** y
 *   sus celdas de costo y enlace rotuladas «a confirmar». Eso NO se puede
 *   fechar, y la guía lo dice en la cara del lector.
 *
 * Lo que se dejó fuera y por qué está en el sembrador. Lo que se vigila aquí
 * es que nadie lo vuelva a meter por descuido: ni un costo sin verificar, ni
 * un enlace que prometa un trámite y abra una portada.
 */
class GuiaDeLosDoceMunicipiosTest extends TestCase
{
    use RefreshDatabase;

    /** Los once que levantó el archivo del gremio. Armenia va aparte. */
    private const array ONCE = [
        'calarca', 'circasia', 'cordoba', 'buenavista', 'filandia', 'genova',
        'la-tebaida', 'montenegro', 'pijao', 'quimbaya', 'salento',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MunicipioSeeder::class);
        $this->seed(RequisitoAperturaSeeder::class);
    }

    public function test_el_departamento_entero_tiene_municipio_en_la_base(): void
    {
        $this->assertSame(12, Municipio::query()->count());

        foreach (self::ONCE as $slug) {
            $this->assertNotNull(
                Municipio::where('slug', $slug)->first(),
                "Falta el municipio {$slug}."
            );
        }
    }

    /**
     * Armenia no se toca: sus ocho fichas salen del documento de la Alcaldía y
     * están verificadas desde el 20 de agosto. Si el marco general genérico se
     * les colara encima, el municipio con mejor información acabaría con dos
     * fichas para el mismo trámite.
     */
    public function test_armenia_conserva_sus_ocho_fichas_y_no_recibe_el_marco_general(): void
    {
        $armenia = Municipio::where('slug', 'armenia')->firstOrFail();

        $this->assertSame(8, $armenia->requisitos()->count());

        foreach (RequisitoAperturaSeeder::MARCO_GENERAL as $tramite) {
            $this->assertNull(
                RequisitoApertura::where('municipio_id', $armenia->id)
                    ->where('entidad', $tramite['entidad'])
                    ->first(),
                "El marco general se coló en Armenia: {$tramite['entidad']}."
            );
        }
    }

    /** Nueve del marco general más cuatro entidades locales. */
    public function test_cada_municipio_nuevo_trae_las_trece_fichas(): void
    {
        foreach (self::ONCE as $slug) {
            $municipio = Municipio::where('slug', $slug)->firstOrFail();

            $this->assertSame(
                13,
                $municipio->requisitos()->count(),
                "El municipio {$slug} no tiene las 13 fichas."
            );
        }
    }

    /** Lo que hace auditable al marco general: la ley va escrita en la ficha. */
    public function test_el_marco_general_cita_la_norma_en_la_que_se_apoya(): void
    {
        $sinNorma = RequisitoApertura::query()
            ->whereDate('verificado_el', RequisitoAperturaSeeder::VERIFICADO_EL_EXCEL)
            ->get()
            ->filter(fn (RequisitoApertura $r): bool => ! str_contains((string) $r->verificado_con, 'base normativa:'))
            ->pluck('entidad')
            ->all();

        $this->assertSame([], $sinNorma, 'Trámites del marco general sin su norma: '.implode(', ', $sinNorma));
    }

    /**
     * El corazón del asunto: lo que el archivo declara «Pendiente» sale sin
     * fecha, y la guía lo confiesa en la página en vez de pasarlo por bueno.
     */
    public function test_los_bloques_locales_salen_sin_fecha_y_la_guia_lo_confiesa(): void
    {
        $municipio = Municipio::where('slug', 'pijao')->firstOrFail();

        $locales = RequisitoApertura::where('municipio_id', $municipio->id)
            ->whereNull('verificado_el')
            ->get();

        $this->assertCount(4, $locales, 'Pijao debería traer sus 4 bloques locales sin fechar.');

        $this->get(route('guia.index', ['municipio' => 'pijao']))
            ->assertSuccessful()
            ->assertSee('Sin verificar contra la fuente oficial');
    }

    /**
     * La regla que ya costó una limpieza entera: el archivo viene lleno de
     * «Gratuito» y «Vigencia: un (1) año» rotulados «a confirmar», y ni uno
     * solo entra a la base.
     */
    public function test_ninguna_ficha_nueva_publica_un_costo(): void
    {
        $conCosto = RequisitoApertura::query()
            ->whereNotNull('costo_aproximado')
            ->pluck('entidad')
            ->all();

        $this->assertSame([], $conCosto, 'Hay costos sembrados sin que nadie los confirmara: '.implode(', ', $conCosto));
    }

    /**
     * OBS3-10 al revés: de los 25 enlaces del archivo, 18 abren la portada de
     * la alcaldía. Esos no se siembran. Los que sí entran, abren el trámite.
     */
    public function test_los_municipios_nuevos_solo_traen_enlaces_que_abren_el_tramite(): void
    {
        $slugs = Municipio::whereIn('slug', self::ONCE)->pluck('id');

        $mentirosos = RequisitoApertura::query()
            ->whereIn('municipio_id', $slugs)
            ->whereNotNull('enlace_externo')
            ->get()
            ->reject->enlaceEsPuntual()
            ->pluck('enlace_externo')
            ->all();

        $this->assertSame(
            [],
            $mentirosos,
            'Se sembraron enlaces a portada en los municipios nuevos: '.implode(', ', $mentirosos)
        );
    }

    /** Y el sitio los sirve de verdad, no solo la base los tiene. */
    public function test_la_guia_de_un_municipio_nuevo_se_abre_y_ensena_sus_tramites(): void
    {
        $this->get(route('guia.index', ['municipio' => 'genova']))
            ->assertSuccessful()
            ->assertSee('Matrícula Mercantil', escape: false)
            ->assertSee('Cuerpo de Bomberos', escape: false);
    }

    /**
     * La cifra del expediente, verificada por ejecución y no por suma.
     *
     * Once municipios con trece fichas cada uno, más las ocho de Armenia. Si
     * alguien vuelve a sembrar sobre una base ya poblada y `updateOrCreate`
     * deja de casar por su clave natural, esto lo cazaría al instante.
     */
    public function test_el_departamento_queda_cubierto_con_ciento_cincuenta_y_una_fichas(): void
    {
        $this->assertSame(151, RequisitoApertura::query()->count());
    }

    /** Y resembrar no duplica: la clave natural es (municipio, entidad). */
    public function test_volver_a_sembrar_no_duplica_una_sola_ficha(): void
    {
        $antes = RequisitoApertura::query()->count();

        $this->seed(RequisitoAperturaSeeder::class);

        $this->assertSame($antes, RequisitoApertura::query()->count());
    }

    /** Lo sembrado sale publicado; si no, la guía seguiría en un municipio. */
    public function test_lo_sembrado_queda_publicado(): void
    {
        $borradores = RequisitoApertura::query()
            ->where('estado', EstadoPublicacion::Borrador)
            ->count();

        $this->assertSame(0, $borradores, 'Hay fichas en borrador: la guía no las enseñaría.');
    }
}

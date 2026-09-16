<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El enlace de la guía dice lo que cumple.
 *
 * El gremio pide enlaces al trámite exacto y no a la portada de la entidad, y
 * su razón no es estética: llegar a ese registro es difícil, y hay personas
 * que no se llevan bien con la tecnología.
 *
 * ⚠️ Las URL exactas son insumo del gremio y todavía no existen: de las ocho
 * fichas sembradas, las tres que traen enlace apuntan a dominio pelado, y el
 * documento de fuente oficial tampoco las trae. Inventar direcciones de
 * trámites legales choca con la regla del encargo: en producción solo entra
 * contenido de documento oficial del gremio.
 *
 * Así que lo que se construye aquí es la otra mitad: el sitio distingue las
 * dos cosas y no promete la que no tiene, y el día que lleguen las URL la
 * mejora se nota sola sin tocar la vista.
 */
class EnlacePuntualDeLaGuiaTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<array{string, bool}> */
    public static function enlaces(): array
    {
        return [
            'dominio pelado' => ['https://camaraarmenia.org.co', false],
            'dominio con barra' => ['https://camaraarmenia.org.co/', false],
            'con subdominio, sigue siendo portada' => ['https://www.sayco.org', false],
            'con camino' => ['https://camaraarmenia.org.co/tramites/matricula-mercantil', true],
            'con camino de un solo nivel' => ['https://armenia.gov.co/usos-del-suelo', true],
            'con consulta' => ['https://armenia.gov.co/?tramite=usos-del-suelo', true],
            'con ancla' => ['https://www.sayco.org/#licenciamiento', true],
        ];
    }

    #[DataProvider('enlaces')]
    public function test_distingue_el_tramite_de_la_portada(string $url, bool $esPuntual): void
    {
        $requisito = new RequisitoApertura(['enlace_externo' => $url]);

        $this->assertSame($esPuntual, $requisito->enlaceEsPuntual(), "Fallo con «{$url}».");
    }

    /** Sin enlace no hay nada que prometer. */
    public function test_sin_enlace_no_es_puntual(): void
    {
        $this->assertFalse((new RequisitoApertura(['enlace_externo' => null]))->enlaceEsPuntual());
        $this->assertFalse((new RequisitoApertura(['enlace_externo' => '']))->enlaceEsPuntual());
    }

    /**
     * Lo que de verdad importa: la etiqueta que lee el usuario. Con un enlace
     * a portada no puede decir «ir al trámite», porque eso es prometerle que
     * el clic lo deja donde tiene que estar y es justo lo que el gremio pide
     * evitar.
     */
    public function test_un_enlace_a_la_portada_no_promete_el_tramite(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->requisito('https://camaraarmenia.org.co');

        $this->get(route('guia.index'))
            ->assertOk()
            ->assertSee(ajuste('guia_enlace_portada'), escape: false)
            ->assertDontSee(ajuste('guia_enlace_puntual'), escape: false);
    }

    /** Y con el enlace bueno sí lo promete, que es el premio de arreglarlo. */
    public function test_un_enlace_puntual_si_invita_al_tramite(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->requisito('https://camaraarmenia.org.co/tramites/matricula-mercantil');

        $this->get(route('guia.index'))
            ->assertOk()
            ->assertSee(ajuste('guia_enlace_puntual'), escape: false);
    }

    /** Los dos rótulos los edita el gremio, como el resto del contenido. */
    public function test_los_rotulos_son_editables(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->requisito('https://camaraarmenia.org.co/tramites/matricula-mercantil');

        Setting::query()->where('clave', 'guia_enlace_puntual')->first()?->update(['valor' => 'ABRE EL TRAMITE YA']);

        $this->get(route('guia.index'))->assertOk()->assertSee('ABRE EL TRAMITE YA', escape: false);
    }

    /**
     * Medía la deuda de OBS3-10 exigiendo **cero** enlaces puntuales sembrados,
     * y el 15 de septiembre de 2026 se puso roja: el archivo del gremio para los
     * doce municipios trajo los dos primeros que sí abren el trámite (Calarcá).
     * Era el día que la propia prueba anunciaba —«hay que venir a celebrarlo»—,
     * así que la deuda se sigue midiendo, pero donde de verdad queda.
     *
     * Y queda en **Armenia**: sus siete enlaces salen del documento de la
     * Alcaldía, que no trae las URL de trámite, y esa es la decisión D-04 que
     * sigue esperando a que el gremio las consiga. El día que lleguen, esta
     * prueba se pone roja otra vez y se borra de una vez por todas.
     */
    public function test_a_armenia_todavia_le_faltan_las_urls_de_tramite(): void
    {
        $this->seed(DatabaseSeeder::class);

        $armenia = Municipio::where('slug', 'armenia')->firstOrFail();

        $conEnlace = RequisitoApertura::query()
            ->where('municipio_id', $armenia->id)
            ->whereNotNull('enlace_externo')
            ->get();

        $this->assertNotEmpty($conEnlace, 'La guía de Armenia debería traer trámites con enlace.');

        $puntuales = $conEnlace->filter->enlaceEsPuntual()->pluck('enlace_externo')->all();

        $this->assertSame(
            [],
            $puntuales,
            'Armenia ya tiene enlaces al trámite: D-04 dejó de estar bloqueada. '
            .'Celebra, borra esta prueba y anótalo en el §27.2. Enlaces: '.implode(', ', $puntuales)
        );
    }

    /**
     * La otra mitad de la misma regla, que antes no se podía escribir porque no
     * había un solo enlace bueno: lo que SÍ se siembra, abre el trámite. Es lo
     * que separa a este cambio de haber volcado el archivo del gremio entero.
     */
    public function test_fuera_de_armenia_no_se_siembra_ningun_enlace_a_portada(): void
    {
        $this->seed(DatabaseSeeder::class);

        $armenia = Municipio::where('slug', 'armenia')->firstOrFail();

        $mentirosos = RequisitoApertura::query()
            ->where('municipio_id', '!=', $armenia->id)
            ->whereNotNull('enlace_externo')
            ->get()
            ->reject->enlaceEsPuntual()
            ->pluck('enlace_externo')
            ->all();

        $this->assertSame(
            [],
            $mentirosos,
            'Se sembraron enlaces a portada fuera de Armenia: '.implode(', ', $mentirosos)
        );
    }

    private function requisito(?string $enlace): RequisitoApertura
    {
        RequisitoApertura::query()->delete();

        return RequisitoApertura::factory()->create([
            'municipio_id' => Municipio::query()->firstOrFail()->getKey(),
            'enlace_externo' => $enlace,
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }
}

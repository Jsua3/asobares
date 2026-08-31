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
 * El enlace de la guía dice lo que cumple (OBS3-10).
 *
 * El directivo pidió enlaces al trámite exacto: «que sea puntual… o sea que no
 * me abra la página de la cámara solamente» (R23 05:39-05:44), «tiene que ir
 * directamente el enlace» (R23 05:50). Su razón no era estética: «muchas veces
 * llegar a ese registro es difícil» (R23 06:05) y «hay personas que no son tan
 * amigables con la tecnología» (R23 06:27).
 *
 * ⚠️ Las URL exactas son insumo del gremio y todavía no existen: los siete
 * trámites sembrados apuntan a dominio pelado, y el documento de fuente
 * oficial tampoco las trae. Inventar direcciones de trámites legales es lo que
 * el §29.4 prohíbe expresamente.
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
     * el clic lo deja donde tiene que estar y es justo lo que se señaló.
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
     * Deja constancia medida de la deuda que este cambio NO cierra, para que
     * quien lea la suite sepa que el pendiente es contenido y no código. Si
     * un día alguien siembra las URL buenas, esta prueba se pone roja y hay
     * que venir a celebrarlo y borrarla.
     */
    public function test_los_enlaces_sembrados_siguen_siendo_de_portada(): void
    {
        $this->seed(DatabaseSeeder::class);

        $conEnlace = RequisitoApertura::query()->whereNotNull('enlace_externo')->get();

        $this->assertNotEmpty($conEnlace, 'El sembrador debería traer trámites con enlace.');

        $puntuales = $conEnlace->filter->enlaceEsPuntual();

        $this->assertCount(
            0,
            $puntuales,
            'Ya hay enlaces puntuales sembrados: OBS3-10 dejó de estar bloqueado por el insumo del gremio. '
            .'Actualiza esta prueba y el §27.2.'
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

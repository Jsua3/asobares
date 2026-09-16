<?php

namespace Tests\Feature;

use App\Enums\Alcance;
use App\Filament\Resources\Beneficios\Pages\CreateBeneficio;
use App\Models\Beneficio;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\BeneficioSeeder;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Los beneficios se clasifican por territorio (Acta 07, A-01).
 *
 * Un solo módulo, no tres: lo que distingue a un beneficio de ASOBARES Colombia
 * de uno del capítulo o de uno municipal es una columna, no una tabla.
 *
 * La regla que gobierna todo esto: **el sistema no inventa la clasificación**.
 * Un beneficio sin clasificar no lleva etiqueta territorial en el sitio, igual
 * que la franja de cifras no se pinta sin cifras. Quién es de quién lo dice el
 * gremio desde el panel, no el sembrador.
 */
class BeneficiosPorAlcanceTest extends TestCase
{
    use RefreshDatabase;

    /** La marca del sello, para poder afirmar su ausencia sin falsos positivos. */
    private const SELLO = 'asb-sello-alcance';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    // --- Lo que el sitio público distingue ---

    /**
     * Se afirma sobre la marca del sello y no sobre el texto: el sitio entero se
     * llama «ASOBARES Quindío», así que buscar ese nombre en la página no
     * distingue una etiqueta territorial del membrete.
     */
    public function test_un_beneficio_sin_clasificar_no_lleva_etiqueta_territorial(): void
    {
        Beneficio::factory()->create(['titulo' => 'Beneficio Sin Clasificar', 'alcance' => null]);

        $this->get(route('afiliate'))
            ->assertOk()
            ->assertSee('Beneficio Sin Clasificar')
            ->assertDontSee(self::SELLO);
    }

    /**
     * Se mira lo que dice el sello por dentro, y no si el texto aparece en la
     * página: «ASOBARES Quindío» es el membrete del sitio y los municipios
     * salen en el formulario de afiliación, así que un `assertSee` a secas
     * pasaría aunque el sello dijera cualquier otra cosa.
     *
     * Se busca el sello en el árbol del documento y se lee su texto completo.
     * Lo que se vigila es «hay un sello y anuncia este territorio», que no
     * depende de cómo esté escrito el marcado: un atributo nuevo en la etiqueta
     * o un `<span>` envolviendo el rótulo lo dejan diciendo lo mismo.
     */
    private function assertElSelloDice(string $esperado): void
    {
        $xpath = $this->xpathDe($this->get(route('afiliate'))->assertOk()->getContent());

        $dichos = [];

        foreach ($xpath->query('//*['.$this->conLaClase(self::SELLO).']') as $sello) {
            $dichos[] = trim((string) preg_replace('/\s+/u', ' ', $sello->textContent));
        }

        $this->assertContains($esperado, $dichos, "El sello de alcance no dice «{$esperado}».");
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
     * Predicado XPath: el elemento lleva la clase entera, no una subcadena.
     * Rodear de espacios es lo que impide que `text-apagado-suave` pase por
     * `text-apagado`, o que una marca ajena que empiece igual pase por el sello.
     */
    private function conLaClase(string $clase): string
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " '.$clase.' ")';
    }

    /**
     * El sello puede cambiar de forma, pero no puede salirse del sistema de
     * color por su cuenta: tiene que seguir usando `text-apagado`, el token del
     * texto tenue de todo el sitio.
     *
     * Se afirma sobre el TOKEN y no sobre un número: el contraste se decide
     * donde vive el token, en `resources/css/tokens.css`, y lo mide
     * `ContrasteDelTextoTenueTest`. Cambiar el color aquí escondería en un
     * componente una decisión que cruza todas las páginas.
     *
     * Se cuentan los sellos del árbol y se exige que TODOS lleven el token, no
     * que alguno lo lleve. Contarlos por separado es lo que evita el aviso al
     * revés: si la página no pintara ningún sello, la prueba dice que no hay
     * sello en vez de acusar al token de haberse perdido.
     */
    public function test_el_sello_conserva_el_token_de_color_que_se_midio(): void
    {
        $beneficio = Beneficio::factory()->create(['alcance' => Alcance::Departamental]);

        $xpath = $this->xpathDe($this->get(route('afiliate'))->assertOk()->getContent());
        $sellos = '//*['.$this->conLaClase(self::SELLO).']';

        $pintados = $xpath->query($sellos)->length;
        $this->assertGreaterThan(0, $pintados, 'La página no pintó ningún sello de alcance.');

        $this->assertSame(
            $pintados,
            $xpath->query($sellos.'['.$this->conLaClase('text-apagado').']')->length,
            'El sello de alcance perdió `text-apagado`, el token cuyo contraste mide ContrasteDelTextoTenueTest. '
            .'Si se cambia el color hay que volver a medirlo, no suponerlo.'
        );

        $this->assertNotNull($beneficio->etiquetaDeAlcance());
    }

    public function test_un_beneficio_nacional_se_anuncia_como_de_asobares_colombia(): void
    {
        Beneficio::factory()->create(['titulo' => 'Convenio Nacional', 'alcance' => Alcance::Nacional]);

        $this->assertElSelloDice('ASOBARES Colombia');
    }

    public function test_un_beneficio_departamental_se_anuncia_como_del_capitulo(): void
    {
        Beneficio::factory()->create(['titulo' => 'Convenio Del Capitulo', 'alcance' => Alcance::Departamental]);

        $this->assertElSelloDice('ASOBARES Quindío');
    }

    /**
     * El municipal no se anuncia como «Municipal», que no le dice nada a nadie:
     * se anuncia con el nombre del municipio, que es la información.
     */
    public function test_un_beneficio_municipal_se_anuncia_con_el_nombre_de_su_municipio(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Circasia']);

        $beneficio = Beneficio::factory()->create([
            'titulo' => 'Convenio De Circasia',
            'alcance' => Alcance::Municipal,
            'municipio_id' => $municipio->id,
        ]);

        $this->assertSame('Circasia', $beneficio->fresh()->etiquetaDeAlcance());
        $this->assertElSelloDice('Circasia');
    }

    // --- El invariante de los dos lados ---

    /**
     * «Cuando el alcance sea Municipio, exigir el municipio; cuando no lo sea,
     * el municipio debe quedar vacío.» El segundo lado se resuelve en el modelo
     * y no en el formulario: quien cambia el alcance de municipal a nacional
     * dejaría atrás un municipio que ya no significa nada, y esa fila mentiría
     * a cualquier consulta que filtre por municipio.
     */
    public function test_un_beneficio_que_deja_de_ser_municipal_suelta_su_municipio(): void
    {
        $municipio = Municipio::factory()->create();

        $beneficio = Beneficio::factory()->create([
            'alcance' => Alcance::Municipal,
            'municipio_id' => $municipio->id,
        ]);

        $beneficio->update(['alcance' => Alcance::Nacional]);

        $this->assertNull($beneficio->fresh()->municipio_id);
    }

    public function test_un_beneficio_sin_clasificar_tampoco_conserva_municipio(): void
    {
        $municipio = Municipio::factory()->create();

        $beneficio = Beneficio::factory()->create([
            'alcance' => null,
            'municipio_id' => $municipio->id,
        ]);

        $this->assertNull($beneficio->fresh()->municipio_id);
    }

    public function test_el_beneficio_municipal_si_conserva_su_municipio(): void
    {
        $municipio = Municipio::factory()->create();

        $beneficio = Beneficio::factory()->create([
            'alcance' => Alcance::Municipal,
            'municipio_id' => $municipio->id,
        ]);

        $this->assertSame($municipio->id, $beneficio->fresh()->municipio_id);
    }

    // --- El panel ---

    public function test_el_panel_exige_el_municipio_cuando_el_alcance_es_municipal(): void
    {
        $this->actingAs($this->direccion());

        Livewire::test(CreateBeneficio::class)
            ->fillForm([
                'titulo' => 'Beneficio Municipal Sin Municipio',
                'descripcion' => 'Falta decir de qué municipio.',
                'alcance' => Alcance::Municipal->value,
                'municipio_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['municipio_id']);
    }

    public function test_el_panel_no_exige_municipio_cuando_el_alcance_no_es_municipal(): void
    {
        $this->actingAs($this->direccion());

        Livewire::test(CreateBeneficio::class)
            ->fillForm([
                'titulo' => 'Beneficio Nacional',
                'descripcion' => 'De ASOBARES Colombia.',
                'alcance' => Alcance::Nacional->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('beneficios', ['titulo' => 'Beneficio Nacional', 'alcance' => Alcance::Nacional->value]);
    }

    // --- Contenido: el sistema no clasifica por su cuenta ---

    /**
     * Los cinco beneficios sembrados salen del catálogo oficial «BENEFICIOS
     * AFILIADOS», y ese documento no dice de quién es cada uno. Ponerles un
     * alcance por defecto sería publicar una afirmación que nadie hizo: nacen
     * sin clasificar y los clasifica el gremio.
     */
    public function test_el_sembrador_no_inventa_el_alcance_de_los_beneficios_oficiales(): void
    {
        $this->seed(BeneficioSeeder::class);

        $this->assertSame(5, Beneficio::count());
        $this->assertSame(0, Beneficio::whereNotNull('alcance')->count(), 'El sembrador clasificó un beneficio que el documento oficial no clasifica.');
    }

    private function direccion(): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        return $usuario->fresh();
    }
}

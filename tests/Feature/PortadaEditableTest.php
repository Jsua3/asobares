<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Los títulos de sección de la portada se editan desde el panel (OBS3-01).
 *
 * En la revisión del 28 de agosto se le dijo al gremio que «toda la página es
 * completamente editable» (R22 02:53) mientras siete títulos estaban cableados
 * en `publico/inicio.blade.php` — justo en la pantalla que estaban mirando. El
 * `SettingSeeder` ya declaraba la regla en su cabecera: «si un texto se ve en
 * el sitio público, se edita aquí desde el panel, nunca en una vista Blade».
 * Esta prueba es lo que la convierte en algo que se puede incumplir en rojo.
 */
class PortadaEditableTest extends TestCase
{
    use RefreshDatabase;

    /** Las doce claves que gobiernan el texto propio de la portada. */
    private const array TITULOS = [
        'portada_cifras_titulo',
        'portada_guia_titulo',
        'portada_empleo_titulo',
        'portada_destacados_titulo',
        'portada_beneficios_titulo',
        'portada_eventos_titulo',
        'portada_aliados_titulo',
        'portada_aliados_institucionales',
        'portada_aliados_comerciales',
        'portada_guia_texto',
        'portada_empleo_texto',
        'portada_destacados_texto',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Edita el ajuste por el camino que invalida la caché.
     *
     * `Setting::todos()` cachea para siempre y el modelo solo olvida en sus
     * eventos `saved`/`deleted`. Un `update()` masivo NO los dispara —lo dice
     * la propia cabecera de `Setting::olvidarCache()`— así que una prueba
     * escrita con `where(...)->update(...)` leería el valor viejo y pasaría
     * en verde sin haber ejercido nada.
     */
    private function editarAjuste(string $clave, string $valor): void
    {
        $ajuste = Setting::query()->where('clave', $clave)->first();

        $this->assertNotNull($ajuste, "El ajuste «{$clave}» no está sembrado.");
        $this->assertNotSame('', (string) $ajuste->valor, "El ajuste «{$clave}» está vacío.");

        $ajuste->update(['valor' => $valor]);
    }

    /**
     * La prueba que de verdad protege: si alguien vuelve a cablear un título,
     * la portada dejará de obedecer al panel y esto se pone rojo.
     *
     * Solo comprueba el positivo. Un `assertDontSee` del valor de fábrica
     * sería falso rojo: «Abre tu negocio» y «Bolsa de empleo» salen también en
     * la navbar y en el pie, que son otras vistas y no están en discusión. De
     * que no quede una copia cableada al lado se encarga la guardia
     * estructural del final, que mira la vista y no la respuesta.
     */
    public function test_cada_titulo_de_la_portada_obedece_a_su_ajuste(): void
    {
        foreach (self::TITULOS as $indice => $clave) {
            $this->editarAjuste($clave, "TITULO EDITADO DESDE EL PANEL {$indice}");
        }

        $respuesta = $this->get('/')->assertOk();

        foreach (self::TITULOS as $indice => $clave) {
            $respuesta->assertSee("TITULO EDITADO DESDE EL PANEL {$indice}", escape: false);
        }
    }

    /** La entradilla de beneficios también se edita, no solo el título. */
    public function test_la_entradilla_de_beneficios_obedece_a_su_ajuste(): void
    {
        $porDefecto = Setting::valor('portada_beneficios_intro');

        $this->editarAjuste('portada_beneficios_intro', 'ENTRADILLA EDITADA DESDE EL PANEL');

        $this->get('/')->assertOk()
            ->assertSee('ENTRADILLA EDITADA DESDE EL PANEL', escape: false)
            ->assertDontSee($porDefecto, escape: false);
    }

    /**
     * OBS3-01, el señalamiento textual: «lo que gana es como si estuviéramos
     * vendiendo una lotería» (R22 03:05). No basta con que el ajuste exista —
     * el valor sembrado, que es el que verá el gremio en la próxima demo, no
     * puede seguir siendo el que le sonó horrible.
     */
    public function test_la_portada_ya_no_titula_los_beneficios_como_lo_que_gana(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('Lo que gana', escape: false)
            ->assertSee('Beneficios de pertenecer al gremio', escape: false);
    }

    /**
     * Guardia estructural, hermana de la de `TemaClaroOscuroTest`.
     *
     * Existe porque la prueba de comportamiento solo vigila las diez claves
     * que YA existen: un texto nuevo aniadido cableado maniana pasaria por
     * delante de ella sin despeinarla. Esta mira la vista, no la respuesta.
     *
     * La regla, aplicada a `<h2>` y `<p>`: o el cuerpo interpola algo con
     * llaves dobles, o contiene marcado anidado --un enlace, un icono, un
     * componente, que se juzgan por su propio contenido--. Un cuerpo que no
     * tiene ni lo uno ni lo otro es prosa literal, y esa va en `ajustes`.
     */
    public function test_ningun_texto_propio_de_la_portada_esta_cableado(): void
    {
        $vista = resource_path('views/publico/inicio.blade.php');
        $this->assertFileExists($vista);

        $contenido = File::get($vista);
        $cableados = [];

        foreach (['h2', 'p'] as $etiqueta) {
            $encontrados = preg_match_all(
                sprintf('/<%1$s\b[^>]*>(.*?)<\/%1$s>/s', $etiqueta),
                $contenido,
                $coincidencias
            );

            $this->assertGreaterThan(
                0,
                $encontrados,
                "No se encontro ningun <{$etiqueta}> en la portada: el patron de la guardia quedo obsoleto."
            );

            foreach ($coincidencias[1] as $cuerpo) {
                if (str_contains($cuerpo, '{{') || str_contains($cuerpo, '<')) {
                    continue;
                }

                $limpio = trim(preg_replace('/\s+/', ' ', $cuerpo));

                if ($limpio !== '') {
                    $cableados[] = "<{$etiqueta}> {$limpio}";
                }
            }
        }

        $this->assertSame(
            [],
            $cableados,
            "Hay texto cableado en la portada; pasalo a `ajuste()`:\n".implode("\n", $cableados)
        );
    }
}

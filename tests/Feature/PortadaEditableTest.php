<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Los textos propios de la portada se editan desde el panel.
 *
 * Al gremio se le promete que toda la página es editable, y un título
 * cableado en la vista lo desmiente justo en la pantalla que más se mira.
 * `SettingSeeder` declara la regla en su cabecera: «si un texto se ve en el
 * sitio público, se edita aquí desde el panel, nunca en una vista Blade».
 * Esta prueba es lo que la convierte en algo que se puede incumplir en rojo.
 */
class PortadaEditableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Claves de texto propio que pinta la portada. Los subtítulos y los dos
     * textos del cierre están aquí porque la portada editorial los lee de
     * `ajustes`: sin sembrarlos, la vista cae en su respaldo y el panel no
     * puede cambiarlos.
     */
    private const array TITULOS = [
        'portada_cifras_subtitulo',
        'portada_destacados_subtitulo',
        'portada_beneficios_subtitulo',
        'portada_actualidad_subtitulo',
        'cta_editorial_frase',
        'cta_final_boton',
        'portada_cifras_titulo',
        'portada_guia_titulo',
        'portada_destacados_titulo',
        'portada_beneficios_titulo',
        'portada_eventos_titulo',
        'portada_aliados_titulo',
        'portada_aliados_institucionales',
        'portada_aliados_comerciales',
        'portada_videos_rotulo',
        'portada_videos_titulo',
        'hero_frase_corta',
        'hero_resumen_corto',
        'hero_video_rotulo',
        'hero_video_titulo',
        'hero_video_detalle',
        'portada_guia_texto',
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
            $this->editarAjuste($clave, $this->textoEditado($indice));
        }

        $respuesta = $this->get('/')->assertOk();

        foreach (self::TITULOS as $indice => $clave) {
            $respuesta->assertSee($this->textoEditado($indice), escape: false);
        }
    }

    /**
     * El índice va cerrado entre corchetes a propósito. Sin cierre, «PANEL 1»
     * es prefijo de «PANEL 10» a «PANEL 19», y la clave del índice 1 pasaba en
     * verde aunque su vista la cableara: la sostenía el texto de otra clave.
     */
    private function textoEditado(int $indice): string
    {
        return "TITULO EDITADO DESDE EL PANEL [{$indice}]";
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
     * El título de los beneficios no puede sonar a premio: el gremio rechazó
     * «lo que gana» porque se lee «como si estuviéramos vendiendo una
     * lotería». No basta con que el ajuste exista: el valor sembrado es el que
     * se ve mientras nadie lo edite.
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
     * Existe porque la prueba de comportamiento solo vigila las claves que YA
     * existen: un texto nuevo añadido cableado mañana pasaría por delante de
     * ella sin despeinarla. Esta mira la vista, no la respuesta.
     *
     * La regla, aplicada a `<h2>` y `<p>`: o el cuerpo interpola algo con
     * llaves dobles, o contiene marcado anidado --un enlace, un icono, un
     * componente, que se juzgan por su propio contenido--. Un cuerpo que no
     * tiene ni lo uno ni lo otro es prosa literal, y esa va en `ajustes`.
     */
    public function test_ningun_texto_propio_de_la_portada_esta_cableado(): void
    {
        $vistas = array_merge(
            [resource_path('views/publico/inicio.blade.php')],
            File::glob(resource_path('views/components/publico/home/*.blade.php')) ?: []
        );

        $cableados = [];

        foreach ($vistas as $vista) {
            $this->assertFileExists($vista);

            $contenido = File::get($vista);

            foreach (['h2', 'p'] as $etiqueta) {
                preg_match_all(
                    sprintf('/<%1$s\b[^>]*>(.*?)<\/%1$s>/s', $etiqueta),
                    $contenido,
                    $coincidencias
                );

                foreach ($coincidencias[1] as $cuerpo) {
                    if (str_contains($cuerpo, '{{') || str_contains($cuerpo, '<')) {
                        continue;
                    }

                    $limpio = trim(preg_replace('/\s+/', ' ', $cuerpo));

                    if ($limpio !== '') {
                        $cableados[] = basename($vista).": <{$etiqueta}> {$limpio}";
                    }
                }
            }
        }

        $this->assertNotSame([], $vistas, 'La portada editorial no tiene vistas que vigilar.');

        $this->assertSame(
            [],
            $cableados,
            "Hay texto cableado en la portada; pasalo a `ajuste()`:\n".implode("\n", $cableados)
        );
    }
}

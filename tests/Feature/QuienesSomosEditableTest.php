<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * «Quiénes somos» se edita entero desde el panel (OBS3-11).
 *
 * El directivo dijo del texto institucional «ya eso toca cambiarlo» (R22
 * 06:10) y quedó en pedirle a la directora nacional que corrija los datos del
 * capítulo. **La redacción propia es insumo del gremio y sigue pendiente**:
 * este trabajo no la inventa, porque inventar la historia y la misión de una
 * agremiación es justo lo que no puede hacer un programador.
 *
 * Lo que sí resuelve es que, cuando llegue, entre sin tocar código: quince
 * textos de esta página estaban cableados en la vista --ocho títulos de
 * sección y siete rótulos-- mientras el cuerpo ya salía de `ajustes`. Con
 * ellos dentro, «entregar el texto» habría significado abrir un editor.
 *
 * Es la misma deuda que el §27.3 punto 2 destapó en la portada, en la segunda
 * página institucional del sitio. La portada tiene su guardia equivalente en
 * `PortadaEditableTest`; esta cubre `quienes-somos`.
 */
class QuienesSomosEditableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Los quince textos que dejaron de estar cableados, más las cuatro claves
     * del respaldo nacional que entraron el 9 de septiembre de 2026 (las dos
     * cifras de la lámina 3 de la presentación institucional y sus dos
     * rótulos). Los rótulos son ajuste y no texto en la vista justamente por
     * esta guardia: dicen «en el país», que es lo que impide leerlos como el
     * tamaño del capítulo, y el gremio tiene que poder cambiarlos.
     */
    private const array CLAVES = [
        'quienes_titulo_historia',
        'quienes_titulo_que_hacemos',
        'quienes_titulo_barreras',
        'quienes_titulo_lineas',
        'quienes_titulo_armenia',
        'quienes_titulo_direccion',
        'quienes_titulo_beneficios',
        'quienes_titulo_nacional',
        'quienes_rotulo_vision',
        'quienes_barreras_pie',
        'quienes_iniciativas_pie',
        'quienes_lineas_intro',
        'quienes_rotulo_programas',
        'quienes_cargo_presidente',
        'quienes_cargo_directora',
        'nacional_capitulos',
        'nacional_capitulos_rotulo',
        'nacional_afiliados',
        'nacional_afiliados_rotulo',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * La que de verdad protege: si alguien vuelve a cablear uno, la página
     * deja de obedecer al panel y esto se pone rojo.
     */
    public function test_cada_texto_de_la_pagina_obedece_a_su_ajuste(): void
    {
        foreach (self::CLAVES as $indice => $clave) {
            $ajuste = Setting::query()->where('clave', $clave)->first();

            $this->assertNotNull($ajuste, "El ajuste «{$clave}» no está sembrado.");
            $this->assertNotSame('', (string) $ajuste->valor, "El ajuste «{$clave}» está vacío.");

            // Por instancia, no en masa: un `update()` masivo no dispara los
            // eventos del modelo y `Setting::todos()` seguiría sirviendo el
            // valor viejo desde la caché. La prueba pasaría sin ejercer nada.
            $ajuste->update(['valor' => "TEXTO EDITADO DESDE EL PANEL {$indice}"]);
        }

        $respuesta = $this->get(route('quienes-somos'))->assertOk();

        foreach (self::CLAVES as $indice => $clave) {
            $respuesta->assertSee("TEXTO EDITADO DESDE EL PANEL {$indice}", escape: false);
        }
    }

    /**
     * Guardia estructural: un texto NUEVO cableado mañana pasaría por delante
     * de la prueba de arriba, que solo vigila las quince claves que existen.
     */
    public function test_ningun_texto_de_la_pagina_esta_cableado(): void
    {
        $vista = resource_path('views/publico/quienes-somos.blade.php');
        $this->assertFileExists($vista);

        $contenido = File::get($vista);
        $cableados = [];

        foreach (['h1', 'h2', 'h3', 'p'] as $etiqueta) {
            $encontrados = preg_match_all(
                sprintf('/<%1$s\b[^>]*>(.*?)<\/%1$s>/s', $etiqueta),
                $contenido,
                $coincidencias
            );

            if ($encontrados === 0) {
                continue;
            }

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
            "Hay texto cableado en «Quiénes somos»; pásalo a `ajuste()`:\n".implode("\n", $cableados)
        );
    }

    /**
     * El §27.5 lo fija como regla de contenido: «solo directora ejecutiva y
     * presidente, como lo hace la página nacional», porque al directivo «no
     * me gusta como mucha publicidad» personal (R22 05:41-05:47).
     *
     * Ya se cumplía antes de OBS3-11 y por eso no hubo que cambiar nada; la
     * prueba existe para que siga cumpliéndose cuando alguien tenga la buena
     * idea de añadir el resto de la junta.
     */
    public function test_la_direccion_se_muestra_corta(): void
    {
        $respuesta = $this->get(route('quienes-somos'))->assertOk();

        $respuesta->assertSee(ajuste('quienes_cargo_presidente'), escape: false);
        $respuesta->assertSee(ajuste('quienes_cargo_directora'), escape: false);

        foreach (['Tesorero', 'Secretario', 'Vocal', 'Fiscal', 'Suplente'] as $cargoDeMas) {
            $respuesta->assertDontSee($cargoDeMas, escape: false);
        }
    }

    /**
     * El respaldo nacional se comporta como la franja de cifras de la portada:
     * la cifra que la oficina deje en blanco no se pinta, y si borra las dos
     * desaparece el bloque entero.
     *
     * Sin esto, vaciar el número deja el rótulo flotando solo --«Capítulos en
     * el país» sin ningún número al lado--, que es peor que no enseñar nada y
     * es exactamente lo que pasa cuando alguien limpia un campo para volver a
     * escribirlo y se va a comer.
     */
    public function test_el_respaldo_nacional_sin_cifra_no_deja_el_rotulo_solo(): void
    {
        $capitulos = ajuste('nacional_capitulos_rotulo');
        $afiliados = ajuste('nacional_afiliados_rotulo');

        $this->get(route('quienes-somos'))
            ->assertOk()
            ->assertSee($capitulos, escape: false)
            ->assertSee($afiliados, escape: false);

        // Solo espacios: la vista recorta antes de decidir, así que esto tiene
        // que contar como vacía. Por instancia, para que salte el evento que
        // limpia la caché de `Setting::todos()`.
        Setting::query()->where('clave', 'nacional_capitulos')->first()->update(['valor' => '   ']);

        $this->get(route('quienes-somos'))
            ->assertOk()
            ->assertDontSee($capitulos, escape: false)
            ->assertSee($afiliados, escape: false);

        Setting::query()->where('clave', 'nacional_afiliados')->first()->update(['valor' => '']);

        $this->get(route('quienes-somos'))
            ->assertOk()
            ->assertDontSee($afiliados, escape: false);
    }

    /**
     * El caso de producción, que no es el de arriba.
     *
     * `SettingSeeder` **no corre en el despliegue**: cuando esta rama llegue a
     * producción, las cuatro claves del respaldo nacional no existirán en la
     * base hasta que alguien pase `ContenidoOficialSeeder` a mano. Una clave
     * ausente no es lo mismo que una vacía --`ajuste()` devuelve su valor por
     * defecto-- y esa diferencia es la que decide si desplegar rompe la página
     * o simplemente no enseña el bloque todavía.
     *
     * Comprobada borrando las filas, que es literalmente el estado de la base
     * de producción hoy.
     */
    public function test_la_pagina_aguanta_sin_las_claves_del_respaldo_nacional(): void
    {
        $rotulos = [ajuste('nacional_capitulos_rotulo'), ajuste('nacional_afiliados_rotulo')];

        Setting::query()->whereIn('clave', [
            'nacional_capitulos',
            'nacional_capitulos_rotulo',
            'nacional_afiliados',
            'nacional_afiliados_rotulo',
        ])->get()->each->delete();

        $respuesta = $this->get(route('quienes-somos'))->assertOk();

        // Ni el bloque, ni un rótulo suelto, ni una lista vacía.
        foreach ($rotulos as $rotulo) {
            $respuesta->assertDontSee($rotulo, escape: false);
        }

        $respuesta->assertDontSee('<dl', escape: false);

        // Y el resto de la página sigue en pie: el bloque que las alberga es el
        // del respaldo nacional, y su título no depende de las cuatro claves.
        $respuesta->assertSee(ajuste('quienes_titulo_nacional'), escape: false);
    }
}

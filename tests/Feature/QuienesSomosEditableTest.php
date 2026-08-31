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

    /** Los quince textos que dejaron de estar cableados. */
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
}

<?php

namespace Tests\Feature;

use Database\Seeders\SettingSeeder;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Toda clave que una vista lee con `ajuste()` la crea el sembrador.
 *
 * Es la guardia inversa de `AjustesQueSirvenParaAlgoTest`: aquella caza el
 * ajuste sembrado que nadie lee, esta el texto que una vista lee y nadie
 * siembra. `ajuste('clave', 'respaldo')` no falla cuando la clave no existe,
 * pinta el respaldo. Y el panel arma su formulario **desde la base**
 * (`AjustesDelSitio`, `Setting::query()`), así que un texto sin fila se ve en
 * el sitio y no se puede editar desde ningún lado: RNF-09 roto sin que nada
 * se ponga rojo.
 *
 * El caso típico es un texto de la portada editorial --un subtítulo de
 * sección, la frase o el botón del cierre, el rótulo o el pie de la pauta-- que
 * entra en la vista con su respaldo y no en el sembrador.
 *
 * No toca la base: lee las vistas del disco y el sembrador por reflexión.
 */
class AjustesSembradosTest extends TestCase
{
    /**
     * Claves que una vista lee y el sembrador no crea a propósito. Cada una
     * lleva por qué: la excepción se justifica o es un descuido con permiso.
     *
     * @var array<string, string>
     */
    private const array LEIDAS_SIN_SEMBRAR = [
        'guia_foto' => 'Hueco de foto de cabecera (HuecoDeFotoTest): se lee con respaldo null y pinta el marcador de marca mientras el gremio no entregue material autorizado. Una fila vacía le daría a la oficina un campo de texto para una ruta de imagen, sin subida ni validación.',
    ];

    public function test_toda_clave_que_lee_una_vista_esta_sembrada(): void
    {
        $sembradas = array_flip($this->clavesSembradas());
        $sinSembrar = [];

        foreach ($this->clavesLeidasPorLasVistas() as $clave => $vistas) {
            if (! isset($sembradas[$clave]) && ! isset(self::LEIDAS_SIN_SEMBRAR[$clave])) {
                $sinSembrar[] = "{$clave} (".implode(', ', $vistas).')';
            }
        }

        $this->assertSame(
            [],
            $sinSembrar,
            'Estas claves las lee una vista y nadie las siembra: '
            .implode('; ', $sinSembrar)
            .'. El sitio pinta el texto de respaldo y el panel no lo ofrece, así que la oficina '
            .'no lo puede cambiar. Siémbralas en SettingSeeder o justifica la excepción.'
        );
    }

    /**
     * Y al revés: una excepción que deje de hacer falta tiene que caerse sola,
     * o la lista de arriba se convierte en el sitio donde se esconden las
     * claves sin sembrar nuevas.
     */
    public function test_ninguna_excepcion_sobra(): void
    {
        $leidas = $this->clavesLeidasPorLasVistas();
        $sembradas = $this->clavesSembradas();

        foreach (array_keys(self::LEIDAS_SIN_SEMBRAR) as $clave) {
            $this->assertArrayHasKey($clave, $leidas, "«{$clave}» está exceptuada y ya no la lee ninguna vista: quita la excepción.");
            $this->assertNotContains($clave, $sembradas, "«{$clave}» está exceptuada y ya se siembra: quita la excepción.");
        }
    }

    /**
     * Sembrar un texto de la portada no puede cambiar la portada. Mientras la
     * fila no exista, la vista pinta su respaldo; el día que el sembrador la
     * crea, pinta el valor sembrado. Si no son la misma cadena, el resembrado
     * cambia en silencio lo que el gremio ya vio.
     *
     * Solo la portada editorial: en el resto del sitio el valor sembrado sale
     * de documento oficial del gremio y manda sobre el respaldo de la vista.
     */
    public function test_el_valor_sembrado_de_la_portada_es_su_texto_de_respaldo(): void
    {
        $sembrados = $this->valoresSembrados();
        $distintos = [];
        $comparados = 0;

        foreach (File::glob(resource_path('views/components/publico/home/*.blade.php')) ?: [] as $vista) {
            preg_match_all(
                "/\\bajuste\\(\\s*'([a-z0-9_]+)'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*\\)/",
                File::get($vista),
                $llamadas,
                PREG_SET_ORDER
            );

            foreach ($llamadas as [, $clave, $respaldo]) {
                if (! isset($sembrados[$clave])) {
                    continue;
                }

                $comparados++;
                $respaldo = str_replace(["\\'", '\\\\'], ["'", '\\'], $respaldo);

                if ($sembrados[$clave] !== $respaldo) {
                    $distintos[] = basename($vista).": {$clave} siembra «{$sembrados[$clave]}» y la vista respalda «{$respaldo}»";
                }
            }
        }

        $this->assertGreaterThan(0, $comparados, 'No se comparó ningún respaldo: el patrón ya no reconoce las llamadas de la portada.');
        $this->assertSame([], $distintos, "Resembrar cambiaría la portada:\n".implode("\n", $distintos));
    }

    /**
     * Contra el falso verde. Si el patrón deja de reconocer una forma de
     * llamar a `ajuste()`, la guardia de arriba pasa en verde sin vigilar esa
     * llamada. Las claves armadas en tiempo de ejecución no son literales y no
     * se pueden exigir: se ignoran a propósito.
     */
    public function test_el_extractor_reconoce_las_formas_de_llamar_a_ajuste(): void
    {
        $blade = <<<'BLADE'
            <h2>{{ ajuste('con_comilla_simple') }}</h2>
            <p>{{ ajuste("con_comilla_doble") }}</p>
            <x-publico.hero :titulo="ajuste( 'con_espacios' )" />
            {{ ajuste('con_respaldo', 'Texto de respaldo, con coma.') }}
            {{ ajuste('con_comilla_simple') }}
            {{ ajuste($claveVariable) }}
            {{ ajuste("armada_{$sufijo}") }}
            {{ subajuste('otra_funcion') }}
            BLADE;

        $this->assertSame(
            ['con_comilla_simple', 'con_comilla_doble', 'con_espacios', 'con_respaldo'],
            $this->clavesEn($blade)
        );
    }

    /** Y que el recorrido llega de verdad a las vistas anidadas. */
    public function test_el_recorrido_llega_a_las_vistas_anidadas(): void
    {
        $leidas = $this->clavesLeidasPorLasVistas();

        $this->assertArrayHasKey('publicidad_pie', $leidas);
        $this->assertContains('components/publico/home/publicidad.blade.php', $leidas['publicidad_pie']);
        $this->assertArrayHasKey('directorio_titulo', $leidas);
        $this->assertContains('publico/directorio/index.blade.php', $leidas['directorio_titulo']);
    }

    /**
     * Cada clave literal que leen las vistas, con las vistas que la leen.
     *
     * @return array<string, list<string>>
     */
    private function clavesLeidasPorLasVistas(): array
    {
        $leidas = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if (! str_ends_with($archivo->getFilename(), '.blade.php')) {
                continue;
            }

            $vista = str_replace('\\', '/', $archivo->getRelativePathname());

            foreach ($this->clavesEn(File::get($archivo->getPathname())) as $clave) {
                $leidas[$clave][] = $vista;
            }
        }

        $this->assertNotSame([], $leidas, 'El recorrido no encontró ninguna llamada a ajuste() en las vistas.');

        return $leidas;
    }

    /** @return list<string> */
    private function clavesEn(string $blade): array
    {
        preg_match_all('/\bajuste\(\s*([\'"])(.*?)\1/', $blade, $coincidencias);

        $literales = array_filter(
            $coincidencias[2],
            fn (string $clave): bool => $clave !== '' && ! str_contains($clave, '$') && ! str_contains($clave, '{')
        );

        return array_values(array_unique($literales));
    }

    /** @return list<string> */
    private function clavesSembradas(): array
    {
        return array_keys($this->valoresSembrados());
    }

    /** @return array<string, string> */
    private function valoresSembrados(): array
    {
        $metodo = new ReflectionMethod(SettingSeeder::class, 'ajustes');

        return array_column($metodo->invoke(new SettingSeeder), 'valor', 'clave');
    }
}

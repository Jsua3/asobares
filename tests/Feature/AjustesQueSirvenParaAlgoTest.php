<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\CifrasDelGremio;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Ningún ajuste que el panel ofrece editar puede no cambiar nada.
 *
 * RNF-09 dice que nada esté quemado en el código, y de ahí salen los 126 ajustes
 * de «Ajustes del sitio». La cara B de esa regla no la vigilaba nadie: un ajuste
 * **sembrado y que ninguna vista lee** es peor que no tenerlo, porque la oficina
 * lo cambia, guarda, ve el aviso verde y el sitio se queda igual. No hay error,
 * no hay pista, y la confianza en el panel se cae entera.
 *
 * La auditoría del 9 de septiembre de 2026 encontró tres así: `hero_subtitulo`
 * (un párrafo entero que ninguna vista pintaba), `cifra_afiliados` --por el que
 * el expediente afirmaba «el sitio dice 60» cuando el sitio no decía nada-- y
 * `contacto_correo_destino`, etiquetado «Correo que recibe los formularios»
 * cuando ningún formulario lo leía.
 *
 * Comprobada en rojo devolviendo cualquiera de los tres al sembrador.
 */
class AjustesQueSirvenParaAlgoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Claves que se arman en tiempo de ejecución y por eso no aparecen escritas
     * en ningún archivo. Cada una lleva quién la construye: la excepción se
     * justifica o no es una excepción, es un descuido con permiso.
     *
     * @var array<string, string>
     */
    private const ARMADAS_EN_CODIGO = [
        'gremio_cifra_1' => CifrasDelGremio::class.'::clave(1)',
        'gremio_cifra_1_detalle' => CifrasDelGremio::class.'::claveDetalle(1)',
        'gremio_cifra_2' => CifrasDelGremio::class.'::clave(2)',
        'gremio_cifra_2_detalle' => CifrasDelGremio::class.'::claveDetalle(2)',
        'gremio_cifra_3' => CifrasDelGremio::class.'::clave(3)',
        'gremio_cifra_3_detalle' => CifrasDelGremio::class.'::claveDetalle(3)',
        'gremio_cifra_4' => CifrasDelGremio::class.'::clave(4)',
        'gremio_cifra_4_detalle' => CifrasDelGremio::class.'::claveDetalle(4)',
    ];

    public function test_todo_ajuste_sembrado_lo_lee_alguien(): void
    {
        $codigo = $this->codigoDelProyecto();

        $huerfanos = array_values(array_filter(
            $this->clavesSembradas(),
            fn (string $clave): bool => ! isset(self::ARMADAS_EN_CODIGO[$clave])
                && ! str_contains($codigo, "'{$clave}'")
        ));

        $this->assertSame(
            [],
            $huerfanos,
            'Estos ajustes se siembran, se ofrecen en el panel y no los lee nadie: '
            .implode(', ', $huerfanos)
            .'. O se conectan a una vista, o salen del sembrador. Un ajuste que no cambia nada '
            .'es una promesa rota cada vez que alguien lo edita.'
        );
    }

    /**
     * Y al revés: una excepción que deje de hacer falta tiene que caerse sola,
     * o la lista de arriba se convierte en el sitio donde se esconden los
     * huérfanos nuevos.
     */
    public function test_ninguna_excepcion_sobra(): void
    {
        $sembradas = $this->clavesSembradas();

        foreach (array_keys(self::ARMADAS_EN_CODIGO) as $clave) {
            $this->assertContains(
                $clave,
                $sembradas,
                "«{$clave}» está exceptuada y ya no se siembra: quita la excepción."
            );
        }
    }

    /**
     * Quitar una clave del sembrador no la quita de la base, y el panel arma su
     * formulario **desde la base** (`AjustesDelSitio::form`, `Setting::query()`).
     * Sin esta limpieza, un ajuste retirado sigue apareciéndole a la oficina en
     * producción para siempre: el mismo defecto que se venía a arreglar, ahora
     * sin nadie que lo pueda encontrar leyendo el código.
     *
     * La lista es explícita y no «todo lo que no esté en el sembrador» a
     * propósito: un borrado por diferencia sobre datos reales es un modo de
     * fallo demasiado caro para ahorrarse tres líneas.
     */
    public function test_el_sembrador_retira_de_la_base_los_ajustes_jubilados(): void
    {
        Setting::create([
            'clave' => 'hero_subtitulo',
            'valor' => 'Texto viejo que el panel seguiría ofreciendo',
            'tipo' => 'largo',
            'grupo' => 'inicio',
            'etiqueta' => 'Subtítulo del hero',
        ]);

        (new SettingSeeder)->run();

        $this->assertDatabaseMissing('settings', ['clave' => 'hero_subtitulo']);
    }

    /** Y no se lleva por delante nada más. */
    public function test_la_limpieza_no_toca_los_ajustes_vivos(): void
    {
        (new SettingSeeder)->run();

        $this->assertDatabaseHas('settings', ['clave' => 'hero_resumen_corto']);
        $this->assertSame(count($this->clavesSembradas()), Setting::query()->count());
    }

    /** @return list<string> */
    private function clavesSembradas(): array
    {
        $metodo = new ReflectionMethod(SettingSeeder::class, 'ajustes');
        $metodo->setAccessible(true);

        return array_map(
            fn (array $ajuste): string => $ajuste['clave'],
            $metodo->invoke(new SettingSeeder)
        );
    }

    /** Todo el código que puede leer un ajuste, en una sola cadena. */
    private function codigoDelProyecto(): string
    {
        $texto = '';

        foreach ([app_path(), resource_path('views')] as $raiz) {
            $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($raiz));

            foreach ($archivos as $archivo) {
                if ($archivo->isFile() && in_array($archivo->getExtension(), ['php'], strict: true)) {
                    $texto .= file_get_contents($archivo->getPathname());
                }
            }
        }

        return $texto;
    }
}

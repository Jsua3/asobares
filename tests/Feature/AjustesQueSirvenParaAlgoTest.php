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
 * RNF-09 dice que nada esté quemado en el código, y de ahí salen los ajustes de
 * «Ajustes del sitio». Esta es la cara B de esa regla: un ajuste **sembrado y
 * que ninguna vista lee** es peor que no tenerlo, porque la oficina lo cambia,
 * guarda, ve el aviso verde y el sitio se queda igual. No hay error, no hay
 * pista, y la confianza en el panel se cae entera.
 *
 * Rotura: devolver al sembrador un ajuste que ninguna vista lee, como
 * `hero_subtitulo` o `cifra_afiliados`.
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
     * producción para siempre: el mismo defecto de arriba, y sin nadie que lo
     * pueda encontrar leyendo el código.
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

    /**
     * Los trece textos que el rediseño editorial de la portada y del directorio
     * dejó sin vista. Salir del sembrador no basta: sin estar en `JUBILADOS`,
     * producción los sigue ofreciendo en el panel.
     *
     * La lista va escrita aquí y no leída del sembrador a propósito: quitar una
     * clave de `JUBILADOS` tiene que ponerse rojo.
     */
    public function test_el_sembrador_retira_los_textos_que_el_rediseno_dejo_sin_vista(): void
    {
        $retirados = [
            'portada_empleo_titulo',
            'portada_empleo_texto',
            'portada_videos_intro',
            'portada_videos_cta',
            'portada_video_1_titulo',
            'portada_video_1_detalle',
            'portada_video_2_titulo',
            'portada_video_2_detalle',
            'portada_video_3_titulo',
            'portada_video_3_detalle',
            'portada_videos_proxima_rotulo',
            'portada_videos_proxima_texto',
            'directorio_intro',
        ];

        foreach ($retirados as $clave) {
            Setting::create([
                'clave' => $clave,
                'valor' => 'Texto viejo que el panel seguiría ofreciendo',
                'tipo' => 'string',
                'grupo' => 'inicio',
                'etiqueta' => $clave,
            ]);
        }

        (new SettingSeeder)->run();

        foreach ($retirados as $clave) {
            $this->assertDatabaseMissing('settings', ['clave' => $clave]);
        }
    }

    /**
     * Jubilar es solo para lo que ya nadie lee. Un jubilado que el código lea
     * se borraría de producción en el siguiente resembrado y la vista pintaría
     * su respaldo sin que la oficina lo pudiera editar; uno que se siga
     * sembrando se borra y se vuelve a crear en cada pasada.
     */
    public function test_ningun_jubilado_se_siembra_ni_lo_lee_el_codigo(): void
    {
        $codigo = $this->codigoDelProyecto();
        $sembradas = $this->clavesSembradas();
        $jubilados = (new \ReflectionClassConstant(SettingSeeder::class, 'JUBILADOS'))->getValue();

        $this->assertNotSame([], $jubilados);

        foreach ($jubilados as $clave) {
            $this->assertNotContains($clave, $sembradas, "«{$clave}» está jubilada y se sigue sembrando.");
            $this->assertStringNotContainsString("'{$clave}'", $codigo, "«{$clave}» está jubilada y el código la lee: el siguiente resembrado la borra de producción.");
        }
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

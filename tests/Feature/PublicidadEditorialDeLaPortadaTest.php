<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Models\Publicidad;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La pauta de la portada es una pieza editorial clicable, no una tarjeta
 * informativa, y no inventa destino ni reusa el hero.
 *
 * Roturas: href="#"; perder rel=sponsored; pintar el bloque sin pauta;
 * devolver la URL del hero como fallback.
 */
class PublicidadEditorialDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_la_portada_no_pinta_publicidad_si_no_hay_pauta_vigente(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('home-editorial-publicidad', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    /**
     * La prueba de arriba no siembra nada: pasaría con la portada tomando
     * cualquier pauta de Inicio. Cada caso va solo, para que quitar
     * UN filtro (estado, vigencia o ubicación) también se note.
     */
    public function test_la_portada_no_pinta_pautas_sin_publicar_vencidas_futuras_ni_de_otra_ubicacion(): void
    {
        $casos = [
            'Pauta pagada sin publicar' => ['estado' => EstadoPublicidad::Pagada],
            'Pauta pendiente de aprobacion' => ['estado' => EstadoPublicidad::PendienteAprobacion],
            'Pauta publicada vencida' => [
                'estado' => EstadoPublicidad::Publicada,
                'fecha_inicio' => now()->subWeeks(2),
                'fecha_fin' => now()->subDay(),
            ],
            'Pauta publicada futura' => [
                'estado' => EstadoPublicidad::Publicada,
                'fecha_inicio' => now()->addDay(),
                'fecha_fin' => now()->addWeek(),
            ],
            'Pauta publicada del directorio' => [
                'estado' => EstadoPublicidad::Publicada,
                'ubicacion' => UbicacionPublicidad::Directorio,
            ],
        ];

        foreach ($casos as $nombre => $atributos) {
            $pauta = $this->sembrarPauta(['nombre_comercial' => $nombre, ...$atributos]);

            $html = $this->get('/')->assertOk()->getContent();

            $this->assertStringNotContainsString('home-editorial-publicidad', $html, "La portada pintó la «{$nombre}».");
            $this->assertStringNotContainsString($nombre, $html);

            $pauta->delete();
        }

        // Control: la misma siembra, publicada y vigente en Inicio, sí sale.
        // Sin esto las aserciones de arriba podrían pasar por un nombre de
        // clase que ya no existe.
        $this->sembrarPauta(['nombre_comercial' => 'Pauta publicada vigente', 'estado' => EstadoPublicidad::Publicada]);

        $this->assertStringContainsString(
            'Pauta publicada vigente',
            $this->seccionDePublicidad($this->get('/')->assertOk()->getContent())
        );
    }

    public function test_la_portada_conserva_url_real_target_y_rel_de_la_pauta(): void
    {
        $pauta = $this->crearPautaDeInicio([
            'nombre_comercial' => 'Campaña editorial de prueba',
            'url_destino' => 'https://example.com/campana-real',
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDePublicidad($html);

        $this->assertStringContainsString('Campaña editorial de prueba', $seccion);
        $this->assertStringContainsString('href="https://example.com/campana-real"', $seccion);
        $this->assertStringContainsString('target="_blank"', $seccion);
        $this->assertStringContainsString('rel="noopener noreferrer sponsored"', $seccion);
        $this->assertStringContainsString('aria-label=', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringNotContainsString('videos/asobares-institucional', $seccion);
        $this->assertSame($pauta->url_destino, 'https://example.com/campana-real');
        $this->assertSeIdentificaComoPatrocinada($html, 'Campaña editorial de prueba');
    }

    public function test_sin_url_la_pieza_no_inventa_enlace(): void
    {
        $this->crearPautaDeInicio([
            'nombre_comercial' => 'Pauta sin destino',
            'url_destino' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDePublicidad($html);

        $this->assertStringContainsString('Pauta sin destino', $seccion);
        $this->assertStringNotContainsString('<a ', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringNotContainsString('Conocer más', $seccion);
        $this->assertSeIdentificaComoPatrocinada($html, 'Pauta sin destino');
    }

    /**
     * La pauta tiene que decir que es publicidad, a la vista y en el nombre
     * accesible de la sección y de la pieza. Pedir solo `aria-label=` no
     * basta: lo cumple hasta un atributo vacío.
     *
     * Todo se mide sobre el árbol servido. Recortar por `<section class="…"`
     * exige que `class` sea el primer atributo, y un lookahead de aria-label
     * es posicional: exige además que el rótulo venga detrás. Reordenar los
     * atributos, o que entre un `x-data` delante, pone la guardia roja con el
     * nombre accesible intacto, y aflojar el patrón no lo arregla porque el
     * lookahead sigue mirando solo hacia delante.
     */
    private function assertSeIdentificaComoPatrocinada(string $html, string $nombre): void
    {
        $rotulo = 'Contenido patrocinado';
        $xpath = $this->xpathDe($html);
        $seccion = $this->nodoDePublicidad($xpath);

        $this->assertSame(
            $rotulo,
            $seccion->getAttribute('aria-label'),
            'La sección de publicidad perdió su rótulo accesible.'
        );

        $rotulos = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " home-editorial-publicidad__rotulo ")]',
            $seccion
        );

        $this->assertSame(1, $rotulos->length, 'La pieza de publicidad perdió el rótulo visible.');
        $this->assertSame(
            $rotulo,
            trim($rotulos->item(0)->textContent),
            'La pieza de publicidad perdió el rótulo visible.'
        );

        // Exactamente una pieza: la franja no puede montarse dos veces, y una
        // guardia que solo busca la cadena se quedaría con la primera y callaría.
        $piezas = $xpath->query(
            './/*[contains(concat(" ", normalize-space(@class), " "), " home-editorial-publicidad__pieza ")]',
            $seccion
        );

        $this->assertSame(1, $piezas->length, 'La portada no pintó exactamente una pieza de publicidad.');

        // El valor llega desescapado, que es lo que oye un lector de pantalla:
        // así un cambio de escapado de Blade no la pone roja por nada.
        $this->assertSame(
            $rotulo.': '.$nombre,
            $piezas->item(0)->getAttribute('aria-label'),
            'El nombre accesible de la pieza tiene que empezar por el rótulo de patrocinio.'
        );
    }

    /**
     * Siembra directa, sin el hook `saving`: el modelo rechaza publicar
     * fuera de la dirección, y aquí interesa la portada, no el flujo.
     *
     * @param  array<string, mixed>  $atributos
     */
    private function sembrarPauta(array $atributos): Publicidad
    {
        return Publicidad::withoutEvents(fn (): Publicidad => Publicidad::factory()->create(array_merge([
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
        ], $atributos)));
    }

    public function test_la_vista_prioriza_foto_real_y_cae_al_banco_no_al_hero(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/publicidad.blade.php'));
        $inicio = File::get(resource_path('views/publico/inicio.blade.php'));

        $this->assertStringContainsString('@if ($publicidadInicio)', $inicio);
        $this->assertStringContainsString("config('home_banco.publicidad')", $vista);
        $this->assertStringNotContainsString('videos/asobares-institucional', $vista);
        $this->assertStringContainsString('noopener noreferrer sponsored', $vista);
        $this->assertStringContainsString('home-editorial-publicidad__fallback', $vista);
    }

    public function test_el_css_declara_hover_premium_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDePublicidad($css);

        $this->assertStringContainsString('.home-editorial-publicidad__pieza', $bloque);
        $this->assertStringContainsString('translateY(-4px)', $bloque);
        $this->assertStringContainsString('scale(1.012)', $bloque);
        $this->assertStringContainsString('scale(1.03)', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertMatchesRegularExpression(
            '/\.home-editorial-publicidad__cta\s*\{[^}]*background:[^}]*box-shadow:/s',
            $bloque,
            'El CTA de publicidad perdió el velo local que lo hace legible sobre foto variable.'
        );
        $this->assertStringContainsString(
            '.home-editorial-publicidad a.home-editorial-publicidad__pieza:focus-visible',
            $bloque
        );
        $this->assertStringContainsString(
            '.home-editorial-publicidad__cta',
            substr($css, (int) strpos($css, '@media (prefers-reduced-transparency: reduce)'))
        );
    }

    /**
     * @param  array<string, mixed>  $sobrescribir
     */
    private function crearPautaDeInicio(array $sobrescribir = []): Publicidad
    {
        $this->actingAs(tap(User::factory()->create(), function (User $usuario): void {
            $usuario->syncRoles([User::ROL_SUPER_ADMIN]);
        })->fresh());

        $pauta = Publicidad::factory()->create(array_merge([
            'estado' => EstadoPublicidad::Pagada,
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
        ], $sobrescribir));

        $pauta->update(['estado' => EstadoPublicidad::Publicada]);

        return $pauta->fresh();
    }

    private function bloqueDePublicidad(string $css): string
    {
        $inicio = strpos($css, '/* —— Publicidad');
        $fin = strpos($css, '/* —— Aliados');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
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
     * La franja de publicidad como nodo del árbol servido, y una sola vez.
     *
     * Se busca por token de clase: el recorte por `<section class="…"` exige
     * que `class` sea el primer atributo y corta en el primer `</section>`,
     * así que un atributo delante o una sección anidada dejan mudas las
     * aserciones que cuelgan de aquí sin que falte nada.
     */
    private function nodoDePublicidad(\DOMXPath $xpath): \DOMElement
    {
        $secciones = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " home-editorial-publicidad ")]');

        $this->assertSame(1, $secciones->length, 'La portada no pintó la franja editorial de publicidad.');

        return $secciones->item(0);
    }

    /** El interior de la franja, serializado desde el árbol ya localizado. */
    private function seccionDePublicidad(string $html): string
    {
        $seccion = $this->nodoDePublicidad($this->xpathDe($html));
        $interior = '';

        foreach ($seccion->childNodes as $hijo) {
            $interior .= $seccion->ownerDocument->saveHTML($hijo);
        }

        return $interior;
    }
}

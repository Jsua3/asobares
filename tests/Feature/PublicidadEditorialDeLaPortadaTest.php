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
 * HOME-FINAL-06: la pauta de la portada es una pieza editorial clicable,
 * no una tarjeta informativa, y no inventa destino ni reusa el hero.
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
     * La prueba de arriba no siembra nada: pasaba con la portada tomando
     * cualquier pauta de Inicio (MUT-04). Cada caso va solo, para que quitar
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
     * accesible de la sección y de la pieza (MUT-03). Antes solo se pedía
     * `aria-label=`, que cumple hasta un atributo vacío.
     */
    private function assertSeIdentificaComoPatrocinada(string $html, string $nombre): void
    {
        $rotulo = 'Contenido patrocinado';
        $seccion = $this->seccionDePublicidad($html);

        $this->assertMatchesRegularExpression(
            '/<section class="home-editorial-publicidad[^"]*"(?=[^>]*\baria-label="'.preg_quote($rotulo, '/').'")[^>]*>/',
            $html,
            'La sección de publicidad perdió su rótulo accesible.'
        );
        $this->assertStringContainsString(
            '<p class="home-editorial-publicidad__rotulo">'.e($rotulo).'</p>',
            $seccion,
            'La pieza de publicidad perdió el rótulo visible.'
        );
        $this->assertMatchesRegularExpression(
            '/class="home-editorial-publicidad__pieza[^"]*"(?:[^>]*)\baria-label="'.preg_quote(e($rotulo.': '.$nombre), '/').'"/',
            $seccion,
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

    private function seccionDePublicidad(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-publicidad[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja editorial de publicidad.'
        );

        return $seccion[1];
    }

    private function bloqueDePublicidad(string $css): string
    {
        $inicio = strpos($css, '/* —— Publicidad');
        $fin = strpos($css, '/* —— Aliados');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}

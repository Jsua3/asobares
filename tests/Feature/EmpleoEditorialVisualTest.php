<?php

namespace Tests\Feature;

use App\Models\Vacante;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Capa visual de /empleo: hoja propia, hero con foto local, cartelera de vacantes.
 */
class EmpleoEditorialVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_empleo_carga_la_capa_editorial_con_vacantes_reales(): void
    {
        $vacante = Vacante::factory()->publicado()->create([
            'cargo' => 'Bartender de prueba editorial',
            'descripcion' => 'Turnos de fin de semana en barra de alto volumen.',
        ]);

        $html = $this->get(route('empleo.index'))->assertOk()->getContent();

        $this->assertStringContainsString('empleo-editorial', $html);
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/empleo-editorial.css').'"',
            $html,
            'Empleo no enlaza su hoja editorial'
        );
        $this->assertStringContainsString('empleo-editorial-hero', $html);
        $this->assertStringContainsString('data-empleo-hero-slot="img/empleo/hero-empleo.webp"', $html);
        $this->assertStringContainsString('empleo-editorial-cartelera', $html);
        $this->assertStringContainsString($vacante->cargo, $html);
        $this->assertStringContainsString($vacante->asociado->nombre, $html);
        $this->assertStringContainsString($vacante->asociado->municipio->nombre, $html);
        $this->assertStringContainsString('Ver y postularme', $html);
        $this->assertStringContainsString('Abierta', $html);
        $this->assertStringNotContainsString('geometria', $html);
        $this->assertStringNotContainsString('hueco-foto', $html);
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/eventos-editorial.css'),
            $html
        );
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/directorio-editorial.css'),
            $html
        );
        $this->assertSame(1, substr_count($html, $vacante->cargo));
    }

    public function test_el_hero_obedece_titulo_e_intro_administrables(): void
    {
        $this->seed(SettingSeeder::class);

        $vista = File::get(resource_path('views/publico/empleo/index.blade.php'));
        $this->assertStringContainsString("ajuste('empleo_titulo')", $vista);
        $this->assertStringContainsString("ajuste('empleo_intro')", $vista);

        $this->get(route('empleo.index'))
            ->assertOk()
            ->assertSee(ajuste('empleo_titulo'))
            ->assertSee(ajuste('empleo_intro'));
    }

    public function test_la_ficha_carga_la_misma_hoja_y_conserva_la_postulacion(): void
    {
        $vacante = Vacante::factory()->publicado()->create([
            'cargo' => 'Chef de prueba editorial',
        ]);

        $html = $this->get(route('empleo.show', $vacante))->assertOk()->getContent();

        $this->assertStringContainsString('empleo-editorial', $html);
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/empleo-editorial.css').'"',
            $html
        );
        $this->assertStringContainsString('Chef de prueba editorial', $html);
        $this->assertStringContainsString('id="postularme"', $html);
        $this->assertStringContainsString('Enviar mi postulación', $html);
        $this->assertStringContainsString('JobPosting', $html);
        $this->assertStringContainsString(route('empleo.postular', $vacante), $html);
    }

    public function test_el_css_y_vite_declaran_la_hoja_de_empleo(): void
    {
        $this->assertFileExists(resource_path('css/empleo-editorial.css'));
        $this->assertFileExists(public_path('img/empleo/hero-empleo.webp'));

        $css = File::get(resource_path('css/empleo-editorial.css'));
        $vite = File::get(base_path('vite.config.js'));

        $this->assertStringContainsString('.empleo-editorial-hero', $css);
        $this->assertStringContainsString('.empleo-editorial-hero__foto', $css);
        $this->assertStringContainsString('public/img/empleo/hero-empleo.webp', $css);
        $this->assertStringNotContainsString('geometria', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('margin-inline-end: 4.5rem', $css);
        $this->assertStringContainsString('padding-right: 4.5rem', $css);
        $this->assertStringContainsString('padding: var(--emp-tope) 1rem 3.5rem', $css);
        $this->assertStringContainsString('.empleo-editorial-chip--area', $css);
        $this->assertStringContainsString('inset: 0 0 0 34%', $css);
        $this->assertStringNotContainsString('scroll-snap-type: x mandatory', $css);
    }
}

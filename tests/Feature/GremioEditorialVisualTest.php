<?php

namespace Tests\Feature;

use App\Enums\CategoriaNoticia;
use App\Enums\TipoMensaje;
use App\Models\Noticia;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Capa visual de El Gremio: manifiesto, revista y conversación.
 */
class GremioEditorialVisualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_quienes_somos_abre_como_manifiesto_con_folio_01(): void
    {
        $html = $this->get(route('quienes-somos'))->assertOk()->getContent();

        $this->assertStringContainsString('gremio-editorial--manifiesto', $html);
        $this->assertStringContainsString('href="'.Vite::asset('resources/css/gremio-editorial.css').'"', $html);
        $this->assertStringContainsString('EL GREMIO / 01', $html);
        $this->assertStringContainsString('ASOBARES QUINDÍO', $html);
        $this->assertStringContainsString(ajuste('manifiesto_apertura'), $html);
        $this->assertStringContainsString(ajuste('quienes_mision'), $html);
        $this->assertStringContainsString(ajuste('quienes_historia'), $html);
        $this->assertStringContainsString(ajuste('quienes_presidente'), $html);
        $this->assertStringContainsString('gremio-editorial-banda--profunda', $html);
        $this->assertStringContainsString('gremio-editorial-seccion__n', $html);
        $css = File::get(resource_path('css/gremio-editorial.css'));
        $this->assertStringContainsString('3.375rem', $css);
        $this->assertStringNotContainsString('hueco-foto', $html);
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/empleo-editorial.css'),
            $html
        );
    }

    public function test_el_boletin_es_revista_con_folio_02_y_conserva_filtros(): void
    {
        $protagonista = Noticia::factory()->visible()->create([
            'titulo' => 'Noticia protagonista de la revista',
            'categoria' => CategoriaNoticia::Proyecto,
        ]);
        Noticia::factory()->visible()->create([
            'titulo' => 'Pieza secundaria de la revista',
            'publicado_at' => now()->subDays(2),
        ]);

        $html = $this->get(route('boletin.index'))->assertOk()->getContent();

        $this->assertStringContainsString('gremio-editorial--revista', $html);
        $this->assertStringContainsString('EL GREMIO / 02', $html);
        $this->assertStringContainsString(ajuste('boletin_titulo'), $html);
        $this->assertStringContainsString(ajuste('boletin_intro'), $html);
        $this->assertStringContainsString('gremio-editorial-lead', $html);
        $this->assertStringContainsString($protagonista->titulo, $html);
        $this->assertStringContainsString('Todas', $html);
        $this->assertStringContainsString('Observatorio económico', $html);
        $this->assertStringContainsString('inline-flex min-h-11 items-center rounded-xl border px-4 text-sm', $html);
        $this->assertStringContainsString('view-transition-name: filtro-activo', $html);
        $this->assertStringNotContainsString('lg:grid-cols-3', $html);
        $this->assertStringNotContainsString('imagen-inclinable', $html);

        // El rótulo «Observatorio económico» está en la página con o sin filtro
        // (es uno de los botones), así que no prueba nada: se mira qué piezas
        // quedan y qué botón se marca.
        Noticia::factory()->visible()->create([
            'titulo' => 'Cifras del observatorio para la revista',
            'categoria' => CategoriaNoticia::Observatorio,
        ]);

        $filtrada = $this->get(route('boletin.index', ['categoria' => CategoriaNoticia::Observatorio->value]))
            ->assertOk()
            ->assertSee('Cifras del observatorio para la revista')
            ->assertDontSee($protagonista->titulo)
            ->assertDontSee('Pieza secundaria de la revista')
            ->getContent();

        $activo = $this->xpathDe($filtrada)->query('//a[@aria-current="page"][contains(@href, "categoria='.CategoriaNoticia::Observatorio->value.'")]');
        $this->assertSame(1, $activo->length, 'El filtro no marca su propio botón como activo.');
        $this->assertStringContainsString('view-transition-name: filtro-activo', $activo->item(0)->getAttribute('style'));
    }

    public function test_la_ficha_de_noticia_es_lectura_sin_tilt_y_con_retorno_desplazado(): void
    {
        $noticia = Noticia::factory()->visible()->create([
            'titulo' => 'Guía normativa en ficha editorial',
        ]);

        $html = $this->get(route('boletin.show', $noticia))->assertOk()->getContent();

        $this->assertStringContainsString('gremio-editorial-lectura', $html);
        $this->assertStringContainsString('EL GREMIO / 02', $html);
        $this->assertStringContainsString('Volver al boletín', $html);
        $this->assertStringContainsString('--grem-tope', File::get(resource_path('css/gremio-editorial.css')));
        $this->assertStringContainsString($noticia->titulo, $html);
        $this->assertStringContainsString('NewsArticle', $html);
        $vista = File::get(resource_path('views/publico/boletin/show.blade.php'));
        $this->assertStringNotContainsString('imagen-inclinable', $vista);
        $this->assertStringNotContainsString('tarjeta-escena', $vista);
        $this->assertStringNotContainsString('x-data="escena"', $vista);
    }

    public function test_contacto_es_conversacion_con_folio_03_y_formulario_intacto(): void
    {
        $html = $this->get(route('contacto'))->assertOk()->getContent();

        $this->assertStringContainsString('gremio-editorial--conversacion', $html);
        $this->assertStringContainsString('EL GREMIO / 03', $html);
        $this->assertStringContainsString(ajuste('contacto_titulo_pagina'), $html);
        $this->assertStringContainsString(TipoMensaje::Contacto->getLabel(), $html);
        $this->assertStringContainsString(TipoMensaje::Pqr->getLabel(), $html);
        $this->assertStringContainsString(TipoMensaje::Aliado->getLabel(), $html);
        $this->assertStringContainsString(TipoMensaje::Proveedor->getLabel(), $html);
        $this->assertStringNotContainsString('Prensa', File::get(resource_path('views/publico/contacto.blade.php')));
        $this->assertStringContainsString('id="formulario"', $html);
        $this->assertStringContainsString('name="tipo"', $html);
        $this->assertStringContainsString('name="nombre"', $html);
        $this->assertStringContainsString('name="correo"', $html);
        $this->assertStringContainsString('name="telefono"', $html);
        $this->assertStringContainsString('name="mensaje"', $html);
        $this->assertStringContainsString('route(\'contacto.store\')', File::get(resource_path('views/publico/contacto.blade.php')));
        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('name="acepta_datos"', $html);
        $this->assertStringContainsString('gremio-editorial-mapa', $html);
        $this->assertStringContainsString('gremio-editorial-canal__n', $html);
        $this->assertStringContainsString('padding-right: 4.5rem', File::get(resource_path('css/gremio-editorial.css')));
    }

    public function test_vite_declara_la_hoja_del_gremio(): void
    {
        $this->assertFileExists(resource_path('css/gremio-editorial.css'));
        $this->assertStringContainsString('resources/css/gremio-editorial.css', File::get(base_path('vite.config.js')));
        $css = File::get(resource_path('css/gremio-editorial.css'));
        $this->assertStringContainsString('clamp(', $css);
        $this->assertStringContainsString('--grem-elevada', $css);
        $this->assertStringContainsString('--grem-profunda', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringNotContainsString('scroll-snap-type: x mandatory', $css);
        $this->assertStringNotContainsString('hueco-foto', $css);
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }
}

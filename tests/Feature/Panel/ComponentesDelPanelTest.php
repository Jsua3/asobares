<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Los cuatro componentes del panel son la capa que levanta las cuatro
 * pantallas estrella. Se prueban por render, no por captura de pantalla.
 */
class ComponentesDelPanelTest extends TestCase
{
    public function test_el_vidrio_rinde_con_su_clase_y_pasa_atributos(): void
    {
        $html = Blade::render('<x-panel.vidrio class="p-4" data-prueba="si">contenido</x-panel.vidrio>');

        $this->assertStringContainsString('vidrio', $html);
        $this->assertStringContainsString('p-4', $html);
        $this->assertStringContainsString('data-prueba="si"', $html);
        $this->assertStringContainsString('contenido', $html);
    }

    public function test_el_vidrio_solo_anade_resplandor_y_hover_si_se_piden(): void
    {
        $simple = Blade::render('<x-panel.vidrio>x</x-panel.vidrio>');
        $this->assertStringNotContainsString('resplandor-panel', $simple);
        $this->assertStringNotContainsString('vidrio-hover', $simple);

        $adornado = Blade::render('<x-panel.vidrio resplandor hover>x</x-panel.vidrio>');
        $this->assertStringContainsString('resplandor-panel', $adornado);
        $this->assertStringContainsString('vidrio-hover', $adornado);
    }

    /**
     * El vidrio con la receta de oscuro sobre fondo claro se ve lavado: por eso
     * hay dos juegos de valores y no un `opacity` compartido.
     */
    public function test_el_vidrio_tiene_receta_distinta_en_cada_tema(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        // Claro: velo casi opaco + sombra que despega la tarjeta.
        $this->assertStringContainsString('--asb-vidrio-fondo: rgb(255 255 255 / 0.7)', $tema);
        $this->assertStringContainsString('--asb-vidrio-sombra:', $tema);

        // Oscuro: velo tenue y sin sombra, el contraste lo da la superficie.
        $this->assertStringContainsString('--asb-vidrio-fondo: rgb(255 255 255 / 0.05)', $tema);
        $this->assertStringContainsString('--asb-vidrio-sombra: none', $tema);
    }

    public function test_el_movimiento_respeta_prefers_reduced_motion(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $tema);
    }
}

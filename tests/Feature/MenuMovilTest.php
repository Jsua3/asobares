<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El panel móvil vivía EN FLUJO dentro de un `<header sticky>` y se abría con
 * `x-collapse`, o sea interpolando `height` durante 250 ms: cada fotograma
 * reflowaba el documento y empujaba `<main>`.
 *
 * Sacarlo del flujo lo convierte en una capa, y una capa necesita tres
 * salidas que el acordeón no necesitaba. Sin ellas este cambio EMPEORA la
 * accesibilidad en vez de mejorarla, así que se vigilan aquí.
 */
class MenuMovilTest extends TestCase
{
    public function test_el_panel_movil_se_anima_sin_reflowear_el_documento(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        // Fuera la interpolación de altura.
        $this->assertStringNotContainsString('x-collapse', $navbar);

        // Superposición sobre el header, que ya es sticky y establece bloque
        // contenedor. Opaco: el header es translúcido y se vería a través.
        $this->assertStringContainsString('absolute inset-x-0 top-full', $navbar);
        $this->assertStringContainsString('bg-fondo', $navbar);
        $this->assertStringContainsString('overflow-y-auto', $navbar);

        // Solo opacity y transform, con la curva de cajón y salida más rápida.
        $this->assertStringContainsString('duration-(--duracion-panel)', $navbar);
        $this->assertStringContainsString('duration-(--duracion-salida)', $navbar);
        $this->assertStringContainsString('ease-cajon', $navbar);
    }

    public function test_la_capa_tiene_las_tres_salidas_que_un_acordeon_no_necesitaba(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString('x-on:keydown.escape.window="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:click.outside="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:resize.window', $navbar);
    }
}

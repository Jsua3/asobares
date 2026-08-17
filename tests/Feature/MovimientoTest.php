<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El frontend llegó a tener 160 `hover:` sin puerta táctil, cero `:active` en
 * todo el repositorio y una sola curva escrita a mano que además era la
 * prohibida. Nada de eso fue descuido: fue que ningún sitio decía cómo se
 * escribe el movimiento. Esta guardia lo dice, y falla cuando se improvisa.
 */
class MovimientoTest extends TestCase
{
    public function test_los_tokens_de_movimiento_existen(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        // Curvas: van en @theme para que Tailwind genere las utilidades y para
        // que redefinir --ease-out pise la nativa en todo el proyecto.
        $this->assertStringContainsString('--ease-out: cubic-bezier(0.23, 1, 0.32, 1)', $tokens);
        $this->assertStringContainsString('--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)', $tokens);
        $this->assertStringContainsString('--ease-cajon: cubic-bezier(0.32, 0.72, 0, 1)', $tokens);
        $this->assertStringContainsString('--ease-color: ease', $tokens);

        // Duraciones: la escala codifica que la salida es más rápida que la
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);

        // Desplazamientos: son tokens y no literales porque el interruptor de
        // `prefers-reduced-motion` los anula sin tocar las duraciones.
        $this->assertStringContainsString('--asb-levante: -2px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: -4%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: -25%', $tokens);
    }

    /**
     * La regla no dice «quitar toda animación»: dice quitar el movimiento y
     * conservar los fundidos de opacidad y color, que ayudan a comprender.
     * Por eso se anulan los desplazamientos y NO las duraciones — si se
     * pusieran las duraciones a cero moriría también el fundido del borde.
     */
    public function test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tokens);
        $this->assertStringContainsString('--asb-levante: 0px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: 0%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: 0%', $tokens);

        // Las duraciones NO se anulan: si alguien las pone a cero aquí, está
        // deshaciendo la precisión de arriba.
        $this->assertStringNotContainsString('--duracion-boton: 0ms', $tokens);
        $this->assertStringNotContainsString('--duracion-entrada: 0ms', $tokens);
    }

    /**
     * Los tokens solo cubren lo que los usa. Filament y Leaflet traen su propio
     * movimiento, y el scroll suave global lo dispara el enlace «Saltar al
     * contenido», que es navegación de teclado.
     */
    public function test_hay_red_de_seguridad_para_lo_que_no_usa_los_tokens(): void
    {
        $app = File::get(resource_path('css/app.css'));

        // Barrido estrecho: solo `animation` y `scroll-behavior`. NO toca
        // `transition`, para no deshacer la precisión del interruptor.
        $this->assertStringContainsString('animation-duration: 1ms !important', $app);
        $this->assertStringContainsString('animation-iteration-count: 1 !important', $app);
        $this->assertStringContainsString('scroll-behavior: auto !important', $app);
        $this->assertStringNotContainsString('transition-duration: 0', $app);

        // El scroll suave deja de ser incondicional.
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $app);
    }
}

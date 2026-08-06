<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\Feature\TemaClaroOscuroTest;
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

    public function test_el_kpi_muestra_etiqueta_valor_y_detalle(): void
    {
        $html = Blade::render(
            '<x-panel.kpi etiqueta="Recaudado este mes" valor="$1.250.000" detalle="14 transacciones" />'
        );

        $this->assertStringContainsString('Recaudado este mes', $html);
        $this->assertStringContainsString('$1.250.000', $html);
        $this->assertStringContainsString('14 transacciones', $html);
    }

    /** Un KPI que no lleva a ningún lado es un número muerto. */
    public function test_el_kpi_con_url_es_un_enlace_y_sin_url_no(): void
    {
        $conEnlace = Blade::render(
            '<x-panel.kpi etiqueta="Mensajes" valor="7" url="/admin/mensajes" />'
        );
        $this->assertStringContainsString('href="/admin/mensajes"', $conEnlace);
        $this->assertStringContainsString('Ver detalle', $conEnlace);

        $sinEnlace = Blade::render('<x-panel.kpi etiqueta="Mensajes" valor="7" />');
        $this->assertStringNotContainsString('<a ', $sinEnlace);
    }

    /**
     * ⚠️ No se puede afirmar sobre el nombre del icono: `x-filament::icon`
     * renderiza un SVG crudo (`<svg class="fi-icon fi-size-md …">`) donde el
     * nombre `heroicon-o-arrow-trending-up` no aparece por ningún lado.
     *
     * Se afirma sobre lo que el usuario percibe de verdad: el signo en el
     * texto —que es lo que hace legible la dirección sin distinguir colores,
     * WCAG 1.4.1— y el token de color que la refuerza.
     */
    public function test_el_kpi_marca_la_direccion_del_delta(): void
    {
        $sube = Blade::render('<x-panel.kpi etiqueta="Recaudo" valor="10" :delta="12.5" />');
        $this->assertStringContainsString('+12,5', $sube);
        $this->assertStringContainsString('text-exito', $sube);

        $baja = Blade::render('<x-panel.kpi etiqueta="Recaudo" valor="10" :delta="-8.0" />');
        $this->assertStringContainsString('−8,0', $baja);
        $this->assertStringContainsString('text-acento', $baja);
    }

    /**
     * Ninguna cifra se presenta sin su n. Un gremio que lleva porcentajes sin
     * tamaño de muestra a una alcaldía pierde credibilidad una sola vez.
     */
    public function test_el_kpi_rotula_el_tamano_de_muestra_cuando_se_le_da(): void
    {
        $html = Blade::render('<x-panel.kpi etiqueta="Mora" valor="18 %" :n="11" />');

        $this->assertStringContainsString('n = 11', $html);
        $this->assertStringContainsString('muestra pequeña', $html);
    }

    public function test_el_kpi_no_usa_colores_cableados(): void
    {
        $fuente = File::get(resource_path('views/components/panel/kpi.blade.php'));

        foreach (TemaClaroOscuroTest::clasesProhibidas() as $patron => $motivo) {
            $this->assertSame(
                0,
                preg_match($patron, $fuente),
                "El KPI tiene una clase de tema cableada: {$motivo}"
            );
        }
    }

    public function test_la_cola_muestra_etiqueta_antiguedad_y_accion(): void
    {
        $html = Blade::render(
            '<x-panel.cola etiqueta="3 vacantes por aprobar" url="/admin/vacantes" antiguedad="la más antigua, hace 4 días" />'
        );

        $this->assertStringContainsString('3 vacantes por aprobar', $html);
        $this->assertStringContainsString('la más antigua, hace 4 días', $html);
        $this->assertStringContainsString('href="/admin/vacantes"', $html);
        $this->assertStringContainsString('Revisar', $html);
    }

    public function test_la_cola_permite_renombrar_la_accion(): void
    {
        $html = Blade::render(
            '<x-panel.cola etiqueta="7 mensajes" url="/admin/mensajes" accion="Abrir bandeja" />'
        );

        $this->assertStringContainsString('Abrir bandeja', $html);
        $this->assertStringNotContainsString('Revisar', $html);
    }

    /** Lo urgente no se distingue solo por color (WCAG 1.4.1). */
    public function test_la_cola_marca_lo_urgente_con_algo_mas_que_color(): void
    {
        $html = Blade::render(
            '<x-panel.cola etiqueta="2 PQR vencidos" url="/admin/mensajes" urgente />'
        );

        $this->assertStringContainsString('text-aviso', $html);
        $this->assertStringContainsString('Urgente', $html);
    }

    public function test_la_cola_no_usa_colores_cableados(): void
    {
        $fuente = File::get(resource_path('views/components/panel/cola.blade.php'));

        foreach (TemaClaroOscuroTest::clasesProhibidas() as $patron => $motivo) {
            $this->assertSame(0, preg_match($patron, $fuente), "La cola tiene una clase cableada: {$motivo}");
        }
    }
}

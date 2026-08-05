<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El panel dejó de usar el tema de fábrica: comparte los tokens del sitio,
 * así que un cambio de paleta los mueve a los dos a la vez.
 */
class TemaDelPanelTest extends TestCase
{
    public function test_el_panel_registra_su_tema_compilado(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(
            'resources/css/filament/admin/theme.css',
            $panel->getViteTheme(),
            'El panel debe compilar su propio tema, no usar el de fábrica.'
        );
    }

    public function test_el_tema_del_panel_importa_los_tokens_compartidos(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString(
            "@import '../../tokens.css'",
            $tema,
            'Sin los tokens compartidos el panel repetiría los colores a mano.'
        );
    }

    public function test_los_tokens_viven_en_un_solo_archivo(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $sitio = File::get(resource_path('css/app.css'));

        // La definición está en tokens.css...
        $this->assertStringContainsString('--asb-fondo: #f5f3f4', $tokens);
        $this->assertStringContainsString('--asb-acento: #b71f18', $tokens);
        $this->assertStringContainsString('--asb-acento: #f27166', $tokens);

        // ...y el sitio la importa en vez de repetirla.
        $this->assertStringContainsString("@import './tokens.css'", $sitio);
        $this->assertStringNotContainsString('--asb-fondo:', $sitio);
    }

    public function test_el_panel_usa_poppins_y_no_la_tipografia_de_fabrica(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString("--font-family: 'Poppins'", $tema);
    }
}

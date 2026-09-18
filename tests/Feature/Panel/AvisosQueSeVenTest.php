<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Panel;
use Tests\TestCase;

/**
 * Una notificación persistente necesita una campana visible en el panel.
 * La banda de pendientes del tablero permanece independiente de esta campana.
 */
class AvisosQueSeVenTest extends TestCase
{
    /** Archivos que envían notificaciones persistentes mediante Filament. */
    private function quienesEscribenNotificaciones(): array
    {
        $escritores = [];

        $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($archivos as $archivo) {
            if (! $archivo->isFile() || $archivo->getExtension() !== 'php') {
                continue;
            }

            $contenido = (string) file_get_contents($archivo->getPathname());

            if (str_contains($contenido, '->sendToDatabase(') || str_contains($contenido, '->toDatabase(')) {
                $escritores[] = str_replace('\\', '/', str_replace(app_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname()));
            }
        }

        return $escritores;
    }

    public function test_los_avisos_persistentes_tienen_campana_visible_en_el_topbar(): void
    {
        $escritores = $this->quienesEscribenNotificaciones();
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertContains('Support/AvisoDeEventoComunitario.php', $escritores);
        $this->assertTrue($panel->hasDatabaseNotifications(), 'Hay avisos persistentes pero la campana está apagada.');
        $this->assertTrue($panel->hasTopbar());
        $this->assertSame(DatabaseNotificationsPosition::Topbar, $panel->getDatabaseNotificationsPosition());
    }
}

<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Tests\TestCase;

/**
 * Un aviso que nadie puede ver no es un aviso.
 *
 * El panel no activa `databaseNotifications()`: lo pendiente lo cuentan la banda
 * «Te está esperando» del tablero y los contadores del menú. Una notificación de
 * base escrita sin campana es trabajo en cada guardado y filas que ninguna
 * pantalla lee, y una prueba que afirme sobre ellas queda en verde sobre algo
 * que el usuario no ve.
 *
 * Esta clase es la guarda de la pareja. Las dos mitades tienen que moverse
 * juntas: o hay campana y hay quien escriba en ella, o no hay ninguna de las dos.
 */
class AvisosQueSeVenTest extends TestCase
{
    /** Los archivos que escriben notificaciones de base. */
    private function quienesEscribenNotificaciones(): array
    {
        $escritores = [];

        $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($archivos as $archivo) {
            if (! $archivo->isFile() || $archivo->getExtension() !== 'php') {
                continue;
            }

            // `->sendToDatabase(` y no `sendToDatabase` a secas: una explicación
            // en un comentario --como la que lleva `AvisoDeMensajeAlGremio` para
            // contar por qué NO la usa-- no escribe ninguna fila.
            if (str_contains((string) file_get_contents($archivo->getPathname()), '->sendToDatabase(')) {
                $escritores[] = str_replace(app_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());
            }
        }

        return $escritores;
    }

    private function hayCampana(): bool
    {
        return (new AdminPanelProvider($this->app))
            ->panel(Panel::make())
            ->hasDatabaseNotifications();
    }

    public function test_nadie_escribe_notificaciones_si_no_hay_campana_que_las_enseñe(): void
    {
        if ($this->hayCampana()) {
            $this->assertTrue(true, 'Hay campana: escribir notificaciones tiene sentido.');

            return;
        }

        $escritores = $this->quienesEscribenNotificaciones();

        $this->assertSame(
            [],
            $escritores,
            'La campana del panel está apagada y estos archivos siguen escribiendo notificaciones '
            .'que nadie puede leer: '.implode(', ', $escritores)
            .'. O se enciende `databaseNotifications()` en AdminPanelProvider, o se quita el envío. '
            .'Las dos mitades se mueven juntas.'
        );
    }
}

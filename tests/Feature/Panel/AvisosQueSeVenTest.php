<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Tests\TestCase;

/**
 * Un aviso que nadie puede ver no es un aviso.
 *
 * El 7 de septiembre de 2026 se retiró la campana del panel (D-L22): sus dos
 * líneas --`databaseNotifications()` y su sondeo-- quedaron comentadas en
 * `AdminPanelProvider`, con el argumento de que la banda «Te está esperando» del
 * tablero cuenta mejor lo mismo. Lo que no se retiró fue **quien escribía en esa
 * campana**: `FlujoDeAprobacionObserver` siguió consultando todos los usuarios,
 * preguntándole a la policy por cada uno y guardando una notificación en la base
 * por cada registro enviado a revisión. Trabajo en cada guardado, filas
 * acumulándose, y ninguna pantalla que las lea.
 *
 * Peor que el desperdicio: `FlujoDeAprobacionTest` tenía **cuatro aserciones en
 * verde** sobre esas notificaciones. Verdes, correctas, y sobre algo que el
 * usuario no puede ver desde hace dos días. Es el número trece de la lista de
 * falsos verdes de este proyecto, y salió al construir el aviso de PQR del
 * Acta 08 --que iba a cometer exactamente el mismo error--.
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
            'La campana del panel está apagada (D-L22) y estos archivos siguen escribiendo notificaciones '
            .'que nadie puede leer: '.implode(', ', $escritores)
            .'. O se enciende `databaseNotifications()` en AdminPanelProvider, o se quita el envío. '
            .'Las dos mitades se mueven juntas.'
        );
    }
}

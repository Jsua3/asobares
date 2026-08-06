<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as TableroDeFabrica;

/**
 * El tablero del panel, con vocabulario del gremio y rejilla propia.
 *
 * Reemplaza al de fábrica para poder ordenar las tres bandas del diseño:
 * primero lo que hay que hacer (`PendientesDeAprobacion`), después las cuatro
 * cifras del oficio (`ResumenDelGremio`), y al final las gráficas.
 */
class Dashboard extends TableroDeFabrica
{
    protected static ?string $title = 'Tablero del gremio';

    protected static ?string $navigationLabel = 'Tablero';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getSubheading(): ?string
    {
        return 'Lo que te espera hoy, y cómo va el gremio.';
    }

    /** @return int|array<string, int> */
    public function getColumns(): int|array
    {
        // Cuatro columnas: es lo que necesitan las cuatro tarjetas de la
        // banda 2 para caber en una sola fila en escritorio.
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 4,
        ];
    }
}

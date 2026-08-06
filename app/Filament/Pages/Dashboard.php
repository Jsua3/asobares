<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as TableroDeFabrica;

/**
 * El tablero del panel, con vocabulario del gremio.
 *
 * Reemplaza al de fábrica para poder ordenar las tres bandas del diseño:
 * primero lo que hay que hacer (`PendientesDeAprobacion`), después las cuatro
 * cifras del oficio (`ResumenDelGremio`), y al final las gráficas. Las tres
 * bandas las ordena `getSort()` de cada widget, no una rejilla propia: los
 * cinco son `columnSpan = 'full'`, así que la página no necesita declarar
 * columnas.
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

    /*
     * Sin `getColumns()` propio: los cinco widgets del tablero declaran
     * `columnSpan = 'full'` (los cuatro explícitos, más `ResumenDelGremio`
     * porque lo hereda de `StatsOverviewWidget`), así que cada uno ocupa
     * toda la fila sin importar cuántas columnas tenga la rejilla. Las
     * cuatro tarjetas de la banda 2 no se reparten por esta rejilla: las
     * calcula `StatsOverviewWidget` por dentro, ajeno al `getColumns()` de
     * la página. Una rejilla de 4 columnas aquí no ayudaba a esas tarjetas y
     * sí perjudicaba a `AsociadosPorMunicipio`, el único widget que no
     * declaraba ancho: heredaba el `1` de `Widget` y quedaba en un cuarto
     * de fila con el resto vacío.
     */
}

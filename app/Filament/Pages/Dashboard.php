<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as TableroDeFabrica;

/**
 * El tablero del panel, con vocabulario del gremio.
 *
 * Reemplaza al de fábrica para poder ordenar las bandas del diseño: primero lo
 * que hay que hacer (`PendientesDeAprobacion`), después las cuatro cifras del
 * oficio (`ResumenDelGremio`), y al final los widgets operativos reales. Las
 * bandas las ordena `getSort()` de cada widget. La rejilla (`xl` = 6) solo
 * reparte recaudo (4) y municipios (2) en escritorio; no cambia datos ni
 * permisos.
 */
class Dashboard extends TableroDeFabrica
{
    protected static ?string $title = 'Tablero del gremio';

    protected static ?string $navigationLabel = 'Tablero';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    /**
     * Marca el cuerpo del tablero para que la cabecera institucional
     * (`.asb-dashboard .fi-page-header-main-ctn > .fi-header`) no dependa de `:has()` ni
     * se pinte en otras páginas. No cambia textos, widgets ni datos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-dashboard',
    ];

    public function getSubheading(): ?string
    {
        return 'Lo que te espera hoy, y cómo va el gremio.';
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 6,
        ];
    }
}

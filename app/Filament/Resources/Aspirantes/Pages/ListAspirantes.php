<?php

namespace App\Filament\Resources\Aspirantes\Pages;

use App\Filament\Resources\Aspirantes\AspiranteResource;
use Filament\Resources\Pages\ListRecords;

class ListAspirantes extends ListRecords
{
    protected static string $resource = AspiranteResource::class;

    /**
     * Marca el listado para el patrón visual operativo (`.asb-operativo`).
     * No cambia consultas, filtros, acciones ni permisos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    /** Los perfiles entran por el formulario público, no a mano. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

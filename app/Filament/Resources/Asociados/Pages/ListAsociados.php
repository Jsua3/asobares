<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Filament\Resources\Asociados\AsociadoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAsociados extends ListRecords
{
    protected static string $resource = AsociadoResource::class;

    /**
     * Marca el listado para el patrón visual operativo (`.asb-operativo`).
     * No cambia consultas, filtros, acciones ni permisos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

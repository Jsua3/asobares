<?php

namespace App\Filament\Resources\Proveedors\Pages;

use App\Filament\Resources\Proveedors\ProveedorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProveedors extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

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

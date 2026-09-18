<?php

namespace App\Filament\Resources\RequisitoAperturas\Pages;

use App\Filament\Resources\RequisitoAperturas\RequisitoAperturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRequisitoAperturas extends ListRecords
{
    protected static string $resource = RequisitoAperturaResource::class;

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

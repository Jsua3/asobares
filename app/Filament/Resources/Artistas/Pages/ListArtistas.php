<?php

namespace App\Filament\Resources\Artistas\Pages;

use App\Filament\Resources\Artistas\ArtistaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArtistas extends ListRecords
{
    protected static string $resource = ArtistaResource::class;

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

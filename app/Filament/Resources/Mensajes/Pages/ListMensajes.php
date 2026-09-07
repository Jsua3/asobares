<?php

namespace App\Filament\Resources\Mensajes\Pages;

use App\Filament\Resources\Mensajes\MensajeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMensajes extends ListRecords
{
    protected static string $resource = MensajeResource::class;

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

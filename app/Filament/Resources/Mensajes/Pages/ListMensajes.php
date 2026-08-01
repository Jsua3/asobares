<?php

namespace App\Filament\Resources\Mensajes\Pages;

use App\Filament\Resources\Mensajes\MensajeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMensajes extends ListRecords
{
    protected static string $resource = MensajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

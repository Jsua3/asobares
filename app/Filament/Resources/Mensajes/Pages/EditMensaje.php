<?php

namespace App\Filament\Resources\Mensajes\Pages;

use App\Filament\Resources\Mensajes\MensajeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMensaje extends EditRecord
{
    protected static string $resource = MensajeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

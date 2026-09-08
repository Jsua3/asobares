<?php

namespace App\Filament\Resources\SolicitudAfiliacions\Pages;

use App\Filament\Resources\SolicitudAfiliacions\SolicitudAfiliacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudAfiliacion extends EditRecord
{
    protected static string $resource = SolicitudAfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

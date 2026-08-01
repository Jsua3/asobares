<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Filament\Resources\Asociados\AsociadoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAsociado extends EditRecord
{
    protected static string $resource = AsociadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

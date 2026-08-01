<?php

namespace App\Filament\Resources\Vacantes\Pages;

use App\Filament\Resources\Vacantes\VacanteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVacante extends EditRecord
{
    protected static string $resource = VacanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

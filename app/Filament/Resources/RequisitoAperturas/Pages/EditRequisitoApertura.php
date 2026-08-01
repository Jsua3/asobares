<?php

namespace App\Filament\Resources\RequisitoAperturas\Pages;

use App\Filament\Resources\RequisitoAperturas\RequisitoAperturaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRequisitoApertura extends EditRecord
{
    protected static string $resource = RequisitoAperturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Aspirantes\Pages;

use App\Filament\Resources\Aspirantes\AspiranteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAspirante extends EditRecord
{
    protected static string $resource = AspiranteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

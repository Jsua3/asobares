<?php

namespace App\Filament\Resources\Aliados\Pages;

use App\Filament\Resources\Aliados\AliadoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAliado extends EditRecord
{
    protected static string $resource = AliadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

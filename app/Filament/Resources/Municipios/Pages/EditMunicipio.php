<?php

namespace App\Filament\Resources\Municipios\Pages;

use App\Filament\Resources\Municipios\MunicipioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMunicipio extends EditRecord
{
    protected static string $resource = MunicipioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

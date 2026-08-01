<?php

namespace App\Filament\Resources\Municipios\Pages;

use App\Filament\Resources\Municipios\MunicipioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMunicipios extends ListRecords
{
    protected static string $resource = MunicipioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

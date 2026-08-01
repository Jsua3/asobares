<?php

namespace App\Filament\Resources\Vacantes\Pages;

use App\Filament\Resources\Vacantes\VacanteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacantes extends ListRecords
{
    protected static string $resource = VacanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

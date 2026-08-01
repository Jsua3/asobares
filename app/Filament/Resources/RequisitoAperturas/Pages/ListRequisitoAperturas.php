<?php

namespace App\Filament\Resources\RequisitoAperturas\Pages;

use App\Filament\Resources\RequisitoAperturas\RequisitoAperturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRequisitoAperturas extends ListRecords
{
    protected static string $resource = RequisitoAperturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Iniciativas\Pages;

use App\Filament\Resources\Iniciativas\IniciativaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIniciativas extends ListRecords
{
    protected static string $resource = IniciativaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

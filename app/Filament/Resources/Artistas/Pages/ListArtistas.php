<?php

namespace App\Filament\Resources\Artistas\Pages;

use App\Filament\Resources\Artistas\ArtistaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArtistas extends ListRecords
{
    protected static string $resource = ArtistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

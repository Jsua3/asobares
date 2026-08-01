<?php

namespace App\Filament\Resources\Aspirantes\Pages;

use App\Filament\Resources\Aspirantes\AspiranteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAspirantes extends ListRecords
{
    protected static string $resource = AspiranteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Aliados\Pages;

use App\Filament\Resources\Aliados\AliadoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAliados extends ListRecords
{
    protected static string $resource = AliadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

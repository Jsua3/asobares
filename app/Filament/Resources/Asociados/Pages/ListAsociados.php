<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Filament\Resources\Asociados\AsociadoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAsociados extends ListRecords
{
    protected static string $resource = AsociadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

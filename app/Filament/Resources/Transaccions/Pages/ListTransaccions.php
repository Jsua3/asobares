<?php

namespace App\Filament\Resources\Transaccions\Pages;

use App\Filament\Resources\Transaccions\TransaccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransaccions extends ListRecords
{
    protected static string $resource = TransaccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

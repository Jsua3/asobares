<?php

namespace App\Filament\Resources\Noticias\Pages;

use App\Filament\Resources\Noticias\NoticiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNoticias extends ListRecords
{
    protected static string $resource = NoticiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Artistas\Pages;

use App\Filament\Resources\Artistas\ArtistaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArtista extends EditRecord
{
    protected static string $resource = ArtistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

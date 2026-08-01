<?php

namespace App\Filament\Resources\Iniciativas\Pages;

use App\Filament\Resources\Iniciativas\IniciativaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIniciativa extends EditRecord
{
    protected static string $resource = IniciativaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

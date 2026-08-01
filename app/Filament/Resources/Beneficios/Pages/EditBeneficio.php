<?php

namespace App\Filament\Resources\Beneficios\Pages;

use App\Filament\Resources\Beneficios\BeneficioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeneficio extends EditRecord
{
    protected static string $resource = BeneficioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

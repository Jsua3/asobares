<?php

namespace App\Filament\Resources\Vacantes\Pages;

use App\Filament\Resources\Vacantes\VacanteResource;
use Filament\Resources\Pages\ListRecords;

class ListVacantes extends ListRecords
{
    protected static string $resource = VacanteResource::class;

    /** Nadie crea vacantes desde el panel: las publica el asociado. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

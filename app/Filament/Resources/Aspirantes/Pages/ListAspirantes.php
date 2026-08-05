<?php

namespace App\Filament\Resources\Aspirantes\Pages;

use App\Filament\Resources\Aspirantes\AspiranteResource;
use Filament\Resources\Pages\ListRecords;

class ListAspirantes extends ListRecords
{
    protected static string $resource = AspiranteResource::class;

    /** Los perfiles entran por el formulario público, no a mano. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

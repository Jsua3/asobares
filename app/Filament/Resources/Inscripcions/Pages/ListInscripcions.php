<?php

namespace App\Filament\Resources\Inscripcions\Pages;

use App\Filament\Resources\Inscripcions\InscripcionResource;
use Filament\Resources\Pages\ListRecords;

class ListInscripcions extends ListRecords
{
    protected static string $resource = InscripcionResource::class;

    /** Las inscripciones entran por la ficha pública del evento, no a mano. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

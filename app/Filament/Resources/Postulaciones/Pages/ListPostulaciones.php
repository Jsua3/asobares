<?php

namespace App\Filament\Resources\Postulaciones\Pages;

use App\Filament\Resources\Postulaciones\PostulacionResource;
use Filament\Resources\Pages\ListRecords;

class ListPostulaciones extends ListRecords
{
    protected static string $resource = PostulacionResource::class;

    /** Las postulaciones entran por el formulario público, no a mano. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}

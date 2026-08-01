<?php

namespace App\Filament\Resources\Artistas\Pages;

use App\Filament\Resources\Artistas\ArtistaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArtista extends CreateRecord
{
    protected static string $resource = ArtistaResource::class;
}

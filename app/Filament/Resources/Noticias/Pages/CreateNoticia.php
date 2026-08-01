<?php

namespace App\Filament\Resources\Noticias\Pages;

use App\Filament\Resources\Noticias\NoticiaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNoticia extends CreateRecord
{
    protected static string $resource = NoticiaResource::class;
}

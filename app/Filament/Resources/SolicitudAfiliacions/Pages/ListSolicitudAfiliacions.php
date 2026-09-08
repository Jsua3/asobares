<?php

namespace App\Filament\Resources\SolicitudAfiliacions\Pages;

use App\Filament\Resources\SolicitudAfiliacions\SolicitudAfiliacionResource;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudAfiliacions extends ListRecords
{
    protected static string $resource = SolicitudAfiliacionResource::class;

    /** @var array<string, string> */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];
}

<?php

namespace App\Filament\Resources\Publicidades\Pages;

use App\Filament\Resources\Publicidades\PublicidadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicidades extends ListRecords
{
    protected static string $resource = PublicidadResource::class;

    /** @var array<string, string> */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

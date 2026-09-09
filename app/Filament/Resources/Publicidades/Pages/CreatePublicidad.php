<?php

namespace App\Filament\Resources\Publicidades\Pages;

use App\Enums\EstadoPublicidad;
use App\Filament\Resources\Publicidades\PublicidadResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicidad extends CreateRecord
{
    protected static string $resource = PublicidadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->can('publicar_publicidad') !== true) {
            $data['estado'] = EstadoPublicidad::PendienteAprobacion->value;
            $data['aprobado_por'] = null;
            $data['aprobado_at'] = null;
        }

        return $data;
    }
}

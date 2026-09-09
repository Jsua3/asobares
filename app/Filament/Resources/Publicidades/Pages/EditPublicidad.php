<?php

namespace App\Filament\Resources\Publicidades\Pages;

use App\Enums\EstadoPublicidad;
use App\Filament\Resources\Publicidades\PublicidadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPublicidad extends EditRecord
{
    protected static string $resource = PublicidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->can('publicar_publicidad') !== true) {
            unset($data['estado'], $data['aprobado_por'], $data['aprobado_at']);

            if ($this->record->estado === EstadoPublicidad::Publicada) {
                $data['estado'] = EstadoPublicidad::PendienteAprobacion->value;
                $data['aprobado_por'] = null;
                $data['aprobado_at'] = null;
            }
        }

        return $data;
    }
}

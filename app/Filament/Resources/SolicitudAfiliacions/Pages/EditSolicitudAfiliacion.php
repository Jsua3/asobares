<?php

namespace App\Filament\Resources\SolicitudAfiliacions\Pages;

use App\Enums\EstadoSolicitudAfiliacion;
use App\Filament\Resources\SolicitudAfiliacions\SolicitudAfiliacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSolicitudAfiliacion extends EditRecord
{
    protected static string $resource = SolicitudAfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! array_key_exists('estado', $data)) {
            return $data;
        }

        $estado = EstadoSolicitudAfiliacion::tryFrom((string) $data['estado']);

        if ($estado === null || ! $this->esEstadoFinal($estado)) {
            return $data;
        }

        if ($this->record->estado === $estado
            && $this->record->asociado_id !== null
            && $this->record->user_id !== null) {
            return $data;
        }

        throw ValidationException::withMessages([
            'data.estado' => 'La aprobación debe hacerse con la acción "Aprobar y crear acceso".',
        ]);
    }

    private function esEstadoFinal(EstadoSolicitudAfiliacion $estado): bool
    {
        return in_array($estado, [
            EstadoSolicitudAfiliacion::Aprobada,
            EstadoSolicitudAfiliacion::Rechazada,
        ], true);
    }
}

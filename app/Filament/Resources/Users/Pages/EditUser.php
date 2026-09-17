<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Si en este guardado la oficina escribió una contraseña. Vive lo que la petición. */
    private bool $seEscribioUnaContrasena = false;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * El campo solo llega cuando se escribió algo (`dehydrated(filled)` en
     * `UserForm`): corregir el nombre no toca la contraseña ni la marca.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->seEscribioUnaContrasena = filled($data['password'] ?? null);

        return $data;
    }

    /** Una contraseña que escribe la oficina la conoce la oficina: la de un afiliado vuelve a ser provisional. */
    protected function afterSave(): void
    {
        if (! $this->seEscribioUnaContrasena) {
            return;
        }

        /** @var User $usuario */
        $usuario = $this->getRecord();
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();
    }
}

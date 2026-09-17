<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * La contraseña de una cuenta nueva la escribe la oficina, no su titular:
     * si la cuenta es de un afiliado, nace provisional. Corre después de
     * guardar los roles.
     */
    protected function afterCreate(): void
    {
        /** @var User $usuario */
        $usuario = $this->getRecord();
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();
    }
}

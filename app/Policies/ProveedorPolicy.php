<?php

namespace App\Policies;

class ProveedorPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'proveedor';
    }
}

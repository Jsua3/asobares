<?php

namespace App\Policies;

class AsociadoPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'asociado';
    }
}

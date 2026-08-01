<?php

namespace App\Policies;

class AliadoPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'aliado';
    }
}

<?php

namespace App\Policies;

class VacantePolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'vacante';
    }
}

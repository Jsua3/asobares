<?php

namespace App\Policies;

class BeneficioPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'beneficio';
    }
}

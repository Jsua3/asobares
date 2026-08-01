<?php

namespace App\Policies;

class IniciativaPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'iniciativa';
    }
}

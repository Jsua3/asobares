<?php

namespace App\Policies;

class RequisitoAperturaPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'requisito';
    }
}

<?php

namespace App\Policies;

class PublicidadPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'publicidad';
    }
}

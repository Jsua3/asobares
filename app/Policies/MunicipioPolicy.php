<?php

namespace App\Policies;

class MunicipioPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'municipio';
    }
}

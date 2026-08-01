<?php

namespace App\Policies;

class CategoriaPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'categoria';
    }
}

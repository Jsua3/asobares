<?php

namespace App\Policies;

class ArtistaPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'artista';
    }
}

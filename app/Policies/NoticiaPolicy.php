<?php

namespace App\Policies;

class NoticiaPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'noticia';
    }
}

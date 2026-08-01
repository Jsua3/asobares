<?php

namespace App\Policies;

class EventoPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'evento';
    }
}

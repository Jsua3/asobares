<?php

namespace App\Filament\Resources\Transaccions\Pages;

use App\Filament\Resources\Transaccions\TransaccionResource;
use Filament\Resources\Pages\ListRecords;

/**
 * El recurso de transacciones es de SOLO LECTURA por diseño: una transacción
 * la escribe la pasarela y nadie más. Sin página de creación, un
 * `CreateAction` aquí no quedaría inerte: abriría un modal de creación y
 * dejaría registrar a mano un cobro que la pasarela nunca produjo.
 * Es la misma frontera que guarda `FlujoDePagoTest` del lado de las
 * inscripciones: nadie confirma un pago a mano.
 */
class ListTransaccions extends ListRecords
{
    protected static string $resource = TransaccionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

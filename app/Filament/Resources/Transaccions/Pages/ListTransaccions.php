<?php

namespace App\Filament\Resources\Transaccions\Pages;

use App\Filament\Resources\Transaccions\TransaccionResource;
use Filament\Resources\Pages\ListRecords;

/**
 * El recurso de transacciones es de SOLO LECTURA por diseño: una transacción
 * la escribe la pasarela y nadie más. `TransaccionResource::getPages()` solo
 * declara `index`, así que el `CreateAction` que vivía aquí era huérfano —
 * y peor que huérfano: Filament lo habría resuelto abriendo el formulario en
 * un modal, dejando que el personal fabricara a mano un cobro que nunca
 * ocurrió. Es la misma frontera que ya guarda `FlujoDePagoTest` del lado de
 * las inscripciones (G9: nadie confirma un pago a mano).
 */
class ListTransaccions extends ListRecords
{
    protected static string $resource = TransaccionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

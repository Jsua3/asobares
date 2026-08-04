<x-mail::message>
# Tu vacante necesita un ajuste

La secretaría revisó **{{ $vacante->cargo }}** y la devolvió antes de publicarla.

**Motivo:**

> {{ $vacante->motivo_devolucion }}

<x-mail::button :url="route('mi-cuenta.vacantes.editar', $vacante)">
Corregir y reenviar
</x-mail::button>

Al reenviarla vuelve a la fila de revisión.

ASOBARES Capítulo Quindío
</x-mail::message>

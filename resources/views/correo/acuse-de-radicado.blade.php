<x-mail::message>
# Recibimos tu PQR

Hola {{ $mensaje->nombre }},

Confirmamos la recepción de tu solicitud en **{{ ajuste('sitio_nombre') }}**.

<x-mail::panel>
Número de radicado: **{{ $mensaje->radicado }}**
Fecha de radicación: {{ $mensaje->created_at->translatedFormat('d \d\e F \d\e Y, g:i a') }}
</x-mail::panel>

**Lo que nos escribiste:**

> {{ $mensaje->mensaje }}

Guarda este número: con él puedes hacerle seguimiento a tu solicitud. Las consultas se atienden en
un máximo de diez (10) días hábiles y los reclamos en quince (15) días hábiles, conforme a la
Ley 1581 de 2012.

<x-mail::button :url="route('contacto')">
Ir a la página de contacto
</x-mail::button>

Gracias,<br>
{{ ajuste('sitio_nombre') }}<br>
{{ ajuste('contacto_direccion') }} · {{ ajuste('contacto_ciudad') }}
</x-mail::message>

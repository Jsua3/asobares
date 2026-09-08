@component('mail::message')
# {{ $reenvio ? 'Nuevo enlace de acceso' : 'Afiliación aprobada' }}

Hola, {{ $usuario->name }}.

@if ($reenvio)
Te enviamos un nuevo enlace seguro para definir la contraseña de Mi Cuenta de **{{ $asociado->nombre }}**.
@else
La solicitud de afiliación de **{{ $asociado->nombre }}** fue aprobada por ASOBARES Capítulo Quindío.

Ya creamos tu acceso a Mi Cuenta. Para entrar, primero define tu contraseña personal desde este enlace seguro:
@endif

@component('mail::button', ['url' => $url])
Definir mi contraseña
@endcomponent

El enlace es temporal. Si vence, solicita a ASOBARES que regenere el acceso.

No tienes obligaciones creadas por este correo y no estamos enviando ninguna contraseña en texto plano.

{{ ajuste('sitio_nombre') }}
@endcomponent

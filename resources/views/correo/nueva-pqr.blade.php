@component('mail::message')
# Nueva PQR radicada

Una persona acaba de radicar una PQR en el sitio público.

**Radicado:** {{ $mensaje->radicado }}  
**Nombre:** {{ $mensaje->nombre }}  
**Correo:** {{ $mensaje->correo }}  
**Teléfono:** {{ $mensaje->telefono ?: 'No informado' }}  
**Tipo:** {{ $mensaje->tipo->getLabel() }}  
**Fecha:** {{ $mensaje->created_at?->format('d/m/Y h:i a') }}

@component('mail::panel')
{{ $mensaje->mensaje }}
@endcomponent

Revisa el caso en el panel administrativo para darle seguimiento.

{{ ajuste('sitio_nombre') }}
@endcomponent

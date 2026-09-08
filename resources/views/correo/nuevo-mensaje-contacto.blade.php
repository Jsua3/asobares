@component('mail::message')
# Nuevo mensaje de contacto

Llegó un mensaje desde el formulario público de contacto.

**Nombre:** {{ $mensaje->nombre }}  
**Correo:** {{ $mensaje->correo }}  
**Teléfono:** {{ $mensaje->telefono ?: 'No informado' }}  
**Tipo:** {{ $mensaje->tipo->getLabel() }}  
**Fecha:** {{ $mensaje->created_at?->format('d/m/Y h:i a') }}

@component('mail::panel')
{{ $mensaje->mensaje }}
@endcomponent

{{ ajuste('sitio_nombre') }}
@endcomponent

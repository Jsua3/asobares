@component('mail::message')
# Nueva solicitud de afiliación

Un establecimiento acaba de dejar sus datos en el sitio público.

**Establecimiento:** {{ $solicitud->establecimiento_nombre }}  
**Razón social:** {{ $solicitud->razon_social }}  
**NIT:** {{ $solicitud->nit }}  
**Municipio:** {{ $solicitud->municipio?->nombre }}  
**Categoría:** {{ $solicitud->categoria?->nombre }}

**Solicitante:** {{ $solicitud->solicitante_nombre }}  
**Cargo:** {{ $solicitud->solicitante_cargo }}  
**Teléfono:** {{ $solicitud->solicitante_telefono }}  
**Correo:** {{ $solicitud->solicitante_correo }}

@component('mail::panel')
{{ $solicitud->descripcion }}
@endcomponent

Revisa la solicitud en el panel administrativo para iniciar el seguimiento.

{{ ajuste('sitio_nombre') }}
@endcomponent

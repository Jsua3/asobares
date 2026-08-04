<x-mail::message>
# Nueva postulación

Alguien se postuló a tu vacante de **{{ $postulacion->vacante->cargo }}**.

**Nombre:** {{ $postulacion->nombre }}
**Correo:** {{ $postulacion->correo }}
@if ($postulacion->telefono)
**Teléfono:** {{ $postulacion->telefono }}
@endif

@if ($postulacion->experiencia)
> {{ $postulacion->experiencia }}
@endif

<x-mail::button :url="route('mi-cuenta.vacantes.show', $postulacion->vacante)">
Ver todas las postulaciones
</x-mail::button>

Los datos de esta persona son confidenciales: úsalos solo para este proceso de selección.

ASOBARES Capítulo Quindío
</x-mail::message>

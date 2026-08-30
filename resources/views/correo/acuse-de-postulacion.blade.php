<x-mail::message>
# Recibimos tu postulación

Hola {{ $postulacion->nombre }},

Tu postulación a **{{ $postulacion->vacante->cargo }}**, en **{{ $postulacion->vacante->asociado->nombre }}**, ya está registrada.

Si tu perfil les encaja, el establecimiento te contacta directamente al correo o al teléfono que dejaste. ASOBARES no participa en la selección ni conoce cuándo te van a llamar.

<x-mail::button :url="route('empleo.index')">
Ver más vacantes del sector
</x-mail::button>

ASOBARES Capítulo Quindío
</x-mail::message>

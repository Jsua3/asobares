<x-mail::message>
# Tu vacante ya está publicada

La secretaría aprobó **{{ $vacante->cargo }}** y desde ya aparece en la bolsa de empleo del sitio.

@if ($vacante->fecha_limite)
Se retirará sola el {{ $vacante->fecha_limite->translatedFormat('d \d\e F \d\e Y') }}.
@endif

<x-mail::button :url="route('empleo.show', $vacante)">
Ver la vacante publicada
</x-mail::button>

Cuando llenes el puesto, ciérrala desde tu cuenta para que deje de recibir postulaciones.

ASOBARES Capítulo Quindío
</x-mail::message>

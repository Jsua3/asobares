<x-mail::message>
# {{ $mensaje->esPqr() ? 'Entró una PQR' : 'Entró un mensaje' }}

@if ($mensaje->esPqr())
Radicado **{{ $mensaje->radicado }}**, del {{ $mensaje->created_at->translatedFormat('j \d\e F \d\e Y') }}.

La ley da **quince días hábiles** para responder (Ley 1755 de 2015): el plazo vence el **{{ $mensaje->venceEl()?->translatedFormat('j \d\e F') }}**.
@else
Llegó por el formulario de {{ $mensaje->tipo->getLabel() }}, el {{ $mensaje->created_at->translatedFormat('j \d\e F \d\e Y') }}.
@endif

**Asunto:** {{ $mensaje->asunto }}

<x-mail::button :url="route('filament.admin.resources.mensajes.index')">
Abrir la bandeja
</x-mail::button>

{{--
    A propósito no van aquí ni el texto del mensaje, ni el correo, ni el teléfono
    de quien escribe. Este aviso sale de la aplicación hacia un buzón que el
    proyecto no controla, se reenvía y se archiva durante años; copiar ahí los
    datos personales del ciudadano los sacaría del único sitio que sabe borrarlos
    cuando vence su plazo de retención (`mensajes:depurar`). El contenido se lee
    en el panel, que es donde vive.
--}}
El contenido está en el panel. Este aviso no lo copia para no sacar los datos de quien escribe del sistema que sabe borrarlos a su tiempo.

ASOBARES Capítulo Quindío
</x-mail::message>

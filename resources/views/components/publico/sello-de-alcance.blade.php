@props(['beneficio'])

{{--
    De quién es el beneficio (Acta 07, A-01). Un solo componente para las tres
    pantallas que los pintan, porque tres copias del mismo sello es lo que hace
    que una se quede atrás el día que cambie.

    Si el gremio no lo ha clasificado, aquí no sale nada: el sistema no adivina
    de quién es un beneficio. Y el municipal se anuncia con el nombre de su
    municipio y no con la palabra «Municipal», que no le dice nada al lector.
--}}
@if ($etiqueta = $beneficio->etiquetaDeAlcance())
    <p class="mt-2">
        <span class="asb-sello-alcance inline-flex items-center rounded-full border border-linea px-2.5 py-0.5 text-2xs uppercase tracking-wide text-apagado">
            {{ $etiqueta }}
        </span>
    </p>
@endif

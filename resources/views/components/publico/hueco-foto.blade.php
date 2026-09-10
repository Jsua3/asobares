@props([
    'foto' => null,
    'alt' => '',
    'ancho' => 1600,
    'alto' => 700,
    'prioridad' => false,
])

{{--
    El sitio de una foto que todavía no existe.

    ASOBARES aún no ha entregado material fotográfico autorizado, y el §9 de
    `encargo.md` es claro: en producción solo entra contenido de documento
    oficial del gremio, y ninguna ficha se publica sin autorización del titular.
    Así que los huecos se abren AHORA, con la marca puesta, y las fotos entran
    el día que lleguen — cambiar un `null` por una ruta, sin rehacer plantillas.

    El marcador no es un adorno ni un «cargando»: es el mismo lenguaje que ya
    usa `Database\Seeders\Support\GeneradorImagen` para las portadas del demo
    --diagonales grises sobre la superficie de la tarjeta, más el monograma--,
    de modo que un hueco vacío se lee como una pieza de marca y no como una
    imagen rota. Ese gris está elegido para dar 3,7:1 sobre las superficies de
    los DOS temas, así que la misma textura sirve en claro y en oscuro.

    Es decorativo de principio a fin: `aria-hidden` en el marcador y `alt`
    vacío por defecto en la foto. Lo que informa es el titular que va encima,
    no el fondo. Quien pase una foto que sí aporte información tiene que pasar
    también su `alt`.
--}}

@if ($foto)
    <img src="{{ $foto }}"
         alt="{{ $alt }}"
         width="{{ $ancho }}"
         height="{{ $alto }}"
         @if ($prioridad) fetchpriority="high" @else loading="lazy" decoding="async" @endif
         {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}>
@else
    <div {{ $attributes->merge(['class' => 'hueco-foto h-full w-full']) }} aria-hidden="true">
        {{-- El monograma del kit, sin recortar ni recolorear: el manual de
             marca lo prohíbe, y es el mismo archivo que compone el generador. --}}
        <img src="{{ asset('img/monograma-asobares.png') }}"
             alt=""
             width="156"
             height="108"
             loading="lazy"
             decoding="async"
             class="hueco-foto__marca">
    </div>
@endif

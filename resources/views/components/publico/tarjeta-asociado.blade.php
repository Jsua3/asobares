@props(['asociado', 'variante' => 'ficha'])

@php
    $esEditorial = $variante === 'editorial';
    $esHorizontal = $variante === 'horizontal';
@endphp

<article @class([
    'tarjeta tarjeta-hover group overflow-hidden',
    'tarjeta-escena' => $esEditorial,
    'sm:flex' => $esHorizontal,
])
         @if ($esEditorial)
             x-data="escena"
             x-on:pointermove="seguir($event)"
             x-on:pointerleave="salir()"
             x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`"
         @endif>
    {{-- El portador va en este <a> y NO en el <article>: `:active` casa
         también con los ancestros, así que arriba haría que pulsar «Ver ficha»
         encogiera la tarjeta entera y atenuara el enlace a la vez. --}}
    <a href="{{ route('directorio.show', $asociado) }}" @class([
        'tarjeta-pulsable block',
        'sm:w-[42%] sm:shrink-0' => $esHorizontal,
    ])>
        <div @class([
            'relative overflow-hidden bg-superficie-alta',
            'aspect-[4/3]' => ! $esEditorial && ! $esHorizontal,
            'aspect-[5/4] sm:aspect-[4/3] lg:aspect-[5/4]' => $esEditorial,
            'aspect-[4/3] sm:aspect-auto sm:h-full sm:min-h-40' => $esHorizontal,
        ])>
            @if ($asociado->foto_portada)
                <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                     alt="Portada de {{ $asociado->nombre }}"
                     loading="lazy" decoding="async"
                     width="{{ $esEditorial ? 800 : 400 }}" height="{{ $esEditorial ? 640 : 300 }}"
                     style="view-transition-name: portada-asociado-{{ $asociado->id }}"
                     class="imagen-viva h-full w-full object-cover">
            @endif

            @if ($asociado->destacado)
                <span class="absolute left-3 top-3 rounded-full bg-marca-500 px-2.5 py-1 text-2xs font-semibold uppercase tracking-wide text-white">
                    Destacado
                </span>
            @endif

            <div @class([
                'absolute inset-x-0 bottom-0 bg-gradient-to-t from-fondo via-fondo/80 to-transparent p-4 pt-10',
                'sm:p-6' => $esEditorial,
            ])>
                <h3 @class([
                    'font-display font-semibold text-fuerte',
                    'text-base leading-tight' => ! $esEditorial,
                    'text-xl sm:text-2xl' => $esEditorial,
                ])>{{ $asociado->nombre }}</h3>
                <p class="mt-1 text-xs text-tenue">
                    {{ $asociado->categoria->nombre }} · {{ $asociado->municipio->nombre }}
                </p>
            </div>
        </div>
    </a>

    <div @class([
        'p-4' => ! $esEditorial,
        'p-5 sm:p-6' => $esEditorial,
        'sm:flex sm:flex-1 sm:flex-col sm:justify-between sm:p-5' => $esHorizontal,
    ])>
        <p @class([
            'line-clamp-2 text-sm text-tenue',
            'sm:line-clamp-3' => $esHorizontal || $esEditorial,
        ])>
            {{ Str::limit($asociado->descripcion, $esEditorial ? 180 : 120) }}
        </p>

        <div class="mt-4 flex items-center justify-between gap-3">
            <a href="{{ route('directorio.show', $asociado) }}"
               class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                Ver ficha
            </a>

            @if ($enlace = enlaceWhatsapp($asociado->whatsapp, "Hola {$asociado->nombre}, los vi en la página de ASOBARES Quindío."))
                <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                   {{-- ::after y no `min-h-11`: el chip tiene borde visible y a 44 px
                        de alto se leería como una pastilla pesada junto a un enlace de
                        texto. 33,2 + 16 = 49,2 px de área, con el dibujo intacto. --}}
                   class="pulsable relative rounded-lg border border-linea px-3 py-1.5 text-xs text-suave after:absolute after:inset-x-0 after:-inset-y-2 after:content-[''] hover:border-marca-500/50 hover:text-fuerte">
                    WhatsApp
                </a>
            @endif
        </div>
    </div>
</article>

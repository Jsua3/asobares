@props(['asociado'])

<article class="tarjeta tarjeta-hover group overflow-hidden">
    <a href="{{ route('directorio.show', $asociado) }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden bg-superficie-alta">
            @if ($asociado->foto_portada)
                <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                     alt="Portada de {{ $asociado->nombre }}"
                     loading="lazy" decoding="async" width="400" height="300"
                     class="h-full w-full object-cover transition-transform duration-(--duracion-boton) ease-out group-hover:scale-105">
            @endif

            @if ($asociado->destacado)
                <span class="absolute left-3 top-3 rounded-full bg-marca-500 px-2.5 py-1 text-[.65rem] font-semibold uppercase tracking-wide text-white">
                    Destacado
                </span>
            @endif

            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-fondo via-fondo/80 to-transparent p-4 pt-10">
                <h3 class="font-display text-base font-semibold leading-tight text-fuerte">{{ $asociado->nombre }}</h3>
                <p class="mt-1 text-xs text-tenue">
                    {{ $asociado->categoria->nombre }} · {{ $asociado->municipio->nombre }}
                </p>
            </div>
        </div>
    </a>

    <div class="p-4">
        <p class="line-clamp-2 text-sm leading-relaxed text-tenue">
            {{ Str::limit($asociado->descripcion, 120) }}
        </p>

        <div class="mt-4 flex items-center justify-between gap-3">
            <a href="{{ route('directorio.show', $asociado) }}"
               class="text-sm font-medium text-acento transition-colors hover:text-acento-fuerte">
                Ver ficha
            </a>

            @if ($enlace = enlaceWhatsapp($asociado->whatsapp, "Hola {$asociado->nombre}, los vi en la página de ASOBARES Quindío."))
                <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                   class="rounded-lg border border-linea px-3 py-1.5 text-xs text-suave transition-colors hover:border-marca-500/50 hover:text-fuerte">
                    WhatsApp
                </a>
            @endif
        </div>
    </div>
</article>

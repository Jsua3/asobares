@props(['asociado'])

<article class="tarjeta tarjeta-hover group overflow-hidden">
    <a href="{{ route('directorio.show', $asociado) }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden bg-noche-800">
            @if ($asociado->foto_portada)
                <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                     alt="Portada de {{ $asociado->nombre }}"
                     loading="lazy" decoding="async" width="400" height="300"
                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            @endif

            @if ($asociado->destacado)
                <span class="absolute left-3 top-3 rounded-full bg-marca-500 px-2.5 py-1 text-[.65rem] font-semibold uppercase tracking-wide text-white">
                    Destacado
                </span>
            @endif

            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-noche-950 via-noche-950/80 to-transparent p-4 pt-10">
                <h3 class="font-display text-base font-semibold leading-tight text-white">{{ $asociado->nombre }}</h3>
                <p class="mt-1 text-xs text-noche-300">
                    {{ $asociado->categoria->nombre }} · {{ $asociado->municipio->nombre }}
                </p>
            </div>
        </div>
    </a>

    <div class="p-4">
        <p class="line-clamp-2 text-sm leading-relaxed text-noche-300">
            {{ Str::limit($asociado->descripcion, 120) }}
        </p>

        <div class="mt-4 flex items-center justify-between gap-3">
            <a href="{{ route('directorio.show', $asociado) }}"
               class="text-sm font-medium text-marca-400 transition-colors hover:text-marca-300">
                Ver ficha
            </a>

            @if ($enlace = enlaceWhatsapp($asociado->whatsapp, "Hola {$asociado->nombre}, los vi en la página de ASOBARES Quindío."))
                <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                   class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-noche-200 transition-colors hover:border-marca-500/50 hover:text-white">
                    WhatsApp
                </a>
            @endif
        </div>
    </div>
</article>

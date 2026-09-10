@php
    $enlace = enlaceSeguro($aliado->url);
    $inicial = Str::of($aliado->nombre)->trim()->substr(0, 1)->upper();
@endphp

<article class="tarjeta flex h-full flex-col overflow-hidden p-5">
    <div class="flex h-24 items-center justify-center rounded-2xl border border-linea bg-superficie">
        @if ($aliado->logo)
            <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                 alt="{{ $aliado->nombre }}"
                 loading="lazy"
                 decoding="async"
                 width="224"
                 height="112"
                 class="h-16 max-w-[80%] object-contain">
        @else
            <span class="font-display text-3xl font-bold text-acento" aria-hidden="true">{{ $inicial }}</span>
        @endif
    </div>

    <div class="mt-5 flex flex-1 flex-col">
        <p class="w-fit rounded-full border border-linea px-3 py-1 text-2xs font-semibold uppercase tracking-wider text-apagado">
            {{ $aliado->tipo->getLabel() }}
        </p>
        <h3 class="mt-3 font-display text-lg font-bold leading-tight text-balance">{{ $aliado->nombre }}</h3>

        @if (filled($aliado->descripcion))
            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-tenue">{{ $aliado->descripcion }}</p>
        @endif

        @if ($enlace)
            <a href="{{ $enlace }}"
               target="_blank"
               rel="noopener"
               class="enlace-accion mt-5 inline-flex min-h-11 items-center text-sm font-semibold text-acento hover:text-acento-fuerte">
                Visitar sitio&nbsp;<x-publico.flecha direccion="externa" />
            </a>
        @endif
    </div>
</article>

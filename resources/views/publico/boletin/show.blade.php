@php
    // El contenido viene de un editor enriquecido: se sanea antes de mostrarlo.
    $contenidoSeguro = (new \Symfony\Component\HtmlSanitizer\HtmlSanitizer(
        (new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->forceHttpsUrls()
    ))->sanitize((string) $noticia->contenido);
@endphp

<x-layouts.publico :titulo="$noticia->titulo.' — Boletín ASOBARES Quindío'"
                   :descripcion="Str::limit($noticia->extracto, 155)"
                   ogTipo="article"
                   :ogImagen="$noticia->imagen ? Storage::disk('public')->url($noticia->imagen) : null">

    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $noticia->titulo,
            'description' => $noticia->extracto,
            'datePublished' => $noticia->publicado_at?->toIso8601String(),
            'image' => $noticia->imagen ? Storage::disk('public')->url($noticia->imagen) : null,
            'publisher' => ['@type' => 'Organization', 'name' => ajuste('sitio_nombre')],
        ]);
    @endphp

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <a href="{{ route('boletin.index') }}" class="enlace-accion relative inline-block text-sm text-apagado after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento"><x-publico.flecha direccion="izquierda" />&nbsp;Volver al boletín</a>

        <div class="mt-5 flex items-center gap-2 text-xs">
            <span class="rounded-full bg-marca-500/15 px-3 py-1 font-medium text-acento-fuerte">
                {{ $noticia->categoria->getLabel() }}
            </span>
            <time datetime="{{ $noticia->publicado_at->toDateString() }}" class="text-apagado">
                {{ $noticia->publicado_at->translatedFormat('d \d\e F \d\e Y') }}
            </time>
        </div>

        <h1 class="mt-4 font-display text-3xl font-bold leading-tight tracking-tight text-balance sm:text-4xl">
            {{ $noticia->titulo }}
        </h1>

        @if ($noticia->extracto)
            <p class="mt-5 text-lg leading-relaxed text-suave text-pretty">{{ $noticia->extracto }}</p>
        @endif

        @if ($noticia->imagen)
            <img src="{{ Storage::disk('public')->url($noticia->imagen) }}" alt=""
                 width="1200" height="675" decoding="async"
                 class="mt-8 aspect-video w-full rounded-2xl border border-linea object-cover">
        @endif

        <div class="prose-asobares mt-8 space-y-5 text-base leading-relaxed text-suave
                    [&_a]:text-acento [&_a]:underline [&_a]:underline-offset-2
                    [&_p]:text-pretty [&_strong]:font-semibold [&_strong]:text-fuerte">
            {!! $contenidoSeguro !!}
        </div>

        @if ($relacionadas->isNotEmpty())
            <section class="mt-16 border-t border-linea pt-10" aria-labelledby="relacionadas">
                <h2 id="relacionadas" class="font-display text-lg font-semibold">Más del boletín</h2>
                <ul class="mt-5 space-y-3">
                    @foreach ($relacionadas as $relacionada)
                        <li>
                            <a href="{{ route('boletin.show', $relacionada) }}"
                               class="tarjeta tarjeta-hover tarjeta-pulsable block p-5">
                                <span class="text-xs text-apagado">
                                    {{ $relacionada->categoria->getLabel() }} ·
                                    {{ $relacionada->publicado_at->translatedFormat('d M Y') }}
                                </span>
                                <span class="mt-1.5 block font-display text-sm font-semibold">{{ $relacionada->titulo }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
</x-layouts.publico>

@props(['titulo', 'subtitulo' => null, 'compacto' => false])

<section class="resplandor-marca border-b border-white/[.09]">
    <div @class([
        'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8',
        'py-14 sm:py-20' => ! $compacto,
        'py-10 sm:py-14' => $compacto,
    ])>
        <div class="max-w-3xl">
            {{ $encima ?? '' }}
            <h1 @class([
                'font-display font-bold tracking-tight text-balance',
                'text-3xl sm:text-5xl lg:text-6xl' => ! $compacto,
                'text-2xl sm:text-4xl' => $compacto,
            ])>{{ $titulo }}</h1>

            @if ($subtitulo)
                <p class="mt-5 text-base leading-relaxed text-noche-200 sm:text-lg text-pretty">{{ $subtitulo }}</p>
            @endif

            {{ $slot }}
        </div>
    </div>
</section>

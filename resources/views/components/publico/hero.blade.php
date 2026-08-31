@props(['titulo', 'subtitulo' => null, 'compacto' => false])

{{--
    Ranura `medio` (OBS3-02): imagen o video de fondo para darle vida al hero.
    El directivo lo pidió en `R21 05:22` --«el banner que va moviéndose o el
    video, algo que le genere vida»-- y él mismo le puso el límite en
    `R21 05:35`: «no sea que afecte la visibilidad de las letras».

    Ese límite lo garantiza `.hero-medio::after`, el velo, cuya opacidad es un
    mínimo calculado en `--asb-velo-hero`. No se pinta medio sin velo: van en
    la misma clase a propósito, para que no se puedan separar por descuido.

    Sin la ranura, esto rinde exactamente lo de siempre --las otras doce
    páginas que usan el hero no cambian ni un atributo--.

    Si lo que se pasa es un video: `muted`, `playsinline`, `loop` y un `poster`,
    y no lo pongas en `autoplay` sin mirar `prefers-reduced-motion`. El bloque
    global de movimiento reducido de `app.css` frena animaciones CSS, no la
    reproducción de un <video>.
--}}
<section @class([
    'resplandor-marca border-b border-linea',
    'hero-con-medio' => isset($medio),
])>
    @isset($medio)
        {{-- Decoración: no aporta contenido y no debe leerlo un lector de pantalla. --}}
        <div class="hero-medio" aria-hidden="true">
            {{ $medio }}
        </div>
    @endisset

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
                <p class="mt-5 text-base leading-relaxed text-suave sm:text-lg text-pretty">{{ $subtitulo }}</p>
            @endif

            {{ $slot }}
        </div>
    </div>
</section>

@props(['titulo', 'subtitulo' => null, 'compacto' => false, 'atmosfera' => false, 'audiovisual' => false, 'portada' => false])

{{--
    Ranura `medio` (OBS3-02): imagen o video de fondo para darle vida al hero.
    El directivo lo pidió en `R21 05:22` --«el banner que va moviéndose o el
    video, algo que le genere vida»-- y él mismo le puso el límite en
    `R21 05:35`: «no sea que afecte la visibilidad de las letras».

    Ese límite lo garantiza `.hero-medio::after`, el velo, cuya opacidad es un
    mínimo calculado en `--asb-velo-hero`. No se pinta medio sin velo: van en
    la misma clase a propósito, para que no se puedan separar por descuido.

    Ranura `escena`: fotografía al lado del texto, no debajo. El velo no la
    cubre porque el titular no se superpone. Es la composición editorial:
    aire a un lado, establecimiento real al otro.

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
    'hero-editorial' => isset($escena),
    'hero-portada' => $portada,
    'luz-ambiente' => $atmosfera,
])>
    @isset($medio)
        {{-- Decoración: no aporta contenido y no debe leerlo un lector de pantalla. --}}
        <div class="hero-medio" aria-hidden="true">
            {{ $medio }}
        </div>
    @endisset

    <div @class([
        'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8',
        'flex min-h-[calc(100svh-1rem)] items-center pb-20 pt-28 sm:pb-24 sm:pt-32 lg:pt-36' => $portada,
        'py-16 sm:py-24 lg:py-28' => ! $compacto && isset($escena),
        'py-14 sm:py-20' => ! $compacto && ! isset($escena) && ! $portada,
        'py-10 sm:py-14' => $compacto,
    ])>
        <div @class([
            'w-full' => $portada,
            'grid items-center gap-10 lg:grid-cols-12 lg:gap-14' => isset($escena) && ! $audiovisual,
            'grid items-center gap-10 lg:grid-cols-12 lg:gap-10' => isset($escena) && $audiovisual,
        ])>
            <div @class([
                'max-w-3xl' => ! isset($escena) && ! $portada,
                'max-w-4xl' => $portada,
                'lg:col-span-6 xl:col-span-5' => isset($escena) && ! $audiovisual,
                'lg:col-span-4' => isset($escena) && $audiovisual,
            ])>
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

            @isset($escena)
                <div @class([
                    'lg:col-span-6 xl:col-span-7' => ! $audiovisual,
                    'lg:col-span-8' => $audiovisual,
                ])>
                    {{ $escena }}
                </div>
            @endisset
        </div>
    </div>
</section>

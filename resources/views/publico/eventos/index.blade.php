<x-layouts.publico titulo="Eventos y capacitaciones — ASOBARES Quindío"
                   descripcion="ExpoBar, foros, congresos y capacitaciones del gremio de la vida nocturna del Quindío.">

    <x-publico.hero titulo="Eventos y capacitaciones" compacto
                    subtitulo="Solo eventos del gremio: ferias, foros y formación para los establecimientos del Quindío." />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <div class="inline-flex rounded-xl border border-linea p-1" role="group" aria-label="Filtrar por fecha">
            @foreach (['proximos' => "Próximos ({$totalProximos})", 'pasados' => "Pasados ({$totalPasados})"] as $clave => $texto)
                <a href="{{ route('eventos.index', ['cuando' => $clave]) }}"
                   @class([
                       'rounded-lg px-5 py-2 text-sm transition-colors',
                       'bg-marca-500 font-medium text-white' => $cuando === $clave,
                       'text-tenue hover:text-fuerte' => $cuando !== $clave,
                   ])
                   @if ($cuando === $clave) aria-current="true" @endif>
                    {{ $texto }}
                </a>
            @endforeach
        </div>

        @if ($eventos->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">
                    {{ $cuando === 'proximos' ? 'No hay eventos programados por ahora' : 'Todavía no hay eventos pasados' }}
                </p>
                <p class="mt-2 text-sm text-tenue">Publicamos aquí la agenda del gremio.</p>
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($eventos as $evento)
                    <article class="tarjeta tarjeta-hover flex flex-col overflow-hidden">
                        <a href="{{ route('eventos.show', $evento) }}" class="flex flex-1 flex-col">
                            @if ($evento->imagen)
                                <img src="{{ Storage::disk('public')->url($evento->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="225"
                                     style="view-transition-name: portada-{{ $evento->id }}"
                                     class="aspect-video w-full object-cover">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $evento->tipo->getLabel() }}
                                    </span>
                                    @if ($evento->esGratuito())
                                        <span class="rounded-full border border-linea px-2.5 py-1 text-tenue">Gratuito</span>
                                    @else
                                        <span class="rounded-full border border-linea px-2.5 py-1 text-tenue">{{ pesos($evento->precio) }}</span>
                                    @endif
                                </div>

                                <h2 class="mt-3 font-display text-base font-semibold leading-snug">{{ $evento->titulo }}</h2>

                                <p class="mt-2 text-xs text-apagado">
                                    {{ $evento->fecha_inicio->translatedFormat('l d \d\e F, Y') }}
                                    @if ($evento->lugar)
                                        <span class="block">{{ $evento->lugar }}</span>
                                    @endif
                                </p>

                                <p class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-tenue">
                                    {{ Str::limit(strip_tags($evento->descripcion), 130) }}
                                </p>

                                <span class="mt-4 text-sm font-medium text-acento">Ver detalle →</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $eventos->links() }}</div>
        @endif
    </div>
</x-layouts.publico>

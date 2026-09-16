<x-layouts.publico :titulo="ajuste('seo_eventos_titulo', 'Eventos y capacitaciones — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_eventos_descripcion', 'ExpoBar, foros, congresos y capacitaciones del gremio de la vida nocturna del Quindío.')">

    @push('cabeza')
        @vite(['resources/css/eventos-editorial.css'])
    @endpush

    <div class="eventos-editorial">
        <x-publico.hero-eventos
            :titulo="ajuste('eventos_titulo', 'Eventos y capacitaciones')"
            :subtitulo="ajuste('eventos_intro', 'Eventos, capacitaciones y experiencias del gremio y sus aliados para el sector gastronómico y de entretenimiento del Quindío.')" />

        <div class="eventos-editorial-cuerpo mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
             x-data="{
                 avanzar(sentido) {
                     const pista = this.$refs.pista;
                     if (! pista) {
                         return;
                     }
                     const fichas = Array.from(pista.querySelectorAll('.eventos-editorial-ficha'));
                     const origen = pista.getBoundingClientRect().left;
                     let indice = 0;
                     let menor = Infinity;
                     fichas.forEach((ficha, posicion) => {
                         const delta = Math.abs(ficha.getBoundingClientRect().left - origen);
                         if (delta < menor) {
                             menor = delta;
                             indice = posicion;
                         }
                     });
                     const destino = fichas[indice + sentido];
                     if (! destino) {
                         return;
                     }
                     const izquierda = destino.getBoundingClientRect().left - origen + pista.scrollLeft;
                     const suave = ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                     pista.scrollTo({ left: izquierda, behavior: suave ? 'smooth' : 'auto' });
                 }
             }">

            <div class="eventos-editorial-agenda__tope">
                <x-publico.conmutador-eventos :activo="$cuando"
                                              :total-proximos="$totalProximos"
                                              :total-pasados="$totalPasados" />

                @if ($eventos->isNotEmpty() && $eventos->count() > 1)
                    <div class="eventos-editorial-riel__mandos">
                        <button type="button"
                                class="eventos-editorial-riel__mando pulsable"
                                aria-controls="eventos-riel"
                                aria-label="Evento anterior"
                                x-on:click="avanzar(-1)">
                            <x-publico.flecha direccion="izquierda" />
                        </button>
                        <button type="button"
                                class="eventos-editorial-riel__mando pulsable"
                                aria-controls="eventos-riel"
                                aria-label="Evento siguiente"
                                x-on:click="avanzar(1)">
                            <x-publico.flecha />
                        </button>
                    </div>
                @endif
            </div>

            @if ($eventos->isEmpty())
                <div class="eventos-editorial-vacio">
                    <p class="font-display text-lg font-semibold">
                        {{ $cuando === 'proximos' ? ajuste('eventos_vacios_proximos', 'No hay eventos programados por ahora') : ajuste('eventos_vacios_pasados', 'Todavía no hay eventos pasados') }}
                    </p>
                    <p class="mt-2 text-sm text-tenue">{{ ajuste('eventos_vacios_texto', 'Publicamos aquí la agenda del gremio.') }}</p>
                </div>
            @else
                <section @class([
                             'eventos-editorial-riel',
                             'eventos-editorial-riel--hay-mas' => $eventos->count() > 1,
                         ])
                         aria-label="Agenda de eventos">
                    <div id="eventos-riel"
                         class="eventos-editorial-riel__pista"
                         x-ref="pista"
                         tabindex="0">
                        @foreach ($eventos as $evento)
                            <x-publico.evento-ficha :evento="$evento" :realizado="$cuando === 'pasados'" />
                        @endforeach
                    </div>
                </section>

                <div class="mt-10">{{ $eventos->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.publico>

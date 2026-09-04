<x-layouts.publico :titulo="$evento->titulo.' — ASOBARES Quindío'"
                   :descripcion="Str::limit(strip_tags($evento->descripcion), 155)"
                   ogTipo="article"
                   :ogImagen="$evento->imagen ? Storage::disk('public')->url($evento->imagen) : null">

    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $evento->titulo,
            'description' => strip_tags((string) $evento->descripcion),
            'startDate' => $evento->fecha_inicio->toIso8601String(),
            'endDate' => $evento->fecha_fin?->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => $evento->lugar ? ['@type' => 'Place', 'name' => $evento->lugar] : null,
            'organizer' => ['@type' => 'Organization', 'name' => ajuste('sitio_nombre'), 'url' => route('inicio')],
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $evento->precio,
                'priceCurrency' => 'COP',
                'url' => route('eventos.show', $evento),
            ],
        ]);
    @endphp

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    <article class="revelar mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8" data-revelar>
        <a href="{{ route('eventos.index') }}" class="enlace-accion relative inline-block text-sm text-apagado after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento"><x-publico.flecha direccion="izquierda" />&nbsp;Todos los eventos</a>

        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-marca-500/15 px-3 py-1 font-medium text-acento-fuerte">{{ $evento->tipo->getLabel() }}</span>
            <span class="rounded-full border border-linea px-3 py-1 text-tenue">
                {{ $evento->esGratuito() ? 'Entrada libre' : pesos($evento->precio) }}
            </span>
            @unless ($evento->esFuturo())
                <span class="rounded-full border border-linea px-3 py-1 text-apagado">Evento realizado</span>
            @endunless
        </div>

        <h1 class="mt-4 font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl">{{ $evento->titulo }}</h1>

        @if ($evento->imagen)
            <div class="tarjeta-escena group mt-7 overflow-hidden rounded-[1.75rem] border border-linea bg-superficie-alta"
                 x-data="escena"
                 x-on:pointermove="seguir($event)"
                 x-on:pointerleave="salir()"
                 x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`">
                <img src="{{ Storage::disk('public')->url($evento->imagen) }}" alt=""
                     width="1200" height="675" decoding="async"
                     style="view-transition-name: portada-evento-{{ $evento->id }}"
                     class="imagen-viva imagen-inclinable aspect-video w-full object-cover">
            </div>
        @endif

        <div class="mt-10 grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="space-y-4 text-base leading-relaxed text-suave">
                    @foreach (array_filter(explode("\n", (string) $evento->descripcion)) as $parrafo)
                        <p class="text-pretty">{{ trim($parrafo) }}</p>
                    @endforeach
                </div>
            </div>

            <aside class="space-y-5">
                <div class="vidrio rounded-[1.5rem] p-6">
                    <dl class="space-y-3.5 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-apagado">Cuándo</dt>
                            <dd class="mt-0.5 text-tinta">
                                {{ $evento->fecha_inicio->translatedFormat('l d \d\e F, Y') }}<br>
                                <span class="text-tenue">{{ $evento->fecha_inicio->format('g:i a') }}</span>
                                @if ($evento->fecha_fin)
                                    <span class="text-tenue">– {{ $evento->fecha_fin->format('g:i a') }}</span>
                                @endif
                            </dd>
                        </div>
                        @if ($evento->lugar)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-apagado">Dónde</dt>
                                <dd class="mt-0.5 text-tinta">{{ $evento->lugar }}</dd>
                            </div>
                        @endif
                        @if ($evento->cupos)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-apagado">Cupos</dt>
                                <dd class="mt-0.5 text-tinta">
                                    {{ $evento->cuposDisponibles() }} disponibles de {{ $evento->cupos }}
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if ($evento->delegaRegistroExterno())
                        <x-publico.boton :href="$evento->enlace_externo" target="_blank" rel="noopener" class="mt-6 w-full">
                            Registrarme en la Nacional&nbsp;<x-publico.flecha direccion="externa" />
                        </x-publico.boton>
                        <p class="mt-2.5 text-xs text-apagado">
                            La inscripción de este evento la gestiona directamente Asobares Colombia.
                        </p>
                    @elseif ($evento->admiteInscripciones())
                        <x-publico.boton href="#inscripcion" class="mt-6 w-full">
                            {{ $evento->esGratuito() ? 'Inscribirme' : 'Inscribirme y pagar' }}
                        </x-publico.boton>
                    @else
                        <p class="mt-6 rounded-xl border border-linea px-4 py-3 text-center text-sm text-apagado">
                            Las inscripciones están cerradas.
                        </p>
                    @endif
                </div>
            </aside>
        </div>

        {{-- Inscripción --}}
        @if ($evento->admiteInscripciones())
            <section id="inscripcion" class="tarjeta mt-14 p-7 sm:p-9" aria-labelledby="titulo-inscripcion">
                <h2 id="titulo-inscripcion" class="font-display text-xl font-semibold">Inscríbete</h2>
                <p class="mt-2 text-sm text-tenue">
                    @if ($evento->esGratuito())
                        Déjanos tus datos y te confirmamos el cupo por correo.
                    @else
                        Al enviar el formulario pasarás a la pasarela de pago. Tu cupo se confirma cuando el pago sea aprobado.
                    @endif
                </p>

                @if (session('exito'))
                    <x-publico.alerta class="mt-5">{{ session('exito') }}</x-publico.alerta>
                @endif
                @if (session('error'))
                    <x-publico.alerta tipo="error" class="mt-5">{{ session('error') }}</x-publico.alerta>
                @endif

                <form method="POST" action="{{ route('eventos.inscribir', $evento) }}" class="mt-7 space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-publico.campo nombre="nombre" etiqueta="Nombre completo" requerido />
                        <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                        <x-publico.campo nombre="telefono" etiqueta="Teléfono" tipo="tel" requerido />
                        <x-publico.campo nombre="establecimiento" etiqueta="Establecimiento"
                                         ayuda="Opcional, si vienes en representación de un negocio." />
                    </div>

                    <x-publico.habeas-data />

                    <x-publico.boton class="w-full sm:w-auto">
                        {{ $evento->esGratuito() ? 'Confirmar inscripción' : 'Continuar al pago de '.pesos($evento->precio) }}
                    </x-publico.boton>
                </form>
            </section>
        @endif
    </article>
</x-layouts.publico>

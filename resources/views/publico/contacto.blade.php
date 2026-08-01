@php
    $whatsapp = enlaceWhatsapp(ajuste('contacto_whatsapp'), 'Hola, escribo desde la página de ASOBARES Quindío.');
@endphp

<x-layouts.publico titulo="Contacto y PQR — ASOBARES Quindío"
                   descripcion="Escríbenos: contacto general, peticiones, quejas y reclamos, propuestas de alianza o solicitud para entrar a la bolsa de proveedores.">

    <x-publico.hero titulo="Hablemos" compacto
                    subtitulo="Contacto general, PQR, propuestas de alianza o solicitud para entrar a la bolsa de proveedores." />

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-5">

            {{-- Formulario --}}
            <section id="formulario" class="lg:col-span-3" aria-labelledby="titulo-formulario">
                <div class="tarjeta p-7 sm:p-8">
                    <h2 id="titulo-formulario" class="font-display text-xl font-semibold">Escríbenos</h2>

                    @if (session('radicado'))
                        <x-publico.alerta class="mt-5">
                            <span class="block">{{ session('exito') }}</span>
                            <span class="mt-3 block rounded-lg border border-emerald-500/30 bg-noche-950 px-4 py-3 font-mono text-base font-semibold tracking-wide text-emerald-200">
                                {{ session('radicado') }}
                            </span>
                            <span class="mt-2 block text-xs opacity-80">Guarda este número: con él puedes hacerle seguimiento a tu solicitud.</span>
                        </x-publico.alerta>
                    @elseif (session('exito'))
                        <x-publico.alerta class="mt-5">{{ session('exito') }}</x-publico.alerta>
                    @endif

                    <form method="POST" action="{{ route('contacto.store') }}" class="mt-6 space-y-5">
                        @csrf

                        <x-publico.campo nombre="tipo" etiqueta="¿Sobre qué nos escribes?" tipo="select" requerido
                                         :opciones="collect($tipos)->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])->all()"
                                         ayuda="Si eliges PQR, el sistema genera un número de radicado." />

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-publico.campo nombre="nombre" etiqueta="Tu nombre" requerido />
                            <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                        </div>

                        <x-publico.campo nombre="telefono" etiqueta="Teléfono" tipo="tel" />

                        <x-publico.campo nombre="mensaje" etiqueta="Mensaje" tipo="textarea" requerido filas="5"
                                         placeholder="Cuéntanos con el mayor detalle posible." />

                        <x-publico.habeas-data />

                        <button type="submit"
                                class="w-full rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600 sm:w-auto">
                            Enviar mensaje
                        </button>
                    </form>
                </div>
            </section>

            {{-- Datos de la oficina --}}
            <aside class="space-y-5 lg:col-span-2">
                <div class="tarjeta p-6">
                    <h2 class="font-display text-base font-semibold">La oficina</h2>
                    <address class="mt-4 space-y-3 text-sm not-italic text-noche-200">
                        <p>{{ ajuste('contacto_direccion') }}<br>{{ ajuste('contacto_ciudad') }}</p>
                        <p>
                            <a href="mailto:{{ ajuste('contacto_correo') }}" class="text-marca-400 hover:text-marca-300">
                                {{ ajuste('contacto_correo') }}
                            </a>
                        </p>
                        @if ($whatsapp)
                            <p>
                                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="text-marca-400 hover:text-marca-300">
                                    WhatsApp {{ ajuste('contacto_whatsapp_visible') }}
                                </a>
                            </p>
                        @endif
                        <p>
                            <a href="https://instagram.com/{{ ajuste('contacto_instagram') }}" target="_blank" rel="noopener"
                               class="text-marca-400 hover:text-marca-300">
                                &#64;{{ ajuste('contacto_instagram') }}
                            </a>
                        </p>
                    </address>
                </div>

                <x-publico.mapa
                    :lat="(float) ajuste('contacto_lat', 4.5378)"
                    :lng="(float) ajuste('contacto_lng', -75.6757)"
                    :zoom="16" alto="h-72"
                    :puntos="[[
                        'lat' => (float) ajuste('contacto_lat', 4.5378),
                        'lng' => (float) ajuste('contacto_lng', -75.6757),
                        'nombre' => ajuste('sitio_nombre'),
                        'html' => '<strong>'.e(ajuste('sitio_nombre')).'</strong><br>'.e(ajuste('contacto_direccion')),
                    ]]" />
            </aside>
        </div>
    </div>
</x-layouts.publico>

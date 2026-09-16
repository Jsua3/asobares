@php
    $whatsapp = enlaceWhatsapp(ajuste('contacto_whatsapp'), 'Hola, escribo desde la página de ASOBARES Quindío.');
@endphp

<x-layouts.publico :titulo="ajuste('seo_contacto_titulo', 'Contacto y PQR — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_contacto_descripcion', 'Escríbenos: contacto general, peticiones, quejas y reclamos, propuestas de alianza o solicitud para entrar a la bolsa de proveedores.')">

    @push('cabeza')
        @vite(['resources/css/gremio-editorial.css'])
    @endpush

    <div class="gremio-editorial gremio-editorial--conversacion">
        <div class="gremio-editorial-cuerpo">

            <header class="gremio-editorial-apertura revelar" data-revelar>
                <x-publico.folio-gremio numero="03" />
                <h1>{{ ajuste('contacto_titulo_pagina', 'Hablemos') }}</h1>
                <p class="gremio-editorial-entradilla">{{ ajuste('contacto_subtitulo', 'Contacto general, PQR, propuestas de alianza o solicitud para entrar a la bolsa de proveedores.') }}</p>
                <ul class="gremio-editorial-motivos">
                    @foreach ($tipos as $tipo)
                        <li><a href="#formulario">{{ $tipo->getLabel() }}</a></li>
                    @endforeach
                </ul>
            </header>

            <div class="gremio-editorial-canales revelar" data-revelar>
                @if ($whatsapp)
                    <div class="gremio-editorial-canal gremio-editorial-canal--principal">
                        <span class="gremio-editorial-canal__n" aria-hidden="true">01</span>
                        <p class="gremio-editorial-canal__rotulo">WhatsApp</p>
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="enlace-accion">
                            {{ ajuste('contacto_whatsapp_visible') }}
                        </a>
                        <p class="gremio-editorial-canal__aviso">{{ ajuste('contacto_whatsapp_aviso') }}</p>
                    </div>
                @endif
                <div class="gremio-editorial-canal gremio-editorial-canal--largo">
                    <span class="gremio-editorial-canal__n" aria-hidden="true">02</span>
                    <p class="gremio-editorial-canal__rotulo">Correo</p>
                    {{-- El corte, si hace falta, va después de la arroba: partir el dominio
                         por la última letra («asobares.or / g») no se lee como un correo. --}}
                    <a href="mailto:{{ ajuste('contacto_correo') }}" class="enlace-accion">{{ Str::before(ajuste('contacto_correo'), '@') }}@<wbr>{{ Str::after(ajuste('contacto_correo'), '@') }}</a>
                </div>
                <div class="gremio-editorial-canal gremio-editorial-canal--largo">
                    <span class="gremio-editorial-canal__n" aria-hidden="true">03</span>
                    <p class="gremio-editorial-canal__rotulo">{{ ajuste('contacto_oficina_titulo', 'La oficina') }}</p>
                    <p>{{ ajuste('contacto_direccion') }}<br>{{ ajuste('contacto_ciudad') }}</p>
                </div>
                <div class="gremio-editorial-canal">
                    <span class="gremio-editorial-canal__n" aria-hidden="true">04</span>
                    <p class="gremio-editorial-canal__rotulo">Instagram</p>
                    <a href="https://instagram.com/{{ ajuste('contacto_instagram') }}" target="_blank" rel="noopener" class="enlace-accion">
                        &#64;{{ ajuste('contacto_instagram') }}
                    </a>
                </div>
            </div>

            <section id="formulario" class="gremio-editorial-formulario revelar" data-revelar aria-labelledby="titulo-formulario">
                <h2 id="titulo-formulario">{{ ajuste('contacto_formulario_titulo', 'Escríbenos') }}</h2>

                @if (session('radicado'))
                    <x-publico.alerta class="mt-5">
                        <span class="block">{{ session('exito') }}</span>
                        <span class="mt-3 block rounded-lg border border-exito-linea bg-fondo px-4 py-3 font-mono text-base font-semibold tracking-wide text-exito-suave">
                            {{ session('radicado') }}
                        </span>
                        <span class="mt-2 block text-xs opacity-80">Guarda este número: con él puedes hacerle seguimiento a tu solicitud.</span>
                    </x-publico.alerta>
                @elseif (session('exito'))
                    <x-publico.alerta class="mt-5">{{ session('exito') }}</x-publico.alerta>
                @endif

                <form method="POST" action="{{ route('contacto.store') }}" class="space-y-6">
                    @csrf

                    <x-publico.campo nombre="tipo" etiqueta="¿Sobre qué nos escribes?" tipo="select" requerido
                                     :opciones="collect($tipos)->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])->all()"
                                     ayuda="Si eliges PQR, el sistema genera un número de radicado." />

                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-publico.campo nombre="nombre" etiqueta="Tu nombre" requerido />
                        <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                    </div>

                    <x-publico.campo nombre="telefono" etiqueta="Teléfono" tipo="tel" />

                    <x-publico.campo nombre="mensaje" etiqueta="Mensaje" tipo="textarea" requerido filas="5"
                                     placeholder="Cuéntanos con el mayor detalle posible." />

                    <x-publico.habeas-data />

                    <x-publico.boton class="w-full sm:w-auto">
                        Enviar mensaje
                    </x-publico.boton>
                </form>
            </section>

            <section class="gremio-editorial-mapa revelar" data-revelar aria-labelledby="mapa-oficina">
                <h2 id="mapa-oficina">{{ ajuste('contacto_oficina_titulo', 'La oficina') }}</h2>
                <div class="overflow-hidden rounded-sm">
                    <x-publico.mapa
                        :lat="(float) ajuste('contacto_lat', 4.5378)"
                        :lng="(float) ajuste('contacto_lng', -75.6757)"
                        :zoom="16" alto="h-64"
                        :puntos="[[
                            'lat' => (float) ajuste('contacto_lat', 4.5378),
                            'lng' => (float) ajuste('contacto_lng', -75.6757),
                            'nombre' => ajuste('sitio_nombre'),
                            'html' => '<strong>'.e(ajuste('sitio_nombre')).'</strong><br>'.e(ajuste('contacto_direccion')),
                        ]]" />
                </div>
            </section>
        </div>
    </div>
</x-layouts.publico>

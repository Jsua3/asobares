@php
    $whatsapp = enlaceWhatsapp(ajuste('contacto_whatsapp'), 'Hola, quiero afiliar mi establecimiento a ASOBARES Quindío.');
@endphp

<x-layouts.publico :titulo="ajuste('afiliate_titulo').' — ASOBARES Quindío'"
                   :descripcion="ajuste('afiliate_intro')">

    <x-publico.hero :titulo="ajuste('afiliate_titulo')" :subtitulo="ajuste('afiliate_intro')" atmosfera />

    <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">

        {{-- Beneficios en grande --}}
        <section class="revelar" data-revelar aria-labelledby="beneficios">
            <h2 id="beneficios" class="font-display text-2xl font-bold">Lo que incluye la afiliación</h2>
            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                @foreach ($beneficios as $indice => $beneficio)
                    <div @class([
                        'p-6',
                        'vidrio rounded-[1.5rem]' => $indice === 0,
                        'tarjeta tarjeta-hover' => $indice !== 0,
                    ])>
                        {{-- Marca de agua: numera visualmente, no aporta nada al lector de pantalla. --}}
                        <span aria-hidden="true" class="font-display text-3xl font-bold text-marca-500/30">0{{ $indice + 1 }}</span>
                        <h3 class="mt-2 font-display text-lg font-semibold">{{ $beneficio->titulo }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-tenue">{{ $beneficio->descripcion }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Cómo funciona --}}
        <section class="revelar mt-16" data-revelar aria-labelledby="como">
            <h2 id="como" class="font-display text-2xl font-bold">Cómo funciona</h2>
            <ol class="mt-7 space-y-4">
                @foreach (array_filter(explode("\n", (string) ajuste('afiliate_como_funciona'))) as $indice => $paso)
                    <li class="tarjeta tarjeta-hover flex items-start gap-4 p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-marca-500/15 font-display text-sm font-bold text-acento">
                            {{ $indice + 1 }}
                        </span>
                        <span class="pt-1 text-sm leading-relaxed text-suave">{{ trim($paso) }}</span>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Formulario --}}
        <section id="formulario" class="revelar vidrio mt-16 rounded-[1.75rem] p-7 sm:p-9" data-revelar aria-labelledby="titulo-formulario">
            <h2 id="titulo-formulario" class="font-display text-2xl font-bold">Déjanos tus datos</h2>
            <p class="mt-2 text-sm text-tenue">Te contactamos para agendar la visita a tu establecimiento.</p>

            @if (session('exito'))
                <x-publico.alerta class="mt-6">
                    {{ session('exito') }}
                    @if ($whatsapp)
                        <x-publico.boton :href="$whatsapp" target="_blank" rel="noopener" class="mt-3">
                            Escribirnos por WhatsApp
                        </x-publico.boton>
                {{-- OBS3-14: el aviso honesto. Al otro lado hay una persona,
                     no un bot; prometer inmediatez con un «ya» es lo que hace
                     que el silencio de una noche se lea como abandono. --}}
                <p class="mt-2 text-2xs text-apagado">{{ ajuste('contacto_whatsapp_aviso') }}</p>
                    @endif
                </x-publico.alerta>
            @endif

            <form method="POST" action="{{ route('afiliate.store') }}" class="mt-7 space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-publico.campo nombre="nombre" etiqueta="Tu nombre" requerido />
                    <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                    <x-publico.campo nombre="telefono" etiqueta="Teléfono o WhatsApp" tipo="tel" />
                </div>

                <x-publico.campo nombre="mensaje" etiqueta="Cuéntanos de tu establecimiento" tipo="textarea" requerido
                                 placeholder="Nombre del negocio, municipio, tipo de establecimiento y desde cuándo está abierto."
                                 ayuda="Entre más nos cuentes, mejor preparamos la visita." />

                <x-publico.habeas-data />

                <x-publico.boton class="w-full sm:w-auto">
                    Enviar solicitud
                </x-publico.boton>
            </form>

            @if ($whatsapp)
                <p class="mt-6 border-t border-linea pt-6 text-sm text-tenue">
                    ¿Prefieres hablar directo?
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="enlace-accion text-acento hover:text-acento-fuerte">
                        Escríbenos por WhatsApp al {{ ajuste('contacto_whatsapp_visible') }}
                    </a>
                </p>
                <p class="mt-1 text-2xs text-apagado">{{ ajuste('contacto_whatsapp_aviso') }}</p>
            @endif
        </section>
    </div>
</x-layouts.publico>

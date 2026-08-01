@php
    // Cada línea de trabajo viene como «Nombre | Descripción | Programa; Programa»
    $lineas = collect(explode("\n", (string) ajuste('quienes_lineas')))
        ->filter()
        ->map(function (string $fila): array {
            [$nombre, $descripcion, $programas] = array_pad(array_map('trim', explode('|', $fila)), 3, '');

            return [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'programas' => array_filter(array_map('trim', explode(';', $programas))),
            ];
        });
@endphp

<x-layouts.publico titulo="Quiénes somos — ASOBARES Capítulo Quindío"
                   descripcion="El gremio que representa, fortalece y dinamiza el sector nocturno, gastronómico y de entretenimiento del Quindío.">

    <x-publico.hero titulo="Representar para proponer" :subtitulo="ajuste('quienes_mision')">
        <x-slot:encima>
            <p class="antetitulo mb-4 text-marca-400">{{ ajuste('sitio_eslogan') }}</p>
        </x-slot:encima>
    </x-publico.hero>

    <div class="mx-auto max-w-4xl space-y-14 px-4 py-14 sm:px-6 lg:px-8">

        <section aria-labelledby="historia">
            <h2 id="historia" class="font-display text-2xl font-bold">Cómo nació el capítulo</h2>
            <p class="mt-4 text-base leading-relaxed text-noche-200 text-pretty">{{ ajuste('quienes_historia') }}</p>
        </section>

        <section aria-labelledby="hacemos">
            <h2 id="hacemos" class="font-display text-2xl font-bold">Qué hace el gremio</h2>
            <p class="mt-4 text-base leading-relaxed text-noche-200 text-pretty">{{ ajuste('quienes_que_hacemos') }}</p>

            <blockquote class="trama-puntos mt-7 rounded-2xl border border-marca-500/25 p-7">
                <p class="font-display text-lg font-bold leading-snug text-balance sm:text-xl">
                    «{{ ajuste('quienes_vision') }}»
                </p>
            </blockquote>
        </section>

        {{-- Las tres líneas del plan de acción --}}
        <section aria-labelledby="lineas">
            <h2 id="lineas" class="font-display text-2xl font-bold">Nuestras líneas de trabajo</h2>
            <p class="mt-2 text-sm text-noche-300">
                Todo lo que hace el capítulo se organiza alrededor de estos tres ejes.
            </p>

            <div class="mt-7 space-y-5">
                @foreach ($lineas as $indice => $linea)
                    <article class="tarjeta p-7">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-marca-500/15 font-display text-sm font-bold text-marca-400">
                                {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-display text-xl font-bold">{{ $linea['nombre'] }}</h3>
                                <p class="mt-2.5 text-sm leading-relaxed text-noche-300 text-pretty">
                                    {{ $linea['descripcion'] }}
                                </p>

                                @if ($linea['programas'])
                                    <p class="antetitulo mt-5 text-noche-400">Programas</p>
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($linea['programas'] as $programa)
                                            <li class="rounded-lg border border-white/10 px-3 py-1.5 text-xs text-noche-100">
                                                {{ $programa }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="armenia">
            <h2 id="armenia" class="font-display text-2xl font-bold">Armenia Nocturna</h2>
            <p class="mt-4 text-base leading-relaxed text-noche-200 text-pretty">
                {{ ajuste('quienes_estrategia_armenia') }}
            </p>
        </section>

        <section aria-labelledby="direccion">
            <h2 id="direccion" class="font-display text-2xl font-bold">La dirección</h2>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="tarjeta p-6">
                    <p class="antetitulo text-noche-400">Presidente</p>
                    <p class="mt-2 font-display text-lg font-bold">{{ ajuste('quienes_presidente') }}</p>
                </div>
                <div class="tarjeta p-6">
                    <p class="antetitulo text-noche-400">Directora ejecutiva</p>
                    <p class="mt-2 font-display text-lg font-bold">{{ ajuste('quienes_directora') }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-noche-400">Capítulo fundado el {{ ajuste('quienes_fundacion') }} en Armenia.</p>
        </section>

        <section aria-labelledby="beneficios">
            <h2 id="beneficios" class="font-display text-2xl font-bold">Beneficios del afiliado</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach ($beneficios as $beneficio)
                    <div class="tarjeta p-5">
                        <h3 class="font-display text-base font-bold">{{ $beneficio->titulo }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-noche-300">{{ $beneficio->descripcion }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tarjeta p-8 text-center">
            <h2 class="font-display text-xl font-bold">Somos el capítulo regional de Asobares Colombia</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-noche-300">
                Aterrizamos en el Quindío los programas nacionales del gremio:
                {{ collect(array_filter(explode("\n", (string) ajuste('quienes_programas_nacionales'))))->map(fn ($p) => trim($p))->join(', ', ' y ') }}.
                Lo que no es local se gestiona directamente con la Nacional.
            </p>
            <a href="{{ ajuste('url_nacional') }}" target="_blank" rel="noopener"
               class="mt-6 inline-block rounded-xl border border-white/15 px-6 py-2.5 text-sm font-semibold transition-colors hover:border-marca-500/50">
                Ir a Asobares Nacional ↗
            </a>
        </section>
    </div>
</x-layouts.publico>

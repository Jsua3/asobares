<x-layouts.publico titulo="Quiénes somos — ASOBARES Capítulo Quindío"
                   descripcion="El gremio de la vida nocturna del Quindío: representación ante las instituciones, formación y acompañamiento para bares, gastrobares, cafés y discotecas.">

    <x-publico.hero titulo="Representar para proponer"
                    :subtitulo="ajuste('quienes_mision')" />

    <div class="mx-auto max-w-4xl space-y-14 px-4 py-14 sm:px-6 lg:px-8">

        <section aria-labelledby="historia">
            <h2 id="historia" class="font-display text-2xl font-bold">Cómo nació el capítulo</h2>
            <p class="mt-4 text-base leading-relaxed text-noche-200 text-pretty">{{ ajuste('quienes_historia') }}</p>
        </section>

        <section aria-labelledby="hacemos">
            <h2 id="hacemos" class="font-display text-2xl font-bold">Qué hace el gremio</h2>
            <p class="mt-4 text-base leading-relaxed text-noche-200 text-pretty">{{ ajuste('quienes_que_hacemos') }}</p>
        </section>

        <section aria-labelledby="direccion">
            <h2 id="direccion" class="font-display text-2xl font-bold">La dirección</h2>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="tarjeta p-6">
                    <p class="text-xs uppercase tracking-wide text-noche-400">Presidente</p>
                    <p class="mt-1.5 font-display text-lg font-semibold">{{ ajuste('quienes_presidente') }}</p>
                </div>
                <div class="tarjeta p-6">
                    <p class="text-xs uppercase tracking-wide text-noche-400">Directora ejecutiva</p>
                    <p class="mt-1.5 font-display text-lg font-semibold">{{ ajuste('quienes_directora') }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-noche-400">Capítulo fundado el {{ ajuste('quienes_fundacion') }} en Armenia.</p>
        </section>

        <section aria-labelledby="programas">
            <h2 id="programas" class="font-display text-2xl font-bold">Programas</h2>
            <ul class="mt-5 space-y-3">
                @foreach (array_filter(explode("\n", (string) ajuste('quienes_programas'))) as $programa)
                    <li class="tarjeta flex items-start gap-3 p-5 text-sm leading-relaxed text-noche-200">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-marca-500"></span>
                        <span>{{ trim($programa) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="beneficios">
            <h2 id="beneficios" class="font-display text-2xl font-bold">Beneficios del afiliado</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach ($beneficios as $beneficio)
                    <div class="tarjeta p-5">
                        <h3 class="font-display text-base font-semibold">{{ $beneficio->titulo }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-noche-300">{{ $beneficio->descripcion }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="tarjeta p-8 text-center">
            <h2 class="font-display text-xl font-semibold">Somos el capítulo regional de Asobares Colombia</h2>
            <p class="mt-2 text-sm text-noche-300">
                Los eventos y programas nacionales se gestionan directamente con la Nacional.
            </p>
            <a href="{{ ajuste('url_nacional') }}" target="_blank" rel="noopener"
               class="mt-5 inline-block rounded-xl border border-white/15 px-6 py-2.5 text-sm font-semibold hover:border-marca-500/50">
                Ir a Asobares Nacional ↗
            </a>
        </section>
    </div>
</x-layouts.publico>

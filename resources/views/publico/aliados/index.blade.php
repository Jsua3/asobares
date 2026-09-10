<x-layouts.publico titulo="Aliados y convenios — ASOBARES Capítulo Quindío"
                   descripcion="Aliados institucionales y convenios comerciales publicados por ASOBARES Capítulo Quindío.">

    <x-publico.hero :titulo="ajuste('portada_aliados_titulo', 'Aliados del capítulo')"
                    :subtitulo="ajuste('mi_cuenta_convenios_texto', 'El detalle de cada convenio es información privada de los afiliados. No aparece en el sitio público.')"
                    compacto
                    atmosfera>
        <x-slot:encima>
            <p class="antetitulo mb-4 text-acento">{{ ajuste('sitio_eslogan') }}</p>
        </x-slot:encima>
    </x-publico.hero>

    <main class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        @if ($aliadosInstitucionales->isEmpty() && $aliadosComerciales->isEmpty())
            <section class="tarjeta revelar p-8 text-center sm:p-10" data-revelar aria-labelledby="aliados-vacio">
                <p class="antetitulo text-acento">Aliados</p>
                <h2 id="aliados-vacio" class="mt-3 font-display text-2xl font-bold">No hay aliados publicados</h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-tenue">
                    Cuando el gremio publique entidades y convenios activos, aparecerán en esta página.
                </p>
                <div class="mt-7">
                    <x-publico.boton :href="route('afiliate')">Afiliar mi establecimiento</x-publico.boton>
                </div>
            </section>
        @else
            @if ($aliadosInstitucionales->isNotEmpty())
                <section class="revelar" data-revelar aria-labelledby="aliados-institucionales">
                    <p class="antetitulo text-acento">{{ ajuste('portada_aliados_institucionales', 'Respaldo institucional') }}</p>
                    <h2 id="aliados-institucionales" class="mt-3 font-display text-2xl font-bold text-balance sm:text-3xl">
                        Entidades que respaldan al gremio
                    </h2>

                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($aliadosInstitucionales as $aliado)
                            @include('publico.aliados.partials.tarjeta', ['aliado' => $aliado])
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($aliadosComerciales->isNotEmpty())
                <section @class(['revelar', 'mt-16' => $aliadosInstitucionales->isNotEmpty()]) data-revelar aria-labelledby="aliados-comerciales">
                    <p class="antetitulo text-acento">{{ ajuste('portada_aliados_comerciales', 'Convenios para afiliados') }}</p>
                    <h2 id="aliados-comerciales" class="mt-3 font-display text-2xl font-bold text-balance sm:text-3xl">
                        Convenios para afiliados
                    </h2>
                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-tenue">
                        El detalle comercial de cada convenio se consulta desde Mi Cuenta.
                    </p>

                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($aliadosComerciales as $aliado)
                            @include('publico.aliados.partials.tarjeta', ['aliado' => $aliado])
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="vidrio revelar mt-16 flex flex-col gap-5 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8" data-revelar aria-labelledby="aliados-cta">
                <div>
                    <p class="antetitulo text-acento">Afiliación</p>
                    <h2 id="aliados-cta" class="mt-2 font-display text-2xl font-bold text-balance">
                        Accede a los beneficios del gremio
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-tenue">
                        La información privada de convenios está reservada para establecimientos afiliados.
                    </p>
                </div>
                <x-publico.boton :href="route('afiliate')" class="shrink-0">Afiliar mi establecimiento</x-publico.boton>
            </section>
        @endif
    </main>
</x-layouts.publico>

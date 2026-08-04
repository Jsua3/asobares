<x-layouts.publico titulo="Página no encontrada — ASOBARES Quindío"
                   descripcion="La página que buscas no existe o cambió de dirección.">

    <div class="resplandor-marca flex min-h-[65vh] items-center">
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
            <p class="font-display text-7xl font-bold text-marca-500 sm:text-8xl">404</p>

            <h1 class="mt-6 font-display text-2xl font-bold text-balance sm:text-3xl">
                Esta página cerró más temprano de lo previsto
            </h1>

            <p class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-tenue text-pretty">
                La dirección que buscas no existe o cambió. Prueba desde el directorio de establecimientos
                o vuelve al inicio.
            </p>

            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('inicio') }}"
                   class="rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                    Volver al inicio
                </a>
                <a href="{{ route('directorio.index') }}"
                   class="rounded-xl border border-linea-fuerte px-6 py-3 text-sm font-semibold transition-colors hover:border-marca-500/50">
                    Ver el directorio
                </a>
            </div>
        </div>
    </div>
</x-layouts.publico>

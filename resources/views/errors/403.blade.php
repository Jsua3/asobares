<x-layouts.publico titulo="Acceso no permitido — ASOBARES Quindío"
                   descripcion="No tienes permiso para abrir esta página o el enlace que usaste ya caducó.">

    <div class="resplandor-marca flex min-h-[65vh] items-center">
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
            <p class="font-display text-7xl font-bold text-marca-500 sm:text-8xl">403</p>

            <h1 class="mt-6 font-display text-2xl font-bold text-balance sm:text-3xl">
                No puedes abrir esta página
            </h1>

            <p class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-tenue text-pretty">
                El enlace que usaste caducó o tu cuenta no tiene permiso para ver esta sección.
                Vuelve al inicio o busca desde el directorio de establecimientos.
            </p>

            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <x-publico.boton :href="route('inicio')">
                    Volver al inicio
                </x-publico.boton>
                <x-publico.boton variante="contorno" :href="route('directorio.index')">
                    Ver el directorio
                </x-publico.boton>
            </div>
        </div>
    </div>
</x-layouts.publico>

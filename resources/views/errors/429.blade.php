{{--
    Sale sobre todo con los `throttle:6,1` de los formularios públicos. Igual
    que la 419, «Volver a la página» usa solo la ruta de la dirección anterior
    para no sacar a nadie del sitio. Lo fija PaginasDeErrorTest.
--}}
<x-layouts.publico titulo="Demasiados intentos — ASOBARES Quindío"
                   descripcion="Hiciste muchos envíos seguidos. Espera un minuto y vuelve a intentarlo.">

    <div class="resplandor-marca flex min-h-[65vh] items-center">
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
            <p class="font-display text-7xl font-bold text-marca-500 sm:text-8xl">429</p>

            <h1 class="mt-6 font-display text-2xl font-bold text-balance sm:text-3xl">
                Demasiados intentos seguidos
            </h1>

            <p class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-tenue text-pretty">
                Para proteger los formularios limitamos cuántos envíos se pueden hacer en poco tiempo.
                Espera un minuto y vuelve a intentarlo.
            </p>

            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <x-publico.boton :href="url(url()->previousPath())">
                    Volver a la página
                </x-publico.boton>
                <x-publico.boton variante="contorno" :href="route('inicio')">
                    Volver al inicio
                </x-publico.boton>
            </div>
        </div>
    </div>
</x-layouts.publico>

{{--
    El caso real es un formulario público (afíliate, contacto) que se quedó
    abierto más de lo que dura la sesión. «Volver a la página» lleva a la
    dirección de la que vino el envío, pero solo su ruta: `previousPath()`
    descarta el dominio, así que un Referer ajeno no convierte el botón en un
    enlace fuera del sitio. Lo fija PaginasDeErrorTest.
--}}
<x-layouts.publico titulo="La página caducó — ASOBARES Quindío"
                   descripcion="La página estuvo abierta demasiado tiempo. Vuelve a abrirla y envía de nuevo.">

    <div class="resplandor-marca flex min-h-[65vh] items-center">
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
            <p class="font-display text-7xl font-bold text-marca-500 sm:text-8xl">419</p>

            <h1 class="mt-6 font-display text-2xl font-bold text-balance sm:text-3xl">
                La página estuvo abierta demasiado tiempo
            </h1>

            <p class="mx-auto mt-4 max-w-lg text-sm leading-relaxed text-tenue text-pretty">
                Por seguridad, los formularios caducan cuando pasan mucho rato abiertos, y este envío no se
                pudo recibir. Vuelve a la página, recárgala y envía de nuevo tus datos.
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

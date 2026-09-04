<x-layouts.publico :titulo="ajuste('proveedores_titulo').' — ASOBARES Quindío'"
                   descripcion="Bolsa de proveedores verificados para bares y gastrobares del Quindío: un beneficio para los establecimientos afiliados a ASOBARES.">

    <x-publico.hero :titulo="ajuste('proveedores_titulo')" :subtitulo="ajuste('proveedores_intro')" compacto atmosfera />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        @php
            $usuario = auth()->user();
            $esAfiliado = (bool) ($usuario?->esAsociado() && $usuario?->asociado_id);
        @endphp

        {{-- Esta página es la cara pública de la bolsa y no entrega ni un nombre
             ni un contacto: los datos viven en /mi-cuenta/proveedores, detrás de
             la sesión del afiliado. La URL sigue abierta a propósito —cerrarla
             entera mandaría a un login seco a quien llega desde un buscador y
             sacaría del índice una sección que hoy trae visitas—, así que
             cualquier dato que se agregue aquí hay que mirarlo dos veces. --}}
        <section class="revelar tarjeta p-8 sm:p-10" data-revelar>
            <h2 class="font-display text-xl font-bold">Un beneficio de estar afiliado</h2>
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-tenue">
                La secretaría verifica cada proveedor y anota la fecha de la última revisión, para que
                nadie llame a un número que ya no responde. El listado con nombres, WhatsApp y correos
                es para los establecimientos afiliados: aquí solo se ve de qué está hecho.
            </p>

            @if ($total > 0)
                <p class="mt-6 font-display text-4xl font-bold tracking-tight">{{ $total }}</p>
                <p class="text-sm text-apagado">
                    {{ $total === 1 ? 'proveedor verificado y al día' : 'proveedores verificados y al día' }}
                </p>
            @endif
        </section>

        {{-- Categorías: qué hay, cuánto hay, y nada más. --}}
        <section class="revelar mt-12" data-revelar aria-labelledby="categorias">
            <h2 id="categorias" class="font-display text-xl font-bold">Qué vas a encontrar</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categorias as $categoria)
                    <div class="tarjeta flex items-center gap-3 p-5">
                        <x-dynamic-component :component="$categoria->icono()" class="h-5 w-5 shrink-0 text-acento" />
                        <span class="flex-1 text-sm font-medium">{{ $categoria->getLabel() }}</span>
                        <span class="text-sm text-apagado">{{ $conteos[$categoria->value] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Dos salidas, y cuál se ve primero depende de quién mira. --}}
        <section class="revelar tarjeta-escena vidrio mt-16 rounded-[1.75rem] p-8 text-center" data-revelar>
            @if ($esAfiliado)
                <h2 class="font-display text-xl font-semibold">Ya estás afiliado</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    Entra al directorio completo con los contactos de cada proveedor.
                </p>
                <x-publico.boton :href="route('mi-cuenta.proveedores.index')" class="mt-6">
                    Ver el directorio
                </x-publico.boton>
            @else
                <h2 class="font-display text-xl font-semibold">¿Quieres los contactos?</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    El directorio con nombres, WhatsApp y correos es para los establecimientos afiliados
                    a ASOBARES Capítulo Quindío.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <x-publico.boton :href="route('afiliate')">Afiliar mi establecimiento</x-publico.boton>
                    <x-publico.boton variante="contorno" :href="route('mi-cuenta.entrar')">Ya soy afiliado</x-publico.boton>
                </div>
            @endif
        </section>

        <section class="revelar mt-8 text-center" data-revelar>
            <p class="text-sm text-tenue">
                ¿Le vendes al sector nocturno del Quindío?
                <a href="{{ route('proveedores.inscripcion') }}"
                   class="enlace-accion text-acento underline underline-offset-2 hover:text-acento-fuerte">Inscríbete en la bolsa</a>.
            </p>
        </section>
    </div>
</x-layouts.publico>

<x-layouts.publico :titulo="ajuste('seo_proveedores_titulo', ajuste('proveedores_titulo').' — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_proveedores_descripcion', 'Bolsa de proveedores verificados para bares y gastrobares del Quindío: un beneficio para los establecimientos afiliados a ASOBARES.')">

    @push('cabeza')
        @vite(['resources/css/proveedores-editorial.css'])
    @endpush

    <div class="proveedores-editorial">
        <section class="proveedores-editorial-hero" aria-labelledby="proveedores-titulo">
            <div class="proveedores-editorial-hero__plano"></div>
            <div class="proveedores-editorial-hero__foto" aria-hidden="true">
                <img src="{{ asset('img/proveedores/hero-proveedores.webp') }}" alt="" width="1672" height="941" decoding="async">
            </div>
            <div class="proveedores-editorial-hero__velo"></div>
            <div class="proveedores-editorial-hero__cuerpo">
                <span class="proveedores-editorial-eyebrow">Red de soluciones B2B</span>
                <h1 id="proveedores-titulo">{{ ajuste('proveedores_titulo') }}</h1>
                <p>{{ ajuste('proveedores_intro') }}</p>
            </div>
        </section>

    <div class="proveedores-editorial-cuerpo">

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
        <section class="proveedores-editorial-panel revelar" data-revelar>
            <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="font-display text-xl font-bold">{{ ajuste('proveedores_beneficio_titulo', 'Un beneficio de estar afiliado') }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-tenue">
                        {{ ajuste('proveedores_beneficio_texto', 'La secretaría verifica cada proveedor y anota la fecha de la última revisión, para que nadie llame a un número que ya no responde. El listado con nombres, WhatsApp y correos es para los establecimientos afiliados: aquí solo se ve de qué está hecho.') }}
                    </p>
                </div>

                @if ($total > 0)
                    <div class="proveedores-editorial-total">
                        <p class="font-display text-4xl font-bold tracking-tight">{{ $total }}</p>
                        <p class="mt-1 text-center text-xs font-medium text-current">
                            {{ $total === 1 ? 'proveedor al día' : 'proveedores al día' }}
                        </p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Categorías: qué hay, cuánto hay, y nada más. --}}
        <section class="revelar mt-12" data-revelar aria-labelledby="categorias">
            <h2 id="categorias" class="font-display text-xl font-bold">Qué vas a encontrar</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categorias as $categoria)
                    <div class="proveedores-editorial-categoria">
                        <span class="proveedores-editorial-categoria__icono">
                            <x-dynamic-component :component="$categoria->icono()" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold">{{ $categoria->getLabel() }}</span>
                            <span class="mt-1 block text-xs text-tenue">Disponibilidad verificada para afiliados</span>
                        </span>
                        <span class="font-display text-xl font-bold text-acento">{{ $conteos[$categoria->value] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Dos salidas, y cuál se ve primero depende de quién mira. --}}
        <section class="proveedores-editorial-cta revelar mt-16" data-revelar>
            @if ($esAfiliado)
                <h2 class="font-display text-xl font-semibold">{{ ajuste('proveedores_afiliado_titulo', 'Ya estás afiliado') }}</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    {{ ajuste('proveedores_afiliado_texto', 'Entra al directorio completo con los contactos de cada proveedor.') }}
                </p>
                <x-publico.boton :href="route('mi-cuenta.proveedores.index')" class="mt-6">
                    {{ ajuste('proveedores_afiliado_cta', 'Ver el directorio') }}
                </x-publico.boton>
            @else
                <h2 class="font-display text-xl font-semibold">{{ ajuste('proveedores_no_afiliado_titulo', '¿Quieres los contactos?') }}</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    {{ ajuste('proveedores_no_afiliado_texto', 'El directorio con nombres, WhatsApp y correos es para los establecimientos afiliados a ASOBARES Capítulo Quindío.') }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <x-publico.boton :href="route('afiliate')">{{ ajuste('proveedores_no_afiliado_cta', 'Afiliar mi establecimiento') }}</x-publico.boton>
                    <x-publico.boton variante="contorno" :href="route('mi-cuenta.entrar')">{{ ajuste('proveedores_no_afiliado_login_cta', 'Ya soy afiliado') }}</x-publico.boton>
                </div>
            @endif
        </section>

        <section class="proveedores-editorial-inscripcion revelar mt-8" data-revelar>
            <p class="text-sm text-tenue">
                {{ ajuste('proveedores_inscripcion_texto', '¿Le vendes al sector nocturno del Quindío?') }}
                <a href="{{ route('proveedores.inscripcion') }}"
                   class="enlace-accion text-acento underline underline-offset-2 hover:text-acento-fuerte">{{ ajuste('proveedores_inscripcion_cta', 'Inscríbete en la bolsa') }}</a>.
            </p>
        </section>
    </div>
    </div>
</x-layouts.publico>

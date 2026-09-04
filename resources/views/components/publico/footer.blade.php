@php
    $whatsapp = enlaceWhatsapp(ajuste('contacto_whatsapp'), 'Hola, escribo desde la página de ASOBARES Quindío.');
@endphp

<footer class="luz-ambiente mt-24 border-t border-linea bg-superficie">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

            <div class="lg:col-span-1">
                <x-publico.logo alto="h-10" />
                <p class="mt-5 font-display text-sm font-semibold uppercase tracking-[.18em] text-acento">
                    {{ ajuste('sitio_eslogan') }}
                </p>
                <p class="mt-3 text-sm leading-relaxed text-tenue">{{ ajuste('sitio_descripcion_corta') }}</p>
                <p class="mt-3 text-xs text-apagado">Fundado el {{ ajuste('quienes_fundacion') }}</p>
            </div>

            <div>
                <h2 class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">El gremio</h2>
                {{-- Sin `space-y-2.5`: dos objetivos de 44 px con paso de 30 se
                     solapan 14 y el segundo del DOM le roba el clic al primero.
                     El paso lo abre el propio `min-h-11` y queda en 44 exactos.
                     `mt-1` en vez de `mt-4` porque el objetivo ya trae 11,15 px
                     de aire sobre el texto. Coste declarado: la columna pasa de
                     148 a 220 px. --}}
                <ul class="mt-1 text-sm">
                    <li><a href="{{ route('quienes-somos') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Quiénes somos</a></li>
                    <li><a href="{{ route('afiliate') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Afíliate</a></li>
                    <li><a href="{{ route('boletin.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Boletín</a></li>
                    <li><a href="{{ route('eventos.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Eventos y capacitaciones</a></li>
                    @if ($enlaceNacional = enlaceSeguro(ajuste('url_nacional')))
                        <li><a href="{{ $enlaceNacional }}" rel="noopener" target="_blank" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Asobares Nacional&nbsp;<x-publico.flecha direccion="externa" /></a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h2 class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">Directorios</h2>
                <ul class="mt-1 text-sm">
                    <li><a href="{{ route('directorio.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Establecimientos</a></li>
                    <li><a href="{{ route('guia.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Abre tu negocio</a></li>
                    <li><a href="{{ route('empleo.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Bolsa de empleo</a></li>
                    <li><a href="{{ route('artistas.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Artistas</a></li>
                    <li><a href="{{ route('proveedores.index') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Proveedores</a></li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">Contacto</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-suave">
                    <li>{{ ajuste('contacto_direccion') }}</li>
                    <li>{{ ajuste('contacto_ciudad') }}</li>
                    <li>
                        <a href="mailto:{{ ajuste('contacto_correo') }}" class="enlace-accion flex min-h-11 items-center hover:text-acento">
                            {{ ajuste('contacto_correo') }}
                        </a>
                    </li>
                    @if ($whatsapp)
                        <li>
                            <a href="{{ $whatsapp }}" rel="noopener" target="_blank" class="enlace-accion flex min-h-11 items-center hover:text-acento">
                                WhatsApp {{ ajuste('contacto_whatsapp_visible') }}
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="https://instagram.com/{{ ajuste('contacto_instagram') }}" rel="noopener" target="_blank"
                           class="enlace-accion flex min-h-11 items-center hover:text-acento">
                            &#64;{{ ajuste('contacto_instagram') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-4 border-t border-linea pt-6 text-xs text-apagado sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ ajuste('sitio_nombre') }}. Todos los derechos reservados.</p>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ route('politica-de-datos') }}" class="enlace-accion flex min-h-11 items-center hover:text-acento">Política de tratamiento de datos</a>
                <a href="{{ route('contacto') }}" class="enlace-accion flex min-h-11 items-center hover:text-acento">Contacto y PQR</a>
            </div>
        </div>
    </div>
</footer>

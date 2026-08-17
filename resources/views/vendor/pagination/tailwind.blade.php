{{--
    Paginador del sitio público.

    Sustituye al que trae Laravel por dos motivos: aquel venía cableado en
    grises de Tailwind (2,63:1 sobre el fondo oscuro, más una caja blanca sobre
    página casi negra) y, sin carpeta `lang/`, mostraba las claves crudas
    «pagination.previous» y «pagination.next» junto a un «Showing … results» en
    inglés dentro de un sitio íntegramente en español.
--}}
@php
    $pastilla = 'inline-flex items-center px-4 py-2 text-sm font-medium';
    $enlace = "{$pastilla} bg-superficie text-tenue transition-colors duration-(--duracion-boton) ease-color hover:bg-superficie-alta hover:text-fuerte";
    $inerte = "{$pastilla} bg-superficie text-apagado cursor-not-allowed";
    $actual = "{$pastilla} bg-marca-500 text-white";
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación">

        {{-- Móvil: solo anterior y siguiente --}}
        <div class="flex items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="{{ $inerte }} rounded-xl border border-linea" aria-disabled="true">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="{{ $enlace }} rounded-xl border border-linea">Anterior</a>
            @endif

            <span class="text-xs text-apagado">
                {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="{{ $enlace }} rounded-xl border border-linea">Siguiente</a>
            @else
                <span class="{{ $inerte }} rounded-xl border border-linea" aria-disabled="true">Siguiente</span>
            @endif
        </div>

        {{-- Escritorio --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between sm:gap-4">
            <p class="text-sm text-tenue">
                Mostrando
                @if ($paginator->firstItem())
                    <span class="font-semibold text-fuerte">{{ $paginator->firstItem() }}</span>
                    a
                    <span class="font-semibold text-fuerte">{{ $paginator->lastItem() }}</span>
                @else
                    <span class="font-semibold text-fuerte">{{ $paginator->count() }}</span>
                @endif
                de
                <span class="font-semibold text-fuerte">{{ $paginator->total() }}</span>
                {{ Str::plural('resultado', $paginator->total()) }}
            </p>

            <span class="inline-flex divide-x divide-linea overflow-hidden rounded-xl border border-linea">

                {{-- Anterior --}}
                @if ($paginator->onFirstPage())
                    <span class="{{ $inerte }} px-2.5" aria-disabled="true" aria-label="Página anterior">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="{{ $enlace }} px-2.5" aria-label="Página anterior">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @endif

                {{-- Números --}}
                @foreach ($elements as $elemento)
                    @if (is_string($elemento))
                        <span class="{{ $inerte }} cursor-default" aria-disabled="true">{{ $elemento }}</span>
                    @endif

                    @if (is_array($elemento))
                        @foreach ($elemento as $pagina => $url)
                            @if ($pagina == $paginator->currentPage())
                                <span class="{{ $actual }}" aria-current="page">{{ $pagina }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $enlace }}"
                                   aria-label="Ir a la página {{ $pagina }}">{{ $pagina }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Siguiente --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="{{ $enlace }} px-2.5" aria-label="Página siguiente">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @else
                    <span class="{{ $inerte }} px-2.5" aria-disabled="true" aria-label="Página siguiente">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif

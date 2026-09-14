{{--
    Cinta editorial entre el Hero y Cifras. El isotipo queda fijo; el
    recorrido solo lleva destinos públicos reales, con rótulos cortos.
--}}
@php
    /** @var list<array{texto: string, href: string}> $items */
    $items = [];

    foreach ([
        ['texto' => 'Directorio', 'href' => route('directorio.index')],
        ['texto' => 'Abre tu negocio', 'href' => route('guia.index')],
        ['texto' => 'Eventos', 'href' => route('eventos.index')],
        ['texto' => 'Empleo', 'href' => route('empleo.index')],
        ['texto' => 'Boletín', 'href' => route('boletin.index')],
        ['texto' => 'Aliados', 'href' => route('aliados.index')],
        ['texto' => 'Iniciativas', 'href' => route('quienes-somos').'#iniciativas'],
        ['texto' => 'Afíliate', 'href' => route('afiliate')],
    ] as $enlace) {
        if (filled($enlace['texto']) && filled($enlace['href']) && ! str_ends_with($enlace['href'], '#')) {
            $items[] = $enlace;
        }
    }
@endphp

@if ($items !== [])
    <aside class="home-editorial-cinta" aria-label="{{ ajuste('sitio_nombre') }}" x-data="cintaEditorial" x-bind:class="pausada && 'home-editorial-cinta--pausada'">
        <a href="{{ route('inicio') }}" class="home-editorial-cinta__marca enlace-accion">
            <img src="{{ asset('img/monograma-asobares.png') }}"
                 alt="{{ ajuste('sitio_nombre') }}"
                 width="156"
                 height="108"
                 class="home-editorial-cinta__isotipo">
        </a>

        <button type="button"
                class="control-movimiento control-movimiento--cinta"
                aria-pressed="false"
                aria-label="Pausar cinta editorial"
                x-on:click="alternar()"
                x-bind:aria-pressed="pausada ? 'true' : 'false'"
                x-bind:aria-label="pausada ? 'Reanudar cinta editorial' : 'Pausar cinta editorial'">
            <x-heroicon-o-pause class="h-4 w-4" x-show="! pausada" aria-hidden="true" />
            <x-heroicon-o-play class="h-4 w-4" x-show="pausada" x-cloak aria-hidden="true" />
            <span class="sr-only" x-text="pausada ? 'Reanudar cinta editorial' : 'Pausar cinta editorial'"></span>
        </button>

        <div class="home-editorial-cinta__pista">
            <span class="home-editorial-cinta__velo home-editorial-cinta__velo--izq" aria-hidden="true"></span>
            <div class="home-editorial-cinta__recorrido">
                @foreach ([false, true] as $decorativa)
                    <ul class="home-editorial-cinta__lista"@if ($decorativa) aria-hidden="true"@endif>
                        @foreach ($items as $item)
                            <li class="home-editorial-cinta__item">
                                <a href="{{ $item['href'] }}" class="home-editorial-cinta__enlace enlace-accion"@if ($decorativa) tabindex="-1"@endif>{{ $item['texto'] }}</a>
                                <span class="home-editorial-cinta__sep" aria-hidden="true"></span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
            <span class="home-editorial-cinta__velo home-editorial-cinta__velo--der" aria-hidden="true"></span>
        </div>
    </aside>
@endif

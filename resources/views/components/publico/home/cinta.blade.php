{{--
    Cinta editorial entre el Hero y Cifras. Solo ítems con destino público
    real: si no hay ruta, no se pinta. El movimiento vive en home-editorial.css.
--}}
@php
    /** @var list<array{texto: string, href: string}> $items */
    $items = [];

    foreach ([
        ['texto' => 'Directorio', 'href' => route('directorio.index')],
        ['texto' => ajuste('portada_guia_titulo'), 'href' => route('guia.index')],
        ['texto' => ajuste('eventos_titulo'), 'href' => route('eventos.index')],
        ['texto' => ajuste('portada_empleo_titulo'), 'href' => route('empleo.index')],
        ['texto' => ajuste('boletin_titulo'), 'href' => route('boletin.index')],
        ['texto' => ajuste('portada_aliados_titulo'), 'href' => route('aliados.index')],
        ['texto' => ajuste('hero_cta_afiliate'), 'href' => route('afiliate')],
        ['texto' => ajuste('iniciativas_titulo'), 'href' => route('quienes-somos').'#iniciativas'],
    ] as $enlace) {
        if (filled($enlace['texto']) && filled($enlace['href']) && ! str_ends_with($enlace['href'], '#')) {
            $items[] = $enlace;
        }
    }
@endphp

@if ($items !== [])
    <aside class="home-editorial-cinta" aria-label="{{ ajuste('sitio_nombre') }}">
        <a href="{{ route('inicio') }}" class="home-editorial-cinta__marca enlace-accion">
            <img src="{{ asset('img/monograma-asobares.png') }}"
                 alt="{{ ajuste('sitio_nombre') }}"
                 width="156"
                 height="108"
                 class="home-editorial-cinta__isotipo">
        </a>

        <div class="home-editorial-cinta__pista">
            <div class="home-editorial-cinta__recorrido">
                @foreach ([false, true] as $decorativa)
                    <ul class="home-editorial-cinta__lista"@if ($decorativa) aria-hidden="true"@endif>
                        @foreach ($items as $item)
                            <li class="home-editorial-cinta__item">
                                @if ($decorativa)
                                    <span class="home-editorial-cinta__enlace">{{ $item['texto'] }}&nbsp;<x-publico.flecha /></span>
                                @else
                                    <a href="{{ $item['href'] }}" class="home-editorial-cinta__enlace enlace-accion">{{ $item['texto'] }}&nbsp;<x-publico.flecha /></a>
                                @endif
                                <span class="home-editorial-cinta__sep" aria-hidden="true"></span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </aside>
@endif

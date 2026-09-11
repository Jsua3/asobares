@props(['iniciativas', 'beneficios', 'totalAsociados' => 0])

{{--
    Cinta editorial entre el Hero y Cifras. Recupera iniciativas, beneficios
    y accesos que la portada compactó. El movimiento vive en home-editorial.css.
--}}
@php
    $marca = trim((string) preg_replace('/\s+Capítulo\s+/iu', ' ', (string) ajuste('sitio_nombre')));
    $partesDeMarca = preg_split('/\s+/u', $marca, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    /** @var list<array{texto: string, href: ?string}> $items */
    $items = [];

    if ($totalAsociados > 0) {
        $items[] = [
            'texto' => $totalAsociados.' afiliados en el Quindío',
            'href' => null,
        ];
    }

    foreach ($iniciativas as $iniciativa) {
        $items[] = [
            'texto' => $iniciativa->nombre.' — '.$iniciativa->estado_iniciativa->getLabel(),
            'href' => null,
        ];
    }

    foreach ($beneficios as $beneficio) {
        $items[] = [
            'texto' => $beneficio->titulo,
            'href' => null,
        ];
    }

    foreach ([
        ['texto' => ajuste('portada_guia_titulo'), 'href' => route('guia.index')],
        ['texto' => ajuste('portada_empleo_titulo'), 'href' => route('empleo.index')],
        ['texto' => ajuste('eventos_titulo'), 'href' => route('eventos.index')],
        ['texto' => ajuste('boletin_titulo'), 'href' => route('boletin.index')],
        ['texto' => ajuste('portada_aliados_titulo'), 'href' => route('aliados.index')],
    ] as $enlace) {
        if (filled($enlace['texto'])) {
            $items[] = $enlace;
        }
    }
@endphp

@if ($items !== [])
    <aside class="home-editorial-cinta" aria-label="{{ ajuste('sitio_nombre') }}">
        <div class="home-editorial-cinta__marca">
            <span class="home-editorial-cinta__punto" aria-hidden="true"></span>
            <span class="home-editorial-cinta__marca-texto">
                @foreach ($partesDeMarca as $parte)
                    <span>{{ $parte }}</span>
                @endforeach
            </span>
        </div>

        <div class="home-editorial-cinta__pista">
            <div class="home-editorial-cinta__recorrido">
                @foreach ([false, true] as $decorativa)
                    <ul class="home-editorial-cinta__lista"@if ($decorativa) aria-hidden="true"@endif>
                        @foreach ($items as $item)
                            <li class="home-editorial-cinta__item">
                                @if ($item['href'] && ! $decorativa)
                                    <a href="{{ $item['href'] }}" class="home-editorial-cinta__enlace enlace-accion">{{ $item['texto'] }}</a>
                                @else
                                    <span @class(['home-editorial-cinta__enlace' => filled($item['href'])])>{{ $item['texto'] }}</span>
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

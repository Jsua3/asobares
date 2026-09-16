@php
    // Cada línea de trabajo viene como «Nombre | Descripción | Programa; Programa»
    $lineas = collect(explode("\n", (string) ajuste('quienes_lineas')))
        ->filter()
        ->map(function (string $fila): array {
            [$nombre, $descripcion, $programas] = array_pad(array_map('trim', explode('|', $fila)), 3, '');

            return [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'programas' => array_filter(array_map('trim', explode(';', $programas))),
            ];
        });

    // Barreras: «Titular | Explicación»
    $barreras = collect(explode("\n", (string) ajuste('barreras')))
        ->filter()
        ->map(function (string $fila): array {
            [$titular, $explicacion] = array_pad(array_map('trim', explode('|', $fila)), 2, '');

            return ['titular' => $titular, 'explicacion' => $explicacion];
        });
@endphp

<x-layouts.publico titulo="Quiénes somos — ASOBARES Capítulo Quindío"
                   descripcion="El gremio que representa, fortalece y dinamiza el sector nocturno, gastronómico y de entretenimiento del Quindío.">

    @push('cabeza')
        @vite(['resources/css/gremio-editorial.css'])
    @endpush

    <div class="gremio-editorial gremio-editorial--manifiesto">
        <div class="gremio-editorial-cuerpo">

            <header class="gremio-editorial-banda gremio-editorial-banda--apertura revelar" data-revelar>
                <div class="gremio-editorial-banda__interior gremio-editorial-apertura">
                    <x-publico.folio-gremio numero="01" />
                    <p class="gremio-editorial-apertura__lema">{{ ajuste('sitio_eslogan') }}</p>
                    <h1>{{ ajuste('manifiesto_apertura') }}</h1>
                    <p class="gremio-editorial-apertura__cuerpo">{{ ajuste('quienes_mision') }}</p>
                </div>
            </header>

            <section class="gremio-editorial-banda gremio-editorial-banda--elevada revelar" data-revelar aria-labelledby="historia">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <div class="gremio-editorial-seccion__cabeza">
                        <span class="gremio-editorial-seccion__n" aria-hidden="true">01</span>
                        <h2 id="historia">{{ ajuste('quienes_titulo_historia') }}</h2>
                    </div>
                    <p class="gremio-editorial-seccion__prosa">{{ ajuste('quienes_historia') }}</p>
                </div>
            </section>

            <section class="gremio-editorial-banda revelar" data-revelar aria-labelledby="hacemos">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <div class="gremio-editorial-seccion__cabeza">
                        <span class="gremio-editorial-seccion__n" aria-hidden="true">02</span>
                        <h2 id="hacemos">{{ ajuste('quienes_titulo_que_hacemos') }}</h2>
                    </div>
                    <p class="gremio-editorial-seccion__prosa">{{ ajuste('quienes_que_hacemos') }}</p>
                    <blockquote class="gremio-editorial-cita">
                        <p>«{{ ajuste('quienes_vision') }}»</p>
                    </blockquote>
                </div>
            </section>

            <section class="gremio-editorial-banda gremio-editorial-banda--elevada revelar" data-revelar aria-labelledby="vision">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <p class="gremio-editorial-apertura__lema">{{ ajuste('quienes_rotulo_vision') }}</p>
                    <h2 id="vision">{{ ajuste('vision_titulo') }}</h2>
                    <p class="gremio-editorial-vision__nota">{{ ajuste('vision_nota') }}</p>
                    <div class="gremio-editorial-vision__detalle">
                        @foreach (array_filter(explode("\n", (string) ajuste('vision_detalle'))) as $detalle)
                            <p>{{ trim($detalle) }}</p>
                        @endforeach
                    </div>
                </div>
            </section>

            @if ($barreras->isNotEmpty())
                <section class="gremio-editorial-banda gremio-editorial-banda--profunda revelar" data-revelar aria-labelledby="barreras">
                    <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                        <div class="gremio-editorial-seccion__cabeza">
                            <span class="gremio-editorial-seccion__n" aria-hidden="true">03</span>
                            <h2 id="barreras">{{ ajuste('quienes_titulo_barreras') }}</h2>
                        </div>
                        <p class="gremio-editorial-pie">{{ ajuste('quienes_barreras_pie') }}</p>
                        <ol class="gremio-editorial-abiertas">
                            @foreach ($barreras as $indice => $barrera)
                                <li>
                                    <span class="gremio-editorial-abiertas__n">{{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <h3>{{ $barrera['titular'] }}</h3>
                                    <p>{{ $barrera['explicacion'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endif

            @if ($iniciativas->isNotEmpty())
                <section class="gremio-editorial-banda revelar" data-revelar aria-labelledby="iniciativas">
                    <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                        <h2 id="iniciativas">{{ ajuste('iniciativas_titulo') }}</h2>
                        <p class="gremio-editorial-pie">{{ ajuste('iniciativas_intro') }}</p>
                        <ol class="gremio-editorial-listado">
                            @foreach ($iniciativas as $iniciativa)
                                <li>
                                    <p class="gremio-editorial-meta">
                                        <strong>{{ $iniciativa->estado_iniciativa->getLabel() }}</strong>
                                        @if ($iniciativa->linea)
                                            <span>{{ $iniciativa->linea }}</span>
                                        @endif
                                        @if ($iniciativa->lugar)
                                            <span>{{ $iniciativa->lugar }}</span>
                                        @endif
                                    </p>
                                    <h3>{{ $iniciativa->nombre }}</h3>
                                    <p>{{ $iniciativa->resumen }}</p>
                                    @if ($iniciativa->descripcion)
                                        <p>{{ $iniciativa->descripcion }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        <p class="gremio-editorial-pie">{{ ajuste('quienes_iniciativas_pie') }}</p>
                    </div>
                </section>
            @endif

            <section class="gremio-editorial-banda gremio-editorial-banda--profunda revelar" data-revelar aria-labelledby="lineas">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <div class="gremio-editorial-seccion__cabeza">
                        <span class="gremio-editorial-seccion__n" aria-hidden="true">04</span>
                        <h2 id="lineas">{{ ajuste('quienes_titulo_lineas') }}</h2>
                    </div>
                    <p class="gremio-editorial-pie">{{ ajuste('quienes_lineas_intro') }}</p>
                    <ol class="gremio-editorial-secuencia">
                        @foreach ($lineas as $indice => $linea)
                            <li>
                                <span class="gremio-editorial-abiertas__n">{{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $linea['nombre'] }}</h3>
                                <p>{{ $linea['descripcion'] }}</p>
                                @if ($linea['programas'])
                                    <p class="gremio-editorial-apertura__lema">{{ ajuste('quienes_rotulo_programas') }}</p>
                                    <ul class="gremio-editorial-programas">
                                        @foreach ($linea['programas'] as $programa)
                                            <li>{{ $programa }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section class="gremio-editorial-banda gremio-editorial-banda--elevada revelar" data-revelar aria-labelledby="armenia">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <h2 id="armenia">{{ ajuste('quienes_titulo_armenia') }}</h2>
                    <p class="gremio-editorial-seccion__prosa">{{ ajuste('quienes_estrategia_armenia') }}</p>
                </div>
            </section>

            <section class="gremio-editorial-banda revelar" data-revelar aria-labelledby="direccion">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <h2 id="direccion">{{ ajuste('quienes_titulo_direccion') }}</h2>
                    <div class="gremio-editorial-firma">
                        <p class="gremio-editorial-firma__cargo">{{ ajuste('quienes_cargo_presidente') }}</p>
                        <p class="gremio-editorial-firma__nombre">{{ ajuste('quienes_presidente') }}</p>
                        <p class="gremio-editorial-firma__cargo">{{ ajuste('quienes_cargo_directora') }}</p>
                        <p class="gremio-editorial-firma__nombre">{{ ajuste('quienes_directora') }}</p>
                    </div>
                    <p class="gremio-editorial-pie">Capítulo fundado el {{ ajuste('quienes_fundacion') }} en Armenia.</p>
                </div>
            </section>

            <section class="gremio-editorial-banda gremio-editorial-banda--elevada revelar" data-revelar aria-labelledby="beneficios">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <div class="gremio-editorial-seccion__cabeza">
                        <span class="gremio-editorial-seccion__n" aria-hidden="true">05</span>
                        <h2 id="beneficios">{{ ajuste('quienes_titulo_beneficios') }}</h2>
                    </div>
                    <ul class="gremio-editorial-beneficios">
                        @foreach ($beneficios as $beneficio)
                            <li>
                                <h3>{{ $beneficio->titulo }}</h3>
                                <x-publico.sello-de-alcance :beneficio="$beneficio" />
                                <p>{{ $beneficio->descripcion }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="gremio-editorial-banda gremio-editorial-cierre revelar" data-revelar aria-labelledby="cierre">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                    <h2 id="cierre">{{ ajuste('manifiesto_cierre_titulo') }}</h2>
                    <p class="gremio-editorial-apertura__lema">{{ ajuste('manifiesto_cierre_firma') }}</p>
                </div>
            </section>

            <section class="gremio-editorial-banda gremio-editorial-banda--elevada revelar" data-revelar aria-labelledby="nacional">
                <div class="gremio-editorial-banda__interior gremio-editorial-seccion">
                <h2 id="nacional">{{ ajuste('quienes_titulo_nacional') }}</h2>

                {{-- El tamaño de ese respaldo, de la lámina 3 de la presentación
                     institucional. Los rótulos dicen «en el país» a propósito: son
                     cifras de la Nacional, y sin esa palabra se leen como el tamaño
                     del capítulo, que es otra cosa y todavía no tiene documento que
                     la sostenga. Como en la franja de la portada, la cifra que la
                     oficina deje en blanco no se pinta, y si borra las dos
                     desaparece el bloque. --}}
                @php
                    $respaldoNacional = array_values(array_filter([
                        ['cifra' => trim((string) ajuste('nacional_capitulos')), 'rotulo' => ajuste('nacional_capitulos_rotulo')],
                        ['cifra' => trim((string) ajuste('nacional_afiliados')), 'rotulo' => ajuste('nacional_afiliados_rotulo')],
                    ], fn (array $dato): bool => $dato['cifra'] !== ''));
                @endphp

                @if ($respaldoNacional !== [])
                    <dl class="gremio-editorial-cifras">
                        @foreach ($respaldoNacional as $dato)
                            <div>
                                <dt>{{ $dato['rotulo'] }}</dt>
                                <dd>{{ $dato['cifra'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                <p class="gremio-editorial-seccion__prosa">
                    Aterrizamos en el Quindío los programas nacionales del gremio:
                    {{ collect(array_filter(explode("\n", (string) ajuste('quienes_programas_nacionales'))))->map(fn ($p) => trim($p))->join(', ', ' y ') }}.
                    Lo que no es local se gestiona directamente con la Nacional.
                </p>
                @if ($enlaceNacional = enlaceSeguro(ajuste('url_nacional')))
                    <div class="gremio-editorial-nacional__accion">
                        <x-publico.boton variante="contorno" :href="$enlaceNacional" target="_blank" rel="noopener">
                            Ir a Asobares Nacional&nbsp;<x-publico.flecha direccion="externa" />
                        </x-publico.boton>
                    </div>
                @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts.publico>

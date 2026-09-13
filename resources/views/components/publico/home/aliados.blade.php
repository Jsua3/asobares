@props(['aliadosInstitucionales', 'aliadosComerciales'])

@php
    $ambosNiveles = $aliadosInstitucionales->isNotEmpty() && $aliadosComerciales->isNotEmpty();
@endphp

<section class="home-editorial-aliados revelar" data-revelar aria-labelledby="aliados">
    <div class="home-editorial-aliados__franja">
        <div class="home-editorial-aliados__marco">
            <div class="home-editorial-aliados__cabecera">
                <div>
                    <h2 id="aliados" class="home-editorial-eyebrow home-editorial-eyebrow--claro">
                        {{ ajuste('portada_aliados_titulo') }}
                    </h2>
                    @if ($ambosNiveles)
                        <p class="home-editorial-aliados__lema">
                            {{ ajuste('portada_aliados_institucionales') }} y convenios que fortalecen al gremio.
                        </p>
                    @endif
                </div>
                <a href="{{ route('aliados.index') }}"
                   class="home-editorial-enlace home-editorial-aliados__cta enlace-accion">
                    Ver todos los aliados&nbsp;<x-publico.flecha />
                </a>
            </div>

            @if ($aliadosInstitucionales->isNotEmpty())
                @include('components.publico.home.partials.fila-aliados', [
                    'coleccion' => $aliadosInstitucionales,
                    'etiqueta' => ajuste('portada_aliados_institucionales'),
                    'variante' => 'institucionales',
                ])
            @endif

            @if ($aliadosComerciales->isNotEmpty())
                @include('components.publico.home.partials.fila-aliados', [
                    'coleccion' => $aliadosComerciales,
                    'etiqueta' => ajuste('portada_aliados_comerciales'),
                    'variante' => 'comerciales',
                ])
            @endif
        </div>
    </div>
</section>

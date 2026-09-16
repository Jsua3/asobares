@props([
    'vacante',
    'compacta' => false,
])

{{--
    Una oferta del muro público. Solo campos reales del modelo publicado.
    El nombre del establecimiento enlaza al directorio cuando la ficha
    está publicada; el cargo y el CTA van a la vacante.
--}}
<li class="empleo-editorial-oferta">
    <article class="empleo-editorial-oferta__enlace">
        <div>
            <div class="empleo-editorial-oferta__meta">
                <span class="empleo-editorial-chip empleo-editorial-chip--tipo">{{ $vacante->tipo->getLabel() }}</span>
                <span class="empleo-editorial-chip empleo-editorial-chip--area">{{ $vacante->categoria_cargo->getLabel() }}</span>
                <span class="empleo-editorial-chip empleo-editorial-chip--estado">Abierta</span>
            </div>

            <h3 class="empleo-editorial-oferta__cargo">
                <a href="{{ route('empleo.show', $vacante) }}" class="enlace-accion">{{ $vacante->cargo }}</a>
            </h3>

            <p class="empleo-editorial-oferta__empresa">
                @if ($vacante->asociado->estaPublicado())
                    <a href="{{ route('directorio.show', $vacante->asociado) }}" class="enlace-accion">{{ $vacante->asociado->nombre }}</a>
                @else
                    {{ $vacante->asociado->nombre }}
                @endif
            </p>

            <p class="empleo-editorial-oferta__datos">
                {{ $vacante->asociado->municipio->nombre }}
                · publicada {{ $vacante->created_at->diffForHumans() }}
                @if ($vacante->franja_horaria)
                    · {{ $vacante->franja_horaria }}
                @endif
                @if ($vacante->fecha_limite)
                    · se cierra el {{ $vacante->fecha_limite->translatedFormat('d \d\e F') }}
                @endif
            </p>

            @if (! $compacta && $vacante->descripcion)
                <p class="empleo-editorial-oferta__extracto">{{ $vacante->descripcion }}</p>
            @endif
        </div>

        <a href="{{ route('empleo.show', $vacante) }}" class="empleo-editorial-oferta__cta pulsable">Ver y postularme</a>
    </article>
</li>

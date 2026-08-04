<x-layouts.publico titulo="Mis vacantes — ASOBARES Quindío"
                   descripcion="Vacantes publicadas por tu establecimiento en la bolsa de empleo del gremio.">

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-apagado">{{ $asociado->nombre }}</p>
                <h1 class="mt-1 font-display text-3xl font-bold tracking-tight">Mis vacantes</h1>
                <p class="mt-1.5 text-sm text-tenue">
                    Las publicas tú y las aprueba la secretaría. Nadie del gremio edita lo que escribiste.
                </p>
            </div>
            <a href="{{ route('mi-cuenta.vacantes.crear') }}"
               class="rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white hover:bg-marca-600">
                Publicar una vacante
            </a>
        </header>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        @if (session('error'))
            <div class="mt-8 rounded-xl border border-marca-500/40 bg-marca-panel p-5 text-sm text-tinta">
                {{ session('error') }}
            </div>
        @endif

        @if ($vacantes->isEmpty())
            <div class="tarjeta mt-10 p-12 text-center">
                <p class="font-display text-lg font-semibold">Todavía no has publicado ninguna vacante</p>
                <p class="mt-2 text-sm text-tenue">
                    Publica la primera y aparecerá en la bolsa de empleo apenas la apruebe la secretaría.
                </p>
            </div>
        @else
            <ul class="mt-10 space-y-4">
                @foreach ($vacantes as $vacante)
                    <li class="tarjeta p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 font-medium',
                                        'bg-emerald-500/15 text-exito' => $vacante->estaPublicado() && $vacante->estaVigente(),
                                        'bg-amber-500/15 text-aviso' => $vacante->estado === \App\Enums\EstadoPublicacion::PendienteAprobacion,
                                        'bg-marca-500/15 text-acento-fuerte' => $vacante->estado === \App\Enums\EstadoPublicacion::Borrador,
                                    ])>
                                        @if ($vacante->estaCerrada())
                                            Cerrada
                                        @elseif ($vacante->estaVencida())
                                            Vencida
                                        @else
                                            {{ $vacante->estado->getLabel() }}
                                        @endif
                                    </span>
                                    <span class="text-apagado">{{ $vacante->tipo->getLabel() }}</span>
                                </div>

                                <h2 class="mt-3 font-display text-lg font-semibold">{{ $vacante->cargo }}</h2>

                                @if ($vacante->fecha_limite)
                                    <p class="mt-1 text-xs text-apagado">
                                        Hasta el {{ $vacante->fecha_limite->translatedFormat('d \d\e F \d\e Y') }}
                                    </p>
                                @endif

                                @if ($vacante->motivo_devolucion)
                                    <div class="mt-4 rounded-xl border border-marca-500/25 bg-marca-panel p-4">
                                        <p class="text-[.65rem] font-semibold uppercase tracking-wider text-acento">
                                            La secretaría pidió un ajuste
                                        </p>
                                        <p class="mt-2 text-sm leading-relaxed text-tinta">{{ $vacante->motivo_devolucion }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-2">
                                <a href="{{ route('mi-cuenta.vacantes.show', $vacante) }}"
                                   class="rounded-xl border border-linea-fuerte px-4 py-2 text-sm font-semibold hover:border-marca-500/50">
                                    {{ $vacante->postulaciones_count }}
                                    {{ $vacante->postulaciones_count === 1 ? 'postulación' : 'postulaciones' }}
                                </a>
                                <a href="{{ route('mi-cuenta.vacantes.editar', $vacante) }}"
                                   class="text-sm text-acento hover:text-acento-fuerte">Editar</a>

                                @if ($vacante->estaCerrada())
                                    <form method="POST" action="{{ route('mi-cuenta.vacantes.reabrir', $vacante) }}">
                                        @csrf
                                        <button type="submit" class="text-sm text-acento hover:text-acento-fuerte">Reabrir</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('mi-cuenta.vacantes.cerrar', $vacante) }}">
                                        @csrf
                                        <button type="submit" class="text-sm text-tenue hover:text-fuerte">Ya contraté</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10">{{ $vacantes->links() }}</div>
        @endif
    </div>
</x-layouts.publico>

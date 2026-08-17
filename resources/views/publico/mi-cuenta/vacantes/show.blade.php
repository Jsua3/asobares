<x-layouts.publico :titulo="$vacante->cargo.' — Postulaciones'"
                   descripcion="Personas que se postularon a esta vacante.">

    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.vacantes.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                ← Mis vacantes
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">{{ $vacante->cargo }}</h1>
            <p class="mt-1.5 text-sm text-tenue">
                {{ $vacante->tipo->getLabel() }} · {{ $postulaciones->total() }}
                {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}
            </p>
        </header>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        <p class="mt-8 rounded-xl border border-linea bg-marca-panel p-4 text-xs leading-relaxed text-tenue">
            Estos datos personales te los confiaron para este proceso de selección. No los compartas ni los uses
            para otra cosa. El gremio los borra automáticamente {{ config('bolsas.retencion_postulaciones_meses') }} meses después de que cierre la vacante.
        </p>

        @if ($postulaciones->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">Todavía nadie se ha postulado</p>
                <p class="mt-2 text-sm text-tenue">Te avisamos por correo apenas llegue la primera postulación.</p>
            </div>
        @else
            <ul class="mt-8 space-y-4">
                @foreach ($postulaciones as $postulacion)
                    <li class="tarjeta p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <h2 class="font-display text-base font-semibold">{{ $postulacion->nombre }}</h2>
                                <p class="mt-1 text-sm text-tenue">
                                    <a href="mailto:{{ $postulacion->correo }}" class="enlace-accion text-acento hover:text-acento-fuerte">
                                        {{ $postulacion->correo }}
                                    </a>
                                    @if ($postulacion->telefono)
                                        · {{ $postulacion->telefono }}
                                    @endif
                                </p>
                                @if ($postulacion->experiencia)
                                    <p class="mt-3 text-sm leading-relaxed text-suave">{{ $postulacion->experiencia }}</p>
                                @endif
                                <p class="mt-3 text-xs text-apagado">
                                    Se postuló {{ $postulacion->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('mi-cuenta.postulaciones.gestionar', $postulacion) }}"
                                  class="flex shrink-0 items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="estado"
                                        class="rounded-xl border border-linea bg-fondo px-3 py-2 text-sm text-tinta focus:outline-none focus:ring-2 focus:ring-marca-500/60">
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado->value }}" @selected($postulacion->estado === $estado)>
                                            {{ $estado->getLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-publico.boton variante="contorno">
                                    Guardar
                                </x-publico.boton>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10">{{ $postulaciones->links() }}</div>
        @endif
    </div>
</x-layouts.publico>

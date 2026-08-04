<x-layouts.publico titulo="Editar vacante — ASOBARES Quindío"
                   descripcion="Corrige una vacante de tu establecimiento.">

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="font-display text-3xl font-bold tracking-tight">Editar vacante</h1>

        @if ($vacante->estaPublicado())
            <p class="mt-2 text-sm text-tenue">
                Está publicada. Al guardar el cambio vuelve a revisión y sale de la bolsa hasta que la secretaría lo apruebe.
            </p>
        @endif

        @if ($vacante->motivo_devolucion)
            <div class="mt-6 rounded-xl border border-marca-500/25 bg-marca-panel p-4">
                <p class="text-[.65rem] font-semibold uppercase tracking-wider text-acento">La secretaría pidió un ajuste</p>
                <p class="mt-2 text-sm leading-relaxed text-tinta">{{ $vacante->motivo_devolucion }}</p>
            </div>
        @endif

        <x-publico.mi-cuenta.formulario-vacante
            :vacante="$vacante"
            :accion="route('mi-cuenta.vacantes.update', $vacante)"
            metodo="PUT"
            :categorias="$categorias"
            :tipos="$tipos"
            texto-boton="Guardar y enviar a revisión" />
    </div>
</x-layouts.publico>

<x-layouts.publico titulo="Publicar una vacante — ASOBARES Quindío"
                   descripcion="Publica una vacante de tu establecimiento en la bolsa de empleo del gremio.">

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('mi-cuenta.vacantes.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
            ← Mis vacantes
        </a>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">Publicar una vacante</h1>
        <p class="mt-2 text-sm text-tenue">
            Al enviarla queda en revisión de la secretaría. Cuando la apruebe, aparece en la bolsa de empleo.
        </p>

        <x-publico.mi-cuenta.formulario-vacante
            :vacante="null"
            :accion="route('mi-cuenta.vacantes.store')"
            metodo="POST"
            :categorias="$categorias"
            :tipos="$tipos"
            texto-boton="Enviar a revisión" />
    </div>
</x-layouts.publico>

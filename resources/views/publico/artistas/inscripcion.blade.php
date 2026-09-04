<x-layouts.publico titulo="Inscríbete en la bolsa de artistas — ASOBARES Quindío"
                   descripcion="DJ, banda o solista: inscríbete gratis en el directorio de artistas de ASOBARES Capítulo Quindío.">

    <div class="luz-ambiente border-b border-linea">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <a href="{{ route('artistas.index') }}" class="enlace-accion relative inline-block text-sm text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte"><x-publico.flecha direccion="izquierda" />&nbsp;Ver el directorio</a>

            <h1 class="mt-4 font-display text-3xl font-bold tracking-tight">Inscríbete en la bolsa de artistas</h1>
            <p class="mt-2 text-sm text-tenue">
                Cuando un bar necesite música a las doce de la noche, va a buscar aquí. La secretaría revisa cada
                inscripción antes de publicarla.
            </p>
        </div>
    </div>

    <div class="revelar mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8" data-revelar>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        <form method="POST" action="{{ route('artistas.inscripcion.store') }}" enctype="multipart/form-data" class="vidrio rounded-[1.75rem] p-7 sm:p-9 mt-8 space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-publico.campo nombre="nombre" etiqueta="Nombre artístico" requerido placeholder="DJ Tornamesa" />
                <x-publico.campo nombre="tipo" etiqueta="Qué eres" tipo="select" requerido
                                 :opciones="collect($tipos)->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])->all()" />
                <x-publico.campo nombre="genero_musical" etiqueta="Género musical" placeholder="Crossover, salsa, rock…" />
                <x-publico.campo nombre="municipio_id" etiqueta="Municipio" tipo="select" requerido
                                 :opciones="$municipios->pluck('nombre', 'id')->all()" />
                <x-publico.campo nombre="whatsapp" etiqueta="WhatsApp" tipo="tel" />
                <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email"
                                 ayuda="Te avisamos ahí cuando tu ficha esté publicada." />
                <x-publico.campo nombre="tarifa_desde" etiqueta="Tarifa desde (COP)" tipo="number"
                                 ayuda="Solo la ve el gremio, para poder recomendarte. En tu ficha pública nunca aparece un precio." />
                <x-publico.campo nombre="instagram_url" etiqueta="Instagram" tipo="url"
                                 placeholder="https://instagram.com/tu-cuenta" />
            </div>

            <x-publico.campo nombre="video_url" etiqueta="Video de YouTube" tipo="url"
                             placeholder="https://www.youtube.com/watch?v=..."
                             ayuda="Solo YouTube. Es lo que más ayuda a que te contraten." />

            <x-publico.campo nombre="descripcion" etiqueta="Cuéntanos de ti" tipo="textarea" filas="4"
                             placeholder="Qué tocas, dónde has trabajado y qué equipo llevas." />

            <div>
                <label for="foto" class="mb-1.5 block text-sm font-medium text-tinta">Foto</label>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                       class="w-full rounded-xl border border-linea bg-fondo px-4 py-1.5 text-sm text-tinta file:mr-4 file:min-h-11 file:rounded-lg file:border-0 file:bg-marca-500 file:px-4 file:text-sm file:font-semibold file:text-white">
                <p class="mt-1.5 text-xs text-apagado">JPG, PNG o WebP. Máximo 5 MB.</p>
                @error('foto')
                    <p class="mt-1.5 text-xs text-acento">{{ $message }}</p>
                @enderror
            </div>

            <x-publico.habeas-data />

            <x-publico.boton class="w-full sm:w-auto">
                Enviar mi inscripción
            </x-publico.boton>
        </form>
    </div>
</x-layouts.publico>

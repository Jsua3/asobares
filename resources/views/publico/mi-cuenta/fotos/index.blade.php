<x-layouts.publico titulo="Mis fotos — ASOBARES Quindío"
                  descripcion="Fotos de tu establecimiento en el directorio del gremio.">

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <p class="mt-3 text-sm text-apagado">{{ $asociado->nombre }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold tracking-tight">Mis fotos</h1>
            <p class="mt-1.5 text-sm text-tenue">
                Las subes tú y las revisa la secretaría antes de que salgan en tu ficha.
                Puedes retirar cualquiera cuando quieras, sin pedir permiso.
            </p>
        </header>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        @error('foto')
            <x-publico.alerta tipo="error" class="mt-8">{{ $message }}</x-publico.alerta>
        @enderror

        {{-- Subida --}}
        <section class="tarjeta mt-8 p-6" aria-labelledby="subir">
            <h2 id="subir" class="font-display text-lg font-semibold">Subir una foto</h2>
            <p class="mt-1 text-sm text-tenue">
                JPG, PNG o WebP. Mínimo 600 × 400 píxeles y máximo 5 MB.
                Llevas {{ $aprobadas->count() + $pendientes->count() }} de {{ $maximo }}.
            </p>

            <form method="POST" action="{{ route('mi-cuenta.fotos.store') }}" enctype="multipart/form-data"
                  class="mt-5 flex flex-wrap items-end gap-4">
                @csrf
                <div class="min-w-64 flex-1">
                    <label for="foto" class="block text-sm font-medium">Archivo</label>
                    <input id="foto" name="foto" type="file" required
                           accept="image/jpeg,image/png,image/webp"
                           class="mt-2 block w-full min-h-11 rounded-xl border border-linea bg-fondo px-3 py-2 text-sm text-tinta file:mr-3 file:rounded-lg file:border-0 file:bg-superficie-alta file:px-3 file:py-1.5 file:text-sm file:text-tinta">
                </div>
                <x-publico.boton tipo="submit">Enviar a revisión</x-publico.boton>
            </form>
        </section>

        {{-- Pendientes: solo las ve el dueño --}}
        @if ($pendientes->isNotEmpty())
            <section class="mt-10" aria-labelledby="pendientes">
                <h2 id="pendientes" class="font-display text-lg font-semibold">Esperando aprobación</h2>
                <p class="mt-1 text-sm text-tenue">
                    Todavía no salen en tu ficha. El gremio las revisa antes de publicarlas.
                </p>

                <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($pendientes as $foto)
                        <li class="tarjeta overflow-hidden">
                            <img src="{{ $foto->getUrl() }}" alt="{{ $foto->name }}"
                                 loading="lazy" decoding="async" width="400" height="300"
                                 class="aspect-4/3 w-full object-cover opacity-70">
                            <div class="p-4">
                                <p class="text-2xs uppercase tracking-wide text-aviso-suave">En revisión</p>
                                @if ($motivo = $foto->getCustomProperty(\App\Models\Asociado::FOTO_MOTIVO))
                                    <p class="mt-2 text-sm text-aviso-suave">Devuelta: {{ $motivo }}</p>
                                @endif
                                <form method="POST" action="{{ route('mi-cuenta.fotos.destroy', $foto) }}" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="pulsable min-h-11 text-sm text-acento hover:text-acento-fuerte">
                                        Retirar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Publicadas --}}
        <section class="mt-10" aria-labelledby="publicadas">
            <h2 id="publicadas" class="font-display text-lg font-semibold">En tu ficha</h2>

            @if ($aprobadas->isEmpty())
                <p class="mt-3 text-sm text-tenue">
                    Todavía no hay fotos aprobadas. Las que subas aparecerán aquí cuando el gremio las revise.
                </p>
            @else
                <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($aprobadas as $foto)
                        <li class="tarjeta overflow-hidden">
                            <img src="{{ $foto->getUrl() }}" alt="{{ $foto->name }}"
                                 loading="lazy" decoding="async" width="400" height="300"
                                 class="aspect-4/3 w-full object-cover">
                            <div class="p-4">
                                <p class="text-2xs uppercase tracking-wide text-exito-suave">Publicada</p>
                                <form method="POST" action="{{ route('mi-cuenta.fotos.destroy', $foto) }}" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="pulsable min-h-11 text-sm text-acento hover:text-acento-fuerte">
                                        Retirar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.publico>

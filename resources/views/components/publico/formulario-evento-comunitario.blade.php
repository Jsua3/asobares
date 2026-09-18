<dialog x-ref="formulario" x-on:close="alCerrar()" x-on:click.self="cerrar()" aria-modal="true"
        aria-labelledby="evento-comunitario-titulo" aria-describedby="evento-comunitario-aviso"
        class="eventos-editorial-modal">
    <div class="eventos-editorial-modal__cabecera">
        <div>
            <h2 id="evento-comunitario-titulo" class="font-display text-xl font-semibold">Agregar evento</h2>
            <p id="evento-comunitario-aviso" class="mt-1 text-sm text-tenue">
                Tu evento se publicará inmediatamente. ASOBARES podrá retirarlo después.
            </p>
        </div>
        <button type="button" x-on:click="cerrar()" class="eventos-editorial-modal__cerrar" aria-label="Cerrar formulario">&times;</button>
    </div>

    @if ($errors->any())
        <div role="alert" class="mt-5 rounded-xl border border-marca-500/40 bg-marca-panel px-4 py-3 text-sm text-acento-fuerte">
            <p class="font-semibold">Revisa estos datos:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('eventos.comunidad.store') }}" enctype="multipart/form-data" class="mt-5 space-y-3">
        @csrf
        @php($trampa = \App\Support\Formulario::CAMPO_TRAMPA)
        <div class="hidden" aria-hidden="true">
            <label for="{{ $trampa }}">No llenes este campo</label>
            <input type="text" id="{{ $trampa }}" name="{{ $trampa }}" tabindex="-1" autocomplete="off" value="">
        </div>

        <x-publico.campo nombre="titulo" etiqueta="Título" :requerido="true" />

        <div>
            <label for="evento-comunitario-fecha" class="mb-1.5 block text-sm font-medium text-tinta">Fecha <span aria-hidden="true">*</span></label>
            <input type="date" id="evento-comunitario-fecha" name="fecha" x-model="fecha" required
                   min="2020-01-01" max="{{ now()->addYears(5)->endOfYear()->toDateString() }}"
                   @error('fecha') aria-invalid="true" aria-describedby="evento-comunitario-fecha-error" @enderror
                   class="min-h-11 w-full rounded-xl border border-linea bg-fondo px-4 py-2.5 text-sm text-tinta">
            @error('fecha') <p id="evento-comunitario-fecha-error" class="mt-1 text-xs text-acento">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-publico.campo nombre="hora_inicio" etiqueta="Hora de inicio" tipo="time" :requerido="true" />
            <x-publico.campo nombre="hora_fin" etiqueta="Hora de fin" tipo="time"
                             ayuda="Si termina después de medianoche, se registrará al día siguiente." />
        </div>

        <x-publico.campo nombre="lugar" etiqueta="Lugar" :requerido="true" />
        <x-publico.campo nombre="descripcion" etiqueta="Descripción breve" tipo="textarea" :filas="3" :requerido="true" />
        <x-publico.campo nombre="enlace_externo" etiqueta="Enlace externo" tipo="url" ayuda="Instagram, boletería o sitio web. Solo http:// o https://." />

        <div>
            <label for="evento-comunitario-imagen" class="mb-1.5 block text-sm font-medium text-tinta">Imagen o afiche (opcional)</label>
            <input type="file" id="evento-comunitario-imagen" name="imagen" accept="image/jpeg,image/png,image/webp"
                   @error('imagen') aria-invalid="true" aria-describedby="evento-comunitario-imagen-error" @enderror
                   class="min-h-11 w-full rounded-xl border border-linea bg-fondo px-3 py-2 text-sm text-tinta">
            <p class="mt-1 text-xs text-tenue">JPG, PNG o WebP. Máximo 5 MB.</p>
            @error('imagen') <p id="evento-comunitario-imagen-error" class="mt-1 text-xs text-acento">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="eventos-editorial-modal__enviar min-h-11 w-full rounded-xl px-5 py-3 text-sm font-semibold">
            Publicar evento
        </button>
    </form>
</dialog>

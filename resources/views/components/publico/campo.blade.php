@props([
    'nombre',
    'etiqueta',
    'tipo' => 'text',
    'requerido' => false,
    'ayuda' => null,
    'placeholder' => null,
    'opciones' => null,
    'filas' => 4,
    'valor' => null,
])

@php
    $id = $nombre.'-'.Str::random(4);
    $hayError = $errors->has($nombre);
    $clases = 'w-full rounded-xl border bg-fondo px-4 py-2.5 text-sm text-tinta placeholder:text-apagado focus:outline-none focus:ring-2 focus:ring-marca-500/60 '
        .($hayError ? 'border-marca-500' : 'border-linea');

    // Un <select requerido> sin ninguna opción marcada como seleccionada
    // igual muestra la primera en pantalla: quien no lo toca manda ese
    // valor por defecto sin darse cuenta. Si quien llama al componente no
    // trajo ya su propia opción vacía (los filtros del muro sí traen la
    // suya, «Todos los municipios» y similares), se la anteponemos aquí.
    $necesitaOpcionVacia = $tipo === 'select' && $requerido && ! array_key_exists('', $opciones ?? []);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-tinta">
        {{ $etiqueta }}
        @if ($requerido)
            <span class="text-acento" aria-hidden="true">*</span>
            <span class="sr-only">(obligatorio)</span>
        @endif
    </label>

    @if ($tipo === 'textarea')
        <textarea id="{{ $id }}" name="{{ $nombre }}" rows="{{ $filas }}"
                  @required($requerido) placeholder="{{ $placeholder }}"
                  @if ($hayError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                  class="{{ $clases }}">{{ old($nombre, $valor) }}</textarea>
    @elseif ($tipo === 'select')
        <select id="{{ $id }}" name="{{ $nombre }}" @required($requerido)
                @if ($hayError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                class="{{ $clases }}">
            @if ($necesitaOpcionVacia)
                <option value="" disabled @selected(blank(old($nombre, $valor)))>Selecciona una opción</option>
            @endif
            @foreach ($opciones as $opcionValor => $opcionTexto)
                <option value="{{ $opcionValor }}" @selected(old($nombre, $valor) == $opcionValor)>{{ $opcionTexto }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $tipo }}" id="{{ $id }}" name="{{ $nombre }}" value="{{ old($nombre, $valor) }}"
               @required($requerido) placeholder="{{ $placeholder }}"
               @if ($hayError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
               class="{{ $clases }}">
    @endif

    @if ($ayuda && ! $hayError)
        <p class="mt-1.5 text-xs text-apagado">{{ $ayuda }}</p>
    @endif

    @error($nombre)
        <p id="{{ $id }}-error" class="mt-1.5 text-xs text-acento">{{ $message }}</p>
    @enderror
</div>

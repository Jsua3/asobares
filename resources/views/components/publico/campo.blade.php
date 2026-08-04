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

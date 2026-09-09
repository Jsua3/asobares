@php
    use App\Enums\CargoDelSolicitante;

    $opcionActual = old('solicitante_cargo_opcion', '');
    $esOtro = $opcionActual === CargoDelSolicitante::Otro->value;
    $idSelect = 'campo-solicitante-cargo-opcion';
    $idOtro = 'campo-solicitante-cargo-otro';
    $errorOpcion = $errors->has('solicitante_cargo_opcion');
    $errorOtro = $errors->has('solicitante_cargo_otro');
    $clases = 'w-full min-h-11 rounded-xl border bg-fondo px-4 py-2.5 text-sm text-tinta placeholder:text-apagado focus:border-marca-500 ';
@endphp

<div class="contents" x-data="{ esOtro: @js($esOtro) }">
    <div>
        <label for="{{ $idSelect }}" class="mb-1.5 block text-sm font-medium text-tinta">
            Cargo o rol
            <span class="text-acento" aria-hidden="true">*</span>
            <span class="sr-only">(obligatorio)</span>
        </label>

        <select id="{{ $idSelect }}"
                name="solicitante_cargo_opcion"
                required
                x-on:change="esOtro = $event.target.value === @js(CargoDelSolicitante::Otro->value)"
                @if ($errorOpcion) aria-invalid="true" @endif
                @if ($errorOpcion) aria-describedby="{{ $idSelect }}-error" @endif
                class="{{ $clases }}{{ $errorOpcion ? 'border-marca-500' : 'border-linea' }}">
            <option value="" disabled hidden @selected(blank($opcionActual))>Selecciona una opción</option>
            @foreach (CargoDelSolicitante::opcionesParaFormulario() as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($opcionActual === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>

        <div class="mt-1.5 min-h-5 space-y-1 text-xs">
            @error('solicitante_cargo_opcion')
                <p id="{{ $idSelect }}-error" class="text-acento">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div x-show="esOtro"
         x-cloak
         x-bind:aria-hidden="! esOtro">
        <label for="{{ $idOtro }}" class="mb-1.5 block text-sm font-medium text-tinta">
            Especifique el cargo o rol
            <span class="text-acento" aria-hidden="true">*</span>
            <span class="sr-only">(obligatorio cuando seleccionaste Otro)</span>
        </label>

        <input type="text"
               id="{{ $idOtro }}"
               name="solicitante_cargo_otro"
               value="{{ old('solicitante_cargo_otro') }}"
               x-bind:required="esOtro"
               placeholder="Coordinador operativo, socia fundadora..."
               @if ($errorOtro) aria-invalid="true" @endif
               @if ($errorOtro) aria-describedby="{{ $idOtro }}-error" @endif
               class="{{ $clases }}{{ $errorOtro ? 'border-marca-500' : 'border-linea' }}">

        <div class="mt-1.5 min-h-5 space-y-1 text-xs">
            @error('solicitante_cargo_otro')
                <p id="{{ $idOtro }}-error" class="text-acento">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{--
    Autorización de tratamiento de datos (Ley 1581 de 2012). Obligatoria en
    todo formulario que capture datos personales. Incluye el honeypot.
--}}
@php
    $id = 'habeas-'.Str::random(4);
    $trampa = \App\Support\Formulario::CAMPO_TRAMPA;
@endphp

{{-- Campo trampa: invisible para personas, irresistible para bots. --}}
<div class="hidden" aria-hidden="true">
    <label for="{{ $trampa }}">No llenes este campo</label>
    <input type="text" id="{{ $trampa }}" name="{{ $trampa }}" tabindex="-1" autocomplete="off" value="">
</div>

<div>
    <div class="flex items-start gap-3">
        <input type="checkbox" id="{{ $id }}" name="acepta_datos" value="1" required
               @checked(old('acepta_datos'))
               class="mt-0.5 h-4 w-4 shrink-0 rounded border-white/20 bg-noche-950 text-marca-500 focus:ring-2 focus:ring-marca-500/60">
        <label for="{{ $id }}" class="text-xs leading-relaxed text-noche-300">
            Autorizo a ASOBARES Capítulo Quindío a tratar mis datos personales para atender esta solicitud,
            conforme a la
            <a href="{{ route('politica-de-datos') }}" target="_blank" rel="noopener"
               class="text-marca-400 underline underline-offset-2 hover:text-marca-300">política de tratamiento de datos</a>.
            <span class="text-marca-400" aria-hidden="true">*</span>
        </label>
    </div>

    @error('acepta_datos')
        <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
    @enderror
</div>

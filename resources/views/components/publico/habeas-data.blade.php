{{--
    Autorización de tratamiento de datos (Ley 1581 de 2012). Obligatoria en
    todo formulario que capture datos personales. Incluye el honeypot.
--}}
@php
    // Determinista por el mismo motivo que en `campo.blade.php`: el id tiene que
    // sobrevivir al render para que el mensaje de error se pueda referenciar y
    // enlazar. Hay exactamente una casilla de estas por página en las siete
    // vistas que la usan, así que no puede colisionar consigo misma.
    $id = 'habeas-datos';
    $trampa = \App\Support\Formulario::CAMPO_TRAMPA;
@endphp

{{-- Campo trampa: invisible para personas, irresistible para bots. --}}
<div class="hidden" aria-hidden="true">
    <label for="{{ $trampa }}">No llenes este campo</label>
    <input type="text" id="{{ $trampa }}" name="{{ $trampa }}" tabindex="-1" autocomplete="off" value="">
</div>

<div>
    <div class="flex items-start gap-3">
        {{-- Ya no lleva el anillo de marca-500 al 60 %. Aquí el outline de
             `app.css` nunca llegó a apagarse, así que el foco cumplía y el
             anillo solo dibujaba encima suyo un duplicado de 2,21:1, por debajo
             del 3:1 de WCAG 2.1 §1.4.11. Todo el sitio acusa el foco con la
             misma regla o no hay forma de medirlo una sola vez. --}}
        {{-- Envoltorio de 44x44 con margen negativo: la casilla se sigue viendo
             de 16 px y en el mismo píxel —el reparto del margen negativo es
             asimétrico: `-mt-3` y `-mb-3.5` dejan la casilla 2 px más abajo del
             centro, que es justo el empujón que daba `mt-0.5`— pero el dedo
             tiene un objetivo de 44 y el cuadrado de 44x44 centrado en la
             casilla cae DENTRO de la etiqueta, que es lo que se mide.

             Y es un <label>, no un <span>, porque un <span> no es pulsable: el
             envoltorio dibujaría 44 px de área muerta y la medición saldría en
             verde igual. Una segunda etiqueta VACÍA para el mismo control es
             legal y no cambia el nombre accesible —la concatenación le añade
             una cadena vacía—, pero sí extiende la zona que dispara la casilla.

             Tampoco vale un `::before` sobre el propio <input>: sin
             `appearance:none` (no hay @tailwindcss/forms) la casilla es nativa
             y Chrome no le pinta pseudoelementos. El solape con la etiqueta de
             la derecha es inocuo: dispara el mismo control. --}}
        <label for="{{ $id }}" class="-mx-3.5 -mb-3.5 -mt-3 flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center">
            <input type="checkbox" id="{{ $id }}" name="acepta_datos" value="1" required
                   @checked(old('acepta_datos'))
                   @error('acepta_datos') aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
                   class="h-4 w-4 rounded border-linea-fuerte bg-fondo text-marca-500">
        </label>
        <label for="{{ $id }}" class="min-h-11 text-xs leading-relaxed text-tenue">
            Autorizo a ASOBARES Capítulo Quindío a tratar mis datos personales para atender esta solicitud
            —incluida su entrega a terceros cuando el trámite lo requiera—, conforme a la
            <a href="{{ route('politica-de-datos') }}" target="_blank" rel="noopener"
               class="enlace-accion text-acento underline underline-offset-2 hover:text-acento-fuerte">política de tratamiento de datos</a>.
            {{-- El asterisco viaja con su equivalente hablado, como en
                 `campo.blade.php`: el sitio dice la misma cosa de la misma
                 forma en los dos únicos sitios donde la dice. --}}
            <span class="text-acento" aria-hidden="true">*</span>
            <span class="sr-only">(obligatorio)</span>
        </label>
    </div>

    @error('acepta_datos')
        <p id="{{ $id }}-error" class="mt-1.5 text-xs text-acento">{{ $message }}</p>
    @enderror
</div>

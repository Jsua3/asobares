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
    // Determinista, y no `Str::random(4)`: un id que cambia en cada render no
    // rompe el `<label for>` —se calcula una vez— pero sí todo lo que necesita
    // nombrar el campo desde fuera: enlazar a /afiliate#campo-correo, un resumen
    // de errores en cabecera, la heurística de autorrelleno del navegador y
    // cualquier selector de prueba. Colisionaría si una página repitiera un
    // `nombre`; hoy ninguna lo hace y `FocoVisibleTest` lo vigila.
    $id = 'campo-'.Str::slug(str_replace(['[', ']', '.'], ['-', '', '-'], $nombre));
    $hayError = $errors->has($nombre);

    // `aria-describedby` admite varios ids separados por espacio. Hasta hoy solo
    // se emitía al errar, así que las once ayudas del sitio no existían para un
    // lector de pantalla ni cuando se veían en pantalla (SC 1.3.1). La ayuda va
    // primero porque describe el formato; el error, que es la consecuencia de
    // no seguirlo, se anuncia después.
    $descripciones = array_filter([
        $ayuda ? $id.'-ayuda' : null,
        $hayError ? $id.'-error' : null,
    ]);

    /*
     * Lo que este atributo NO lleva es la mitad del diseño, así que va escrito.
     *
     * Llevaba la utilidad que apaga el outline en foco. Compila en
     * `@layer utilities` y el `:focus-visible` del proyecto (`app.css`) vive en
     * `@layer base`: utilities gana a base por orden de capa, sin que la
     * especificidad entre en juego, así que apagaba el único indicador de foco
     * de todos los formularios del sitio. Y el anillo de marca-500 al 60 % que
     * lo sustituía, compuesto sobre lo que tiene detrás, da 2,21:1 en claro y
     * 2,47:1 en oscuro: por debajo del 3:1 que exige WCAG 2.1 §1.4.11, o sea
     * visible lo justo para que nadie lo denunciara y no lo bastante para
     * cumplir. Sin las dos gobierna el outline de `app.css`: 3,49:1 y 5,15:1.
     *
     * `focus:border-marca-500` repone la única pérdida real de quitarlas:
     * `:focus-visible` no empareja en un `<select>` desplegado con ratón —ni en
     * Chrome ni en Firefox— y el `focus:` viejo sí. Es un borde OPACO, con las
     * mismas cifras que el outline por ser el mismo color sin transparencia. El
     * indicador de foco sigue siendo el outline; el borde solo dice qué campo
     * está activo, y en un campo errado ya era rojo de todos modos.
     *
     * `FocoVisibleTest` vigila las dos cosas: que ninguna vista vuelva a apagar
     * el outline, y que ningún anillo translúcido se reponga por debajo de 3:1.
     */
    // `min-h-11` y no `py-3`: con la escala óptica vigente el control mide
    // 21,7 + 20 + 2 = 43,7 px y solo le faltan 0,3. Subir el relleno movería
    // el texto de los tres controles en todos los formularios del sitio para
    // ganar lo mismo; el mínimo de altura no mueve nada. El <textarea> lo
    // ignora porque sus cuatro filas ya pasan de 44.
    $clases = 'w-full min-h-11 rounded-xl border bg-fondo px-4 py-2.5 text-sm text-tinta placeholder:text-apagado focus:border-marca-500 '
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
                  @if ($hayError) aria-invalid="true" @endif
                  @if ($descripciones) aria-describedby="{{ implode(' ', $descripciones) }}" @endif
                  class="{{ $clases }}">{{ old($nombre, $valor) }}</textarea>
    @elseif ($tipo === 'select')
        <select id="{{ $id }}" name="{{ $nombre }}" @required($requerido)
                @if ($hayError) aria-invalid="true" @endif
                @if ($descripciones) aria-describedby="{{ implode(' ', $descripciones) }}" @endif
                class="{{ $clases }}">
            @if ($necesitaOpcionVacia)
                {{-- `hidden` además de `disabled`: sin él el marcador de posición
                     sigue ocupando un renglón en la lista desplegada, aunque no
                     se pueda elegir. Donde el navegador no lo entienda se queda
                     el comportamiento de hoy, que ya era correcto. --}}
                <option value="" disabled hidden @selected(blank(old($nombre, $valor)))>Selecciona una opción</option>
            @endif
            {{-- `?? []` porque el componente no puede reventar con
                 «foreach() argument must be of type array|object» si alguien
                 escribe `tipo="select"` y se le olvidan las opciones. --}}
            @foreach ($opciones ?? [] as $opcionValor => $opcionTexto)
                <option value="{{ $opcionValor }}" @selected(old($nombre, $valor) == $opcionValor)>{{ $opcionTexto }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $tipo }}" id="{{ $id }}" name="{{ $nombre }}" value="{{ old($nombre, $valor) }}"
               @required($requerido) placeholder="{{ $placeholder }}"
               @if ($hayError) aria-invalid="true" @endif
               @if ($descripciones) aria-describedby="{{ implode(' ', $descripciones) }}" @endif
               class="{{ $clases }}">
    @endif

    {{-- Ranura de apoyo INCONDICIONAL, y las dos cosas que arregla se pierden
         si alguien vuelve a condicionarla:

         1. La ayuda ya no desaparece al errar. Quien acaba de incumplir el
            formato («Solo YouTube», «Déjalo vacío si prefieres decir “a
            convenir”») es exactamente quien necesita releerlo.
         2. La rejilla deja de saltar. `min-h-5` reserva 20 px, que cubren la
            caja de una línea de `text-xs` — 0,75rem × 1,6 = 19,2 px con la
            escala óptica de `app.css`, no los 16 px del default de fábrica.
            Sin la reserva, estrenar un error empujaba al campo vecino en los
            `sm:grid-cols-2`. `FocoVisibleTest` recalcula esa caja contra la
            escala vigente: si la tipografía cambia, la prueba pide el
            `min-h-*` siguiente en vez de dejar que la rejilla salte otra vez.

         El precio son 26 px bajo cada campo que no trae ayuda. Si un
         formulario respira de más, se recorta su `space-y-*`; volver a
         condicionar esta ranura reabre las dos brechas de arriba. --}}
    <div class="mt-1.5 min-h-5 space-y-1 text-xs">
        @if ($ayuda)
            <p id="{{ $id }}-ayuda" class="text-apagado">{{ $ayuda }}</p>
        @endif

        @error($nombre)
            <p id="{{ $id }}-error" class="text-acento">{{ $message }}</p>
        @enderror
    </div>
</div>

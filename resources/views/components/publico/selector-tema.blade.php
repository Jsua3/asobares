{{--
    Selector de apariencia: claro u oscuro.

    Se apoya en `$store.tema`, así que si aparece en más de una superficie se
    marca siempre a la vez. Escribir aquí cambia también el panel /admin:
    comparten la clave.
--}}
@php
    $opciones = [
        [
            'valor' => 'light',
            'etiqueta' => 'Claro',
            'icono' => 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
        ],
        [
            'valor' => 'dark',
            'etiqueta' => 'Oscuro',
            'icono' => 'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z',
        ],
    ];
@endphp

<div role="group" aria-label="Apariencia del sitio"
     {{ $attributes->merge(['class' => 'flex items-center gap-1 rounded-xl border border-linea p-1']) }}>
    @foreach ($opciones as $opcion)
        <button type="button"
                x-on:click="$store.tema.elegir('{{ $opcion['valor'] }}')"
                x-bind:aria-pressed="$store.tema.preferencia === '{{ $opcion['valor'] }}' ? 'true' : 'false'"
                {{-- El anillo no es decoración: el relleno al 15 % solo da
                     1,22:1 contra el panel, así que sin él la opción elegida no
                     se distingue de las otras dos sin percibir el color. --}}
                x-bind:class="$store.tema.preferencia === '{{ $opcion['valor'] }}'
                    ? 'bg-marca-500/15 text-acento ring-1 ring-acento'
                    : 'text-apagado hover:bg-superficie-alta hover:text-tinta'"
                class="pulsable flex min-h-11 flex-1 items-center justify-center rounded-lg px-3"
                title="{{ $opcion['etiqueta'] }}">
            <svg class="h-[1.125rem] w-[1.125rem]" fill="none" stroke="currentColor" stroke-width="1.7"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $opcion['icono'] }}"/>
            </svg>
            <span class="sr-only">{{ $opcion['etiqueta'] }}</span>
        </button>
    @endforeach
</div>

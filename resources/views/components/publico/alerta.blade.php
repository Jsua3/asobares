@props(['tipo' => 'exito'])

@php
    $estilos = match ($tipo) {
        'error' => 'border-marca-500/40 bg-marca-panel text-acento-fuerte',
        'aviso' => 'border-aviso-linea bg-aviso-fondo text-aviso-suave',
        default => 'border-exito-linea bg-exito-fondo text-exito-suave',
    };
    $icono = match ($tipo) {
        'error' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        'aviso' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        default => 'm9 12.75 2.25 2.25 4.5-4.5m5.25 1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    };
@endphp

<div role="status" {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3.5 text-sm {$estilos}"]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icono }}"/>
    </svg>
    <div class="leading-relaxed">{{ $slot }}</div>
</div>

@php
    /**
     * El módulo de cuenta, al pie de la barra lateral (D-L21).
     *
     * Baja aquí desde la parte superior por dos razones que Sua nombró el 7 sep:
     * la barra necesitaba el tercer módulo que le faltaba, y la cuenta necesitaba
     * el nombre y el rango que el círculo de iniciales no mostraba. Es el mismo
     * chip que la barra pública de escritorio, girado al pie.
     *
     * Es un «disclosure», no un menú ARIA: botón con aria-expanded más el panel
     * que controla. Sin aria-haspopup ni role="menu", que anunciarían navegación
     * con flechas que este panel no implementa.
     */
    /*
     * `$donde` dice en cuál de los dos ganchos se está pintando: 'cromo' en
     * escritorio, 'pie' en el teléfono. Dos copias del mismo componente con el
     * mismo `id` dejarían un `aria-controls` apuntando a dos sitios, y el
     * lector de pantalla se queda con el primero. Por eso el identificador se
     * compone y no es literal.
     */
    $donde = $donde ?? 'cromo';
    $hoja = 'asb-hoja-cuenta-'.$donde;

    $usuario = filament()->auth()->user();

    $rango = match (true) {
        (bool) $usuario?->esSuperAdmin() => 'Dirección del gremio',
        (bool) $usuario?->esSubadmin() => 'Secretaría del gremio',
        default => 'Cuenta del gremio',
    };

    $iniciales = collect(explode(' ', trim((string) $usuario?->name)))
        ->filter()
        ->take(2)
        ->map(fn (string $parte): string => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

@auth
    <div class="asb-barra-cuenta asb-cuenta-{{ $donde === 'pie' ? 'al-pie' : 'en-el-cromo' }}"
         x-data="{
             abierto: false,
             cerrar() { this.abierto = false; },
             cerrarYVolverAlFoco() { this.abierto = false; this.$refs.disparador?.focus(); },
         }"
         x-on:keydown.escape.window="cerrarYVolverAlFoco()"
         x-on:pointerdown.outside="cerrar()">
        <button type="button"
                x-ref="disparador"
                x-on:click="abierto = ! abierto"
                x-bind:aria-expanded="abierto ? 'true' : 'false'"
                aria-controls="{{ $hoja }}"
                class="asb-barra-chip">
            <span class="sr-only">{{ $usuario?->name }}, {{ $rango }}: perfil y sesión</span>

            <span aria-hidden="true" class="asb-barra-avatar">{{ $iniciales }}</span>

            <span aria-hidden="true" class="min-w-0 flex-1 text-left">
                <span class="block truncate text-sm font-medium">{{ $usuario?->name }}</span>
                <span class="block truncate text-2xs">{{ $rango }}</span>
            </span>

            <x-heroicon-o-chevron-up-down aria-hidden="true" class="h-4 w-4 shrink-0" />
        </button>
        {{-- Arriba abre hacia abajo; al pie de la barra, hacia arriba. --}}
        <div x-cloak
             x-show="abierto"
             x-transition:enter="transicion-desplegable ease-out duration-(--duracion-entrada)"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             id="{{ $hoja }}"
             class="asb-barra-hoja">
            <a href="{{ route('filament.admin.auth.profile') }}"
               class="asb-barra-fila">
                <x-heroicon-o-user-circle class="h-5 w-5 shrink-0" aria-hidden="true" />
                <span>Mi perfil</span>
            </a>

            <a href="{{ route('inicio') }}" class="asb-barra-fila">
                <x-heroicon-o-globe-alt class="h-5 w-5 shrink-0" aria-hidden="true" />
                <span>Ver el sitio</span>
            </a>

            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit" class="asb-barra-fila w-full">
                    <x-heroicon-o-arrow-left-on-rectangle class="h-5 w-5 shrink-0" aria-hidden="true" />
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>

    </div>
@endauth

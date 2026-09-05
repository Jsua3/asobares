{{--
    Desplegable de cuenta de la navbar.

    El cambio de apariencia vive en la barra lateral (móvil) y en el control
    de tema (escritorio); este menú queda solo para sesión, con el atajo al
    sitio que corresponde: /mi-cuenta al dueño del establecimiento, /admin a
    la secretaría y a la dirección.
--}}
@php
    $usuario = auth()->user();

    $iniciales = $usuario
        ? Str::of($usuario->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $parte): string => Str::upper(Str::substr($parte, 0, 1)))
            ->implode('')
        : null;

    $esDelEquipo = (bool) ($usuario?->esSuperAdmin() || $usuario?->esSubadmin());

    $rol = match (true) {
        (bool) $usuario?->esSuperAdmin() => 'Dirección del gremio',
        (bool) $usuario?->esSubadmin() => 'Secretaría del gremio',
        (bool) $usuario?->esAsociado() => $usuario->asociado?->nombre ?? 'Establecimiento afiliado',
        default => null,
    };

    /* Lo que va en la barra, corto: el rol largo sigue dentro del panel. */
    $prefijoRol = match (true) {
        (bool) $usuario?->esSuperAdmin() => 'Admin',
        (bool) $usuario?->esSubadmin() => 'Sec.',
        default => null,
    };
@endphp

{{-- Es un «disclosure», no un menú ARIA: botón con aria-expanded más el panel
     que controla. Por eso no lleva aria-haspopup ni role="menu", que anunciarían
     navegación con flechas que este panel no implementa. --}}
@auth
<div x-data="desplegable"
     x-on:pointerenter="asomar($event)"
     x-on:pointerleave="retirar($event)"
     x-on:desplegable-abierto.window="ceder($event.detail)"
     x-on:click.outside="cerrar()"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     {{-- Ya no es el último control de la barra: le siguen el tema y el
          idioma, así que tabular fuera tiene que cerrarlo como a los grupos. --}}
     x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"
     class="relative">

    <button type="button"
            x-ref="disparador"
            x-on:click="alternar()"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="menu-cuenta"
            {{-- Padding negativo óptico: `p-1` lleva el botón a 44x44 y `-m-1`
                 devuelve al flujo los 36x36 del avatar, que es marca y no se
                 puede agrandar. El nombre al lado es escritorio: en móvil el
                 panel ya lo escribe. --}}
            class="pulsable -m-1 flex items-center gap-2 rounded-full p-1 text-tenue hover:text-tinta">
        <span class="sr-only">Configuración y sesión de {{ $usuario->name }}</span>
        <span aria-hidden="true"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-marca-500 text-xs font-bold tracking-wide text-white">
            {{ $iniciales }}
        </span>
        <span aria-hidden="true" class="hidden max-w-40 truncate pr-1 text-sm font-medium lg:block">
            @if ($prefijoRol)<span class="text-apagado">{{ $prefijoRol }}</span> @endif{{ $usuario->name }}
        </span>
    </button>

    <div id="menu-cuenta"
         x-show="abierto"
         x-cloak
         x-transition:enter="transicion-desplegable ease-out duration-(--duracion-entrada)"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         {{-- `hoja-flotante` en vez de `shadow-xl shadow-black/20`: aquella
              sombra era negro cableado, o sea la receta CLARA aplicada a los
              dos temas, y sobre Pub Black una sombra negra no se ve. El
              portador trae las dos recetas — sombra en claro, filo de luz en
              oscuro— desde los tokens. --}}
         class="hoja-flotante absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-2xl p-2">

        <div class="border-b border-linea px-3 pb-3 pt-2">
            <p class="truncate text-sm font-semibold text-fuerte">{{ $usuario->name }}</p>
            @if ($rol)
                <p class="mt-0.5 truncate text-xs text-apagado">{{ $rol }}</p>
            @endif
        </div>

        <div class="pt-2">
            @if ($esDelEquipo)
                <a href="{{ route('filament.admin.pages.dashboard') }}"
                   class="fila-pulsable flex items-center gap-2.5 rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12 11.204 3.045c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                    </svg>
                    Ir al panel del gremio
                </a>
            @endif

            @if ($usuario?->esAsociado())
                <a href="{{ route('mi-cuenta.index') }}"
                   class="fila-pulsable flex items-center gap-2.5 rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                    Mi cuenta
                </a>

                <a href="{{ route('mi-cuenta.vacantes.index') }}"
                   class="fila-pulsable flex items-center gap-2.5 rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                    Mis vacantes
                </a>
            @endif

            <form method="POST" action="{{ route('mi-cuenta.salir') }}">
                @csrf
                <button type="submit"
                        class="fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm text-suave hover:text-fuerte">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</div>
@endauth

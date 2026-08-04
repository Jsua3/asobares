@php
    $esDelEquipo = $usuario?->esSuperAdmin() || $usuario?->esSubadmin();
    $rol = match (true) {
        (bool) $usuario?->esSuperAdmin() => 'la dirección del gremio',
        (bool) $usuario?->esSubadmin() => 'la secretaría del gremio',
        default => 'un usuario sin establecimiento vinculado',
    };
@endphp

<x-layouts.publico titulo="Esta sección es para los afiliados — ASOBARES Quindío"
                   descripcion="Acceso exclusivo de los establecimientos afiliados al capítulo.">

    <div class="resplandor-marca flex min-h-[75vh] items-center">
        <div class="mx-auto w-full max-w-lg px-4 py-16 text-center sm:px-6">

            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-marca-500/15">
                <svg class="h-7 w-7 text-acento" fill="none" stroke="currentColor" stroke-width="1.7"
                     viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </span>

            <h1 class="mt-6 font-display text-2xl font-bold text-balance sm:text-3xl">
                Esta sección es para los establecimientos afiliados
            </h1>

            @auth
                <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-tenue text-pretty">
                    Tienes la sesión abierta como <strong class="text-tinta">{{ $usuario->name }}</strong>,
                    que es {{ $rol }}. Mi cuenta muestra el estado de cartera de un establecimiento, así que
                    hay que entrar con el usuario del dueño.
                </p>

                <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                    @if ($esDelEquipo)
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                           class="rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                            Ir al panel del gremio
                        </a>
                    @endif

                    <form method="POST" action="{{ route('mi-cuenta.salir') }}">
                        @csrf
                        <input type="hidden" name="destino" value="entrar">
                        <button type="submit"
                                class="w-full rounded-xl border border-linea-fuerte px-6 py-3 text-sm font-semibold transition-colors hover:border-marca-500/50 sm:w-auto">
                            Cerrar sesión y entrar como afiliado
                        </button>
                    </form>
                </div>
            @else
                <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-tenue text-pretty">
                    Tu usuario todavía no está vinculado a ningún establecimiento. Escríbenos y lo revisamos.
                </p>

                <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('mi-cuenta.entrar') }}"
                       class="rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                        Entrar con otra cuenta
                    </a>
                    <a href="{{ route('contacto') }}"
                       class="rounded-xl border border-linea-fuerte px-6 py-3 text-sm font-semibold transition-colors hover:border-marca-500/50">
                        Escribirnos
                    </a>
                </div>
            @endauth

            <p class="mt-8 text-xs text-apagado">
                ¿Todavía no eres afiliado?
                <a href="{{ route('afiliate') }}" class="text-acento hover:text-acento-fuerte">Conoce la afiliación</a>.
            </p>
        </div>
    </div>
</x-layouts.publico>

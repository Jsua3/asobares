<x-layouts.publico titulo="Seguridad de la cuenta — ASOBARES Quindío"
                   descripcion="Cambia la contraseña de tu acceso a Mi Cuenta.">

    <div class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">{{ ajuste('mi_cuenta_seguridad_titulo', 'Seguridad de la cuenta') }}</h1>
            <p class="mt-1.5 text-sm text-tenue">
                @if ($usuario->contrasena_provisional)
                    {{ ajuste('mi_cuenta_seguridad_provisional_texto', 'Entraste con la contraseña provisional que el gremio les entregó a los afiliados. Cámbiala por una que solo conozcas tú: hasta entonces, las secciones con datos de otras personas siguen cerradas.') }}
                @else
                    {{ ajuste('mi_cuenta_seguridad_texto', 'Cambia tu contraseña cuando quieras. Al guardarla se cierran las sesiones que tengas abiertas en otros equipos.') }}
                @endif
            </p>
        </header>

        @if (session('aviso'))
            <x-publico.alerta tipo="aviso" class="mt-8">{{ session('aviso') }}</x-publico.alerta>
        @endif

        {{-- Los nombres de los campos son los que Laravel no devuelve a la
             sesión tras un error: ver SeguridadDeLaCuentaController. --}}
        <form method="POST" action="{{ route('mi-cuenta.seguridad.actualizar') }}" class="tarjeta mt-8 space-y-5 p-7">
            @csrf
            @method('PUT')

            <x-publico.campo nombre="current_password" etiqueta="Contraseña actual" tipo="password" requerido />
            <x-publico.campo nombre="password" etiqueta="Contraseña nueva" tipo="password" requerido
                             ayuda="Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos." />
            <x-publico.campo nombre="password_confirmation" etiqueta="Confirma la contraseña nueva" tipo="password" requerido />

            <x-publico.boton class="w-full">
                Guardar contraseña
            </x-publico.boton>
        </form>
    </div>
</x-layouts.publico>

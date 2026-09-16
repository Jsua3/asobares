<x-layouts.publico titulo="Entrar a mi cuenta — ASOBARES Quindío"
                   descripcion="Acceso para los establecimientos afiliados: consulta tu estado de cuenta y el detalle de los convenios."
                   :sin-navegacion="true">

    <div class="acceso-asociado">
        <a href="{{ route('inicio') }}" class="acceso-asociado__volver">
            <x-publico.flecha direccion="izquierda" />&nbsp;Volver al sitio
        </a>

        <div class="acceso-asociado__marco">
            <div class="text-center">
                <a href="{{ route('inicio') }}" class="acceso-asociado__marca" aria-label="Ir al inicio, ASOBARES Capítulo Quindío">
                    <x-publico.logo alto="h-10" class="mx-auto" />
                </a>
                <h1 class="mt-5 font-display text-2xl font-bold">Entra a tu cuenta</h1>
                <p class="mt-2 text-sm text-tenue">
                    Para los establecimientos afiliados al capítulo.
                </p>
            </div>

            @if (session('exito'))
                <x-publico.alerta class="mt-6">{{ session('exito') }}</x-publico.alerta>
            @endif

            <form method="POST" action="{{ route('mi-cuenta.entrar.post') }}" class="acceso-asociado__tarjeta mt-8 space-y-5 p-7">
                @csrf

                <x-publico.campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
                <x-publico.campo nombre="password" etiqueta="Contraseña" tipo="password" requerido />

                {{-- La etiqueta ES el objetivo: envuelve a la casilla de 16 px y dispara
                     el mismo control. Con `min-h-11` toda la fila pasa a 44. --}}
                <label class="flex min-h-11 items-center gap-2.5 text-sm text-tenue">
                    <input type="checkbox" name="recordarme" value="1"
                           class="h-4 w-4 rounded border-linea-fuerte bg-fondo text-marca-500">
                    Mantener la sesión iniciada
                </label>

                <x-publico.boton class="w-full">
                    Entrar
                </x-publico.boton>
            </form>

            <p class="mt-6 text-center text-xs leading-relaxed text-apagado">
                ¿Todavía no eres afiliado?
                <a href="{{ route('afiliate') }}" class="enlace-accion text-acento hover:text-acento-fuerte">Conoce la afiliación</a>.
                <br>
                El equipo del gremio ingresa por
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="enlace-accion text-acento hover:text-acento-fuerte">/admin</a>.
            </p>
        </div>
    </div>
</x-layouts.publico>

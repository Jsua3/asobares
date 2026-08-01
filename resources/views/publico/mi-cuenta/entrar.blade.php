<x-layouts.publico titulo="Entrar a mi cuenta — ASOBARES Quindío"
                   descripcion="Acceso para los establecimientos afiliados: consulta tu estado de cuenta y el detalle de los convenios.">

    <div class="resplandor-marca flex min-h-[75vh] items-center">
        <div class="mx-auto w-full max-w-md px-4 py-16 sm:px-6">

            <div class="text-center">
                <x-publico.logo alto="h-10" class="mx-auto" />
                <h1 class="mt-5 font-display text-2xl font-bold">Entra a tu cuenta</h1>
                <p class="mt-2 text-sm text-noche-300">
                    Para los establecimientos afiliados al capítulo.
                </p>
            </div>

            @if (session('exito'))
                <x-publico.alerta class="mt-6">{{ session('exito') }}</x-publico.alerta>
            @endif

            <form method="POST" action="{{ route('mi-cuenta.entrar.post') }}" class="tarjeta mt-8 space-y-5 p-7">
                @csrf

                <x-publico.campo nombre="email" etiqueta="Correo electrónico" tipo="email" requerido />
                <x-publico.campo nombre="password" etiqueta="Contraseña" tipo="password" requerido />

                <label class="flex items-center gap-2.5 text-sm text-noche-300">
                    <input type="checkbox" name="recordarme" value="1"
                           class="h-4 w-4 rounded border-white/20 bg-noche-950 text-marca-500 focus:ring-2 focus:ring-marca-500/60">
                    Mantener la sesión iniciada
                </label>

                <button type="submit"
                        class="w-full rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                    Entrar
                </button>
            </form>

            <p class="mt-6 text-center text-xs leading-relaxed text-noche-400">
                ¿Todavía no eres afiliado?
                <a href="{{ route('afiliate') }}" class="text-marca-400 hover:text-marca-300">Conoce la afiliación</a>.
                <br>
                El equipo del gremio ingresa por <a href="/admin" class="text-marca-400 hover:text-marca-300">/admin</a>.
            </p>
        </div>
    </div>
</x-layouts.publico>

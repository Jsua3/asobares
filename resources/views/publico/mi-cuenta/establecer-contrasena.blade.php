<x-layouts.publico :titulo="'Crear contraseña — ASOBARES Quindío'"
                   :descripcion="'Define la contraseña de tu acceso a Mi Cuenta de ASOBARES Quindío.'">
    <section class="mx-auto flex min-h-[70vh] max-w-xl items-center px-4 py-16 sm:px-6 lg:px-8">
        <div class="vidrio w-full rounded-[1.75rem] p-7 sm:p-9">
            <p class="antetitulo text-acento">Mi Cuenta</p>
            <h1 class="mt-3 font-display text-3xl font-bold">Crea tu contraseña</h1>
            <p class="mt-3 text-sm leading-relaxed text-tenue">
                Usa una contraseña propia. ASOBARES no la conocerá ni la enviará por correo.
            </p>

            <form method="POST" action="{{ route('mi-cuenta.password.update') }}" class="mt-7 space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <x-publico.campo nombre="email" etiqueta="Correo electrónico" tipo="email" :valor="$email" requerido />
                <x-publico.campo nombre="password" etiqueta="Contraseña" tipo="password" requerido
                                 ayuda="Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos." />
                <x-publico.campo nombre="password_confirmation" etiqueta="Confirmar contraseña" tipo="password" requerido />

                <x-publico.boton class="w-full">
                    Crear contraseña
                </x-publico.boton>
            </form>
        </div>
    </section>
</x-layouts.publico>

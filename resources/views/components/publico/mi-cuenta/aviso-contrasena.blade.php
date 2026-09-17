{{--
    Aviso de contraseña provisional, arriba de las páginas de Mi Cuenta que
    siguen abiertas mientras la marca esté puesta.

    El aviso ENTERO es el enlace, y no un botón dentro de una alerta: en el
    teléfono se toca donde sea. `min-h-11` le da los 44 px de objetivo táctil.
    No lleva `role="status"`: está ahí desde que carga la página, no es un
    mensaje que llega después.
--}}
@if (auth()->user()?->contrasena_provisional)
    <a href="{{ route('mi-cuenta.seguridad') }}"
       {{ $attributes->merge(['class' => 'flex min-h-11 items-start gap-3 rounded-xl border border-aviso-linea bg-aviso-fondo px-4 py-3.5 text-sm text-aviso-suave']) }}>
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
        </svg>
        <span class="leading-relaxed">
            <span class="block font-semibold">{{ ajuste('mi_cuenta_aviso_provisional_titulo', 'Estás usando la contraseña provisional que te dio el gremio') }}</span>
            <span class="block">{{ ajuste('mi_cuenta_aviso_provisional_texto', 'Toca aquí para cambiarla por una tuya. Mientras tanto, el banco de talento, los proveedores, los artistas y la bolsa de empleo siguen cerrados.') }}</span>
        </span>
    </a>
@endif

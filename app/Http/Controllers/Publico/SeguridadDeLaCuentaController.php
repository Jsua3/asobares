<?php

namespace App\Http\Controllers\Publico;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * El titular de una cuenta de afiliado cambia su contraseña desde /mi-cuenta.
 *
 * Es la única puerta que apaga `contrasena_provisional`: la genérica de la
 * importación y la que escribe la oficina en el panel las conoce alguien más.
 *
 * Tres cosas que no son de estilo:
 *
 * - **Los campos se llaman `current_password`, `password` y
 *   `password_confirmation`.** Son los que Laravel no devuelve a la sesión al
 *   fallar la validación; con otro nombre la contraseña viajaría a la tabla
 *   de sesiones y el componente `campo` la pintaría en el HTML.
 * - **Cada regla lleva su mensaje escrito.** No hay `lang/`, y la regla
 *   Password falla con `password.symbols` y compañía: sin la clave exacta se
 *   imprime la clave cruda.
 * - **Las demás sesiones se cierran solas.** Cada sesión guarda al entrar el
 *   hash de la contraseña con la que entró (el oyente de `Login` en
 *   `AppServiceProvider`) y `AuthenticateSession` (grupo `web`) lo compara en
 *   cada petición: al cambiarlo, las sesiones abiertas con la contraseña vieja
 *   caen en su siguiente petición, y la de quien la cambió guarda el hash
 *   nuevo al terminar esta. Quien entró con la genérica antes que el dueño no
 *   sobrevive al cambio, aunque no haya hecho más que el POST de entrada.
 */
class SeguridadDeLaCuentaController
{
    public function editar(Request $request): View
    {
        return view('publico.mi-cuenta.seguridad', [
            'usuario' => $request->user(),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Escribe tu contraseña actual.',
            'current_password.current_password' => 'Esa no es tu contraseña actual.',
            'password.required' => 'Escribe la contraseña nueva.',
            'password.confirmed' => 'La confirmación no coincide con la contraseña nueva.',
            'password.different' => 'La contraseña nueva tiene que ser distinta de la actual.',
            'password.min' => 'La contraseña nueva necesita al menos 12 caracteres.',
            'password.password.mixed' => 'La contraseña nueva necesita al menos una mayúscula y una minúscula.',
            'password.password.numbers' => 'La contraseña nueva necesita al menos un número.',
            'password.password.symbols' => 'La contraseña nueva necesita al menos un símbolo.',
        ]);

        /** @var User $usuario */
        $usuario = $request->user();

        // Sueltos y no en `update()`: `#[Fillable]` descartaría la marca en
        // silencio. El cast `hashed` calcula el hash de la contraseña.
        $usuario->password = $datos['password'];
        $usuario->remember_token = Str::random(60);
        $usuario->contrasena_provisional = false;
        $usuario->save();

        $request->session()->regenerate();

        activity('sesion')
            ->causedBy($usuario)
            ->performedOn($usuario)
            ->event('updated')
            ->log('cambió su contraseña');

        return redirect()
            ->route('mi-cuenta.index')
            ->with('exito', 'Listo: tu contraseña quedó cambiada. Ya puedes entrar a todas las secciones de Mi Cuenta.');
    }
}

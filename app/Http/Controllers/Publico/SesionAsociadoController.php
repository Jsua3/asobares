<?php

namespace App\Http\Controllers\Publico;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Login propio de /mi-cuenta, con la identidad oscura del sitio.
 * El panel de Filament tiene el suyo aparte, en /admin/login.
 */
class SesionAsociadoController
{
    public function mostrarFormulario(): View|RedirectResponse
    {
        if (Auth::user() instanceof User && Auth::user()->esAsociado()) {
            return redirect()->route('mi-cuenta.index');
        }

        return view('publico.mi-cuenta.entrar');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'correo',
            'password' => 'contraseña',
        ]);

        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'email' => 'Ese correo y esa contraseña no coinciden.',
            ]);
        }

        $usuario = Auth::user();

        // El panel y /mi-cuenta comparten guard: si entra alguien del equipo
        // por acá, se le devuelve a su sitio en vez de dejarlo a medio camino.
        if (! $usuario instanceof User || ! $usuario->esAsociado()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Esta entrada es para establecimientos afiliados. El equipo del gremio ingresa por /admin.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('mi-cuenta.index'));
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Quien sale desde la pantalla de «sesión equivocada» quiere entrar
        // con otra cuenta, no volver al inicio.
        if ($request->input('destino') === 'entrar') {
            return redirect()->route('mi-cuenta.entrar')
                ->with('exito', 'Sesión cerrada. Ahora entra con el usuario del establecimiento.');
        }

        return redirect()->route('inicio')->with('exito', 'Cerraste sesión.');
    }
}

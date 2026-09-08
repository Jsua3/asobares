<?php

namespace App\Http\Controllers\Publico;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ContrasenaAsociadoController
{
    public function editar(Request $request, string $token): View
    {
        return view('publico.mi-cuenta.establecer-contrasena', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ], [], [
            'email' => 'correo',
            'password' => 'contraseña',
        ]);

        $estado = Password::reset($datos, function ($usuario, string $password): void {
            $usuario->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($estado !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($estado)]);
        }

        return redirect()
            ->route('mi-cuenta.entrar')
            ->with('exito', 'Contraseña creada. Ahora entra a Mi Cuenta con tu correo.');
    }
}

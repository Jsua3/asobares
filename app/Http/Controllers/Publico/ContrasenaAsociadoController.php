<?php

namespace App\Http\Controllers\Publico;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ContrasenaAsociadoController
{
    private const string ENLACE_NO_VALIDO = 'Este enlace ya no es válido para ese correo. Si ya creaste tu contraseña, entra a Mi Cuenta; si no, solicita a ASOBARES un enlace nuevo.';

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
        ], [
            'token.required' => self::ENLACE_NO_VALIDO,
            'token.string' => self::ENLACE_NO_VALIDO,
            'email.required' => 'Escribe el correo al que llegó el enlace.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'password.required' => 'Escribe una contraseña.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
            'password.string' => 'La contraseña tiene que ser texto.',
            'password.min' => 'La contraseña necesita al menos :min caracteres.',
            'password.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
            'password.numbers' => 'La contraseña necesita al menos un número.',
            'password.symbols' => 'La contraseña necesita al menos un símbolo.',
        ], [
            'email' => 'correo',
            'password' => 'contraseña',
        ]);

        $estado = Password::reset($datos, function (User $usuario, string $password): void {
            $usuario->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
                'contrasena_provisional' => false,
            ])->save();
        });

        if ($estado !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => self::ENLACE_NO_VALIDO]);
        }

        return redirect()
            ->route('mi-cuenta.entrar')
            ->with('exito', 'Contraseña creada. Ahora entra a Mi Cuenta con tu correo.');
    }
}

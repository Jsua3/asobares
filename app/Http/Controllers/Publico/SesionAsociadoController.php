<?php

namespace App\Http\Controllers\Publico;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login propio de /mi-cuenta, con la identidad oscura del sitio.
 * El panel de Filament tiene el suyo aparte, en /admin/login.
 */
class SesionAsociadoController
{
    /** Fallos tolerados por cuenta antes de cerrarla un rato. */
    private const int INTENTOS_POR_CUENTA = 5;

    private const int BLOQUEO_EN_SEGUNDOS = 60;

    /**
     * Un único mensaje para todo lo que no termine en sesión de asociado. Si
     * el texto cambiara según el motivo, quien adivina contraseñas sabría
     * cuándo acertó: la del equipo del gremio es correcta pero entra por
     * /admin, y esa diferencia bastaba para confirmar credenciales del panel
     * desde aquí, sin pasar por el segundo factor.
     */
    private const string MENSAJE_GENERICO = 'Ese correo y esa contraseña no coinciden.';

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

        // El `throttle` de la ruta cuenta por IP, que no frena a quien las
        // rota. Este contador cuelga de la cuenta atacada.
        $llave = $this->llaveDeIntentos($credenciales['email']);

        if (RateLimiter::tooManyAttempts($llave, self::INTENTOS_POR_CUENTA)) {
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos con este correo. Vuelve a probar en '
                    .RateLimiter::availableIn($llave).' segundos.',
            ]);
        }

        if (! $this->entroUnAsociado($request, $credenciales)) {
            RateLimiter::hit($llave, self::BLOQUEO_EN_SEGUNDOS);

            throw ValidationException::withMessages(['email' => self::MENSAJE_GENERICO]);
        }

        RateLimiter::clear($llave);
        $request->session()->regenerate();

        // `intended()` leería un destino de sesión que pudo llegar por la
        // cabecera Referer, y eso deja aterrizar en un sitio ajeno justo
        // después de un login legítimo. /mi-cuenta es la portada del área
        // privada, así que no hay ruta profunda que valga la pena conservar.
        return redirect()->route('mi-cuenta.index');
    }

    /**
     * Autentica y exige que quien entra sea un asociado. Cualquier desenlace
     * distinto deja la sesión limpia y devuelve `false`, para que arriba se
     * responda siempre lo mismo.
     *
     * @param  array{email: string, password: string}  $credenciales
     */
    private function entroUnAsociado(Request $request, array $credenciales): bool
    {
        if (! Auth::attempt($credenciales, $request->boolean('recordarme'))) {
            return false;
        }

        $usuario = Auth::user();

        if ($usuario instanceof User && $usuario->esAsociado()) {
            return true;
        }

        // El panel y /mi-cuenta comparten guard. La pantalla ya avisa que el
        // equipo del gremio entra por /admin, así que el aviso no se pierde.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return false;
    }

    private function llaveDeIntentos(string $email): string
    {
        return 'entrar-asociado|'.Str::lower(trim($email));
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

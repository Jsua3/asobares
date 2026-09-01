<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

use function Laravel\Prompts\password as preguntarClave;

/**
 * Da de alta a alguien en el panel sin pasar por el sembrador del demo.
 *
 * Hasta ahora la única forma de tener una cuenta era `UsuarioSeeder`, que
 * **se niega a correr en producción** y con razón: crea tres cuentas con la
 * contraseña `Asobares2026*`, publicada en el README de un repositorio
 * **público**. Eso dejaba el sitio desplegado sin ninguna manera legítima de
 * entrar a `/admin`. Esto es esa manera.
 *
 * Tres decisiones que no son de estilo:
 *
 * - **La contraseña no viaja por la línea de órdenes.** Se pregunta, o se lee
 *   de `ASOBARES_CLAVE_INICIAL`. Un argumento queda en el historial del shell
 *   y en los registros de quien ejecute el comando en remoto.
 * - **La contraseña publicada está prohibida explícitamente.** Es el error que
 *   de verdad va a cometer alguien con prisa: copiarla del README porque «es
 *   la que ya conozco».
 * - **No se activa el segundo factor por correo.** El panel exige segundo
 *   factor (`AdminPanelProvider`, `isRequired: true`) y quien no lo tenga cae
 *   en la pantalla de alta obligatoria, donde puede registrar su app de
 *   autenticación. Encender el de correo aquí dejaría la cuenta encerrada:
 *   las direcciones del demo son `.test` —un dominio reservado que por
 *   definición no recibe correo (RFC 6761)— y la instalación todavía no tiene
 *   proveedor SMTP contratado (§29.1).
 */
class CrearUsuarioDelPanel extends Command
{
    protected $signature = 'asobares:crear-usuario
        {email : Correo con el que va a entrar}
        {--nombre= : Nombre que se muestra en el panel}
        {--rol=super_admin : super_admin, subadmin o asociado}';

    protected $description = 'Crea o actualiza una cuenta del panel pidiendo la contraseña, sin usar el sembrador del demo';

    /** La del README del repositorio público. No entra ni aunque la escriban a mano. */
    public const string CLAVE_PUBLICADA = 'Asobares2026*';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $rol = (string) $this->option('rol');

        $rolesValidos = [User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN, User::ROL_ASOCIADO];

        if (! in_array($rol, $rolesValidos, true)) {
            $this->error("Rol «{$rol}» desconocido. Usa uno de: ".implode(', ', $rolesValidos));

            return self::FAILURE;
        }

        $clave = $this->clave();

        if ($clave === null) {
            return self::FAILURE;
        }

        try {
            $this->validarClave($clave);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existia = User::query()->where('email', $email)->exists();

        $usuario = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) ($this->option('nombre') ?: $email),
                'password' => Hash::make($clave),
            ]
        );

        // ⚠️ Estos dos se asignan sueltos y no en el `updateOrCreate` de
        // arriba: `User` declara `#[Fillable(['name', 'email', 'password',
        // 'asociado_id'])]`, así que la asignación masiva descarta cualquier
        // otro campo **en silencio**. Puesto dentro del array, la cuenta salía
        // sin verificar y sin que nada lo avisara.
        $usuario->email_verified_at ??= now();

        // Explícito, no por omisión: si la cuenta ya existía con el factor de
        // correo encendido, se apaga. Sin SMTP el código no llega a ninguna
        // parte y la cuenta queda inaccesible aunque la contraseña sea buena.
        $usuario->has_email_authentication = false;
        $usuario->save();

        $usuario->syncRoles([$rol]);

        $this->info(($existia ? 'Actualizada' : 'Creada')." la cuenta {$email} con el rol {$rol}.");

        if ($rol === User::ROL_ASOCIADO) {
            $this->line('  El rol asociado no entra al panel: su sesión sirve para /mi-cuenta.');

            return self::SUCCESS;
        }

        $this->line('  En el primer inicio de sesión el panel va a pedirle registrar el segundo');
        $this->line('  factor con una app de autenticación. Es obligatorio y no se puede saltar.');

        return self::SUCCESS;
    }

    /**
     * De la variable de entorno o preguntada. Nunca de un argumento: ahí la
     * lee el historial del shell y cualquiera que mire los registros.
     */
    private function clave(): ?string
    {
        $deEntorno = env('ASOBARES_CLAVE_INICIAL');

        if (is_string($deEntorno) && $deEntorno !== '') {
            return $deEntorno;
        }

        $preguntada = $this->input->isInteractive()
            ? preguntarClave('Contraseña para la cuenta')
            : null;

        if (is_string($preguntada) && $preguntada !== '') {
            return $preguntada;
        }

        // ⚠️ No basta con mirar `isInteractive()`. En el ejecutor remoto de
        // Laravel Cloud da `true` aunque no haya terminal de verdad, así que
        // la pregunta devuelve cadena vacía y el comando seguía adelante hasta
        // morir en la validación con un mensaje que no explicaba nada. Lo que
        // decide es si al final hay contraseña, no si el proceso se cree
        // interactivo.
        $this->error('No se recibió ninguna contraseña.');
        $this->line('  En una terminal, el comando la pregunta. Sin terminal --y el ejecutor de');
        $this->line('  Laravel Cloud no la tiene, aunque diga que sí-- pásala en la variable de');
        $this->line('  entorno ASOBARES_CLAVE_INICIAL.');
        $this->line('');
        $this->line('  ⚠️ En Laravel Cloud una variable recién creada NO llega al proceso hasta');
        $this->line('  el siguiente despliegue. Comprobado: `printenv ASOBARES_CLAVE_INICIAL`');
        $this->line('  devolvía vacío justo después de crearla. Crea la variable, despliega, y');
        $this->line('  entonces corre esto.');

        return null;
    }

    /** @throws ValidationException */
    private function validarClave(#[SensitiveParameter] string $clave): void
    {
        if (hash_equals(self::CLAVE_PUBLICADA, $clave)) {
            throw ValidationException::withMessages([
                'clave' => 'Esa es la contraseña del demo, publicada en el README de un repositorio público. '
                    .'Cualquiera que lea el repositorio entraría al panel. Elige otra.',
            ]);
        }

        // Los mensajes van escritos aquí, uno por regla, por dos razones que
        // se descubrieron corriendo esto en producción:
        //
        // 1. «La contraseña» estaba de TERCER argumento de `validator()`, que
        //    es `$messages` y no `$attributes`. Resultado: cualquier fallo se
        //    imprimía como el literal «La contraseña». El comando devolvió eso
        //    y exit 1, y parecía una clave débil cuando llegaba vacía.
        // 2. La aplicación corre con `locale` y `fallback_locale` en `es` y no
        //    hay carpeta `lang/`; el framework solo trae `en`. Así que sin
        //    mensaje propio esto imprime `validation.min.string`, que tampoco
        //    le sirve a nadie. (Los formularios públicos no tienen el problema:
        //    sus `messages()` cubren `required` y `max` con claves generales.)
        validator(
            ['clave' => $clave],
            ['clave' => ['required', Password::min(12)->letters()->mixedCase()->numbers()->symbols()]],
            [
                'clave.required' => 'Hace falta una contraseña.',
                'clave.min' => 'La contraseña necesita al menos 12 caracteres.',
                'clave.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                'clave.letters' => 'La contraseña necesita al menos una letra.',
                'clave.numbers' => 'La contraseña necesita al menos un número.',
                'clave.symbols' => 'La contraseña necesita al menos un símbolo.',
            ],
        )->validate();
    }
}

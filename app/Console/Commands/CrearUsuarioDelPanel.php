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
 * `UsuarioSeeder` **se niega a correr en producción**, y con razón: crea tres
 * cuentas con la contraseña `Asobares2026*`, publicada en el README de un
 * repositorio **público**. Sin este comando el sitio desplegado no tendría
 * ninguna manera legítima de entrar a `/admin`.
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
 *   autenticación. Encender el de correo aquí dejaría la cuenta encerrada si
 *   el código no llega: las direcciones del demo son `.test` —un dominio
 *   reservado que por definición no recibe correo (RFC 6761)— y sin proveedor
 *   SMTP configurado no sale ninguno.
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
        // otro campo **en silencio**. Puesto dentro del array, la cuenta
        // saldría sin verificar y sin que nada lo avisara.
        $usuario->email_verified_at ??= now();

        // Explícito, no por omisión: si la cuenta ya existía con el factor de
        // correo encendido, se apaga. Sin SMTP el código no llega a ninguna
        // parte y la cuenta queda inaccesible aunque la contraseña sea buena.
        $usuario->has_email_authentication = false;
        $usuario->save();

        $usuario->syncRoles([$rol]);

        // La contraseña la escribió quien corre el comando, no el titular: si
        // la cuenta es de un afiliado, nace provisional.
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

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
        // la pregunta devuelve cadena vacía. Lo que decide es si al final hay
        // contraseña, no si el proceso se cree interactivo.
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

        // Los mensajes van escritos aquí, uno por regla, por dos razones:
        //
        // 1. El TERCER argumento de `validator()` es `$messages`, no
        //    `$attributes`: un nombre de campo puesto ahí se imprime tal cual
        //    como mensaje de cualquier fallo, y una clave vacía parecería una
        //    clave débil.
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
                // La regla Password falla con `password.mixed` y compañía: la
                // clave del mensaje es `clave.password.mixed`, no `clave.mixed`.
                'clave.password.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                'clave.password.letters' => 'La contraseña necesita al menos una letra.',
                'clave.password.numbers' => 'La contraseña necesita al menos un número.',
                'clave.password.symbols' => 'La contraseña necesita al menos un símbolo.',
            ],
        )->validate();
    }
}

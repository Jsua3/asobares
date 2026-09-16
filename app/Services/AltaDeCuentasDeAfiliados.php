<?php

namespace App\Services;

use App\Models\Asociado;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SensitiveParameter;

/**
 * Crea las cuentas de /mi-cuenta de los afiliados que trae la base del
 * gremio, con la contraseña genérica que escribe la dirección y marcadas
 * como provisionales.
 *
 * Tres reglas que no son de estilo:
 *
 * 1. **Una cuenta que ya existe no se toca**: ni su contraseña, ni su vínculo,
 *    ni su rol, ni su marca. Volver a importar el archivo corregido no puede
 *    devolverle la genérica a quien ya la cambió.
 * 2. **Ante la duda, no se crea.** Un correo repetido en dos fichas del mismo
 *    archivo no dice de cuál local es el dueño, y una cuenta se vincula a un
 *    solo establecimiento: se reporta y decide el gremio.
 * 3. **El hash se calcula una vez.** Es la misma contraseña para todos, así
 *    que repetir el hash no protege nada y multiplica el tiempo de la
 *    petición del panel. Deja de ser la misma en cuanto cada dueño la cambia.
 */
class AltaDeCuentasDeAfiliados
{
    public const string SIN_CORREO = 'sin correo';

    public const string CORREO_INVALIDO = 'correo inválido';

    public const string CORREO_COMPARTIDO = 'el mismo correo está también en';

    public const string YA_TENIA_CUENTA = 'ya tenía cuenta';

    public const string CORREO_DEL_EQUIPO = 'el correo es de una cuenta del equipo del gremio';

    public const string CORREO_CON_CUENTA = 'el correo ya tiene una cuenta';

    /** @param  Collection<int, Asociado>  $fichas */
    public function crear(Collection $fichas, #[SensitiveParameter] string $contrasenaGenerica): ResultadoDeAltaDeCuentas
    {
        $resultado = new ResultadoDeAltaDeCuentas;
        $hash = Hash::make($contrasenaGenerica);

        $correos = $fichas->mapWithKeys(fn (Asociado $ficha): array => [$ficha->id => $this->normalizar($ficha->correo_interno)]);

        $fichasPorCorreo = $fichas
            ->filter(fn (Asociado $ficha): bool => $this->esValido($correos[$ficha->id]))
            ->groupBy(fn (Asociado $ficha): string => $correos[$ficha->id]);

        foreach ($fichas as $ficha) {
            $correo = $correos[$ficha->id];

            if ($correo === '') {
                $resultado->agregarSinCuenta($ficha->nombre, self::SIN_CORREO);

                continue;
            }

            if (! $this->esValido($correo)) {
                $resultado->agregarSinCuenta($ficha->nombre, self::CORREO_INVALIDO);

                continue;
            }

            $conElMismoCorreo = $fichasPorCorreo->get($correo);

            if ($conElMismoCorreo->count() > 1) {
                $otras = $conElMismoCorreo
                    ->reject(fn (Asociado $otra): bool => $otra->is($ficha))
                    ->map(fn (Asociado $otra): string => "«{$otra->nombre}»")
                    ->implode(', ');

                $resultado->agregarSinCuenta($ficha->nombre, self::CORREO_COMPARTIDO.' '.$otras);

                continue;
            }

            if ($ficha->usuarios()->exists()) {
                $resultado->agregarSinCuenta($ficha->nombre, self::YA_TENIA_CUENTA);

                continue;
            }

            $existente = User::query()->whereRaw('LOWER(email) = ?', [$correo])->first();

            if ($existente !== null) {
                $motivo = $existente->hasAnyRole([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN])
                    ? self::CORREO_DEL_EQUIPO
                    : self::CORREO_CON_CUENTA;

                $resultado->agregarSinCuenta($ficha->nombre, $motivo);

                continue;
            }

            // `forceFill` y no `create`: `#[Fillable]` descartaría en silencio
            // la marca y la verificación. El cast `hashed` reconoce el hash
            // ya calculado y no lo vuelve a calcular.
            $usuario = new User;
            $usuario->forceFill([
                'name' => filled($ficha->representante) ? $ficha->representante : $ficha->nombre,
                'email' => $correo,
                'password' => $hash,
                'asociado_id' => $ficha->id,
                'email_verified_at' => now(),
                'contrasena_provisional' => true,
            ])->save();
            $usuario->assignRole(User::ROL_ASOCIADO);

            $resultado->contarCreada();
        }

        return $resultado;
    }

    private function normalizar(?string $correo): string
    {
        return Str::lower(trim((string) $correo));
    }

    private function esValido(string $correo): bool
    {
        return $correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }
}

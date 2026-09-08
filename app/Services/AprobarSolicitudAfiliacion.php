<?php

namespace App\Services;

use App\Enums\EstadoPublicacion;
use App\Enums\EstadoSolicitudAfiliacion;
use App\Mail\BienvenidaAsociado;
use App\Models\Asociado;
use App\Models\SolicitudAfiliacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

class AprobarSolicitudAfiliacion
{
    /**
     * @throws LogicException
     */
    public function __invoke(SolicitudAfiliacion $solicitud, User $admin, ?string $notas = null): bool
    {
        $resultado = DB::transaction(function () use ($solicitud, $admin, $notas): array {
            $solicitud->refresh();

            $this->validarAprobable($solicitud);

            $asociado = Asociado::create($this->datosDelAsociado($solicitud));
            $usuario = User::create([
                'name' => $solicitud->solicitante_nombre,
                'email' => $solicitud->solicitante_correo,
                'password' => Str::password(64, symbols: true),
                'asociado_id' => $asociado->id,
                'email_verified_at' => now(),
            ]);

            $usuario->assignRole(User::ROL_ASOCIADO);

            $solicitud->update([
                'estado' => EstadoSolicitudAfiliacion::Aprobada,
                'asociado_id' => $asociado->id,
                'user_id' => $usuario->id,
                'aprobado_por' => $admin->id,
                'aprobado_at' => now(),
                'resuelto_at' => now(),
                'gestion_notas' => $notas ?: $solicitud->gestion_notas,
            ]);

            return [
                'usuario' => $usuario,
                'asociado' => $asociado,
                'token' => Password::createToken($usuario),
            ];
        });

        return $this->enviarBienvenida($resultado['usuario'], $resultado['asociado'], $resultado['token']);
    }

    private function validarAprobable(SolicitudAfiliacion $solicitud): void
    {
        if ($solicitud->estado === EstadoSolicitudAfiliacion::Aprobada || $solicitud->asociado_id !== null || $solicitud->user_id !== null) {
            throw new LogicException('Esta solicitud ya fue aprobada o ya tiene entidades vinculadas.');
        }

        if ($solicitud->estado === EstadoSolicitudAfiliacion::Rechazada) {
            throw new LogicException('Una solicitud rechazada no puede aprobarse.');
        }

        if (User::where('email', $solicitud->solicitante_correo)->exists()) {
            throw new LogicException('Ya existe un usuario con el correo del solicitante.');
        }

        if (Asociado::where('documento', $solicitud->nit)->exists()) {
            throw new LogicException('Ya existe un asociado con el mismo NIT o documento.');
        }
    }

    /** @return array<string, mixed> */
    private function datosDelAsociado(SolicitudAfiliacion $solicitud): array
    {
        return [
            'nombre' => $solicitud->establecimiento_nombre,
            'slug' => $this->slugDisponible($solicitud->establecimiento_nombre),
            'categoria_id' => $solicitud->categoria_id,
            'municipio_id' => $solicitud->municipio_id,
            'descripcion' => $solicitud->descripcion,
            'direccion' => $solicitud->direccion,
            'whatsapp' => $solicitud->establecimiento_telefono,
            'estado' => EstadoPublicacion::Borrador,
            'representante' => $solicitud->solicitante_nombre,
            'documento' => $solicitud->nit,
            'correo_interno' => $solicitud->establecimiento_correo,
            'telefono_interno' => $solicitud->solicitante_telefono,
            'fecha_afiliacion' => now()->toDateString(),
            'autorizacion_datos_at' => $solicitud->consentimiento_at,
            'autorizacion_datos_origen' => "Solicitud de afiliación #{$solicitud->id}",
        ];
    }

    private function slugDisponible(string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'asociado';
        $slug = $base;
        $contador = 2;

        while (Asociado::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    private function enviarBienvenida(User $usuario, Asociado $asociado, string $token): bool
    {
        return rescue(function () use ($usuario, $asociado, $token): bool {
            Mail::to($usuario->email)->send(new BienvenidaAsociado($usuario, $asociado, $token));

            return true;
        }, function (Throwable $excepcion): bool {
            report($excepcion);

            return false;
        });
    }
}

<?php

namespace App\Services;

use App\Enums\EstadoSolicitudAfiliacion;
use App\Mail\BienvenidaAsociado;
use App\Models\SolicitudAfiliacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use LogicException;
use Throwable;

class ReenviarEnlaceAccesoAsociado
{
    /**
     * @throws LogicException
     */
    public function __invoke(SolicitudAfiliacion $solicitud): bool
    {
        $solicitud->loadMissing(['asociado', 'user']);

        if ($solicitud->estado !== EstadoSolicitudAfiliacion::Aprobada || $solicitud->user === null || $solicitud->asociado === null) {
            throw new LogicException('Solo se puede reenviar el enlace cuando la solicitud ya está aprobada y tiene usuario asociado.');
        }

        $token = Password::createToken($solicitud->user);

        return rescue(function () use ($solicitud, $token): bool {
            Mail::to($solicitud->user->email)->send(new BienvenidaAsociado(
                $solicitud->user,
                $solicitud->asociado,
                $token,
                reenvio: true,
            ));

            return true;
        }, function (Throwable $excepcion): bool {
            report($excepcion);

            return false;
        });
    }
}

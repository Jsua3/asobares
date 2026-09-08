<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CorreosInstitucionales
{
    public function destinatario(string $flujo = 'general'): ?string
    {
        $correo = trim((string) ajuste('contacto_correo_destino'));

        if ($correo === '') {
            $correo = trim((string) ajuste('contacto_correo'));
        }

        return filter_var($correo, FILTER_VALIDATE_EMAIL) ? $correo : null;
    }

    public function enviar(Mailable $correo, string $flujo = 'general'): bool
    {
        $destinatario = $this->destinatario($flujo);

        if ($destinatario === null) {
            return false;
        }

        return rescue(function () use ($correo, $destinatario): bool {
            Mail::to($destinatario)->send($correo);

            return true;
        }, function (Throwable $excepcion): bool {
            report($excepcion);

            return false;
        });
    }
}

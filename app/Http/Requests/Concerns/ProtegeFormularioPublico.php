<?php

namespace App\Http\Requests\Concerns;

use App\Support\Formulario;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Defensas comunes de todos los formularios públicos.
 *
 * El honeypot es un campo oculto que una persona nunca llena y un bot sí.
 * Se combina con el rate limiting por IP declarado en las rutas.
 */
trait ProtegeFormularioPublico
{
    /** @return array<string, mixed> */
    protected function reglasAntispam(): array
    {
        return [Formulario::CAMPO_TRAMPA => ['nullable', 'size:0']];
    }

    /** @return array<string, mixed> */
    protected function reglasHabeasData(): array
    {
        return ['acepta_datos' => ['accepted']];
    }

    /** @return array<string, string> */
    protected function mensajesComunes(): array
    {
        return [
            'acepta_datos.accepted' => 'Necesitamos tu autorización para tratar tus datos personales.',
            'correo.email' => 'Escribe un correo electrónico válido.',
            'required' => 'Este campo es obligatorio.',
            'max' => 'El texto es demasiado largo.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        // Al bot no se le explica por qué falló: se responde como si todo
        // hubiera salido bien y no se guarda nada.
        if ($validator->errors()->has(Formulario::CAMPO_TRAMPA)) {
            abort(422);
        }

        throw new ValidationException($validator);
    }

    /**
     * Constancia del consentimiento (Ley 1581 de 2012).
     *
     * No basta el booleano y la fecha: ante un reclamo del titular hay que
     * poder decir desde dónde se autorizó, con qué navegador y qué versión
     * de la política estaba publicada al aceptar. La versión es el mismo
     * ajuste que la política muestra como su vigencia, capturado en el
     * momento de aceptar — si la política cambia después, la constancia
     * conserva la que la persona vio.
     */
    protected function selloDeConsentimiento(): array
    {
        return [
            'acepta_datos' => true,
            'consentimiento_at' => now(),
            'consentimiento_ip' => $this->ip(),
            'consentimiento_agente' => Str::limit((string) $this->userAgent(), 255, ''),
            'consentimiento_politica' => ((string) ajuste('politica_actualizacion')) ?: null,
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\TipoMensaje;
use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarMensajeRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoMensaje::class)],
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email:rfc', 'max:180'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'mensaje' => ['required', 'string', 'min:10', 'max:2000'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes() + [
            'mensaje.min' => 'Cuéntanos un poco más para poder ayudarte.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDelMensaje(): array
    {
        return [
            ...$this->safe()->only(['tipo', 'nombre', 'correo', 'telefono', 'mensaje']),
            ...$this->selloDeConsentimiento(),
        ];
    }
}

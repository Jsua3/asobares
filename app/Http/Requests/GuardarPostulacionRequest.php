<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;

class GuardarPostulacionRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email:rfc', 'max:180'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'experiencia' => ['nullable', 'string', 'max:600'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes() + [
            'nombre.required' => 'Escribe tu nombre para que el establecimiento sepa quién eres.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDeLaPostulacion(): array
    {
        return [
            ...$this->safe()->only(['nombre', 'correo', 'telefono', 'experiencia']),
            ...$this->selloDeConsentimiento(),
        ];
    }
}

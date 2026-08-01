<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;

class GuardarAspiranteRequest extends FormRequest
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
            'cargo_interes' => ['required', 'string', 'max:80'],
            'experiencia' => ['nullable', 'string', 'max:600'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes() + [
            'cargo_interes.required' => 'Cuéntanos qué cargo estás buscando.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDelAspirante(): array
    {
        return [
            ...$this->safe()->only(['nombre', 'correo', 'telefono', 'cargo_interes', 'experiencia']),
            ...$this->selloDeConsentimiento(),
        ];
    }
}

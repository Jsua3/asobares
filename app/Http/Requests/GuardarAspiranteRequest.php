<?php

namespace App\Http\Requests;

use App\Enums\CargoDelSector;
use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarAspiranteRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('empleo.index').'#perfil';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email:rfc', 'max:180'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'cargo_interes' => ['required', 'string', 'max:80'],
            'categoria_cargo' => ['required', Rule::enum(CargoDelSector::class)],
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
            ...$this->safe()->only(['nombre', 'correo', 'telefono', 'cargo_interes', 'categoria_cargo', 'experiencia']),
            ...$this->selloDeConsentimiento(),
        ];
    }
}

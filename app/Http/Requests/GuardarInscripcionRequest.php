<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;

class GuardarInscripcionRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * La inscripción queda por debajo de la descripción del evento: sin el
     * ancla, un error de validación devolvería a la persona al tope de la ficha
     * sin ningún error a la vista.
     */
    protected function getRedirectUrl(): string
    {
        return route('eventos.show', $this->route('evento')).'#inscripcion';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email:rfc', 'max:180'],
            'telefono' => ['required', 'string', 'max:30'],
            'establecimiento' => ['nullable', 'string', 'max:150'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes();
    }

    /** @return array<string, mixed> */
    public function datosDeLaInscripcion(): array
    {
        return [
            ...$this->safe()->only(['nombre', 'correo', 'telefono', 'establecimiento']),
            ...$this->selloDeConsentimiento(),
        ];
    }
}

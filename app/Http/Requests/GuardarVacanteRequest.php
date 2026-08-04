<?php

namespace App\Http\Requests;

use App\Enums\CargoDelSector;
use App\Enums\TipoVacante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ni el estado ni el establecimiento se aceptan del formulario: el primero lo
 * fija el controlador y el segundo sale de la sesión. Mandarlos a mano no
 * sirve de nada.
 */
class GuardarVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cargo' => ['required', 'string', 'max:120'],
            'categoria_cargo' => ['required', Rule::enum(CargoDelSector::class)],
            'tipo' => ['required', Rule::enum(TipoVacante::class)],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'franja_horaria' => ['nullable', 'string', 'max:160'],
            'whatsapp_contacto' => ['nullable', 'string', 'max:30'],
            'fecha_limite' => [
                Rule::requiredIf(fn (): bool => $this->input('tipo') === TipoVacante::Momentaneo->value),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cargo.required' => 'Escribe el cargo que estás buscando.',
            'categoria_cargo.required' => 'Elige a qué área del establecimiento pertenece el cargo.',
            'fecha_limite.required' => 'Un empleo de una o dos noches necesita la fecha del turno.',
            'fecha_limite.after_or_equal' => 'La fecha límite no puede estar en el pasado.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDeLaVacante(): array
    {
        return $this->safe()->only([
            'cargo', 'categoria_cargo', 'tipo', 'descripcion',
            'franja_horaria', 'whatsapp_contacto', 'fecha_limite',
        ]);
    }
}

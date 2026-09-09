<?php

namespace App\Http\Requests;

use App\Enums\CargoDelSolicitante;
use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarSolicitudAfiliacionRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('solicitante_cargo_otro')) {
            $this->merge([
                'solicitante_cargo_otro' => trim((string) $this->input('solicitante_cargo_otro')),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge([
            'solicitante_nombre' => ['required', 'string', 'max:120'],
            'solicitante_identificacion' => ['required', 'string', 'max:40', 'regex:/^[\pL\pN\s.\-]+$/u'],
            'solicitante_telefono' => ['required', 'string', 'max:30'],
            'solicitante_correo' => ['required', 'email:rfc', 'max:180'],
            'solicitante_cargo_opcion' => ['required', Rule::enum(CargoDelSolicitante::class)],
            'solicitante_cargo_otro' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[\pL\pN\s.\-\/]+$/u',
                Rule::requiredIf(fn (): bool => $this->input('solicitante_cargo_opcion') === CargoDelSolicitante::Otro->value),
                Rule::prohibitedIf(fn (): bool => $this->input('solicitante_cargo_opcion') !== CargoDelSolicitante::Otro->value),
            ],
            'establecimiento_nombre' => ['required', 'string', 'max:160'],
            'razon_social' => ['required', 'string', 'max:180'],
            'nit' => ['required', 'string', 'max:40', 'regex:/^[\pL\pN\s.\-]+$/u'],
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'direccion' => ['required', 'string', 'max:255'],
            'establecimiento_telefono' => ['required', 'string', 'max:30'],
            'establecimiento_correo' => ['required', 'email:rfc', 'max:180'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'descripcion' => ['required', 'string', 'min:10', 'max:2000'],
        ], $this->reglasHabeasData(), $this->reglasAntispam());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_merge($this->mensajesComunes(), [
            'solicitante_identificacion.regex' => 'Usa solo letras, números, espacios, puntos o guiones.',
            'solicitante_cargo_otro.regex' => 'Usa solo letras, números, espacios, puntos, guiones o barras.',
            'solicitante_cargo_otro.required' => 'Escribe el cargo o rol cuando seleccionas Otro.',
            'nit.regex' => 'Usa solo letras, números, espacios, puntos o guiones.',
            'descripcion.min' => 'Cuéntanos un poco más del establecimiento.',
            'municipio_id.exists' => 'Selecciona un municipio válido.',
            'categoria_id.exists' => 'Selecciona una categoría válida.',
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'solicitante_nombre' => 'nombre completo',
            'solicitante_identificacion' => 'identificación',
            'solicitante_telefono' => 'teléfono',
            'solicitante_correo' => 'correo',
            'solicitante_cargo_opcion' => 'cargo o rol',
            'solicitante_cargo_otro' => 'cargo o rol específico',
            'establecimiento_nombre' => 'nombre comercial',
            'razon_social' => 'razón social',
            'nit' => 'NIT',
            'municipio_id' => 'municipio',
            'direccion' => 'dirección',
            'establecimiento_telefono' => 'teléfono o WhatsApp del establecimiento',
            'establecimiento_correo' => 'correo del establecimiento',
            'categoria_id' => 'categoría',
            'descripcion' => 'descripción',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDeSolicitud(): array
    {
        $datos = $this->safe()->except([
            'acepta_datos',
            'solicitante_cargo_opcion',
            'solicitante_cargo_otro',
        ]);

        $datos['solicitante_cargo'] = $this->cargoFinal();

        return array_merge($datos, $this->selloDeConsentimiento());
    }

    private function cargoFinal(): string
    {
        $opcion = CargoDelSolicitante::from($this->validated('solicitante_cargo_opcion'));

        if ($opcion->esOtro()) {
            return trim((string) $this->validated('solicitante_cargo_otro'));
        }

        return $opcion->value;
    }
}

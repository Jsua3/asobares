<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;

class GuardarSolicitudAfiliacionRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge([
            'solicitante_nombre' => ['required', 'string', 'max:120'],
            'solicitante_identificacion' => ['required', 'string', 'max:40', 'regex:/^[\pL\pN\s.\-]+$/u'],
            'solicitante_telefono' => ['required', 'string', 'max:30'],
            'solicitante_correo' => ['required', 'email:rfc', 'max:180'],
            'solicitante_cargo' => ['required', 'string', 'max:80'],
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
            'solicitante_cargo' => 'cargo o rol',
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
        return array_merge($this->safe()->except(['acepta_datos']), $this->selloDeConsentimiento());
    }
}

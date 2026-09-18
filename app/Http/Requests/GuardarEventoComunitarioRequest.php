<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use Illuminate\Foundation\Http\FormRequest;

class GuardarEventoComunitarioRequest extends FormRequest
{
    use ProtegeFormularioPublico;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sinHtml = 'not_regex:/<[^>]*>/u';

        return [
            'titulo' => ['required', 'string', 'max:150', $sinHtml],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01', 'before_or_equal:'.now()->addYears(5)->endOfYear()->toDateString()],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'lugar' => ['required', 'string', 'max:180', $sinHtml],
            'descripcion' => ['required', 'string', 'max:800', $sinHtml],
            'imagen' => ['nullable', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'enlace_externo' => [
                'nullable', 'url', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! in_array(strtolower((string) parse_url((string) $value, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                        $fail('El enlace debe comenzar por http:// o https://.');
                    }
                },
            ],
            ...$this->reglasAntispam(),
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->mensajesComunes(),
            'titulo.not_regex' => 'No se admite HTML en el título.',
            'lugar.not_regex' => 'No se admite HTML en el lugar.',
            'descripcion.not_regex' => 'No se admite HTML en la descripción.',
            'imagen.mimetypes' => 'Sube una imagen JPG, PNG o WebP.',
        ];
    }
}

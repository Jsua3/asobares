<?php

namespace App\Http\Requests;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardarSolicitudDeProveedorRequest extends FormRequest
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
            'categoria_proveedor' => ['required', Rule::enum(CategoriaProveedor::class)],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email:rfc', 'max:180'],
            'municipio_id' => ['required', 'exists:municipios,id'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes() + [
            'nombre.required' => 'Escribe el nombre de tu empresa.',
            'categoria_proveedor.required' => 'Elige qué le vendes al sector.',
            'municipio_id.required' => 'Dinos desde qué municipio despachas.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDelProveedor(): array
    {
        return [
            ...$this->safe()->only(['nombre', 'categoria_proveedor', 'descripcion', 'whatsapp', 'correo', 'municipio_id']),
            'slug' => $this->slugDisponible($this->string('nombre')->toString()),
            'estado' => EstadoPublicacion::PendienteAprobacion,
            ...$this->selloDeConsentimiento(),
        ];
    }

    /** Dos proveedores pueden llamarse igual; la URL no puede repetirse. */
    private function slugDisponible(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;

        while (Proveedor::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}

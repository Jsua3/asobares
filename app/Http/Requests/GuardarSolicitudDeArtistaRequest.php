<?php

namespace App\Http\Requests;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Http\Requests\Concerns\ProtegeFormularioPublico;
use App\Models\Artista;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * «Quiero aparecer en la bolsa» dejó de ser un mensaje de texto libre que la
 * secretaría tenía que transcribir a mano: la ficha entra ya armada y solo
 * falta aprobarla.
 */
class GuardarSolicitudDeArtistaRequest extends FormRequest
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
            'tipo' => ['required', Rule::enum(TipoArtista::class)],
            'genero_musical' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'tarifa_desde' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'video_url' => ['nullable', 'url', 'url_youtube'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email:rfc', 'max:180'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'municipio_id' => ['required', 'exists:municipios,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ...$this->reglasHabeasData(),
            ...$this->reglasAntispam(),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->mensajesComunes() + [
            'nombre.required' => 'Escribe tu nombre artístico.',
            'municipio_id.required' => 'Dinos desde qué municipio trabajas.',
        ];
    }

    /** @return array<string, mixed> */
    public function datosDelArtista(): array
    {
        return [
            ...$this->safe()->only([
                'nombre', 'tipo', 'genero_musical', 'descripcion', 'tarifa_desde',
                'video_url', 'whatsapp', 'correo', 'instagram_url', 'municipio_id',
            ]),
            'slug' => $this->slugDisponible($this->string('nombre')->toString()),
            'foto' => $this->file('foto')?->store('artistas', 'public'),
            'estado' => EstadoPublicacion::PendienteAprobacion,
            ...$this->selloDeConsentimiento(),
        ];
    }

    /** Dos artistas pueden llamarse igual; la URL no puede repetirse. */
    private function slugDisponible(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;

        while (Artista::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}

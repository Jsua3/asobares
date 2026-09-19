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
 * «Quiero aparecer en la bolsa» entra como una ficha ya armada, no como un
 * mensaje de texto libre que la secretaría tendría que transcribir a mano, y
 * sale publicada al instante: la bolsa se modera después (encargo §13,
 * 18 sep). El estado lo fija el servidor, nunca el formulario.
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
            'foto' => $this->file('foto')?->store('artistas', config('almacenamiento.publico')),
            'estado' => EstadoPublicacion::Publicado,
            ...$this->selloDeConsentimiento(),
        ];
    }

    /**
     * La misma inscripción otra vez: un doble clic o un reenvío del navegador.
     * Mismo nombre, mismo contacto y mismo municipio en los últimos diez
     * minutos. Se mira antes de armar los datos porque armarlos guarda la foto.
     */
    public function yaSeRecibio(): bool
    {
        $datos = $this->safe();

        return Artista::query()
            ->where('nombre', $datos['nombre'])
            ->where('municipio_id', $datos['municipio_id'])
            ->where('correo', $datos['correo'] ?? null)
            ->where('whatsapp', $datos['whatsapp'] ?? null)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
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

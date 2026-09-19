<?php

namespace App\Http\Controllers\Publico;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Http\Requests\GuardarEventoComunitarioRequest;
use App\Models\Evento;
use App\Support\AvisoDeEventoComunitario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EventoComunitarioController
{
    public function store(GuardarEventoComunitarioRequest $request): RedirectResponse
    {
        $datos = $request->safe()->only([
            'titulo', 'fecha', 'hora_inicio', 'hora_fin', 'lugar', 'descripcion', 'enlace_externo',
            'municipio_id', 'direccion', 'mapa_url',
        ]);

        $inicio = Carbon::createFromFormat('!Y-m-d H:i', $datos['fecha'].' '.$datos['hora_inicio']);
        $fin = filled($datos['hora_fin'] ?? null)
            ? Carbon::createFromFormat('!Y-m-d H:i', $datos['fecha'].' '.$datos['hora_fin'])
            : null;

        // Un evento nocturno puede acabar después de medianoche.
        if ($fin?->lessThan($inicio)) {
            $fin->addDay();
        }

        // Un doble clic o un reenvío del navegador: el mismo título, fecha,
        // hora y lugar en diez minutos es el mismo envío, y no se publica ni
        // se avisa otra vez. Antes de guardar la imagen, para no dejarla
        // huérfana.
        if ($this->yaSePublico($datos['titulo'], $inicio, $datos['lugar'])) {
            return $this->alCalendario($inicio);
        }

        $evento = new Evento;
        $evento->fill([
            'titulo' => $datos['titulo'],
            'slug' => Str::slug($datos['titulo']) ?: 'evento-comunidad',
            'tipo' => TipoEvento::Evento,
            'origen' => OrigenEvento::Comunidad,
            'aliado_id' => null,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'lugar' => $datos['lugar'],
            'municipio_id' => $datos['municipio_id'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'mapa_url' => $datos['mapa_url'] ?? null,
            'descripcion' => $datos['descripcion'],
            'enlace_externo' => $datos['enlace_externo'] ?? null,
            'permite_inscripcion' => false,
            'cupos' => null,
            'precio' => 0,
            'estado' => EstadoPublicacion::Publicado,
        ]);
        $evento->slug = Str::limit($evento->slug, 210, '').'-'.Str::lower((string) Str::ulid());
        $evento->marcarAltaComunitariaValidada();

        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $extension = match ($archivo->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            };
            $evento->imagen = $archivo->storeAs('eventos', Str::ulid().'.'.$extension, config('almacenamiento.publico'));
        }

        try {
            $evento->save();
        } catch (Throwable $fallo) {
            if ($evento->imagen) {
                Storage::disk(config('almacenamiento.publico'))->delete($evento->imagen);
            }

            throw $fallo;
        }

        // El aviso es posterior al guardado y no puede deshacer la publicación.
        rescue(fn () => AvisoDeEventoComunitario::enviar($evento));

        return $this->alCalendario($inicio);
    }

    private function yaSePublico(string $titulo, Carbon $inicio, string $lugar): bool
    {
        return Evento::query()
            ->where('origen', OrigenEvento::Comunidad)
            ->where('titulo', $titulo)
            ->where('fecha_inicio', $inicio)
            ->where('lugar', $lugar)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
    }

    private function alCalendario(Carbon $inicio): RedirectResponse
    {
        return redirect()
            ->route('eventos.calendario', [$inicio->year, $inicio->format('m')])
            ->with('exito', 'Tu evento ya aparece en el calendario.');
    }
}

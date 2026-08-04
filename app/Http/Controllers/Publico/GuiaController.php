<?php

namespace App\Http\Controllers\Publico;

use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * La guía normativa por municipio: el producto insignia del sitio.
 */
class GuiaController
{
    public function index(Request $request): View
    {
        $request->validate(['municipio' => ['nullable', 'string', 'exists:municipios,slug']]);

        // Solo se ofrecen municipios que ya tienen la guía levantada.
        $municipiosConGuia = Municipio::whereHas('requisitos', fn ($q) => $q->publicado())
            ->orderBy('nombre')
            ->get();

        $seleccionado = filled($request->string('municipio')->toString())
            ? $municipiosConGuia->firstWhere('slug', $request->string('municipio')->toString())
            : $municipiosConGuia->first();

        $requisitos = $seleccionado
            ? RequisitoApertura::publicado()
                ->where('municipio_id', $seleccionado->id)
                ->orderBy('orden')
                ->get()
            : collect();

        return view('publico.guia.index', [
            'municipios' => $municipiosConGuia,
            'seleccionado' => $seleccionado,
            'requisitos' => $requisitos,
            'costoTotal' => $requisitos->sum(fn (RequisitoApertura $r): float => (float) $r->costo_aproximado),
        ]);
    }

    /**
     * Sirve el formato oficial con un nombre limpio, nunca la ruta interna.
     *
     * Los adjuntos viven en el disco privado justamente para que esta puerta
     * sea la única: mientras estuvieron en el disco público, comprobar aquí el
     * estado de publicación era decorativo, porque el mismo PDF se descargaba
     * por /storage sin pasar por ningún control.
     */
    public function descargarFormato(RequisitoApertura $requisito): StreamedResponse
    {
        abort_unless($requisito->estaPublicado() && $requisito->tieneAdjunto(), 404);

        // La ruta viene de la base y la escribe el panel: se acota a su
        // carpeta para que no pueda apuntar a ningún otro sitio del disco.
        abort_unless(str_starts_with($requisito->adjunto, 'formatos/'), 404);
        abort_unless(Storage::disk('local')->exists($requisito->adjunto), 404);

        $nombre = Str::slug($requisito->adjunto_nombre ?? $requisito->entidad).'.pdf';

        return Storage::disk('local')->download($requisito->adjunto, $nombre, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Publico;

use App\Models\ConsultaGuia;
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

        // Sólo se ofrecen municipios que ya tienen la guía levantada Y vigente:
        // uno cuyos trámites hayan caducado todos saldría en el selector con la
        // guía vacía.
        $municipiosConGuia = Municipio::whereHas('requisitos', fn ($q) => $q->publicado()->vigente())
            ->orderBy('nombre')
            ->get();

        $seleccionado = filled($request->string('municipio')->toString())
            ? $municipiosConGuia->firstWhere('slug', $request->string('municipio')->toString())
            : $municipiosConGuia->first();

        $requisitos = $seleccionado
            ? RequisitoApertura::publicado()
                ->vigente()
                ->where('municipio_id', $seleccionado->id)
                ->orderBy('orden')
                ->get()
            : collect();

        // Conteo anónimo para el observatorio: en qué municipios la gente
        // quiere abrir un negocio. Solo se registra cuando se elige explícitamente
        // para evitar inflar al municipio por defecto con clics accidentales en el menú.
        if ($seleccionado !== null && $request->filled('municipio')) {
            ConsultaGuia::registrar($seleccionado->id);
        }

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
        // La caducidad se comprueba AQUÍ y no sólo en la vista: los formatos
        // viven en el disco privado justamente para que esta sea la única
        // puerta, y un decreto vencido con PDF descargable por URL directa
        // sería el mismo agujero del §8.3 del runbook.
        abort_unless(
            $requisito->estaPublicado() && ! $requisito->haCaducado() && $requisito->tieneAdjunto(),
            404
        );

        // La ruta viene de la base y la escribe el panel: se acota a su
        // carpeta para que no pueda apuntar a ningún otro sitio del disco.
        abort_unless(str_starts_with($requisito->adjunto, 'formatos/'), 404);
        abort_unless(Storage::disk(config('almacenamiento.privado'))->exists($requisito->adjunto), 404);

        // Descargar el formato es la señal más fuerte de intención real.
        ConsultaGuia::registrar($requisito->municipio_id, $requisito->id);

        $nombre = Str::slug($requisito->adjunto_nombre ?? $requisito->entidad).'.pdf';

        return Storage::disk(config('almacenamiento.privado'))->download($requisito->adjunto, $nombre, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

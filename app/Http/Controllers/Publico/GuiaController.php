<?php

namespace App\Http\Controllers\Publico;

use App\Models\ConsultaGuia;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

        return $this->vistaDeGuia(
            $request->string('municipio')->toString() ?: null,
            explicito: $request->filled('municipio'),
        );
    }

    /**
     * URL propia por municipio: /abre-tu-negocio/salento.
     *
     * Mismo motivo que `DirectorioController::porMunicipio()`: la canónica de
     * `index()` colapsa todas las variantes de `?municipio=` en la página
     * base, así que ninguna puede posicionar "cómo abrir un bar en Salento"
     * aparte de "en Armenia". Llegar aquí cuenta para el observatorio igual
     * que elegirlo del desplegable: es la misma intención deliberada.
     */
    public function porMunicipio(Municipio $municipio): View
    {
        abort_unless($municipio->activo, 404);

        return $this->vistaDeGuia($municipio->slug, explicito: true);
    }

    private function vistaDeGuia(?string $slug, bool $explicito): View
    {
        // Sólo se ofrecen municipios que ya tienen la guía levantada Y vigente:
        // uno cuyos trámites hayan caducado todos saldría en el selector con la
        // guía vacía.
        $municipiosConGuia = Municipio::activos()
            ->whereHas('requisitos', fn (Builder $requisitos): Builder => $requisitos->publicado()->vigente())
            ->ordenados()
            ->get();

        $seleccionado = filled($slug)
            ? $municipiosConGuia->firstWhere('slug', $slug)
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
        // -- desde el desplegable o desde su URL propia -- para evitar inflar
        // al municipio por defecto con visitas accidentales a la página base.
        if ($seleccionado !== null && $explicito) {
            ConsultaGuia::registrar($seleccionado->id);
        }

        return view('publico.guia.index', [
            'municipios' => $municipiosConGuia,
            'seleccionado' => $seleccionado,
            'municipioPagina' => $explicito ? $seleccionado : null,
            'requisitos' => $requisitos,
            'costoTotal' => $requisitos->sum(fn (RequisitoApertura $r): float => (float) $r->costo_aproximado),
        ]);
    }

    /**
     * Sirve el formato oficial con un nombre limpio, nunca la ruta interna.
     *
     * Los adjuntos viven en el disco privado justamente para que esta puerta
     * sea la única: en el disco público, comprobar aquí el estado de
     * publicación sería decorativo, porque el mismo PDF se descargaría por
     * /storage sin pasar por ningún control.
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

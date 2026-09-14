<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaProveedor;
use App\Http\Requests\GuardarSolicitudDeProveedorRequest;
use App\Models\Municipio;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Cara pública de la bolsa de proveedores.
 *
 * Explica qué es y cuánto hay, y no entrega ni un nombre ni un contacto: eso
 * vive en `MisProveedoresController`, detrás de la sesión del afiliado. La
 * URL sigue siendo pública a propósito, para no perder el índice ni mandar a
 * un login seco a quien llega desde un buscador.
 */
class ProveedorController
{
    public function index(): View
    {
        // Solo el recuento por categoría: números, nunca filas. Cualquier dato
        // que se agregue aquí hay que mirarlo dos veces, porque esta página la
        // lee cualquiera.
        $conteos = Proveedor::publicado()
            ->vigente()
            ->selectRaw('categoria_proveedor, count(*) as total')
            ->groupBy('categoria_proveedor')
            ->pluck('total', 'categoria_proveedor');

        return view('publico.proveedores.index', [
            'categorias' => CategoriaProveedor::cases(),
            'conteos' => $conteos,
            'total' => $conteos->sum(),
        ]);
    }

    public function inscripcion(): View
    {
        return view('publico.proveedores.inscripcion', [
            'categorias' => CategoriaProveedor::cases(),
            'municipios' => Municipio::orderBy('nombre')->get(),
        ]);
    }

    public function guardarInscripcion(GuardarSolicitudDeProveedorRequest $request): RedirectResponse
    {
        Proveedor::create($request->datosDelProveedor());

        return redirect()
            ->route('proveedores.inscripcion')
            ->with('exito', 'Recibimos tu solicitud. La secretaría la revisa y te avisamos cuando aparezcas en la bolsa.');
    }
}

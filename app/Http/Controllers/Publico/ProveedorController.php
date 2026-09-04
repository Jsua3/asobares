<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaProveedor;
use App\Http\Requests\GuardarSolicitudDeProveedorRequest;
use App\Models\Municipio;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Cara publica de la bolsa de proveedores.
 *
 * Explica que es y cuanto hay, y no entrega ni un nombre ni un contacto: eso
 * vive en `MisProveedoresController`, detras de la sesion del afiliado. La
 * URL sigue siendo publica a proposito, para no perder el indice ni mandar a
 * un login seco a quien llega desde un buscador.
 */
class ProveedorController
{
    public function index(): View
    {
        // Solo el recuento por categoria: numeros, nunca filas. Cualquier dato
        // que se agregue aqui hay que mirarlo dos veces, porque esta pagina la
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

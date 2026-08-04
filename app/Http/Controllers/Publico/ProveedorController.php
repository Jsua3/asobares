<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaProveedor;
use App\Http\Requests\GuardarSolicitudDeProveedorRequest;
use App\Models\Municipio;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CategoriaProveedor::class)],
        ]);

        // `vigente` implementa la monetización: quien no está al día no se lista.
        $consulta = Proveedor::publicado()->vigente()->with('municipio');

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria_proveedor', $datos['categoria']);
        }

        $proveedores = $consulta->orderBy('nombre')->paginate(24)->withQueryString();

        return view('publico.proveedores.index', [
            'proveedores' => $proveedores,
            'grupos' => $proveedores->getCollection()->groupBy(
                fn (Proveedor $proveedor): string => $proveedor->categoria_proveedor->value
            ),
            'categorias' => CategoriaProveedor::cases(),
            'filtros' => $datos,
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

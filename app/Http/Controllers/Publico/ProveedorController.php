<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaProveedor;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
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

        return view('publico.proveedores.index', [
            'proveedores' => $consulta->orderBy('nombre')->get()->groupBy(
                fn (Proveedor $proveedor): string => $proveedor->categoria_proveedor->value
            ),
            'categorias' => CategoriaProveedor::cases(),
            'filtros' => $datos,
        ]);
    }
}

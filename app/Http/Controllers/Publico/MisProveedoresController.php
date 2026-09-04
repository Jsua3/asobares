<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaProveedor;
use App\Models\Proveedor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El directorio de proveedores, con nombres y contactos, es un beneficio de
 * la afiliacion: solo lo ve quien esta adentro.
 *
 * La pagina publica /proveedores sigue existiendo y explica que es la bolsa,
 * pero sin un solo dato de contacto. Ese reparto es deliberado: cerrar la URL
 * entera habria mandado a un login seco a quien llega desde un buscador, y
 * habria borrado del indice una seccion que hoy trae visitas.
 */
class MisProveedoresController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CategoriaProveedor::class)],
        ]);

        // `vigente` implementa la monetizacion: quien no esta al dia no se lista.
        $consulta = Proveedor::publicado()->vigente()->with('municipio');

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria_proveedor', $datos['categoria']);
        }

        $proveedores = $consulta->orderBy('nombre')->paginate(24)->withQueryString();

        return view('publico.mi-cuenta.proveedores.index', [
            'proveedores' => $proveedores,
            'grupos' => $proveedores->getCollection()->groupBy(
                fn (Proveedor $proveedor): string => $proveedor->categoria_proveedor->value
            ),
            'categorias' => CategoriaProveedor::cases(),
            'filtros' => $datos,
        ]);
    }
}

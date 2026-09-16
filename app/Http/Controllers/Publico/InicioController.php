<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoAliado;
use App\Enums\UbicacionPublicidad;
use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Beneficio;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Publicidad;
use App\Support\BandaDeEstablecimientos;
use App\Support\CifrasDelGremio;
use App\Support\ReglaDeAlcaldias;
use Illuminate\Contracts\View\View;

class InicioController
{
    public function __invoke(ReglaDeAlcaldias $reglaDeAlcaldias): View
    {
        // Una sola consulta y dos bandas: `scopeVisible` ya ordena por
        // `orden`, y partir en memoria seis registros es más barato que ir
        // dos veces a la base. Si el juego de alcaldías está incompleto se
        // caen todas antes de repartir en bandas: «a todos o nada» es algo
        // que el sitio no sabe hacer mal (`ReglaDeAlcaldias`).
        $aliados = $reglaDeAlcaldias->filtrar(Aliado::visible()->with('municipio')->get());

        return view('publico.inicio', [
            // Orden alfabético, el mismo criterio del directorio
            // (`DirectorioController`, `destacado desc, nombre`). Por
            // `updated_at` el orden no se distingue del azar desde fuera, y
            // editar una ficha en el panel la subiría al primer puesto de la
            // portada sin que nadie lo hubiera pedido.
            //
            // El `orderBy` de la base elige CUÁLES entran al cupo, de forma
            // estable; `ordenarEnEspanol` decide en qué ORDEN se pintan,
            // porque SQLite ordena por bytes y dejaría «Ámbar» detrás de
            // «Zorba». La franja de la portada gira ese cupo en presentación
            // (`BandaDeEstablecimientos`), sin RANDOM() ni tocar el directorio.
            'destacados' => ordenarEnEspanol(
                Asociado::publicado()
                    ->where('destacado', true)
                    ->with(['categoria', 'municipio'])
                    ->orderBy('nombre')
                    ->take(BandaDeEstablecimientos::TOPE)
                    ->get()
            ),
            'beneficios' => Beneficio::with('municipio')->orderBy('orden')->get(),
            'aliadosInstitucionales' => $aliados->where('tipo', TipoAliado::Institucional)->values(),
            'aliadosComerciales' => $aliados->where('tipo', TipoAliado::Comercial)->values(),
            'proximosEventos' => Evento::publicado()->proximo()->take(3)->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->take(5)->get(),
            'publicidadInicio' => Publicidad::publicaEn(UbicacionPublicidad::Inicio)->first(),
            'totalAsociados' => Asociado::publicado()->count(),
            // Acta 05: la franja del gremio solo existe con cifras
            // tecleadas, y su fecha es la de la última que cambió.
            'cifrasDelGremio' => $cifrasDelGremio = CifrasDelGremio::vigentes(),
            'cifrasDelGremioActualizadas' => $cifrasDelGremio->isNotEmpty() ? CifrasDelGremio::actualizadasEl() : null,
        ]);
    }
}

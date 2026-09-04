<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Beneficio;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Support\CifrasDelGremio;
use App\Support\ReglaDeAlcaldias;
use Illuminate\Contracts\View\View;

class InicioController
{
    public function __invoke(ReglaDeAlcaldias $reglaDeAlcaldias): View
    {
        // Una sola consulta y dos bandas: `scopeVisible` ya ordena por
        // `orden`, y partir en memoria seis registros es mas barato que ir
        // dos veces a la base.
        // OBS3-05: si el juego de alcaldias esta incompleto se caen todas
        // antes de repartir en bandas. «A todos o nada» (R21 03:47) deja de
        // ser una promesa escrita y pasa a ser algo que el sitio no sabe
        // hacer mal.
        $aliados = $reglaDeAlcaldias->filtrar(Aliado::visible()->with('municipio')->get());

        return view('publico.inicio', [
            // OBS3-06. Ordenaba por `updated_at`, y desde fuera eso no se
            // distingue del azar: el directivo se paro justo en esto --«por
            // que esta colina primero, por que mirador... o simplemente un
            // aleatorio» (R21 06:11-06:17)-- y pidio «que sea en orden
            // alfabetico» (R21 06:24) o por ciudades.
            //
            // Alfabetico, que es ademas el mismo criterio que ya usa el
            // directorio (`DirectorioController`, `destacado desc, nombre`):
            // dos listas del mismo sitio ordenadas distinto vuelven a parecer
            // arbitrarias aunque cada una tenga su logica.
            //
            // Efecto colateral querido: el orden deja de moverse solo. Antes,
            // editar una ficha en el panel la subia al primer puesto de la
            // portada sin que nadie lo hubiera pedido.
            //
            // El `orderBy` de la base elige CUALES son los seis, de forma
            // estable; `ordenarEnEspanol` decide en que ORDEN se pintan,
            // porque SQLite ordena por bytes y dejaria «Ambar» detras de
            // «Zorba». Ver el comentario del ayudante.
            'destacados' => ordenarEnEspanol(
                Asociado::publicado()
                    ->where('destacado', true)
                    ->with(['categoria', 'municipio'])
                    ->orderBy('nombre')
                    ->take(6)
                    ->get()
            ),
            'beneficios' => Beneficio::orderBy('orden')->get(),
            'aliadosInstitucionales' => $aliados->where('tipo', TipoAliado::Institucional)->values(),
            'aliadosComerciales' => $aliados->where('tipo', TipoAliado::Comercial)->values(),
            'proximosEventos' => Evento::publicado()->proximo()->take(3)->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->take(5)->get(),
            'totalAsociados' => Asociado::publicado()->count(),
            // D-25, Acta 05: la franja del gremio solo existe con cifras
            // tecleadas, y su fecha es la de la última que cambió.
            'cifrasDelGremio' => $cifrasDelGremio = CifrasDelGremio::vigentes(),
            'cifrasDelGremioActualizadas' => $cifrasDelGremio->isNotEmpty() ? CifrasDelGremio::actualizadasEl() : null,
        ]);
    }
}

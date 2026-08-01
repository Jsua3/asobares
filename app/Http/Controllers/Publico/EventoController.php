<?php

namespace App\Http\Controllers\Publico;

use App\Http\Requests\GuardarInscripcionRequest;
use App\Models\Evento;
use App\Services\RegistroDePagos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventoController
{
    public function index(Request $request): View
    {
        $datos = $request->validate(['cuando' => ['nullable', 'in:proximos,pasados']]);
        $cuando = $datos['cuando'] ?? 'proximos';

        $consulta = Evento::publicado();
        $cuando === 'pasados' ? $consulta->pasado() : $consulta->proximo();

        return view('publico.eventos.index', [
            'eventos' => $consulta->paginate(9)->withQueryString(),
            'cuando' => $cuando,
            'totalProximos' => Evento::publicado()->proximo()->count(),
            'totalPasados' => Evento::publicado()->pasado()->count(),
        ]);
    }

    public function show(Evento $evento): View
    {
        abort_unless($evento->estaPublicado(), 404);

        return view('publico.eventos.show', [
            'evento' => $evento->loadCount('inscripciones'),
        ]);
    }

    public function inscribir(GuardarInscripcionRequest $request, Evento $evento, RegistroDePagos $pagos): RedirectResponse
    {
        abort_unless($evento->estaPublicado(), 404);

        if (! $evento->admiteInscripciones()) {
            return back()->with('error', 'Este evento ya no recibe inscripciones.');
        }

        $inscripcion = $evento->inscripciones()->create($request->datosDeLaInscripcion());

        // Si el evento es gratuito, la inscripción queda lista. Si tiene
        // precio, no se confirma hasta que la transacción sea aprobada.
        if ($evento->esGratuito()) {
            return redirect()
                ->route('eventos.show', $evento)
                ->with('exito', "Tu inscripción a «{$evento->titulo}» quedó registrada. Te enviamos la confirmación a {$inscripcion->correo}.");
        }

        $transaccion = $pagos->cobrarInscripcion($inscripcion);

        return redirect()->away($pagos->enlaceDePago($transaccion));
    }
}

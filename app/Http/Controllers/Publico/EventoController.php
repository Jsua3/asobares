<?php

namespace App\Http\Controllers\Publico;

use App\Http\Requests\GuardarInscripcionRequest;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Services\RegistroDePagos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Comprobar el cupo y luego insertar eran dos pasos sueltos: dos
        // peticiones simultáneas leían el mismo conteo, las dos lo daban por
        // bueno y las dos insertaban. Con el último asiento eso es sobreventa,
        // y en un evento de pago se cobra por una silla que no existe. El
        // bloqueo de la fila del evento serializa a los competidores, y la
        // comprobación se repite ya dentro del cerrojo.
        $inscripcion = DB::transaction(function () use ($evento, $request): ?Inscripcion {
            $enExclusiva = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();

            if (! $enExclusiva->admiteInscripciones()) {
                return null;
            }

            return $enExclusiva->inscripciones()->create($request->datosDeLaInscripcion());
        });

        if (! $inscripcion instanceof Inscripcion) {
            return back()->with('error', 'Este evento ya no recibe inscripciones.');
        }

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

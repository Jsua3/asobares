<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Http\Requests\GuardarVacanteRequest;
use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * El establecimiento publica y corrige sus propias vacantes.
 *
 * Antes las escribía la oficina «a nombre de» el asociado, que es justo lo
 * que hacía que la bolsa no se moviera: quien tiene la necesidad no tenía
 * cómo publicarla.
 */
class MisVacantesController
{
    public function index(Request $request): View
    {
        $asociado = $this->establecimientoDe($request);

        return view('publico.mi-cuenta.vacantes.index', [
            'asociado' => $asociado,
            'vacantes' => $asociado->vacantes()
                ->withCount('postulaciones')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function crear(Request $request): View
    {
        Gate::authorize('create', Vacante::class);

        return view('publico.mi-cuenta.vacantes.crear', [
            'vacante' => null,
            'categorias' => CargoDelSector::cases(),
            'tipos' => TipoVacante::cases(),
        ]);
    }

    public function store(GuardarVacanteRequest $request): RedirectResponse
    {
        Gate::authorize('create', Vacante::class);

        $request->user()->asociado->vacantes()->create([
            ...$request->datosDeLaVacante(),
            // El asociado nunca publica directo: su vacante entra a la fila
            // de revisión de la secretaría.
            'estado' => EstadoPublicacion::PendienteAprobacion,
        ]);

        return redirect()
            ->route('mi-cuenta.vacantes.index')
            ->with('exito', 'Tu vacante quedó enviada a revisión. La secretaría la aprueba y aparece en la bolsa.');
    }

    public function editar(Vacante $vacante): View
    {
        Gate::authorize('update', $vacante);

        return view('publico.mi-cuenta.vacantes.editar', [
            'vacante' => $vacante,
            'categorias' => CargoDelSector::cases(),
            'tipos' => TipoVacante::cases(),
        ]);
    }

    /**
     * Editar una vacante publicada la saca del muro hasta que la secretaría
     * revise el cambio. Es lo que ya hace el resto del contenido del sitio:
     * lo que está publicado es siempre algo que alguien aprobó.
     */
    public function update(GuardarVacanteRequest $request, Vacante $vacante): RedirectResponse
    {
        Gate::authorize('update', $vacante);

        $vacante->update([
            ...$request->datosDeLaVacante(),
            'estado' => EstadoPublicacion::PendienteAprobacion,
            'motivo_devolucion' => null,
        ]);

        return redirect()
            ->route('mi-cuenta.vacantes.index')
            ->with('exito', 'Guardamos el cambio y lo enviamos a revisión. Mientras tanto la vacante no aparece en la bolsa.');
    }

    /** «Ya contraté»: sale del muro sin pasar por la secretaría. */
    public function cerrar(Vacante $vacante): RedirectResponse
    {
        Gate::authorize('cerrar', $vacante);

        $vacante->saltaFlujoDeAprobacion = true;
        $vacante->update(['cerrada_at' => now()]);

        return redirect()
            ->route('mi-cuenta.vacantes.index')
            ->with('exito', 'Cerramos la vacante. Deja de recibir postulaciones y sale de la bolsa.');
    }

    public function reabrir(Vacante $vacante): RedirectResponse
    {
        Gate::authorize('cerrar', $vacante);

        // Reabrir una vencida no la haría visible: la fecha manda igual.
        if ($vacante->estaVencida()) {
            return redirect()
                ->route('mi-cuenta.vacantes.index')
                ->with('error', 'Esa vacante ya pasó su fecha límite. Edítala con una fecha nueva para volver a publicarla.');
        }

        $vacante->saltaFlujoDeAprobacion = true;
        $vacante->update(['cerrada_at' => null]);

        return redirect()
            ->route('mi-cuenta.vacantes.index')
            ->with('exito', 'La vacante volvió a la bolsa de empleo.');
    }

    public function show(Vacante $vacante): View
    {
        Gate::authorize('view', $vacante);

        return view('publico.mi-cuenta.vacantes.show', [
            'vacante' => $vacante,
            'postulaciones' => $vacante->postulaciones()->latest()->paginate(25),
            'estados' => EstadoDeGestion::cases(),
        ]);
    }

    public function gestionarPostulacion(Request $request, Postulacion $postulacion): RedirectResponse
    {
        Gate::authorize('gestionar', $postulacion);

        $datos = $request->validate([
            'estado' => ['required', Rule::enum(EstadoDeGestion::class)],
        ]);

        $postulacion->update($datos);

        return redirect()
            ->route('mi-cuenta.vacantes.show', $postulacion->vacante)
            ->with('exito', 'Actualizamos el estado de la postulación.');
    }

    /** Un usuario con rol asociado pero sin ficha no tiene dónde publicar. */
    private function establecimientoDe(Request $request): Asociado
    {
        $asociado = $request->user()->asociado;

        abort_if($asociado === null, 403, 'Tu usuario todavía no está vinculado a un establecimiento.');

        return $asociado;
    }
}

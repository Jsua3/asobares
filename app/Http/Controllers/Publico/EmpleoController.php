<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CargoDelSector;
use App\Http\Requests\GuardarAspiranteRequest;
use App\Http\Requests\GuardarPostulacionRequest;
use App\Mail\NuevaPostulacion;
use App\Models\Aspirante;
use App\Models\Municipio;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Support\DestinatariosDelAsociado;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Bolsa de empleo del sector: un muro donde solo publican los asociados,
 * y un formulario abierto para quien busca trabajo.
 */
class EmpleoController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CargoDelSector::class)],
            'municipio' => ['nullable', 'string', 'exists:municipios,slug'],
        ]);

        $consulta = Vacante::publicado()
            ->vigente()
            ->with(['asociado.municipio', 'asociado.categoria']);

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria_cargo', $datos['categoria']);
        }

        if (filled($datos['municipio'] ?? null)) {
            $consulta->whereHas('asociado.municipio', fn ($q) => $q->where('slug', $datos['municipio']));
        }

        return view('publico.empleo.index', [
            'vacantes' => $consulta->latest()->paginate(10)->withQueryString(),
            'municipios' => Municipio::orderBy('nombre')->get(),
            'categorias' => CargoDelSector::cases(),
            'filtros' => $datos,
        ]);
    }

    public function show(Vacante $vacante): View
    {
        // Una vacante cerrada, vencida o sin aprobar no existe para el
        // público: 404, no una página que diga «ya no está disponible».
        abort_unless($vacante->aceptaPostulaciones(), 404);

        $vacante->load(['asociado.municipio', 'asociado.categoria']);

        return view('publico.empleo.show', [
            'vacante' => $vacante,
            'similares' => Vacante::publicado()
                ->vigente()
                ->where('categoria_cargo', $vacante->categoria_cargo)
                ->whereKeyNot($vacante->id)
                ->with('asociado.municipio')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    /**
     * Antes «postularse» era un enlace de WhatsApp: no quedaba rastro, el
     * establecimiento no tenía dónde mirar y sin número no había forma de
     * aplicar. Ahora la postulación se guarda y se avisa por correo.
     */
    public function postular(GuardarPostulacionRequest $request, Vacante $vacante): RedirectResponse
    {
        abort_unless($vacante->aceptaPostulaciones(), 404);

        $datos = $request->datosDeLaPostulacion();

        $postulacion = Postulacion::where('vacante_id', $vacante->id)
            ->where('correo', $datos['correo'])
            ->first();

        // Reenviar el formulario actualiza los datos, no crea un duplicado ni
        // vuelve a molestar al establecimiento con un correo repetido.
        if ($postulacion instanceof Postulacion) {
            $postulacion->update($datos);
        } else {
            try {
                $postulacion = $vacante->postulaciones()->create($datos);

                $this->avisarAlEstablecimiento($postulacion);
            } catch (UniqueConstraintViolationException) {
                // El «buscar → si no existe, crear» de arriba no es atómico:
                // un doble clic o un reintento por red lenta puede mandar dos
                // peticiones que pasen ambas el `first()` como null antes de
                // que la primera termine de insertar. Aquí perdimos la
                // carrera contra el índice único (vacante_id, correo); quien
                // la ganó ya avisó al establecimiento, así que solo
                // actualizamos sin repetir el correo.
                $postulacion = Postulacion::where('vacante_id', $vacante->id)
                    ->where('correo', $datos['correo'])
                    ->firstOrFail();

                $postulacion->update($datos);
            }
        }

        return redirect()
            ->route('empleo.show', $vacante)
            ->with('exito', 'Enviamos tu postulación al establecimiento. Si les encaja tu perfil, te contactan directamente.')
            ->withFragment('postularme');
    }

    private function avisarAlEstablecimiento(Postulacion $postulacion): void
    {
        $correos = DestinatariosDelAsociado::correos($postulacion->vacante->asociado);

        if ($correos === []) {
            // Sin correo no hay a quién avisar, pero la postulación ya quedó
            // guardada: el establecimiento la verá al entrar a su cuenta.
            return;
        }

        Mail::to($correos)->send(new NuevaPostulacion($postulacion));
    }

    public function registrarAspirante(GuardarAspiranteRequest $request): RedirectResponse
    {
        $datos = $request->datosDelAspirante();

        // Volver a dejar el perfil actualiza el que ya existe: una persona,
        // un registro. Antes cada reenvío creaba una fila nueva.
        Aspirante::updateOrCreate(['correo' => $datos['correo']], $datos);

        return redirect()
            ->route('empleo.index')
            ->with('exito', 'Tu perfil quedó registrado. Cuando un establecimiento asociado busque tu cargo, te contactamos.')
            ->withFragment('perfil');
    }
}

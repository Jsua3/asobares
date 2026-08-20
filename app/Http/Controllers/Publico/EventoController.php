<?php

namespace App\Http\Controllers\Publico;

use App\Http\Requests\GuardarInscripcionRequest;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Services\RegistroDePagos;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        ] + $this->totalesDelConmutador());
    }

    /** `/eventos/calendario` a secas: al mes en curso, y con una URL canónica por mes. */
    public function calendarioDeHoy(): RedirectResponse
    {
        return redirect()->route('eventos.calendario', [now()->year, now()->format('m')]);
    }

    public function calendario(int $anio, int $mes): View
    {
        /*
         * La restricción de la ruta ya acota el mes a 01-12; esto acota el año.
         * Sin tope, los enlaces «mes anterior» y «mes siguiente» son una máquina
         * de generar URLs: un rastreador camina hasta el año 9999 y cada paso es
         * una página distinta que hay que renderizar.
         */
        abort_unless($anio >= 2020 && $anio <= now()->year + 5, 404);

        $primerDia = Carbon::create($anio, $mes, 1)->startOfMonth();
        $ultimoDia = $primerDia->copy()->endOfMonth();

        /*
         * La rejilla arranca el LUNES de la semana del día 1 y termina el
         * domingo de la semana del último día (locale `es`: `startOfWeek` cae
         * en lunes). Los eventos de esos días colgantes hay que traerlos
         * también, o las celdas de relleno mienten diciendo que no pasa nada:
         * septiembre de 2026 empieza en martes y su primera fila incluye el
         * lunes 31 de agosto.
         */
        $inicioRejilla = $primerDia->copy()->startOfWeek();
        $finRejilla = $ultimoDia->copy()->endOfWeek();

        /*
         * UNA consulta para todo el mes, y el reparto por casillas se hace
         * después en memoria. `imagen` y `descripcion` no se piden: la rejilla
         * no los pinta y son las dos columnas más pesadas de la tabla. `slug`
         * sí, porque es la clave de ruta (`Evento::getRouteKeyName`).
         */
        $eventos = Evento::publicado()
            ->enRango($inicioRejilla, $finRejilla)
            ->get(['id', 'titulo', 'slug', 'tipo', 'lugar', 'fecha_inicio', 'fecha_fin', 'precio']);

        // Un solo agregado para saber si el mes pedido cae fuera de lo que el
        // gremio tiene publicado. Los meses vacíos se navegan igual, pero se
        // marcan `noindex`: son infinitos en las dos direcciones.
        $rango = Evento::publicado()
            ->selectRaw('MIN(fecha_inicio) as primero, MAX(COALESCE(fecha_fin, fecha_inicio)) as ultimo')
            ->first();

        return view('publico.eventos.calendario', [
            'mes' => $primerDia,
            'semanas' => collect(CarbonPeriod::create($inicioRejilla, $finRejilla))->chunk(7),
            'porDia' => $this->repartirPorDia($eventos, $inicioRejilla, $finRejilla),
            'anterior' => $primerDia->copy()->subMonthNoOverflow(),
            'siguiente' => $primerDia->copy()->addMonthNoOverflow(),
            'fueraDeRango' => $rango?->primero === null
                || $ultimoDia->lessThan(Carbon::parse($rango->primero))
                || $primerDia->greaterThan(Carbon::parse($rango->ultimo)),
        ] + $this->totalesDelConmutador());
    }

    /**
     * Un evento de varios días ocupa TODAS sus casillas.
     *
     * Se reparte en PHP y no con un `groupBy` de Eloquent porque una fila tiene
     * que salir en N celdas: `groupBy('fecha_inicio')` la dejaría sólo en la del
     * arranque, que es justo el defecto que el calendario existe para no tener.
     * Y cuesta cero consultas, porque la colección ya está en memoria: la
     * alternativa —preguntar por cada casilla— son 42 consultas por página,
     * invisibles en local con seis eventos sembrados.
     *
     * @param  Collection<int, Evento>  $eventos
     * @return array<string, list<Evento>> 'AAAA-MM-DD' => eventos de ese día
     */
    private function repartirPorDia(Collection $eventos, CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $porDia = [];

        foreach ($eventos as $evento) {
            $arranque = $evento->fecha_inicio->copy()->startOfDay()->max($desde);
            $cierre = ($evento->fecha_fin ?? $evento->fecha_inicio)->copy()->startOfDay()->min($hasta);

            // Una `fecha_fin` anterior a la de inicio es dato corrupto, no un
            // rango vacío: se pinta al menos el día del arranque en vez de
            // desaparecer el evento del calendario sin avisar a nadie.
            if ($cierre->lessThan($arranque)) {
                $cierre = $arranque->copy();
            }

            foreach (CarbonPeriod::create($arranque, $cierre) as $dia) {
                $porDia[$dia->toDateString()][] = $evento;
            }
        }

        return $porDia;
    }

    /**
     * Los dos contadores del conmutador. Los pintan DOS páginas —la rejilla de
     * tarjetas y el calendario— y son dos COUNT baratos que aprovechan el
     * índice `['estado','fecha_inicio']`. Se extraen para que no diverjan: el
     * día que `scopeProximo` vuelva a cambiar, una copia se quedaría atrás.
     *
     * @return array{totalProximos: int, totalPasados: int}
     */
    private function totalesDelConmutador(): array
    {
        return [
            'totalProximos' => Evento::publicado()->proximo()->count(),
            'totalPasados' => Evento::publicado()->pasado()->count(),
        ];
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

        try {
            return redirect()->away($pagos->enlaceDePago($transaccion));
        } catch (Throwable $fallo) {
            // El entorno remoto arranca con `PAYMENT_DRIVER=bold` y sin llaves
            // de Bold (§20.5), así que `PasarelaBold::crearEnlaceDePago` lanza
            // en cuanto alguien pulsa «Inscribirme». Sin este `catch` el
            // visitante veía una página 500 pelada —con APP_DEBUG=false, sin
            // ninguna explicación— en una de las dos rutas que la dirección va
            // a pulsar en la demostración.
            //
            // Mismo trato que en `MiCuentaController::pagarMensualidad`: se
            // registra el fallo, la inscripción y su cobro se quedan
            // pendientes —no se ha duplicado nada y se puede reintentar— y el
            // visitante recibe una frase que sí se entiende.
            Log::error('No se pudo generar el enlace de pago de la inscripción.', [
                'referencia' => $transaccion->referencia,
                'evento' => $evento->slug,
                'excepcion' => $fallo->getMessage(),
            ]);

            return back()->with(
                'error',
                'Tu inscripción quedó registrada, pero no pudimos abrir la pasarela de pago en este momento. '
                .'Inténtalo de nuevo en unos minutos o escríbenos desde /contacto.'
            );
        }
    }
}

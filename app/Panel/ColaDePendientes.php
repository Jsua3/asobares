<?php

namespace App\Panel;

use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\RequisitoApertura;
use App\Models\User;
use App\Models\Vacante;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

/**
 * Qué tiene pendiente de aprobar quien acaba de entrar al panel.
 *
 * Se pregunta a la **policy** y no al rol, igual que hace
 * `FlujoDeAprobacionObserver::avisarAQuienAprueba()`. Así la secretaría ve
 * las tres bolsas —que sí aprueba—, la dirección ve el contenido que redacta
 * la secretaría, y el día que cambie una policy la cola la sigue sin que haya
 * que tocar nada aquí.
 */
class ColaDePendientes
{
    /** Los nueve modelos con flujo editorial, con su nombre en plural. */
    private const array PUBLICABLES = [
        Asociado::class => ['asociado', 'asociados'],
        Evento::class => ['evento', 'eventos'],
        Noticia::class => ['noticia', 'noticias'],
        RequisitoApertura::class => ['requisito', 'requisitos'],
        Iniciativa::class => ['iniciativa', 'iniciativas'],
        Vacante::class => ['vacante', 'vacantes'],
        Artista::class => ['artista', 'artistas'],
        Proveedor::class => ['proveedor', 'proveedores'],
        Aliado::class => ['aliado', 'aliados'],
    ];

    /** Días esperando a partir de los cuales un pendiente se marca urgente. */
    private const int DIAS_PARA_URGENTE = 5;

    /**
     * @return array<int, array{
     *     etiqueta: string,
     *     conteo: int,
     *     url: string,
     *     antiguedad: ?string,
     *     urgente: bool
     * }>
     */
    public function para(User $usuario): array
    {
        $filas = [];

        foreach (self::PUBLICABLES as $clase => [$singular, $plural]) {
            /** @var Model $modelo */
            $modelo = new $clase;

            // Instancia sin guardar: las policies de `publicar` resuelven por
            // permiso, no por el registro, así que basta para preguntar.
            if (! Gate::forUser($usuario)->allows('publicar', $modelo)) {
                continue;
            }

            $pendientes = $clase::query()->pendiente();
            $conteo = $pendientes->count();

            if ($conteo === 0) {
                continue;
            }

            $masAntigua = $clase::query()->pendiente()->min('updated_at');
            $espera = $masAntigua === null ? null : Carbon::parse($masAntigua);

            $filas[] = [
                'etiqueta' => $conteo === 1
                    ? "1 {$singular} esperando tu aprobación"
                    : "{$conteo} {$plural} esperando tu aprobación",
                'conteo' => $conteo,
                'url' => $this->urlDelListado($clase),
                'antiguedad' => $espera === null
                    ? null
                    : 'la más antigua, '.$espera->diffForHumans(),
                'urgente' => $espera !== null
                    && $espera->lt(now()->subDays(self::DIAS_PARA_URGENTE)),
            ];
        }

        // Lo más urgente primero, y a igual urgencia lo más numeroso.
        usort($filas, fn (array $a, array $b): int => [$b['urgente'], $b['conteo']] <=> [$a['urgente'], $a['conteo']]);

        return $filas;
    }

    public function total(User $usuario): int
    {
        return array_sum(array_column($this->para($usuario), 'conteo'));
    }

    /** @param  class-string<Model>  $clase */
    private function urlDelListado(string $clase): string
    {
        $recurso = Filament::getModelResource($clase);

        return $recurso === null ? url('/admin') : $recurso::getUrl('index');
    }
}

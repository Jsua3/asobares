<?php

namespace App\Filament\Resources\Eventos\Pages;

use App\Enums\OrigenEvento;
use App\Filament\Resources\Eventos\EventoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    /**
     * Marca el listado para el patrón visual operativo (`.asb-operativo`).
     * No cambia consultas, filtros, acciones ni permisos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * Todos | ASOBARES | Aliados | Comunidad, sobre la MISMA tabla: cada
     * pestaña solo cambia la consulta del listado, así que edición,
     * despublicación y borrado siguen siendo las mismas acciones de
     * `EventosTable` para cualquier origen.
     *
     * «ASOBARES» reutiliza `Evento::scopeDelGremio()`, que ya trata un
     * `origen` nulo como ASOBARES: son eventos anteriores a la columna
     * (ver la migración `rellena_origen_asobares_en_eventos_huerfanos`,
     * que ya normalizó esos huérfanos en la base) y el resto del sitio —el
     * boot del modelo, la portada pública— ya los lee así. Clasificarlos
     * aparte aquí los desalinearía del resto de la app.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'asobares' => Tab::make('ASOBARES')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->delGremio()),
            'aliados' => Tab::make('Aliados')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('origen', OrigenEvento::Aliado->value)),
            'comunidad' => Tab::make('Comunidad')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('origen', OrigenEvento::Comunidad->value)),
        ];
    }
}

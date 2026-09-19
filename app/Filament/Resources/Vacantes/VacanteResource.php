<?php

namespace App\Filament\Resources\Vacantes;

use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Resources\Vacantes\Tables\VacantesTable;
use App\Models\Vacante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Bandeja de moderación de la bolsa de empleo.
 *
 * Sin formulario a propósito: la vacante la escribe y la corrige el
 * establecimiento desde /mi-cuenta. Aquí solo se aprueba o se devuelve.
 */
class VacanteResource extends Resource
{
    protected static ?string $model = Vacante::class;

    /** Lo que muestra y busca el buscador general del panel. */
    protected static ?string $recordTitleAttribute = 'cargo';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|UnitEnum|null $navigationGroup = 'Bolsas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'vacantes';

    protected static ?string $modelLabel = 'Vacante';

    protected static ?string $pluralModelLabel = 'Bolsa de empleo';

    public static function table(Table $table): Table
    {
        return VacantesTable::configure($table);
    }

    /**
     * La bandeja no tiene página por vacante: el resultado del buscador lleva
     * al listado filtrado por su cargo. El buscador solo recorre este recurso
     * para quien tiene `ver_vacante`, que es el mismo permiso que pide el
     * listado.
     */
    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('index', ['search' => $record->getAttribute('cargo')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacantes::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Vacantes;

use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Resources\Vacantes\Tables\VacantesTable;
use App\Models\Vacante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Bandeja de moderación de la bolsa de empleo.
 *
 * Sin formulario a propósito: la vacante la escribe y la corrige el
 * establecimiento desde /mi-cuenta. Aquí solo se aprueba o se devuelve.
 */
class VacanteResource extends Resource
{
    protected static ?string $model = Vacante::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Bolsas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'vacantes';

    protected static ?string $modelLabel = 'Vacante';

    protected static ?string $pluralModelLabel = 'Bolsa de empleo';

    public static function table(Table $table): Table
    {
        return VacantesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacantes::route('/'),
        ];
    }
}

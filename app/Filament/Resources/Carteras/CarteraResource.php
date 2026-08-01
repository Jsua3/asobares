<?php

namespace App\Filament\Resources\Carteras;

use App\Filament\Resources\Carteras\Pages\ListCarteras;
use App\Filament\Resources\Carteras\Tables\CarterasTable;
use App\Models\Cartera;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

/**
 * La cartera no se edita a mano: entra por el CSV de la contadora y se salda
 * con transacciones aprobadas. Por eso no tiene páginas de crear ni editar.
 */
class CarteraResource extends Resource
{
    protected static ?string $model = Cartera::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'cartera';

    protected static ?string $modelLabel = 'Estado de cuenta';

    protected static ?string $pluralModelLabel = 'Cartera';

    public static function table(Table $table): Table
    {
        return CarterasTable::configure($table);
    }

    /** Badge con la cantidad de afiliados en mora. */
    public static function getNavigationBadge(): ?string
    {
        $enMora = Cartera::enMora()->count();

        return $enMora > 0 ? (string) $enMora : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarteras::route('/'),
        ];
    }
}

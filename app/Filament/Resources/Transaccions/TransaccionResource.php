<?php

namespace App\Filament\Resources\Transaccions;

use App\Filament\Resources\Transaccions\Pages\ListTransaccions;
use App\Filament\Resources\Transaccions\Tables\TransaccionsTable;
use App\Models\Transaccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

class TransaccionResource extends Resource
{
    protected static ?string $model = Transaccion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'transacciones';

    protected static ?string $modelLabel = 'Transacción';

    protected static ?string $pluralModelLabel = 'Transacciones';

    public static function table(Table $table): Table
    {
        return TransaccionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransaccions::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Aliados;

use App\Filament\Resources\Aliados\Pages\CreateAliado;
use App\Filament\Resources\Aliados\Pages\EditAliado;
use App\Filament\Resources\Aliados\Pages\ListAliados;
use App\Filament\Resources\Aliados\Schemas\AliadoForm;
use App\Filament\Resources\Aliados\Tables\AliadosTable;
use App\Models\Aliado;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AliadoResource extends Resource
{
    protected static ?string $model = Aliado::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'aliados';

    protected static ?string $modelLabel = 'Aliado';

    protected static ?string $pluralModelLabel = 'Aliados y convenios';

    public static function form(Schema $schema): Schema
    {
        return AliadoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AliadosTable::configure($table);
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
            'index' => ListAliados::route('/'),
            'create' => CreateAliado::route('/create'),
            'edit' => EditAliado::route('/{record}/edit'),
        ];
    }
}

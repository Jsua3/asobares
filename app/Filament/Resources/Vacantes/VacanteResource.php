<?php

namespace App\Filament\Resources\Vacantes;

use App\Filament\Resources\Vacantes\Pages\CreateVacante;
use App\Filament\Resources\Vacantes\Pages\EditVacante;
use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Resources\Vacantes\Schemas\VacanteForm;
use App\Filament\Resources\Vacantes\Tables\VacantesTable;
use App\Models\Vacante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VacanteResource extends Resource
{
    protected static ?string $model = Vacante::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Bolsas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'vacantes';

    protected static ?string $modelLabel = 'Vacante';

    protected static ?string $pluralModelLabel = 'Bolsa de empleo';

    public static function form(Schema $schema): Schema
    {
        return VacanteForm::configure($schema);
    }

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
            'create' => CreateVacante::route('/create'),
            'edit' => EditVacante::route('/{record}/edit'),
        ];
    }
}

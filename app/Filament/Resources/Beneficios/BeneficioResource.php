<?php

namespace App\Filament\Resources\Beneficios;

use App\Filament\Resources\Beneficios\Pages\CreateBeneficio;
use App\Filament\Resources\Beneficios\Pages\EditBeneficio;
use App\Filament\Resources\Beneficios\Pages\ListBeneficios;
use App\Filament\Resources\Beneficios\Schemas\BeneficioForm;
use App\Filament\Resources\Beneficios\Tables\BeneficiosTable;
use App\Models\Beneficio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BeneficioResource extends Resource
{
    protected static ?string $model = Beneficio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'beneficios';

    protected static ?string $modelLabel = 'Beneficio';

    protected static ?string $pluralModelLabel = 'Beneficios del afiliado';

    public static function form(Schema $schema): Schema
    {
        return BeneficioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiosTable::configure($table);
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
            'index' => ListBeneficios::route('/'),
            'create' => CreateBeneficio::route('/create'),
            'edit' => EditBeneficio::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Inscripcions;

use App\Filament\Resources\Inscripcions\Pages\CreateInscripcion;
use App\Filament\Resources\Inscripcions\Pages\EditInscripcion;
use App\Filament\Resources\Inscripcions\Pages\ListInscripcions;
use App\Filament\Resources\Inscripcions\Schemas\InscripcionForm;
use App\Filament\Resources\Inscripcions\Tables\InscripcionsTable;
use App\Models\Inscripcion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InscripcionResource extends Resource
{
    protected static ?string $model = Inscripcion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Bandejas';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'inscripciones';

    protected static ?string $modelLabel = 'Inscripción';

    protected static ?string $pluralModelLabel = 'Inscripciones';

    public static function form(Schema $schema): Schema
    {
        return InscripcionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InscripcionsTable::configure($table);
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
            'index' => ListInscripcions::route('/'),
            'create' => CreateInscripcion::route('/create'),
            'edit' => EditInscripcion::route('/{record}/edit'),
        ];
    }
}

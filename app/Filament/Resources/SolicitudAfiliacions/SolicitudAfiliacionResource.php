<?php

namespace App\Filament\Resources\SolicitudAfiliacions;

use App\Filament\Resources\SolicitudAfiliacions\Pages\EditSolicitudAfiliacion;
use App\Filament\Resources\SolicitudAfiliacions\Pages\ListSolicitudAfiliacions;
use App\Filament\Resources\SolicitudAfiliacions\Schemas\SolicitudAfiliacionForm;
use App\Filament\Resources\SolicitudAfiliacions\Tables\SolicitudAfiliacionsTable;
use App\Models\SolicitudAfiliacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SolicitudAfiliacionResource extends Resource
{
    protected static ?string $model = SolicitudAfiliacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Bandejas';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'solicitudes-afiliacion';

    protected static ?string $modelLabel = 'Solicitud de afiliación';

    protected static ?string $pluralModelLabel = 'Solicitudes de afiliación';

    public static function form(Schema $schema): Schema
    {
        return SolicitudAfiliacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolicitudAfiliacionsTable::configure($table);
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
            'index' => ListSolicitudAfiliacions::route('/'),
            'edit' => EditSolicitudAfiliacion::route('/{record}/edit'),
        ];
    }
}

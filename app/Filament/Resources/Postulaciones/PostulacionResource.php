<?php

namespace App\Filament\Resources\Postulaciones;

use App\Filament\Resources\Postulaciones\Pages\ListPostulaciones;
use App\Filament\Resources\Postulaciones\Tables\PostulacionesTable;
use App\Models\Postulacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Bandeja de supervisión: quién se está postulando y a qué.
 *
 * El gremio no recibe aviso por cada postulación —eso sería ruido— pero
 * conserva la vista para medir si la bolsa está sirviendo y para responder
 * por los datos que custodia.
 */
class PostulacionResource extends Resource
{
    protected static ?string $model = Postulacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Bandejas';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'postulaciones';

    protected static ?string $modelLabel = 'Postulación';

    protected static ?string $pluralModelLabel = 'Postulaciones';

    public static function table(Table $table): Table
    {
        return PostulacionesTable::configure($table);
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
            'index' => ListPostulaciones::route('/'),
        ];
    }
}

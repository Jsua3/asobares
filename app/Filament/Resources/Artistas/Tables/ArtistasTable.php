<?php

namespace App\Filament\Resources\Artistas\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ArtistasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('genero_musical')
                    ->searchable(),
                TextColumn::make('tarifa_desde')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('video_url')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->searchable(),
                TextColumn::make('instagram_url')
                    ->searchable(),
                TextColumn::make('foto')
                    ->searchable(),
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->searchable(),
                TextColumn::make('estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                AccionesDeAprobacion::aprobarFichaDeBolsa(fn (Model $registro): string => route('artistas.show', $registro)),
                AccionesDeAprobacion::devolver(),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarEnLote('publicar_artista'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

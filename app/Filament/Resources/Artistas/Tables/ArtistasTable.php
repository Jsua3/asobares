<?php

namespace App\Filament\Resources\Artistas\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Artista;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ArtistasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('municipio'))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Artista')
                    ->searchable(['nombre', 'slug', 'genero_musical', 'whatsapp'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Artista $registro): ?string => self::lineaSecundaria([
                        $registro->genero_musical,
                        $registro->municipio?->nombre,
                    ]))
                    ->wrap()
                    ->width('18rem'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ingreso')
                    ->since()
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    AccionesDeAprobacion::aprobarFichaDeBolsa(fn (Model $registro): string => route('artistas.show', $registro)),
                    AccionesDeAprobacion::devolver(),
                    EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones del artista'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarFichasEnLote(
                        'publicar_artista',
                        fn (Model $registro): string => route('artistas.show', $registro)
                    ),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  list<string|null>  $partes
     */
    private static function lineaSecundaria(array $partes): ?string
    {
        $linea = collect($partes)->filter()->implode(' · ');

        return $linea === '' ? null : $linea;
    }
}

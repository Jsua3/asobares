<?php

namespace App\Filament\Resources\SolicitudAfiliacions\Tables;

use App\Enums\EstadoSolicitudAfiliacion;
use App\Models\SolicitudAfiliacion;
use App\Services\AprobarSolicitudAfiliacion;
use App\Services\ReenviarEnlaceAccesoAsociado;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SolicitudAfiliacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('establecimiento_nombre')
                    ->label('Establecimiento')
                    ->searchable(['establecimiento_nombre', 'razon_social', 'nit', 'direccion'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (SolicitudAfiliacion $record): ?string => $record->razon_social)
                    ->wrap()
                    ->width('18rem'),
                TextColumn::make('solicitante_nombre')
                    ->label('Solicitante')
                    ->searchable(['solicitante_nombre', 'solicitante_correo', 'solicitante_identificacion'])
                    ->description(fn (SolicitudAfiliacion $record): string => $record->solicitante_correo)
                    ->wrap(),
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->sortable(),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Solicitada')
                    ->since()
                    ->sortable(),
                TextColumn::make('visita_programada_at')
                    ->label('Visita')
                    ->dateTime('d/m/Y h:i a')
                    ->placeholder('Sin programar')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoSolicitudAfiliacion::class),
                SelectFilter::make('municipio')
                    ->label('Municipio')
                    ->relationship('municipio', 'nombre')
                    ->preload(),
                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->preload(),
                Filter::make('ultima_semana')
                    ->label('Últimos 7 días')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subWeek())),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver'),
                EditAction::make()->label('Gestionar'),
                ActionGroup::make([
                    self::pasarARevision(),
                    self::registrarVisita(),
                    self::aprobar(),
                    self::reenviarEnlace(),
                    self::rechazar(),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Más acciones'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Sin solicitudes de afiliación')
            ->emptyStateDescription('Las nuevas solicitudes enviadas desde /afiliate aparecerán aquí.');
    }

    private static function pasarARevision(): Action
    {
        return Action::make('pasar_a_revision')
            ->label('Pasar a revisión')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->visible(fn (SolicitudAfiliacion $record): bool => $record->estado === EstadoSolicitudAfiliacion::Pendiente
                && auth()->user()?->can('update', $record) === true)
            ->action(function (SolicitudAfiliacion $record): void {
                $record->update(['estado' => EstadoSolicitudAfiliacion::EnRevision]);

                Notification::make()->title('Solicitud en revisión')->success()->send();
            });
    }

    private static function registrarVisita(): Action
    {
        return Action::make('registrar_visita')
            ->label('Registrar visita')
            ->icon('heroicon-o-calendar-days')
            ->color('primary')
            ->schema([
                DateTimePicker::make('visita_programada_at')
                    ->label('Fecha de visita')
                    ->native(false)
                    ->required(),
                Textarea::make('gestion_notas')
                    ->label('Notas de seguimiento')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->visible(fn (SolicitudAfiliacion $record): bool => in_array($record->estado, [
                EstadoSolicitudAfiliacion::Pendiente,
                EstadoSolicitudAfiliacion::EnRevision,
            ], true) && auth()->user()?->can('update', $record) === true)
            ->action(function (SolicitudAfiliacion $record, array $data): void {
                $record->update([
                    'estado' => EstadoSolicitudAfiliacion::Visita,
                    'visita_programada_at' => $data['visita_programada_at'],
                    'gestion_notas' => $data['gestion_notas'] ?? $record->gestion_notas,
                ]);

                Notification::make()->title('Visita registrada')->success()->send();
            });
    }

    private static function aprobar(): Action
    {
        return Action::make('aprobar_solicitud')
            ->label('Aprobar y crear acceso')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aprobar solicitud de afiliación')
            ->modalDescription('Se creará el asociado, el usuario de Mi Cuenta y se enviará un enlace para crear contraseña.')
            ->modalSubmitActionLabel('Sí, aprobar')
            ->schema([
                Textarea::make('gestion_notas')
                    ->label('Observación interna')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->visible(fn (SolicitudAfiliacion $record): bool => in_array($record->estado, [
                EstadoSolicitudAfiliacion::Pendiente,
                EstadoSolicitudAfiliacion::EnRevision,
                EstadoSolicitudAfiliacion::Visita,
            ], true) && $record->asociado_id === null
                && $record->user_id === null
                && auth()->user()?->can('update', $record) === true)
            ->action(function (SolicitudAfiliacion $record, array $data): void {
                try {
                    $correoSalio = app(AprobarSolicitudAfiliacion::class)(
                        $record,
                        auth()->user(),
                        $data['gestion_notas'] ?? null
                    );
                } catch (\LogicException $excepcion) {
                    Notification::make()
                        ->title('No se pudo aprobar')
                        ->body($excepcion->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                if ($correoSalio) {
                    Notification::make()->title('Solicitud aprobada y acceso creado')->success()->send();

                    return;
                }

                Notification::make()
                    ->title('Solicitud aprobada y acceso creado, pero el correo no salió')
                    ->body('El asociado quedó creado. El fallo del correo quedó registrado; será necesario reenviar el enlace por otro medio operativo.')
                    ->warning()
                    ->persistent()
                    ->send();
            });
    }

    private static function rechazar(): Action
    {
        return Action::make('rechazar_solicitud')
            ->label('Rechazar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Rechazar solicitud')
            ->modalSubmitActionLabel('Sí, rechazar')
            ->schema([
                Textarea::make('gestion_notas')
                    ->label('Motivo u observación')
                    ->rows(4)
                    ->required()
                    ->maxLength(1000),
            ])
            ->visible(fn (SolicitudAfiliacion $record): bool => ! in_array($record->estado, [
                EstadoSolicitudAfiliacion::Aprobada,
                EstadoSolicitudAfiliacion::Rechazada,
            ], true) && auth()->user()?->can('update', $record) === true)
            ->action(function (SolicitudAfiliacion $record, array $data): void {
                $record->update([
                    'estado' => EstadoSolicitudAfiliacion::Rechazada,
                    'rechazado_por' => auth()->id(),
                    'rechazado_at' => now(),
                    'resuelto_at' => now(),
                    'gestion_notas' => $data['gestion_notas'],
                ]);

                Notification::make()->title('Solicitud rechazada')->warning()->send();
            });
    }

    private static function reenviarEnlace(): Action
    {
        return Action::make('reenviar_enlace_acceso')
            ->label('Reenviar enlace de acceso')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Reenviar enlace de acceso')
            ->modalDescription('Se generará un nuevo enlace temporal para que el asociado defina su contraseña. No se creará otro usuario ni se cambiará la contraseña directamente.')
            ->modalSubmitActionLabel('Sí, reenviar')
            ->visible(fn (SolicitudAfiliacion $record): bool => $record->estado === EstadoSolicitudAfiliacion::Aprobada
                && $record->asociado_id !== null
                && $record->user_id !== null
                && auth()->user()?->can('update', $record) === true)
            ->action(function (SolicitudAfiliacion $record): void {
                try {
                    $correoSalio = app(ReenviarEnlaceAccesoAsociado::class)($record);
                } catch (\LogicException $excepcion) {
                    Notification::make()
                        ->title('No se pudo reenviar')
                        ->body($excepcion->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                if ($correoSalio) {
                    Notification::make()->title('Enlace de acceso reenviado')->success()->send();

                    return;
                }

                Notification::make()
                    ->title('No se pudo enviar el enlace')
                    ->body('El usuario y el asociado no cambiaron. El fallo del correo quedó registrado para revisión técnica.')
                    ->warning()
                    ->persistent()
                    ->send();
            });
    }
}

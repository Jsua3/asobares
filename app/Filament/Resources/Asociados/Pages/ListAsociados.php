<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Console\Commands\CrearUsuarioDelPanel;
use App\Filament\Resources\Asociados\AsociadoResource;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\User;
use App\Services\ImportacionDeLaBaseDelGremio;
use App\Services\ResultadoDeAltaDeCuentas;
use App\Services\ResultadoDeCargaDeAsociados;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ListAsociados extends ListRecords
{
    protected static string $resource = AsociadoResource::class;

    /** Líneas del resumen que caben en la notificación antes de contar el resto. */
    private const int LINEAS_DEL_RESUMEN = 40;

    /**
     * Marca el listado para el patrón visual operativo (`.asb-operativo`).
     * No cambia consultas, filtros, acciones ni permisos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    protected function getHeaderActions(): array
    {
        return [
            $this->accionImportarBase(),
            CreateAction::make(),
        ];
    }

    /**
     * Carga la base de establecimientos del gremio y, si se pide, crea las
     * cuentas de /mi-cuenta con una contraseña genérica que escribe quien
     * importa.
     *
     * Solo la ve quien puede crear asociados Y usuarios —hoy, la dirección—,
     * sin permiso nuevo: un permiso nuevo no llega a producción por desplegar
     * y la acción nacería en 403.
     *
     * ⚠️ Livewire guarda el archivo en una petición y la acción lo procesa en
     * otra. Funciona porque producción corre en UNA réplica con disco local;
     * si el entorno escala a más, hace falta el bucket (D-13).
     */
    private function accionImportarBase(): Action
    {
        return Action::make('importar')
            ->label('Importar base del gremio')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn (): bool => self::puedeImportar())
            ->modalHeading('Importar la base del gremio')
            ->modalDescription('Las fichas entran en borrador y nunca se publican solas. Una cuenta que ya existe no se toca.')
            ->modalSubmitActionLabel('Importar')
            ->schema([
                FileUpload::make('archivo')
                    ->label('Base de datos (.xlsx)')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->maxSize(4096)
                    ->storeFiles(false),
                Select::make('categoria')
                    ->label('Categoría para las filas que no traen una')
                    ->options(fn (): array => Categoria::query()->orderBy('nombre')->pluck('nombre', 'nombre')->all())
                    ->required(),
                Checkbox::make('crear_cuentas')
                    ->label('Crear cuentas de acceso a Mi Cuenta')
                    ->helperText('Todas con la contraseña genérica de abajo. Cada afiliado queda obligado a cambiarla.')
                    ->live(),
                TextInput::make('contrasena_generica')
                    ->label('Contraseña genérica')
                    ->password()
                    ->revealable()
                    ->visible(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->required(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->confirmed()
                    ->notIn([CrearUsuarioDelPanel::CLAVE_PUBLICADA])
                    ->rule(Password::min(12)->mixedCase()->numbers()->symbols())
                    // La regla Password falla con `password.symbols` y
                    // compañía, no con `symbols`: sin esas claves exactas, y
                    // sin `lang/`, el panel pintaría la clave cruda.
                    ->validationMessages([
                        'required' => 'Escribe la contraseña genérica.',
                        'confirmed' => 'La confirmación no coincide.',
                        'not_in' => 'Esa es la contraseña del demo, publicada en el repositorio. Elige otra.',
                        'min' => 'La contraseña necesita al menos 12 caracteres.',
                        'password.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                        'password.numbers' => 'La contraseña necesita al menos un número.',
                        'password.symbols' => 'La contraseña necesita al menos un símbolo.',
                    ]),
                TextInput::make('contrasena_generica_confirmation')
                    ->label('Confirma la contraseña genérica')
                    ->password()
                    ->visible(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->required(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->validationMessages(['required' => 'Confirma la contraseña genérica.'])
                    ->dehydrated(false),
            ])
            ->action(function (array $data): void {
                $archivo = $data['archivo'] ?? null;
                $archivo = is_array($archivo) ? reset($archivo) : $archivo;

                if (! $archivo instanceof TemporaryUploadedFile) {
                    Notification::make()->title('No se recibió ningún archivo')->danger()->send();

                    return;
                }

                try {
                    $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar(
                        (string) $archivo->getRealPath(),
                        (string) $data['categoria'],
                        ($data['crear_cuentas'] ?? false) ? (string) $data['contrasena_generica'] : null,
                    );
                } catch (Throwable $error) {
                    report($error);

                    Notification::make()
                        ->title('La importación no se aplicó')
                        ->body('No quedó ninguna ficha ni cuenta a medias. El detalle quedó en el registro.')
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                } finally {
                    // Datos personales de terceros: el archivo no se queda en
                    // el disco ni cuando la importación sale bien. Livewire
                    // guarda junto al temporal un `.json` con el nombre
                    // original (FileUploadConfiguration::storeTemporaryFile);
                    // TemporaryUploadedFile::delete() no lo toca, así que
                    // sobrevive si no se borra aparte.
                    $metadatos = $archivo->getRealPath().'.json';
                    $archivo->delete();

                    if (is_file($metadatos)) {
                        unlink($metadatos);
                    }
                }

                $this->notificarResultado($resultado['carga'], $resultado['cuentas']);
            });
    }

    private static function puedeImportar(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && $usuario->can('create', Asociado::class)
            && $usuario->can('create', User::class);
    }

    private function notificarResultado(ResultadoDeCargaDeAsociados $carga, ?ResultadoDeAltaDeCuentas $cuentas): void
    {
        $titulo = 'Fichas: '.$carga->resumen();

        if ($cuentas !== null) {
            $titulo .= ' Cuentas: '.$cuentas->resumen();
        }

        $lineas = [...$carga->errores(), ...($cuentas?->sinCuenta() ?? [])];

        $notificacion = Notification::make()->title($titulo)->persistent();

        if ($lineas === []) {
            $notificacion->success()->send();

            return;
        }

        $visibles = array_slice($lineas, 0, self::LINEAS_DEL_RESUMEN);
        $restantes = count($lineas) - count($visibles);

        if ($restantes > 0) {
            $visibles[] = "…y {$restantes} más.";
        }

        $notificacion->warning()->body(implode("\n", $visibles))->send();
    }
}

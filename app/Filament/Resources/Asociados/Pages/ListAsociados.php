<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Filament\Resources\Asociados\AsociadoResource;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\User;
use App\Services\DescargarAccesosProvisionales;
use App\Services\ImportacionDeLaBaseDelGremio;
use App\Services\ResultadoDeAltaDeCuentas;
use App\Services\ResultadoDeCargaDeAsociados;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            $this->accionDescargarAccesos(),
            CreateAction::make(),
        ];
    }

    /**
     * Carga la base de establecimientos del gremio y, si se pide, crea las
     * cuentas de /mi-cuenta con contraseñas aleatorias individuales.
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
                    // El tipo se lee del contenido, y un .xlsx es un zip: una
                    // copia guardada con otra herramienta puede leerse como
                    // `application/zip`. Se acepta; si no es una hoja válida,
                    // el importador lo reporta como error.
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                    ])
                    ->maxSize(4096)
                    ->storeFiles(false)
                    // Sin `lang/`, una regla sin mensaje propio pinta su clave
                    // cruda (`validation.mimetypes`) en el modal.
                    ->validationMessages([
                        'required' => 'Sube el archivo de la base del gremio.',
                        'mimetypes' => 'El archivo tiene que ser una hoja de Excel (.xlsx).',
                        'max' => 'El archivo pesa más de 4 MB.',
                    ]),
                Select::make('categoria')
                    ->label('Categoría para las filas que no traen una')
                    ->options(fn (): array => Categoria::query()->orderBy('nombre')->pluck('nombre', 'nombre')->all())
                    ->required()
                    ->validationMessages(['required' => 'Elige la categoría para las filas que no traen una.']),
                Checkbox::make('crear_cuentas')
                    ->label('Crear cuentas de acceso a Mi Cuenta')
                    ->helperText('Las contraseñas iniciales son individuales y desconocidas. Dirección podrá descargar accesos provisionales después.'),
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
                        (bool) ($data['crear_cuentas'] ?? false),
                    );
                } catch (Throwable $error) {
                    report(self::sinDatosDeLaHoja($error));

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

    private function accionDescargarAccesos(): Action
    {
        return Action::make('descargarAccesosProvisionales')
            ->label('Descargar accesos provisionales')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->visible(fn (): bool => auth()->user()?->esSuperAdmin() === true)
            ->requiresConfirmation()
            ->modalDescription('Cada descarga reemplaza las contraseñas provisionales anteriores. Entrega el archivo únicamente a las personas correspondientes.')
            ->action(fn (): StreamedResponse => app(DescargarAccesosProvisionales::class)->descargar(auth()->user()));
    }

    /**
     * Lo que se registra cuando la importación revienta: clase, código, archivo
     * y línea de la excepción, sin su mensaje. El de una `QueryException` lleva
     * los valores del SQL —correos, nombres, el hash de la genérica—, y en
     * producción el registro sale por stderr. Tampoco va como excepción
     * previa: el registro la imprimiría entera.
     */
    private static function sinDatosDeLaHoja(Throwable $error): RuntimeException
    {
        return new RuntimeException(sprintf(
            'La importación de la base del gremio no se aplicó: %s (código %s) en %s:%d. '
            .'El mensaje original no se registra porque puede traer datos personales de la hoja.',
            $error::class,
            $error->getCode(),
            $error->getFile(),
            $error->getLine(),
        ));
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
        $titulo = 'Fichas: '.self::resumenDeFichas($carga);

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

        // El panel pinta el cuerpo como HTML saneado, y ese saneado deja pasar
        // enlaces y estilos: cada línea lleva texto de la hoja, así que se
        // escapa. Y se separan con `<br>`, porque en HTML un salto de línea de
        // texto no separa nada.
        $notificacion->warning()->body(implode('<br>', array_map(e(...), $visibles)))->send();
    }

    /**
     * «61 creadas · 1 actualizada»: el aviso habla de fichas. El `resumen()`
     * del importador cuenta en masculino y lo imprime también el comando
     * `asociados:importar`, así que no se toca.
     */
    private static function resumenDeFichas(ResultadoDeCargaDeAsociados $carga): string
    {
        $tramos = [
            $carga->creados() === 1 ? '1 creada' : "{$carga->creados()} creadas",
            $carga->actualizados() === 1 ? '1 actualizada' : "{$carga->actualizados()} actualizadas",
        ];

        if ($carga->tieneErrores()) {
            $tramos[] = count($carga->errores()).' con problemas';
        }

        return implode(' · ', $tramos).'.';
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Todo el texto institucional del sitio, editable sin tocar código (RNF-09).
 */
class AjustesDelSitio extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.ajustes-del-sitio';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Ajustes del sitio';

    protected static ?string $navigationLabel = 'Ajustes del sitio';

    protected static ?string $slug = 'ajustes';

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** Títulos legibles para cada grupo de ajustes. */
    private const array GRUPOS = [
        'identidad' => ['Identidad', 'Nombre, eslogan y descripción para buscadores.'],
        'inicio' => ['Página de inicio', 'Textos del hero y del cierre de la portada.'],
        'manifiesto' => ['Manifiesto del gremio', 'El discurso del capítulo: apertura, visión a 10 años, barreras del sector y cierre.'],
        'cifras' => ['Cifras del Observatorio', 'La franja de datos que se muestra en el inicio.'],
        'institucional' => ['Quiénes somos', 'Historia, misión, dirección y programas del capítulo.'],
        'contacto' => ['Contacto', 'Datos de la oficina, redes y correo que recibe los formularios.'],
        'guia' => ['Guía normativa', 'Textos de la página «Abre tu negocio».'],
        'empleo' => ['Bolsa de empleo', 'Títulos y avisos del muro de vacantes.'],
        'modulos' => ['Artistas y proveedores', 'Textos de los dos directorios.'],
        'boletin' => ['Boletín', 'Encabezados de la sección de noticias.'],
        'afiliacion' => ['Afiliación', 'Textos de la página «Afíliate».'],
        'legal' => ['Legal', 'Datos del responsable del tratamiento de datos.'],
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_ajustes') === true;
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);

        $this->form->fill(Setting::query()->pluck('valor', 'clave')->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->secciones())
            ->statePath('data');
    }

    /** @return list<Section> */
    private function secciones(): array
    {
        $porGrupo = Setting::query()->orderBy('id')->get()->groupBy('grupo');
        $secciones = [];

        foreach (self::GRUPOS as $grupo => [$titulo, $descripcion]) {
            $ajustes = $porGrupo->get($grupo);

            if ($ajustes === null) {
                continue;
            }

            $secciones[] = Section::make($titulo)
                ->description($descripcion)
                ->collapsed($grupo !== 'identidad')
                ->columns(2)
                ->schema($ajustes->map(fn (Setting $ajuste) => $this->campo($ajuste))->all());
        }

        return $secciones;
    }

    private function campo(Setting $ajuste): TextInput|Textarea
    {
        $etiqueta = $ajuste->etiqueta ?? $ajuste->clave;

        if ($ajuste->tipo === 'text') {
            return Textarea::make($ajuste->clave)
                ->label($etiqueta)
                ->rows(4)
                ->columnSpanFull();
        }

        return TextInput::make($ajuste->clave)
            ->label($etiqueta)
            ->maxLength(500);
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can('editar_ajustes') === true, 403);

        foreach ($this->form->getState() as $clave => $valor) {
            Setting::where('clave', $clave)->update(['valor' => $valor]);
        }

        // La actualización masiva no dispara el evento `saved` del modelo,
        // así que la caché de ajustes se limpia a mano.
        Setting::olvidarCache();

        Notification::make()
            ->title('Ajustes guardados')
            ->body('Los cambios ya se ven en el sitio público.')
            ->success()
            ->send();
    }
}

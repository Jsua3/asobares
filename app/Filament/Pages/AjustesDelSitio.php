<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
        'identidad' => ['Identidad', 'Nombre, eslogan y descripción general.'],
        'inicio' => ['Página de inicio', 'Hero, título de cada sección de la portada y cierre.'],
        'manifiesto' => ['Manifiesto del gremio', 'El discurso del capítulo: apertura, visión a 10 años, barreras del sector y cierre.'],
        'cifras' => ['Cifras del Observatorio', 'La franja de datos que se muestra en el inicio.'],
        'gremio' => ['El gremio en cifras', 'Cuatro cifras del capítulo que la oficina actualiza cada quince días con el archivo de la contadora. Se pintan solo las que tengan número; si están todas vacías, la franja no aparece en la portada.'],
        'institucional' => ['Quiénes somos', 'Historia, misión, dirección y programas del capítulo.'],
        'contacto' => ['Contacto', 'Datos de la oficina, redes y correo que recibe los formularios.'],
        'directorio' => ['Directorio', 'Encabezados y textos públicos del directorio de establecimientos.'],
        'guia' => ['Guía normativa', 'Textos de la página «Abre tu negocio».'],
        'empleo' => ['Bolsa de empleo', 'Títulos y avisos del muro de vacantes.'],
        'artistas' => ['Artistas', 'Textos públicos de la bolsa de artistas.'],
        'proveedores' => ['Proveedores', 'Textos públicos de la bolsa de proveedores.'],
        'eventos' => ['Eventos', 'Encabezados y mensajes públicos de eventos.'],
        'boletin' => ['Boletín', 'Encabezados de la sección de noticias.'],
        'afiliacion' => ['Afiliación', 'Textos de la página «Afíliate».'],
        'mi_cuenta' => ['Mi cuenta', 'Mensajes institucionales del portal privado del asociado.'],
        'seo' => ['SEO', 'Títulos y descripciones para buscadores por página.'],
        'legal' => ['Legal', 'Datos del responsable del tratamiento de datos.'],
    ];

    /** Organización visible del panel para que el cliente edite por página. */
    private const array PESTANAS = [
        'general' => ['General', ['identidad']],
        'inicio' => ['Inicio', ['inicio', 'cifras', 'gremio']],
        'institucional' => ['Institucional', ['manifiesto', 'institucional']],
        'directorio' => ['Directorio', ['directorio']],
        'afiliacion' => ['Afiliación', ['afiliacion']],
        'empleo' => ['Empleo', ['empleo']],
        'artistas' => ['Artistas', ['artistas']],
        'proveedores' => ['Proveedores', ['proveedores']],
        'eventos' => ['Eventos', ['eventos']],
        'boletin' => ['Boletín', ['boletin']],
        'contacto' => ['Contacto', ['contacto']],
        'mi_cuenta' => ['Mi cuenta', ['mi_cuenta']],
        'seo' => ['SEO', ['seo']],
        'legal' => ['Legal', ['legal']],
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
            ->components([
                Tabs::make('Ajustes por sección')
                    ->persistTabInQueryString('seccion')
                    ->tabs($this->pestanas()),
            ])
            ->statePath('data');
    }

    /** @return list<Tab> */
    private function pestanas(): array
    {
        $porGrupo = Setting::query()->orderBy('id')->get()->groupBy('grupo');
        $pestanas = [];

        foreach (self::PESTANAS as [$titulo, $grupos]) {
            $secciones = [];

            foreach ($grupos as $grupo) {
                $ajustes = $porGrupo->get($grupo);

                if ($ajustes === null) {
                    continue;
                }

                [$seccion, $descripcion] = self::GRUPOS[$grupo];

                $secciones[] = Section::make($seccion)
                    ->description($descripcion)
                    ->columns(2)
                    ->schema($ajustes->map(fn (Setting $ajuste) => $this->campo($ajuste))->all());
            }

            if ($secciones !== []) {
                $pestanas[] = Tab::make($titulo)->schema($secciones);
            }
        }

        return $pestanas;
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

        $campo = TextInput::make($ajuste->clave)
            ->label($etiqueta)
            ->maxLength(500);

        // Todos los ajustes se trataban como texto libre, pero algunos acaban
        // dentro de un `href` del sitio público. `{{ }}` escapa las comillas
        // —no se puede salir del atributo— pero no filtra el esquema, así que
        // un `javascript:` guardado aquí se ejecutaba al pulsar el enlace en
        // cada página.
        //
        // La regla se aplica sólo cuando el campo trae algo: dejar un ajuste
        // en blanco es legítimo, y si el formato se exigiera también sobre el
        // vacío, un solo ajuste sin llenar bloquearía el guardado de toda la
        // página.
        $siTieneValor = static fn (?string $state): bool => filled($state);

        return match (true) {
            self::esEnlace($ajuste->clave) => $campo->rule('url', $siTieneValor),
            str_contains($ajuste->clave, 'correo') => $campo->rule('email', $siTieneValor),
            str_contains($ajuste->clave, '_lat'),
            str_contains($ajuste->clave, '_lng') => $campo->rule('numeric', $siTieneValor),
            default => $campo,
        };
    }

    /** Los ajustes cuyo valor termina siendo el destino de un enlace. */
    public static function esEnlace(string $clave): bool
    {
        return str_starts_with($clave, 'url_') || str_ends_with($clave, '_url');
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can('editar_ajustes') === true, 403);

        $estado = $this->form->getState();
        $ajustes = Setting::query()->whereIn('clave', array_keys($estado))->get()->keyBy('clave');

        // Solo se escribe lo que cambió. Antes se actualizaban todas las
        // claves en cada guardado, y una actualización masiva sella
        // `updated_at` aunque el valor sea el mismo: la fecha de «El gremio
        // en cifras» habría sido la del último guardado de cualquier cosa.
        foreach ($estado as $clave => $valor) {
            $ajuste = $ajustes->get($clave);

            if (! $ajuste instanceof Setting || (string) $ajuste->valor === (string) $valor) {
                continue;
            }

            $ajuste->update(['valor' => $valor]);
        }

        // Cada `update()` de modelo ya olvida la caché en `saved`; se limpia
        // igual por si nada cambió, para que el panel y el sitio no discrepen.
        Setting::olvidarCache();

        Notification::make()
            ->title('Ajustes guardados')
            ->body('Los cambios ya se ven en el sitio público.')
            ->success()
            ->send();
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Genera la maqueta con la que se mide la barra lateral del panel.
 *
 * Existe por una razón concreta: el panel exige segundo factor, así que
 * ninguna sesión automatizada lo abre, y sin poder verla se entregaron dos
 * regresiones visuales seguidas (bitácora §44.3). La maqueta reproduce el
 * marcado que Filament pinta —incluido el botón de plegado del grupo, sin el
 * cual no reprodujo el defecto del canto derecho del 8 sep— con la hoja
 * compilada de verdad, resuelta por `manifest.json`.
 *
 * No se versiona: vive en `public/_medicion/`, se regenera en cada
 * verificación y se borra al terminar. Lo que se versiona es este comando,
 * que es lo que la hace reproducible.
 */
class GenerarMaquetaDeLaBarra extends Command
{
    protected $signature = 'maqueta:barra
        {--oscuro : Pinta la maqueta en tema oscuro}
        {--cerrada : Sin `fi-sidebar-open`: por debajo de 64 rem, el riel de iconos}
        {--ruta=public/_medicion/barra.html : Dónde se escribe, relativo a la raíz del proyecto}';

    protected $description = 'Genera la maqueta de la barra lateral del panel con la hoja de estilos compilada';

    /**
     * Los mismos apartados y destinos que pinta el panel. Se escriben aquí y
     * no se leen de la navegación real porque la maqueta tiene que poder
     * generarse sin base de datos ni sesión.
     *
     * @var array<string, list<string>>
     */
    private const GRUPOS = [
        'Contenido' => ['Asociados', 'Eventos y capacitaciones', 'Fotos de afiliados', 'Boletín', 'Guía normativa', 'Iniciativas del gremio'],
        'Bolsas' => ['Bolsa de empleo', 'Aspirantes', 'Artistas', 'Proveedores'],
        'Bandejas' => ['Mensajes y PQR', 'Inscripciones', 'Postulaciones'],
        'Gremio' => ['Observatorio', 'Aliados y convenios', 'Beneficios del afiliado', 'Cartera', 'Transacciones'],
        'Configuración' => ['Municipios', 'Categorías', 'Usuarios', 'Ajustes del sitio'],
    ];

    public function handle(): int
    {
        /*
         * Lo que este comando escribe queda SERVIDO: vive dentro de `public/`.
         * En una máquina de trabajo es justo lo que se quiere; en producción es
         * publicar una página que nadie pidió, con el marcado del panel dentro.
         * La negativa va antes de tocar el disco.
         */
        if (app()->isProduction()) {
            $this->error('La maqueta se sirve desde public/: no se genera en producción.');

            return self::FAILURE;
        }

        $activos = $this->activosCompilados();

        if ($activos === null) {
            return self::FAILURE;
        }

        $ruta = base_path((string) $this->option('ruta'));

        File::ensureDirectoryExists(dirname($ruta));
        File::put($ruta, $this->pagina($activos, (bool) $this->option('oscuro')));

        $this->info("Maqueta escrita en {$ruta}");
        $this->line('Sírvela por HTTP: el `file://` no resuelve /build/assets.');

        return self::SUCCESS;
    }

    /**
     * Las rutas compiladas, resueltas por el manifiesto: si se escribieran a
     * mano, la maqueta medirá una hoja vieja sin avisar.
     *
     * @return array{css: string, puntos: string, barra: string, resorte: string}|null
     */
    private function activosCompilados(): ?array
    {
        $manifiesto = public_path('build/manifest.json');

        if (! File::exists($manifiesto)) {
            $this->error('No hay manifiesto de Vite: corre `npm run build` antes de medir.');

            return null;
        }

        $entradas = json_decode(File::get($manifiesto), true);

        $buscar = function (string $sufijo) use ($entradas): ?string {
            foreach ($entradas as $clave => $entrada) {
                if (str_ends_with($clave, $sufijo)) {
                    return $entrada['file'];
                }
            }

            return null;
        };

        $css = $buscar('filament/admin/theme.css');
        $puntos = $buscar('panel-barra-puntos.js');
        $barra = $buscar('panel-barra-lateral.js');
        $resorte = $buscar('panel-barra-resorte.js');

        if ($css === null || $puntos === null || $barra === null || $resorte === null) {
            $this->error('El manifiesto no trae el tema del panel o los módulos de la barra.');

            return null;
        }

        return ['css' => $css, 'puntos' => $puntos, 'barra' => $barra, 'resorte' => $resorte];
    }

    /**
     * @param  array{css: string, puntos: string, barra: string, resorte: string}  $activos
     */
    private function pagina(array $activos, bool $oscuro): string
    {
        $lista = '';
        $primero = true;

        foreach (self::GRUPOS as $rotulo => $destinos) {
            $filas = '';

            foreach ($destinos as $destino) {
                $activo = $primero ? ' fi-active' : '';
                $actual = $primero ? ' aria-current="page"' : '';
                $primero = false;

                $filas .= '<li class="fi-sidebar-item'.$activo.'">'
                    .'<a class="fi-sidebar-item-btn" href="#"'.$actual.'>'.$this->icono()
                    .'<span class="fi-sidebar-item-label">'.e($destino).'</span></a></li>';
            }

            // El botón de plegado no es adorno: sin él la maqueta no reprodujo
            // el defecto del canto derecho que Sua vio el 8 sep.
            $lista .= '<li class="fi-sidebar-group fi-collapsible"><div class="fi-sidebar-group-btn">'
                .'<span class="fi-sidebar-group-label">'.e($rotulo).'</span>'
                .'<button type="button" class="fi-icon-btn fi-sidebar-group-collapse-btn" aria-expanded="true">'.$this->chevron().'</button>'
                .'</div><ul class="fi-sidebar-group-items">'.$filas.'</ul></li>';
        }

        $clase = $oscuro ? 'dark' : '';
        $abierta = $this->option('cerrada') ? '' : ' fi-sidebar-open';
        $fondo = $oscuro ? 'var(--asb-admin-carbon)' : 'var(--asb-admin-fondo-claro)';

        return <<<HTML
        <!doctype html>
        <html lang="es" class="{$clase}">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Maqueta de la barra lateral</title>
        <style>
        /*
         * ANTES de la hoja compilada a propósito: lo de aquí solo rellena lo que
         * el panel real trae de otro sitio, y nunca puede pisar al tema. Yendo
         * después tapaba, por ejemplo, el `display: none` del chevron en el riel.
         */
        [x-cloak] { display: none !important; }
        body { margin: 0; min-height: 100vh; background: {$fondo}; }
        /* El topbar va FUERA de `.fi-layout` porque así lo pinta Filament:
           hermano anterior e hijo directo de `.fi-body`. Cruza el ancho entero
           por encima de la barra, y por eso el resplandor no puede ser de la
           barra. */
        .fi-topbar-ctn { position: sticky; inset-block-start: 0; z-index: 20; }
        .fi-topbar { display: flex; align-items: center; padding-inline: 1rem; }
        .fi-logo { font-weight: 700; letter-spacing: -.02em; color: var(--asb-admin-barra-tinta); }
        /*
         * Aquí NO se declara el posicionamiento de la barra ni el de la lista.
         * La hoja compilada trae el CSS de Filament, así que declararlo aquí lo
         * tapaba: la maqueta medía su propio invento y no el panel. Es lo que
         * escondió durante dos días que `position: relative` estaba pisando al
         * `fixed` de Filament (D-L29).
         */
        .fi-sidebar-group-items { list-style: none; margin: 0; padding: 0; }
        .fi-sidebar-group-btn { display: flex; align-items: center; gap: .5rem; }
        .fi-sidebar-group-label { flex: 1; }
        .fi-icon-btn { padding: 0; border: 0; background: none; color: inherit; cursor: pointer; }
        .fi-sidebar-item-btn { display: flex; align-items: center; gap: .75rem; text-decoration: none; padding-inline: .6rem; }
        .fi-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .hueco { padding: 2rem; font-family: system-ui; color: var(--asb-admin-barra-tinta); }
        </style>
        <link rel="stylesheet" href="/build/{$activos['css']}">
        </head>
        <body class="fi-body fi-body-has-topbar fi-body-has-navigation">
        <div class="fi-topbar-ctn"><div class="fi-topbar">
        <button type="button" class="fi-icon-btn fi-topbar-open-sidebar-btn" aria-label="Abrir el men&uacute;">{$this->hamburguesa()}</button>
        <div class="fi-topbar-start"><span class="fi-logo">asobares</span></div>
        <div class="fi-topbar-end"></div>
        <div class="asb-panel-tema"><button type="button" class="asb-panel-tema__disparador" aria-label="Tema">{$this->sol()}</button></div>
        <div class="asb-barra-cuenta asb-cuenta-en-el-cromo"><button type="button" class="asb-barra-chip"><span class="asb-barra-avatar">NG</span></button></div>
        </div></div>
        <div class="fi-layout">
        <aside class="fi-sidebar{$abierta}"><canvas class="asb-barra-puntos" aria-hidden="true"></canvas>
        <ul class="fi-sidebar-nav">{$lista}</ul>
        <div class="asb-barra-cuenta asb-cuenta-al-pie"><button type="button" class="asb-barra-chip"><span class="asb-barra-avatar">NG</span><span class="min-w-0 flex-1 text-left"><span class="block truncate text-sm font-medium">Natalia Guti&eacute;rrez</span><span class="block truncate text-2xs">Direcci&oacute;n del gremio</span></span>{$this->chevron()}</button></div>
        </aside>
        <div class="fi-main-ctn" style="display: flex; opacity: 1;"><main class="fi-main hueco"><h1>Maqueta</h1><p>Solo para medir la barra lateral.</p></main></div>
        </div>
        <script>
        /*
         * Un `\$store.sidebar` de mentira: los módulos de la barra no lo usan,
         * pero el marcado de Filament lo nombra y sin él una consola llena de
         * errores esconde los que sí importan.
         */
        window.Alpine = window.Alpine || { store: () => ({ isOpen: true, groupIsCollapsed: () => false, toggleCollapsedGroup: () => {} }) };
        </script>
        <script type="module" src="/build/{$activos['puntos']}"></script>
        <script type="module" src="/build/{$activos['barra']}"></script>
        <script type="module" src="/build/{$activos['resorte']}"></script>
        </body>
        </html>
        HTML;
    }

    private function icono(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="3"/></svg>';
    }

    private function hamburguesa(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>';
    }

    private function sol(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="4"/><path d="M12 3v2m0 14v2M3 12h2m14 0h2"/></svg>';
    }

    private function chevron(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 15 6-6 6 6"/></svg>';
    }
}

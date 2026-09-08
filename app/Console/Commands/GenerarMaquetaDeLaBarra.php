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
     * @return array{css: string, puntos: string, barra: string}|null
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

        if ($css === null || $puntos === null || $barra === null) {
            $this->error('El manifiesto no trae el tema del panel o los módulos de la barra.');

            return null;
        }

        return ['css' => $css, 'puntos' => $puntos, 'barra' => $barra];
    }

    /**
     * @param  array{css: string, puntos: string, barra: string}  $activos
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
        $fondo = $oscuro ? 'var(--asb-admin-carbon)' : 'var(--asb-admin-fondo-claro)';

        return <<<HTML
        <!doctype html>
        <html lang="es" class="{$clase}">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Maqueta de la barra lateral</title>
        <link rel="stylesheet" href="/build/{$activos['css']}">
        <style>
        [x-cloak] { display: none !important; }
        body { margin: 0; min-height: 100vh; background: {$fondo}; }
        .fi-layout { display: flex; min-height: 100vh; }
                /* `flex-shrink: 0` porque en el panel el cajón es fijo y no lo encoge
           nadie: sin esto, a 375 px la maqueta medía 246 de ancho, no 252. */
        .fi-sidebar { display: flex; flex-direction: column; flex-shrink: 0; width: var(--asb-admin-sidebar-ancho); height: 100vh; position: sticky; top: 0; }
        .fi-sidebar-nav { display: flex; flex-direction: column; flex-grow: 1; overflow: hidden auto; list-style: none; margin: 0; }
        .fi-sidebar-group-items { list-style: none; margin: 0; padding: 0; }
        .fi-sidebar-group-btn { display: flex; align-items: center; gap: .5rem; }
        .fi-sidebar-group-label { flex: 1; }
        .fi-icon-btn { display: grid; place-items: center; width: 2rem; height: 2rem; padding: 0; border: 0; background: none; color: inherit; cursor: pointer; }
        .fi-sidebar-item-btn { display: flex; align-items: center; gap: .75rem; text-decoration: none; padding-inline: .6rem; }
        .fi-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .hueco { flex: 1; padding: 2rem; font-family: system-ui; color: var(--asb-admin-barra-tinta); }
        </style>
        </head>
        <body>
        <div class="fi-layout">
        <aside class="fi-sidebar fi-sidebar-open"><canvas class="asb-barra-puntos" aria-hidden="true"></canvas>
        <ul class="fi-sidebar-nav">{$lista}</ul></aside>
        <main class="hueco"><h1>Maqueta</h1><p>Solo para medir la barra lateral.</p></main>
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
        </body>
        </html>
        HTML;
    }

    private function icono(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="3"/></svg>';
    }

    private function chevron(): string
    {
        return '<svg class="fi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 15 6-6 6 6"/></svg>';
    }
}

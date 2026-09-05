<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Los objetivos táctiles del sitio público, y por qué hace falta vigilarlos.
 *
 * De 155 sitios de declaración interactivos, 95 no llegaban a 44x44 px: la
 * navbar de escritorio en 37,7, el conmutador Tarjetas/Mapa en 33,7, el chip de
 * WhatsApp de la tarjeta en 33,2 y el paginador en 44 de alto pero 40 de ancho.
 * Ninguno fallaba a la vista, que es el motivo de que llevaran ahí desde el
 * primer día.
 *
 * ESTA CLASE NO MIDE GEOMETRÍA, Y HAY QUE LEERLA SABIÉNDOLO. Un objetivo táctil
 * se mide con el navegador —`document.elementFromPoint` sobre el cuadrado de
 * 44x44, que es lo único que ve el `::after` y ve el robo de clic al vecino— y
 * eso vive en `playwright-cli`, no en PHPUnit. Lo que hace esta clase es impedir
 * que las cadenas que la medición dio por buenas se recorten en una edición con
 * prisa, y que las tres técnicas se escriban a medias, que es como mueren en
 * silencio:
 *
 *   - Un `after:-inset-y-*` sin `relative` cuelga del ancestro posicionado más
 *     cercano, que puede ser la página entera.
 *   - Un `after:absolute` sin `after:content-['']` no genera ninguna caja: el
 *     pseudoelemento no existe y el área pulsable sigue siendo la de antes.
 *   - Un margen negativo sin el relleno que lo compensa no agranda nada: solo
 *     mueve el elemento.
 *   - Un envoltorio de 44x44 que sea un `<span>` en vez de un `<label for>` no
 *     es pulsable: dibuja 44 px de área muerta.
 *
 * Los cuatro dejan la suite en verde y el defecto vivo.
 */
class ObjetivoTactilTest extends TestCase
{
    /**
     * Los siete directorios de vistas que barre `MovimientoTest`, más el
     * paginador publicado, que renderizan ocho vistas públicas.
     *
     * @return list<string>
     */
    private function directoriosDeVistas(): array
    {
        return array_values(array_filter([
            resource_path('views/publico'),
            resource_path('views/components/publico'),
            resource_path('views/components/layouts'),
            resource_path('views/vendor'),
        ], File::isDirectory(...)));
    }

    /**
     * Toda lista de clases de un archivo: las de `class="…"` y las de dentro de
     * `@class([…])`, que es donde viven los chips y los conmutadores.
     *
     * @return list<string>
     */
    private function listasDeClases(string $contenido): array
    {
        $listas = [];

        preg_match_all('/class="([^"]*)"/', $contenido, $atributos);
        foreach ($atributos[1] as $lista) {
            $listas[] = $lista;
        }

        preg_match_all('/@class\(\[(.*?)\]\)/s', $contenido, $arreglos);
        foreach ($arreglos[1] as $arreglo) {
            preg_match_all("/'([^']*)'/", $arreglo, $cadenas);
            foreach ($cadenas[1] as $lista) {
                $listas[] = $lista;
            }
        }

        return $listas;
    }

    // --- A. Que las cadenas medidas no se recorten ---

    /**
     * Cada fila es una cadena que la medición con Chromium dio por buena, con
     * el área efectiva que salió. Si alguien la recorta, esto lo dice y dice
     * cuánto se pierde.
     *
     * Seis de estas cadenas perdieron su `transition-colors` —y las filas del
     * menú móvil su `hover:bg-superficie-alta`— al repartirse los portadores de
     * acuse: en `@layer utilities` esas utilidades pisan al portador de
     * `@layer components` y con ellas moría el `transition-duration: 0ms` del
     * `:active`. Lo que esta clase vigila es la GEOMETRÍA (`py-3`, `min-h-11`,
     * `px-4`), que sigue entera y sigue midiendo lo mismo. Quién puede convivir
     * con quién lo vigila `MovimientoTest`.
     *
     * @return array<string, array{string, string, string}>
     */
    public static function cadenasMedidas(): array
    {
        return [
            // Cromo. Las cuatro primeras son padding negativo óptico: el
            // relleno crea el área y el margen negativo la devuelve al flujo,
            // así que la barra mide exactamente lo mismo que antes (83,38 px).
            'navbar, logo' => [
                'components/publico/navbar.blade.php',
                '-my-1.5 flex shrink-0 items-center py-1.5',
                'el logo mide 32 px de alto en móvil; sin el py-1.5 el objetivo vuelve a 32x175',
            ],
            'navbar, enlaces de sección' => [
                'components/publico/navbar.blade.php',
                '-my-1 rounded-lg px-3 py-3 text-sm',
                'con py-2 la caja es 37,7 px; con py-3 son 45,7 y el -my-1 devuelve los 37,7 al flujo',
            ],
            'navbar, «Mi cuenta» de invitado' => [
                'components/publico/navbar.blade.php',
                '-my-1 rounded-lg px-3 py-3 text-sm text-tenue',
                'mismo caso que los enlaces de sección',
            ],
            'navbar, «Afíliate»' => [
                'components/publico/navbar.blade.php',
                "after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']",
                'es la única pastilla pintada de la barra: crece el área, no el dibujo (37,7 → 45,7)',
            ],
            'navbar, hamburguesa' => [
                'components/publico/navbar.blade.php',
                '-m-0.5 rounded-lg p-2.5',
                'p-2 daba 40x40; p-2.5 da 44,7x44,7 medidos y -m-0.5 devuelve los 40 al flujo',
            ],
            'navbar, filas del menú móvil' => [
                'components/publico/navbar.blade.php',
                'rounded-lg px-3 py-3 text-sm text-tinta',
                'py-2.5 dejaba la fila en 41,7 px',
            ],
            // Los dos grupos que reagruparon la barra. Nacieron cumpliendo:
            // misma geometría que los enlaces sueltos de al lado, para que el
            // disparador mida lo mismo y la barra no cambie de alto por él.
            'navbar, disparador de grupo' => [
                'components/publico/menu-grupo.blade.php',
                '-my-1 inline-flex items-center gap-1 rounded-lg px-3 py-3 text-sm',
                'medido 45,7 px de alto, igual que un enlace suelto; el -my-1 devuelve al flujo los 37,7 de siempre',
            ],
            'navbar, filas del desplegable de grupo' => [
                'components/publico/menu-grupo.blade.php',
                'fila-pulsable block rounded-lg px-3 py-3 text-sm',
                'medidas 45,7 x 206 px dentro del panel de 224',
            ],
            'menú de usuario, disparador' => [
                'components/publico/menu-usuario.blade.php',
                '-m-1 flex items-center gap-2 rounded-full p-1',
                'el avatar es marca y mide 36x36: el p-1 lo lleva a 44,7x44,7 sin tocarlo',
            ],
            'menú de usuario, filas' => [
                'components/publico/menu-usuario.blade.php',
                'rounded-lg px-3 py-3 text-sm text-suave',
                'py-2 dejaba la fila en 37,7 px',
            ],
            // Los controles nuevos de la barra de escritorio (3 sep 2026).
            'navbar, control de tema' => [
                'components/publico/control-tema.blade.php',
                'flex h-11 w-11 items-center justify-center rounded-full',
                'el icono mide 20 px: h-11 w-11 dan 44x44 medidos',
            ],
            'navbar, chip de idioma' => [
                'components/publico/control-idioma.blade.php',
                'flex h-11 min-w-11 items-center justify-center gap-1 rounded-full px-2',
                'medido 50x44: h-11 da 44 de alto exactos y min-w-11 deja que ES + galón nunca bajen de 44 de ancho',
            ],
            'navbar, filas de los popovers' => [
                'components/publico/control-tema.blade.php',
                'fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm',
                'misma geometría que las filas del menú de usuario: 45,7 px medidos',
            ],
            'navbar, filas del popover de idioma' => [
                'components/publico/control-idioma.blade.php',
                'fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm',
                'misma cadena que las filas del popover de tema, en su propio archivo: 45,7 px medidos',
            ],
            // El suelo de 44 px que la spec §6.2 pedía y que ningún paso del
            // plan construyó (revisión final del 5 sep). Los `-my-1` de los
            // hijos devolvían el módulo a 37,7 + 2 de borde.
            'navbar, módulo principal (objetivo del toque)' => [
                'components/publico/navbar.blade.php',
                'modulo modulo-principal hidden min-h-11 items-center gap-1 px-2 lg:flex',
                'con dedo el módulo entero es el control que abre la atención: min-h-11 da 44 px medidos (antes 39,7)',
            ],
            'navbar, indicador de tres puntos' => [
                'components/publico/navbar.blade.php',
                'indicador-mas -my-1 flex min-h-11 min-w-11 items-center justify-center rounded-lg px-2 py-3',
                'solo se ve con puntero grueso y es el toque más natural: 44x44 medidos en iPad Pro 11 landscape (antes 32x40)',
            ],
            // Formularios. `min-h-11` y no `py-3`: el control mide 43,7 px y
            // solo le faltan 0,3, así que el mínimo no mueve ni un campo.
            'campo, los tres controles' => [
                'components/publico/campo.blade.php',
                "\$clases = 'w-full min-h-11 rounded-xl border bg-fondo px-4 py-2.5 text-sm",
                'rige input, select y textarea de TODOS los formularios del sitio',
            ],
            'habeas data, envoltorio de la casilla' => [
                'components/publico/habeas-data.blade.php',
                '-mx-3.5 -mb-3.5 -mt-3 flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center',
                'la casilla nativa mide 16x16 y no se puede agrandar; el reparto asimétrico deja el cuadrado de 44x44 centrado en ella',
            ],
            'artistas, botón interno del campo de archivo' => [
                'publico/artistas/inscripcion.blade.php',
                'file:min-h-11',
                'el ::file-selector-button medía 37,7 px de alto; medido con getComputedStyle, ahora son 44 exactos',
            ],

            // Pie. Aquí NO vale el margen negativo: con paso de 30 px dos cajas
            // de 44 se solapan 14 y la de abajo le roba el clic a la de arriba.
            'pie, columnas de enlaces sin space-y' => [
                'components/publico/footer.blade.php',
                '<ul class="mt-1 text-sm">',
                'el space-y-2.5 dejaba un paso de 30 px: con objetivos de 44 se solapaban',
            ],
            'pie, enlaces' => [
                'components/publico/footer.blade.php',
                'flex min-h-11 items-center text-suave hover:text-acento',
                'sin min-h-11 el enlace vuelve a 21,7 px de alto',
            ],

            // Tarjeta de asociado: los dos objetivos de la fila de acciones.
            'tarjeta, «Ver ficha»' => [
                'components/publico/tarjeta-asociado.blade.php',
                "enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-['']",
                '`.enlace-accion` no puede llevar relleno (app.css lo dice por escrito): 21,7 → 46,6 medidos por el pseudoelemento',
            ],
            'tarjeta, chip de WhatsApp' => [
                'components/publico/tarjeta-asociado.blade.php',
                "after:absolute after:inset-x-0 after:-inset-y-2 after:content-['']",
                'tiene borde visible: a 44 px de alto se leería como una pastilla pesada. 33,2 → 47,8 de área',
            ],

            // Paginador: la única línea del lote que arregla ANCHURA.
            'paginador' => [
                'vendor/pagination/tailwind.blade.php',
                "\$pastilla = 'inline-flex min-h-11 min-w-11 items-center justify-center px-4 text-sm font-medium';",
                'cierra los cinco objetivos del paginador en las ocho vistas que paginan',
            ],

            // Chips y conmutadores.
            'chips de filtro' => [
                'publico/boletin/index.blade.php',
                'inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                'con py-2 el chip mide 39,7 px',
            ],
            // La cadena se mudó de `publico/eventos/index.blade.php` al
            // componente al pasar el conmutador de dos segmentos a tres
            // (Próximos / Pasados / Calendario): ahora lo pintan dos páginas y
            // la geometría medida tiene que vivir en un solo sitio.
            'conmutador Próximos/Pasados/Calendario' => [
                'components/publico/conmutador-eventos.blade.php',
                'pulsable inline-flex min-h-11 items-center rounded-lg px-5 text-sm',
                'con py-2 medía 37,7 px',
            ],

            // Calendario. La rejilla de siete columnas es `sm:` para arriba,
            // donde el puntero es un ratón; el objetivo táctil del mismo evento
            // vive en la agenda de móvil, que es lo que se mide aquí.
            'calendario, evento en la agenda de móvil' => [
                'publico/eventos/calendario.blade.php',
                'enlace-accion flex min-h-11 flex-col justify-center text-sm',
                'sin min-h-11 la fila de dos líneas queda en 39,6 px y es el único objetivo táctil del calendario',
            ],
            'calendario, vuelta a los próximos desde el mes vacío' => [
                'publico/eventos/calendario.blade.php',
                'enlace-accion relative mt-3 inline-flex min-h-11 items-center text-sm',
                '`.enlace-accion` no puede llevar relleno (app.css lo dice por escrito): sin min-h-11 vuelve a 21,7 px',
            ],
            'conmutador Tarjetas/Mapa' => [
                'publico/directorio/index.blade.php',
                'inline-flex min-h-11 items-center rounded-lg px-4 text-sm',
                'era el peor objetivo del lote: 33,7 px con py-1.5',
            ],
            'globo del mapa' => [
                'publico/directorio/index.blade.php',
                'style="display:inline-flex;min-height:44px;align-items:center"',
                'es HTML crudo dentro de un array PHP: Tailwind no compila ese marcado y el arreglo tiene que ir en línea',
            ],
            'miga de pan de la ficha' => [
                'publico/directorio/show.blade.php',
                'flex min-h-11 min-w-11 items-center justify-center hover:text-acento',
                'los eslabones median 33x19,2; crecen de verdad y no con margen negativo porque el <ol> es flex-wrap y al envolver dos cajas de 44 se solaparían',
            ],
            'columna de acciones de mis vacantes' => [
                'publico/mi-cuenta/vacantes/index.blade.php',
                'enlace-accion flex min-h-11 min-w-11 items-center justify-end',
                'la columna tiene gap-2: aquí el ::after robaría el clic al vecino, y «Editar» solo daba 40,4 px de ancho',
            ],
            'salto al contenido' => [
                'components/layouts/publico.blade.php',
                'focus:flex focus:min-h-11 focus:items-center',
                'solo se ve al tabular, pero cuando se ve es un objetivo real y medía 40 px',
            ],
        ];
    }

    #[DataProvider('cadenasMedidas')]
    public function test_las_cadenas_medidas_no_se_recortan(string $vista, string $fragmento, string $porque): void
    {
        $this->assertStringContainsString(
            $fragmento,
            File::get(resource_path('views/'.$vista)),
            "{$vista} perdió el objetivo táctil: {$porque}."
        );
    }

    // --- B. Que las técnicas no se escriban a medias ---

    /**
     * El pseudoelemento que agranda el área pulsable necesita las cuatro
     * piezas o no hace nada, y no hacer nada no da ningún error.
     */
    public function test_todo_pseudoelemento_de_area_pulsable_lleva_sus_cuatro_piezas(): void
    {
        $piezas = [
            'relative' => 'sin `relative` el pseudoelemento cuelga del ancestro posicionado más cercano, que puede ser la página entera',
            'after:absolute' => 'sin posicionarlo, el pseudoelemento se coloca en el flujo y no cubre nada',
            "after:content-['']" => 'sin `content` el pseudoelemento no genera caja: no existe',
            'after:inset-x-0' => 'sin fijar el eje horizontal la caja queda de ancho automático, o sea cero',
        ];

        $hallazgos = [];

        foreach ($this->directoriosDeVistas() as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                if ($archivo->getExtension() !== 'php') {
                    continue;
                }

                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                foreach ($this->listasDeClases($archivo->getContents()) as $lista) {
                    if (! str_contains($lista, 'after:-inset-y-')) {
                        continue;
                    }

                    foreach ($piezas as $pieza => $motivo) {
                        if (! str_contains($lista, $pieza)) {
                            $hallazgos[] = "{$ruta} → «{$lista}» no lleva `{$pieza}`: {$motivo}";
                        }
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Área pulsable a medias:\n".implode("\n", $hallazgos));
    }

    /**
     * El envoltorio de la casilla de habeas data tiene que ser una etiqueta.
     *
     * Con un `<span>` el marcado se ve idéntico, la caja mide 44x44 y cualquier
     * medición de geometría lo da por bueno — pero un `<span>` no dispara nada:
     * serían 44 px de área muerta alrededor de una casilla de 16.
     */
    public function test_el_envoltorio_de_la_casilla_dispara_la_casilla(): void
    {
        $vista = File::get(resource_path('views/components/publico/habeas-data.blade.php'));

        $this->assertMatchesRegularExpression(
            '/<label for="\{\{ \$id \}\}" class="[^"]*h-11 w-11[^"]*">\s*<input type="checkbox"/',
            $vista,
            'El envoltorio de 44x44 de la casilla tiene que ser un <label for> que la envuelva: un <span> dibuja área muerta.'
        );
    }

    /**
     * El paginador es el único sitio del sitio público donde falla la ANCHURA:
     * un número de una cifra da 8 px de glifo más 32 de relleno.
     */
    public function test_el_paginador_cierra_los_dos_ejes(): void
    {
        $paginador = File::get(resource_path('views/vendor/pagination/tailwind.blade.php'));

        foreach (['min-h-11' => 'alto', 'min-w-11' => 'ancho'] as $clase => $eje) {
            $this->assertStringContainsString(
                $clase,
                $paginador,
                "El paginador perdió el mínimo de {$eje} (`{$clase}`)."
            );
        }
    }

    /**
     * Leaflet apila sus paneles internos hasta z-index 800 y `.leaflet-container`
     * no crea contexto de apilamiento propio: esos 800 competían en la raíz
     * contra el `z-40` de la barra. Medido: con el mapa en pantalla, abrir el
     * menú móvil dejaba los botones de zoom POR ENCIMA de las filas del menú y
     * el clic se lo llevaba el mapa. Es el mismo defecto que esta etapa persigue
     * —un control responde por otro—, solo que entre dos capas en vez de entre
     * dos vecinos.
     */
    public function test_el_mapa_no_se_come_los_clics_del_menu(): void
    {
        $mapa = File::get(resource_path('views/components/publico/mapa.blade.php'));

        $this->assertStringContainsString(
            'relative z-0 overflow-hidden',
            $mapa,
            'El contenedor del mapa necesita su propio contexto de apilamiento o los paneles de Leaflet se pintan sobre la barra.'
        );

        $this->assertMatchesRegularExpression(
            '/\.leaflet-touch \.leaflet-bar a\s*\{[^}]*height:\s*44px/',
            $mapa,
            'Los dos botones de zoom vienen del CDN con 30x30: la regla que los sube a 44 va aquí, detrás del <link>.'
        );
    }
}

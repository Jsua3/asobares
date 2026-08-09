<!DOCTYPE html>
<html lang="es" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo ?? ajuste('sitio_nombre') }}</title>
    <meta name="description" content="{{ $descripcion ?? ajuste('sitio_descripcion') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogTipo ?? 'website' }}">
    <meta property="og:site_name" content="{{ ajuste('sitio_nombre') }}">
    <meta property="og:title" content="{{ $titulo ?? ajuste('sitio_nombre') }}">
    <meta property="og:description" content="{{ $descripcion ?? ajuste('sitio_descripcion') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="es_CO">
    @if (! empty($ogImagen))
        {{-- Open Graph exige URL absoluta; el disco las entrega relativas. --}}
        <meta property="og:image" content="{{ Str::startsWith($ogImagen, ['http://', 'https://']) ? $ogImagen : url($ogImagen) }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif

    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon.png') }}">
    {{-- El valor claro es el que corresponde al marcado servido: sin la clase
         `.dark` el CSS pinta el tema claro. El script de abajo lo corrige al
         instante según la preferencia real. --}}
    <meta name="theme-color" content="#F5F3F4">

    {{--
        Tema claro/oscuro. Va aquí, síncrono y antes de las hojas de estilo,
        porque la clase tiene que estar puesta antes del primer pintado: si se
        dejara para Alpine, quien tiene el sitio en claro vería un fogonazo
        negro en cada navegación.

        Comparte `localStorage.theme` con el panel Filament a propósito, así
        que la persona del gremio elige una sola vez y /admin y el sitio
        público quedan siempre iguales.
    --}}
    <script>
        (() => {
            const consultaSistema = window.matchMedia('(prefers-color-scheme: dark)');
            let primeraAplicacion = true;

            /*
             * `preferenciaForzada` la usa el selector de la navbar: si escribir
             * en localStorage falla —almacenamiento bloqueado— releer de ahí
             * devolvería el valor viejo y la página se quedaría con el tema
             * anterior mientras el botón ya se muestra activo.
             */
            const aplicarTema = (preferenciaForzada) => {
                let preferencia = preferenciaForzada ?? null;

                if (preferencia === null) {
                    // En navegación privada muy restringida leer localStorage lanza.
                    try {
                        preferencia = localStorage.getItem('theme');
                    } catch (e) {
                        preferencia = null;
                    }
                }

                // Cualquier cosa que no sea 'light' ni 'dark' —incluido un
                // valor corrupto de otra versión— se trata como 'system', que
                // es el comportamiento por defecto; antes caía en claro.
                const oscuro = preferencia === 'dark'
                    || (preferencia !== 'light' && consultaSistema.matches);

                const pintar = () => {
                    document.documentElement.classList.toggle('dark', oscuro);
                    document.querySelector('meta[name="theme-color"]')
                        ?.setAttribute('content', oscuro ? '#0B090A' : '#F5F3F4');
                };

                // La primera pasada corre dentro del <head>: no hay nada
                // pintado todavía que pueda transicionar.
                if (primeraAplicacion) {
                    primeraAplicacion = false;
                    pintar();

                    return oscuro;
                }

                /*
                 * Chromium no reinicia una transición cuando lo que cambia es
                 * la custom property que hay detrás del valor: la propiedad se
                 * queda congelada en el color del tema anterior. Como medio
                 * sitio lleva `transition-colors`, sin esta mordaza los
                 * enlaces de la navbar y los bordes de las tarjetas se
                 * quedaban con el tema viejo hasta recargar.
                 *
                 * De paso evita que el cambio de tema se vea como un barrido
                 * de 200 ms por toda la página.
                 */
                const mordaza = document.createElement('style');
                mordaza.textContent = '*,*::before,*::after{transition:none !important}';
                document.head.appendChild(mordaza);

                void document.body?.offsetHeight;
                pintar();
                void document.body?.offsetHeight;

                const quitarMordaza = () => mordaza.remove();

                /*
                 * Doble respaldo a propósito: en una pestaña en segundo plano
                 * —el caso real es cambiar el tema desde /admin y que este
                 * evento llegue por `storage`— el navegador no ejecuta
                 * requestAnimationFrame, y sin el temporizador la mordaza se
                 * quedaría puesta dejando la página sin transiciones.
                 */
                requestAnimationFrame(() => requestAnimationFrame(quitarMordaza));
                setTimeout(quitarMordaza, 250);

                return oscuro;
            };

            aplicarTema();

            // En modo «sistema», seguir al sistema operativo sin recargar.
            consultaSistema.addEventListener('change', aplicarTema);

            // Si cambian el tema en otra pestaña —típico: el panel abierto al
            // lado del sitio— esta se entera y se pone al día sola.
            window.addEventListener('storage', (evento) => {
                if (evento.key === 'theme') {
                    aplicarTema();
                }
            });

            // Al volver con Atrás la página se restaura del bfcache tal cual
            // quedó, con la clase del tema de entonces y sin disparar `storage`.
            window.addEventListener('pageshow', (evento) => {
                if (evento.persisted) {
                    aplicarTema();
                }
            });

            window.aplicarTema = aplicarTema;
        })();
    </script>

    @stack('jsonld')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{--
        `app.css` declara `--font-sans: 'Poppins', ...` pero nadie enlazaba el
        @font-face compilado por `bunny('Poppins', ...)` en vite.config.js:
        sin esto Poppins resuelve por fallback y nunca se renderiza de
        verdad. `Vite::fonts()` lee `fonts-manifest.json` y devuelve los
        preload y el <style> con los doce @font-face reales.
    --}}
    {{ Vite::fonts() }}
    @stack('cabeza')
</head>
<body class="min-h-screen bg-fondo text-tinta antialiased">
    <a href="#contenido"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-marca-500 focus:px-4 focus:py-2 focus:text-white">
        Saltar al contenido
    </a>

    <x-publico.navbar />

    <main id="contenido">
        {{ $slot }}
    </main>

    <x-publico.footer />

    @stack('scripts')
</body>
</html>

{{--
    Autónoma a propósito: sin el layout público, sin `ajuste()`, sin Vite y
    sin nada que consulte la base, porque la base puede ser justo lo que
    falló y un error dentro de la página de error deja al visitante con una
    pantalla en blanco. Por lo mismo el enlace usa `url('/')` y no `route()`:
    no depende de que la tabla de rutas haya llegado a cargar.

    Colores del manual de marca: Ambient White #F5F3F4, Pub Black #0B090A y
    Pub Red #EE4137. El botón lleva texto negro sobre rojo (5,4:1); el blanco
    sobre ese rojo no llega a 4,5:1. Lo fija PaginasDeErrorTest.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Error del servidor — ASOBARES Quindío</title>
    <style>
        :root {
            color-scheme: light dark;
            --fondo: #F5F3F4;
            --tinta: #0B090A;
            --marca: #EE4137;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --fondo: #0B090A;
                --tinta: #F5F3F4;
            }
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            background: var(--fondo);
            color: var(--tinta);
            font-family: Poppins, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            text-align: center;
        }

        main { max-width: 36rem; }

        .codigo {
            margin: 0;
            font-size: clamp(4.5rem, 18vw, 6rem);
            font-weight: 700;
            line-height: 1;
            color: var(--marca);
        }

        h1 {
            margin: 1.5rem 0 0;
            font-size: clamp(1.5rem, 5vw, 1.875rem);
            line-height: 1.25;
            text-wrap: balance;
        }

        .texto {
            margin: 1rem auto 0;
            max-width: 32rem;
            font-size: 0.9375rem;
            opacity: 0.8;
            text-wrap: pretty;
        }

        .accion {
            display: inline-block;
            margin-top: 2.25rem;
            padding: 0.875rem 1.75rem;
            border-radius: 999px;
            background: var(--marca);
            color: #0B090A;
            font-weight: 600;
            text-decoration: none;
        }

        .accion:hover { filter: brightness(1.08); }

        .accion:focus-visible {
            outline: 2px solid var(--tinta);
            outline-offset: 3px;
        }
    </style>
</head>
<body>
    <main>
        <p class="codigo">500</p>

        <h1>Algo falló de nuestro lado</h1>

        <p class="texto">
            No es un problema de tu conexión ni de lo que hiciste. Intenta de nuevo en unos minutos.
        </p>

        <a class="accion" href="{{ url('/') }}">Volver al inicio</a>
    </main>
</body>
</html>

@props([
    'puntos' => [],
    'lat' => 4.5339,
    'lng' => -75.6811,
    'zoom' => 12,
    'alto' => 'h-[28rem]',
])

{{--
    Leaflet + OpenStreetMap por CDN: gratis y sin API key. Todo el mapa vive
    en este componente para poder cambiar de proveedor sin tocar las páginas.
--}}
@once
    @push('cabeza')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        {{-- Los dos botones de zoom vienen de la hoja del CDN con 30x30 y son
             el único control del mapa que se pulsa con el dedo. La regla va
             AQUÍ y no en `app.css` por dos motivos: el orden —tiene que cargar
             después del <link>, y `app.css` va antes— y porque este archivo
             dice por escrito que todo el mapa vive en él. Misma especificidad
             que `.leaflet-touch .leaflet-bar a`, más tarde en el documento. --}}
        <style>
            .leaflet-bar a,
            .leaflet-touch .leaflet-bar a {
                width: 44px;
                height: 44px;
                line-height: 44px;
            }

            /*
             * Y el acuse al pulsarlos, que es lo mismo que hace `.pulsable` en
             * los 43 botones del sitio. Va aquí y no en `app.css` por lo mismo
             * que el tamaño: `app.css` declara sus portadores en
             * `@layer components`, y una regla EN CAPA pierde siempre contra la
             * hoja sin capa que Leaflet sirve desde el CDN, da igual la
             * especificidad.
             *
             * Solo `transform`, que es la única propiedad que Leaflet no
             * escribe en estos anclajes: así el acuse entra sin pelear con su
             * fondo ni con su borde. El token se anula a 1 con movimiento
             * reducido, igual que el de la tarjeta.
             */
            .leaflet-control-zoom a {
                transition: transform var(--duracion-instante) var(--ease-out);
            }

            .leaflet-control-zoom a:active {
                transform: scale(var(--asb-encogimiento-control));
                transition-duration: 0ms;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    @endpush
@endonce

{{-- `relative z-0` no es decoracion: Leaflet apila sus paneles internos hasta
     z-index 800 y `.leaflet-container` no crea contexto de apilamiento propio,
     asi que esos 800 competian en la raiz contra el `z-40` de la barra. Medido:
     con el mapa en pantalla, abrir el menu movil dejaba los botones de zoom del
     mapa POR ENCIMA de las filas del menu, y el clic se lo llevaba el mapa.
     Con un contexto propio a z-0, los 800 de Leaflet se quedan dentro. --}}
<div {{ $attributes->merge(['class' => "relative z-0 overflow-hidden rounded-2xl border border-linea {$alto}"]) }}
     x-data
     x-init="
        const dibujar = () => {
            // Leaflet anima teselas, zoom y marcadores con transiciones propias, que el
            // barrido de CSS no alcanza. Aquí es donde el control existe de verdad.
            const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const mapa = L.map($el, {
                scrollWheelZoom: false,
                fadeAnimation: ! movimientoReducido,
                zoomAnimation: ! movimientoReducido,
                markerZoomAnimation: ! movimientoReducido,
            }).setView([{{ $lat }}, {{ $lng }}], {{ $zoom }});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; colaboradores de OpenStreetMap',
                maxZoom: 19,
            }).addTo(mapa);

            const icono = L.divIcon({
                className: '',
                {{-- El aro va en negro fijo y NO sigue el tema: el pin no se
                     dibuja sobre la página sino sobre las teselas de OSM, que
                     son claras en los dos modos. Atarlo a --asb-fondo lo volvía
                     casi blanco sobre mapa claro y el pin se perdía. --}}
                html: `<span style=&quot;display:block;width:18px;height:18px;border-radius:9999px;background:#EE4137;border:3px solid #0B090A;box-shadow:0 0 0 2px rgba(238,65,55,.45)&quot;></span>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });

            const puntos = @js($puntos);
            const marcadores = [];

            puntos.forEach((punto) => {
                if (punto.lat === null || punto.lng === null) return;
                const marcador = L.marker([punto.lat, punto.lng], { icon: icono, title: punto.nombre }).addTo(mapa);
                if (punto.html) marcador.bindPopup(punto.html);
                marcadores.push(marcador);
            });

            if (marcadores.length > 1) {
                mapa.fitBounds(L.featureGroup(marcadores).getBounds().pad(0.2));
            }
        };

        window.L ? dibujar() : window.addEventListener('load', dibujar);
     "
     role="application"
     aria-label="Mapa de ubicaciones"></div>

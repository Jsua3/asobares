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
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    @endpush
@endonce

<div {{ $attributes->merge(['class' => "overflow-hidden rounded-2xl border border-white/[.09] {$alto}"]) }}
     x-data
     x-init="
        const dibujar = () => {
            const mapa = L.map($el, { scrollWheelZoom: false }).setView([{{ $lat }}, {{ $lng }}], {{ $zoom }});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; colaboradores de OpenStreetMap',
                maxZoom: 19,
            }).addTo(mapa);

            const icono = L.divIcon({
                className: '',
                html: `<span style=&quot;display:block;width:18px;height:18px;border-radius:9999px;background:#EE4036;border:3px solid #0C0A0B;box-shadow:0 0 0 2px rgba(238,64,54,.45)&quot;></span>`,
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

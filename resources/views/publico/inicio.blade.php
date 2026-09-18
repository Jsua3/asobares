@php
    /*
     * Quién es el gremio, para el resultado de Google cuando lo buscan por su
     * nombre: la organización y el sitio que publica. Todo sale de los ajustes
     * del panel; lo vacío se cae en vez de salir en blanco. El logo es el
     * favicon porque Google pide al menos 112 px de lado, y el logotipo
     * horizontal mide 108 de alto.
     */
    $instagram = ltrim(trim((string) ajuste('contacto_instagram')), '@');
    $organizacion = route('inicio').'#organizacion';

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'Organization',
                '@id' => $organizacion,
                'name' => ajuste('sitio_nombre'),
                'url' => route('inicio'),
                'logo' => asset('img/favicon.png'),
                'description' => ajuste('sitio_descripcion'),
                'email' => ajuste('contacto_correo'),
                'telephone' => ajuste('contacto_whatsapp_visible'),
                'sameAs' => $instagram !== '' ? ['https://instagram.com/'.$instagram] : null,
                'address' => filled(ajuste('contacto_direccion')) ? array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => ajuste('contacto_direccion'),
                    'addressLocality' => ajuste('contacto_ciudad'),
                    'addressCountry' => 'CO',
                ]) : null,
            ]),
            [
                '@type' => 'WebSite',
                'name' => ajuste('sitio_nombre'),
                'url' => route('inicio'),
                'inLanguage' => 'es-CO',
                'publisher' => ['@id' => $organizacion],
            ],
        ],
    ];
@endphp

@push('cabeza')
    @vite(['resources/css/home-editorial.css'])
@endpush

<x-layouts.publico :titulo="ajuste('sitio_nombre').' — '.ajuste('sitio_eslogan')"
                   :descripcion="ajuste('sitio_descripcion')">

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    <div class="home-editorial">
        <x-publico.home.hero :destacados="$destacados" :total-asociados="$totalAsociados" />

        <x-publico.home.cinta />

        <x-publico.home.cifras
            :cifras-del-gremio="$cifrasDelGremio"
            :cifras-del-gremio-actualizadas="$cifrasDelGremioActualizadas"
        />

        <x-publico.home.descubre :destacados="$destacados" />

        <x-publico.home.respalda :beneficios="$beneficios" :destacados="$destacados" />

        <x-publico.home.actualidad
            :proximos-eventos="$proximosEventos"
            :iniciativas="$iniciativas"
            :destacados="$destacados"
        />

        @if ($publicidadInicio)
            <x-publico.home.publicidad :publicidad="$publicidadInicio" />
        @endif

        @if ($aliadosInstitucionales->isNotEmpty() || $aliadosComerciales->isNotEmpty())
            <x-publico.home.aliados
                :aliados-institucionales="$aliadosInstitucionales"
                :aliados-comerciales="$aliadosComerciales"
            />
        @endif

        <x-publico.home.cta-afiliacion />
    </div>
</x-layouts.publico>

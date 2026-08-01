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
    <meta name="theme-color" content="#0B090A">

    @stack('jsonld')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('cabeza')
</head>
<body class="min-h-screen bg-noche-950 text-noche-50 antialiased">
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

@props([
    'numero',
    'kicker' => null,
])

{{--
    Folio editorial de El Gremio (01 manifiesto, 02 revista, 03 conversación).
    No es chip, badge ni botón: es membrete de publicación.
--}}
<p {{ $attributes->class(['gremio-editorial-folio']) }}>
    <span class="gremio-editorial-folio__marca">ASOBARES QUINDÍO</span>
    <span class="gremio-editorial-folio__serie">EL GREMIO / {{ $numero }}</span>
    @if (filled($kicker))
        <span class="gremio-editorial-folio__kicker">{{ $kicker }}</span>
    @endif
</p>

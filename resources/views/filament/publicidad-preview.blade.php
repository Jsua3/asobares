@php
    use Illuminate\Support\Facades\Storage;

    $nombre = $publicidad->nombre_comercial ?: $publicidad->anunciante;
    $imagen = $publicidad->imagen
        ? Storage::disk(config('almacenamiento.publico'))->url($publicidad->imagen)
        : null;
@endphp

<div class="space-y-4">
    @if ($imagen)
        <img
            src="{{ $imagen }}"
            alt="Publicidad de {{ $nombre }}"
            class="w-full rounded-lg border border-linea object-cover"
        >
    @endif

    <div>
        <p class="text-sm text-tenue">Publicidad en {{ $publicidad->ubicacion->getLabel() }}</p>
        <h3 class="text-lg font-semibold text-fuerte">{{ $nombre }}</h3>
        <p class="text-sm text-tinta">
            Vigente del {{ $publicidad->fecha_inicio->format('d/m/Y') }} al {{ $publicidad->fecha_fin->format('d/m/Y') }}.
        </p>
    </div>
</div>

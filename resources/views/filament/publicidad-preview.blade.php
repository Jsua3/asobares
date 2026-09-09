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
            class="w-full rounded-lg border border-gray-200 object-cover dark:border-white/10"
        >
    @endif

    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Publicidad en {{ $publicidad->ubicacion->getLabel() }}</p>
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $nombre }}</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Vigente del {{ $publicidad->fecha_inicio->format('d/m/Y') }} al {{ $publicidad->fecha_fin->format('d/m/Y') }}.
        </p>
    </div>
</div>

@push('cabeza')
    @vite(['resources/css/home-editorial.css'])
@endpush

<x-layouts.publico :titulo="ajuste('sitio_nombre').' — '.ajuste('sitio_eslogan')"
                   :descripcion="ajuste('sitio_descripcion')">

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

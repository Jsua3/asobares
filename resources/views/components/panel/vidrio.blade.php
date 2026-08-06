@props(['resplandor' => false, 'hover' => false])

{{--
    Contenedor de vidrio del panel. Las dos recetas (luz en oscuro, sombra en
    claro) viven en la clase `.vidrio` del tema, no aquí: así el componente no
    conoce colores y un cambio de paleta no lo toca.
--}}
<div {{ $attributes->class([
    'vidrio',
    'relative overflow-hidden',
    'resplandor-panel' => $resplandor,
    'vidrio-hover' => $hover,
]) }}>
    {{ $slot }}
</div>

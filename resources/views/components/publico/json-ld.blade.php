@props(['datos'])

{{--
    Datos estructurados dentro de un <script>.

    La codificación por defecto de json_encode escapa la barra, y eso es
    justamente lo que impide que un valor de la base cierre la etiqueta con
    «</script>». Añadir JSON_UNESCAPED_SLASHES desactivaba esa defensa y
    convertía cualquier campo editable —el nombre de un asociado, el título
    de una noticia— en XSS almacenado.

    JSON_HEX_TAG pone el segundo cerrojo: convierte < y > en escapes unicode.
    El resultado sigue siendo JSON-LD válido para los buscadores.
--}}
<script type="application/ld+json">{!! json_encode($datos, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) !!}</script>

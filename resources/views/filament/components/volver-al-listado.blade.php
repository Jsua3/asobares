{{--
    Botón para volver al listado desde crear, editar o ver un registro. Lo pone
    un solo gancho del panel (`AdminPanelProvider::volverAlListado`) en todos
    los recursos. Es un enlace al listado y no `history.back()`: la página
    anterior puede ser otra cosa, y el listado siempre es el mismo sitio.
--}}
<x-filament::button
    tag="a"
    :href="$url"
    color="gray"
    icon="heroicon-m-arrow-left"
    class="asb-volver-al-listado"
>
    {{ $etiqueta }}
</x-filament::button>

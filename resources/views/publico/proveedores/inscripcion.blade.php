<x-layouts.publico titulo="Inscríbete en la bolsa de proveedores — ASOBARES Quindío"
                   descripcion="Hielo, licores, alimentos, aseo, seguridad o mantenimiento: inscríbete en la bolsa de proveedores del gremio.">

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

        <a href="{{ route('proveedores.index') }}" class="text-sm text-acento hover:text-acento-fuerte">← Ver la bolsa</a>

        <h1 class="mt-4 font-display text-3xl font-bold tracking-tight">Inscríbete en la bolsa de proveedores</h1>
        <p class="mt-2 text-sm text-tenue">
            Los bares y gastrobares afiliados consultan esta bolsa cuando necesitan un proveedor.
            La secretaría revisa cada solicitud antes de publicarla.
        </p>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        <form method="POST" action="{{ route('proveedores.inscripcion.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-publico.campo nombre="nombre" etiqueta="Nombre de la empresa" requerido />
                <x-publico.campo nombre="categoria_proveedor" etiqueta="Qué le vendes al sector" tipo="select" requerido
                                 :opciones="collect($categorias)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
                <x-publico.campo nombre="municipio_id" etiqueta="Municipio" tipo="select" requerido
                                 :opciones="$municipios->pluck('nombre', 'id')->all()" />
                <x-publico.campo nombre="whatsapp" etiqueta="WhatsApp" tipo="tel" />
                <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email"
                                 ayuda="Te avisamos ahí cuando tu ficha esté publicada." />
            </div>

            <x-publico.campo nombre="descripcion" etiqueta="Qué ofreces" tipo="textarea" filas="4"
                             placeholder="Productos, cobertura de despacho, horarios y condiciones." />

            <x-publico.habeas-data />

            <x-publico.boton class="w-full sm:w-auto">
                Enviar mi solicitud
            </x-publico.boton>
        </form>
    </div>
</x-layouts.publico>

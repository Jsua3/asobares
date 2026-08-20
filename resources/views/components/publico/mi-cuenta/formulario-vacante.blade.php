@props(['vacante' => null, 'accion', 'metodo' => 'POST', 'categorias', 'tipos', 'textoBoton'])

<form method="POST" action="{{ $accion }}" class="mt-8 space-y-5">
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <x-publico.campo nombre="cargo" etiqueta="Cargo" requerido placeholder="Bartender"
                         :valor="$vacante?->cargo" />
        <x-publico.campo nombre="categoria_cargo" etiqueta="Área del establecimiento" tipo="select" requerido
                         :valor="$vacante?->categoria_cargo?->value"
                         :opciones="collect($categorias)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
        <x-publico.campo nombre="tipo" etiqueta="Tipo de empleo" tipo="select" requerido
                         :valor="$vacante?->tipo?->value"
                         :opciones="collect($tipos)->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])->all()" />
        <x-publico.campo nombre="fecha_limite" etiqueta="Fecha límite" tipo="date"
                         :valor="$vacante?->fecha_limite?->toDateString()"
                         ayuda="Obligatoria para empleos de una o dos noches. Al pasar, la vacante sale sola del muro." />
        <x-publico.campo nombre="franja_horaria" etiqueta="Franja horaria"
                         placeholder="Vie y sáb, 6:00 p. m. – 2:00 a. m."
                         :valor="$vacante?->franja_horaria" />
        <x-publico.campo nombre="whatsapp_contacto" etiqueta="WhatsApp de contacto" tipo="tel"
                         :valor="$vacante?->whatsapp_contacto"
                         ayuda="Opcional. Las postulaciones te llegan igual por correo." />
    </div>

    <x-publico.campo nombre="descripcion" etiqueta="Descripción" tipo="textarea" filas="5"
                     :valor="$vacante?->descripcion"
                     placeholder="Qué se necesita, qué experiencia esperas y cómo se paga." />

    <div class="flex flex-wrap items-center gap-4">
        <x-publico.boton>
            {{ $textoBoton }}
        </x-publico.boton>
        <a href="{{ route('mi-cuenta.vacantes.index') }}" class="enlace-accion flex min-h-11 items-center text-sm text-tenue hover:text-fuerte">Cancelar</a>
    </div>
</form>

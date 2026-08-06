<x-panel.vidrio resplandor class="p-5">
    <div class="flex items-center gap-2">
        <x-filament::icon icon="heroicon-o-inbox-arrow-down" class="h-5 w-5 text-acento" />
        <h2 class="font-display text-lg font-bold text-fuerte">Te está esperando</h2>
    </div>

    <p class="mt-1 text-sm text-tenue">
        Nadie aprueba lo que él mismo redactó: aquí solo aparece lo que te toca a ti.
    </p>

    <div class="mt-4">
        @foreach ($this->filas() as $fila)
            <x-panel.cola
                :etiqueta="$fila['etiqueta']"
                :url="$fila['url']"
                :antiguedad="$fila['antiguedad']"
                :urgente="$fila['urgente']"
            />
        @endforeach
    </div>
</x-panel.vidrio>

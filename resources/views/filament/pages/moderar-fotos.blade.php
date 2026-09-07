<x-filament-panels::page>
    <section class="vidrio mb-6 p-5">
        <p class="text-sm font-semibold text-gray-950 dark:text-white">
            Esta bandeja revisa fotos que suben los afiliados desde su portal.
        </p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Flujo: el afiliado entra a “Mi cuenta”, abre “Mis fotos” y envía una imagen.
            Aquí aparece para aprobarla o devolverla antes de que salga en el directorio público.
        </p>
    </section>

    {{ $this->table }}
</x-filament-panels::page>

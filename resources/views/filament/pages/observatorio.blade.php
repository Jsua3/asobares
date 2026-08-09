<x-filament-panels::page>
    @php
        $composicion = $this->metricas()->composicionDelSector();
        $proveedores = $this->metricas()->coberturaDeProveedores();
        $salud = $this->metricas()->saludFinanciera();
        $mora = $this->metricas()->tasaDeMoraActual();
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-panel.kpi
            etiqueta="Asociados publicados"
            :valor="$this->totalAsociados()"
            :n="$composicion->n"
            detalle="Presencia activa del gremio en el Quindío."
            icono="heroicon-o-building-storefront"
            :url="route('filament.admin.resources.asociados.index')"
        />

        <x-panel.kpi
            etiqueta="Proveedores vigentes"
            :valor="$this->totalProveedores()"
            :n="$proveedores->n"
            detalle="Cobertura del directorio de proveedores."
            icono="heroicon-o-truck"
            :url="route('filament.admin.resources.proveedores.index')"
        />

        <x-panel.kpi
            etiqueta="Recaudo (18 meses)"
            :valor="$this->recaudoDelPeriodo()"
            :n="$salud->n"
            detalle="Transacciones aprobadas del periodo."
            icono="heroicon-o-credit-card"
            :url="route('filament.admin.resources.transacciones.index')"
        />

        <x-panel.kpi
            etiqueta="Tasa de mora actual"
            :valor="$this->tasaDeMora()"
            :n="$mora->n"
            detalle="Carteras en mora hoy, no una tendencia."
            icono="heroicon-o-exclamation-triangle"
            :url="route('filament.admin.resources.cartera.index')"
        />
    </div>
</x-filament-panels::page>

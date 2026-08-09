@props(['serie', 'que'])

{{--
    Lo que se enseña cuando la muestra no da.

    Dibujar barras sobre tres observaciones sugiere una tendencia que no
    existe, y este modulo esta hecho para llevar cifras a una alcaldia. Decir
    «todavia no hay con que afirmar» es informacion; una grafica de adorno es
    lo contrario.
--}}
<div class="flex h-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-linea p-6 text-center">
    <x-filament::icon icon="heroicon-o-chart-bar" class="h-8 w-8 text-apagado" />

    <p class="text-sm font-medium text-tinta">Aún sin muestra suficiente</p>

    <p class="max-w-sm text-xs text-tenue">
        El observatorio ya mide {{ $que }}, pero hoy hay
        <span class="font-medium text-aviso">{{ $serie->rotuloDeMuestra() }}</span>
        y hacen falta al menos {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }}
        para afirmar algo. La gráfica se llenará sola cuando el sector alimente el dato.
    </p>
</div>

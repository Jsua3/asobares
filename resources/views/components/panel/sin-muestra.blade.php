@props(['serie', 'que'])

{{--
    Lo que se enseña cuando la muestra no da.

    Dibujar barras sobre tres observaciones sugiere una tendencia que no
    existe, y este modulo esta hecho para llevar cifras a una alcaldia. Decir
    «todavia no hay con que afirmar» es informacion; una grafica de adorno es
    lo contrario.

    Dos motivos distintos caen aqui, y no es el mismo mensaje: una serie sin
    ningun dato (`estaVacia()`, el dia 1 en produccion con la base recien
    migrada) no es lo mismo que una serie con datos que todavia no alcanzan
    el umbral. «Hoy hay n = 0» es tecnicamente cierto pero lee raro para
    quien abre el panel el primer dia; «todavia no hay datos que mostrar» es
    lo que de verdad esta pasando. El informe impreso
    (`informe-del-observatorio.blade.php`) ya hacia esta distincion desde
    hace rondas atras; esta vista es la primera pantalla que la usa tambien.
--}}
<div class="flex h-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-linea p-6 text-center">
    <x-filament::icon icon="heroicon-o-chart-bar" class="h-8 w-8 text-apagado" />

    @if ($serie->estaVacia())
        <p class="text-sm font-medium text-tinta">Todavía no hay datos que mostrar</p>

        <p class="max-w-sm text-xs text-tenue">
            El observatorio ya mide {{ $que }}, pero el sector todavía no ha
            alimentado ningún dato. La gráfica se llenará sola en cuanto
            exista el primer registro.
        </p>
    @else
        <p class="text-sm font-medium text-tinta">Aún sin muestra suficiente</p>

        <p class="max-w-sm text-xs text-tenue">
            El observatorio ya mide {{ $que }}, pero hoy hay
            <span class="font-medium text-aviso">{{ $serie->rotuloDeMuestra() }}</span>
            y hacen falta al menos {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }}
            para afirmar algo. La gráfica se llenará sola cuando el sector alimente el dato.
        </p>
    @endif
</div>

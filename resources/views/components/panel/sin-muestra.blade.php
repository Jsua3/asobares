@props(['serie', 'que'])

{{--
    Lo que se enseña cuando la muestra no da.

    Dibujar barras sobre tres observaciones sugiere una tendencia que no
    existe, y este módulo está hecho para llevar cifras a una alcaldía. Decir
    «todavía no hay con qué afirmar» es información; una gráfica de adorno es
    lo contrario.

    Dos motivos distintos caen aquí, y no es el mismo mensaje: una serie sin
    ningún dato (`estaVacia()`, el día 1 en producción con la base recién
    migrada) no es lo mismo que una serie con datos que todavía no alcanzan
    el umbral. «Hoy hay n = 0» es técnicamente cierto pero se lee raro para
    quien abre el panel el primer día; «todavía no hay datos que mostrar» es
    lo que de verdad está pasando. El informe impreso
    (`informe-del-observatorio.blade.php`) hace la misma distinción.
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

        {{-- Se nombra la muestra que DECIDE, no el `n` combinado. Medido con
             el combinado, presencia por municipio dice «hoy hay n = 762
             registros y hacen falta al menos 30»: 762 es mayor que 30 y
             quien lo lee tiene razón en desconfiar. Lo que no llega es una
             de las tres señales, y eso es lo que hay que decir. --}}
        <p class="max-w-sm text-xs text-tenue">
            El observatorio ya mide {{ $que }}, pero
            <span class="font-medium text-aviso">{{ $serie->rotuloDeLaMuestraQueDecide() }}</span>
            y hacen falta al menos {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }}
            para afirmar algo. La gráfica se llenará sola cuando el sector alimente el dato.
        </p>
    @endif
</div>

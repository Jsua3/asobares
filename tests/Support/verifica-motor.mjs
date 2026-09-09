/*
 * Verificación numérica del motor REAL (importa el archivo, no una copia).
 *
 * No entra en la suite: el proyecto no tiene runner de JS y añadir uno sería
 * cambiar dependencias. Esto se ejecuta a mano y sus cifras se reportan.
 */
import { pathToFileURL } from 'node:url';

// `matchMedia` no existe en Node y el módulo lo consulta al cargarse.
globalThis.window = { matchMedia: () => ({ matches: false }) };
globalThis.performance = globalThis.performance ?? { now: () => Date.now() };
globalThis.requestAnimationFrame = () => 0;
// `navigator` en Node 24 es de solo lectura y ya existe; el módulo solo lo toca
// dentro de `vibrar()`, que aquí no se ejerce.

const ruta = pathToFileURL(process.argv[2]).href;
const { crearResorte, proyectar, goma, crearRastro } = await import(ruta);

let fallos = 0;
const afirmar = (bien, que, detalle = '') => {
    console.log(`${bien ? '  ok  ' : ' FALLO'}  ${que}${detalle ? '   → ' + detalle : ''}`);
    if (! bien) fallos++;
};

/** Corre un resorte a 60 fps y devuelve la traza. */
const correr = (resorte, segundos = 3, dt = 1 / 60) => {
    const traza = [];
    for (let t = 0; t < segundos; t += dt) {
        resorte.paso(dt);
        traza.push(resorte.valor);
    }
    return traza;
};

console.log('\n═══ 1. Amortiguación crítica (1,0) no debe sobreimpulsar ═══');
{
    const r = crearResorte({ respuesta: 0.4, amortiguacion: 1, valor: 0 });
    r.meta = 100;
    const traza = correr(r);
    const maximo = Math.max(...traza);
    afirmar(maximo <= 100.05, 'no pasa de la meta', `máximo ${maximo.toFixed(4)}`);
    afirmar(Math.abs(traza[traza.length - 1] - 100) < 0.01, 'llega a la meta', `final ${traza[traza.length - 1].toFixed(4)}`);
}

console.log('\n═══ 2. Subamortiguado (0,8) SÍ sobreimpulsa, y se asienta ═══');
{
    const r = crearResorte({ respuesta: 0.4, amortiguacion: 0.8, valor: 0 });
    r.meta = 100;
    const traza = correr(r);
    const maximo = Math.max(...traza);
    afirmar(maximo > 100, 'sobreimpulsa', `máximo ${maximo.toFixed(2)} (${(maximo - 100).toFixed(2)} %)`);
    afirmar(maximo < 120, 'y no se dispara', `máximo ${maximo.toFixed(2)}`);
    afirmar(r.quieto, 'acaba quieto');
}

console.log('\n═══ 3. La respuesta gobierna lo que tarda ═══');
{
    for (const respuesta of [0.15, 0.3, 0.6]) {
        const r = crearResorte({ respuesta, amortiguacion: 1, valor: 0 });
        r.meta = 100;
        let fotogramas = 0;
        const dt = 1 / 60;
        while (! r.quieto && fotogramas < 600) { r.paso(dt); fotogramas++; }
        const seg = (fotogramas * dt).toFixed(3);
        afirmar(fotogramas < 600, `respuesta ${respuesta}s se asienta`, `${seg}s (${fotogramas} fotogramas)`);
    }
}

console.log('\n═══ 4. Interrupción a mitad de vuelo: continua, sin salto ═══');
{
    const r = crearResorte({ respuesta: 0.4, amortiguacion: 1, valor: 0 });
    r.meta = 100;
    for (let i = 0; i < 12; i++) r.paso(1 / 60);
    const antes = r.valor;
    r.meta = 0;                       // se cambia el destino en pleno vuelo
    const despues = r.paso(1 / 60);
    afirmar(Math.abs(despues - antes) < 3, 'la posición no da un salto al redirigir', `${antes.toFixed(2)} → ${despues.toFixed(2)}`);
    const traza = correr(r);
    afirmar(Math.abs(traza[traza.length - 1]) < 0.01, 'y acaba en el destino nuevo', `final ${traza[traza.length - 1].toFixed(4)}`);
}

console.log('\n═══ 5. Entrega de velocidad ═══');
{
    const lento = crearResorte({ respuesta: 0.4, amortiguacion: 0.8, valor: 0 });
    lento.meta = 100;
    const rapido = crearResorte({ respuesta: 0.4, amortiguacion: 0.8, valor: 0 });
    rapido.meta = 100;
    rapido.empujar(600);              // como si el dedo lo lanzara
    lento.paso(1 / 60); rapido.paso(1 / 60);
    afirmar(rapido.valor > lento.valor, 'el empujado avanza más en el primer fotograma', `${rapido.valor.toFixed(2)} vs ${lento.valor.toFixed(2)}`);
}

console.log('\n═══ 6. Un fotograma monstruoso no lo revienta ═══');
{
    const r = crearResorte({ respuesta: 0.15, amortiguacion: 0.7, valor: 0 });
    r.meta = 300;
    r.paso(4.0);                      // como volver de una pestaña al fondo
    afirmar(Number.isFinite(r.valor), 'la posición sigue siendo finita', `${r.valor.toFixed(3)}`);
    afirmar(Math.abs(r.valor) < 1000, 'y no ha salido de la pantalla', `${r.valor.toFixed(3)}`);
    const traza = correr(r);
    afirmar(Math.abs(traza[traza.length - 1] - 300) < 0.01, 'y converge igual', `final ${traza[traza.length - 1].toFixed(3)}`);
}

console.log('\n═══ 7. Proyección de momento (la función de Apple) ═══');
{
    afirmar(Math.abs(proyectar(0)) < 1e-9, 'sin velocidad no proyecta nada');
    const p = proyectar(1000);
    afirmar(Math.abs(p - 499) < 1, '1000 px/s proyecta ~499 px', `${p.toFixed(2)} px`);
    afirmar(proyectar(-1000) < 0, 'respeta el signo', `${proyectar(-1000).toFixed(2)} px`);
    afirmar(proyectar(2000) > 2 * proyectar(500), 'crece con la velocidad');
    // No es la del libro de física: v²/2a daría otra cosa muy distinta.
    afirmar(Math.abs(p - (1000 * 1000) / (2 * 1000)) > 1, 'no coincide con v²/2a, como debe ser');
}

console.log('\n═══ 8. Goma elástica ═══');
{
    afirmar(goma(0, 300) === 0, 'en el borde no resiste');
    const poco = goma(50, 300);
    const mucho = goma(200, 300);
    afirmar(poco < 50, 'sigue menos de lo que se tira', `50 px → ${poco.toFixed(1)} px`);
    afirmar(mucho < 200, 'y cada vez menos', `200 px → ${mucho.toFixed(1)} px`);
    afirmar(mucho / 200 < poco / 50, 'la resistencia CRECE con el exceso', `${(poco / 50).toFixed(3)} → ${(mucho / 200).toFixed(3)}`);
    afirmar(goma(-50, 300) === -poco, 'simétrica');
}

console.log('\n═══ 9. Movimiento reducido: salta, no interpola ═══');
{
    globalThis.window.matchMedia = () => ({ matches: true });
    const mod = await import(ruta + '?reducido');
    const r = mod.crearResorte({ respuesta: 0.4, amortiguacion: 0.8, valor: 0 });
    r.meta = 100;
    r.paso(1 / 60);
    afirmar(r.valor === 100, 'llega de una vez', `${r.valor}`);
    afirmar(r.quieto, 'y se declara quieto');
    globalThis.window.matchMedia = () => ({ matches: false });
}

console.log('\n═══ 10. Rastro de velocidad ═══');
{
    const rastro = crearRastro();
    afirmar(rastro.velocidad() === 0, 'sin muestras no inventa velocidad');
    rastro.anotar(0);
    afirmar(rastro.velocidad() === 0, 'con una muestra tampoco');
}

console.log(`\n${fallos === 0 ? '✓ TODO PASA' : `✗ ${fallos} FALLOS`}\n`);
process.exit(fallos === 0 ? 0 : 1);

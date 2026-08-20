/**
 * Motor de impresión compartido por los PDF del expediente.
 *
 * Imprime con el Chrome o el Edge que ya está instalado, conducido por el
 * protocolo DevTools sobre el WebSocket nativo de Node. Es más código que
 * `--print-to-pdf`, y hace falta: solo `Page.printToPDF` acepta plantilla de
 * pie, que es lo que pone el número de página en cada hoja.
 *
 * Sin dependencias de npm a propósito: `package.json` es del sitio, no de la
 * documentación.
 */

import { writeFileSync, existsSync, readdirSync, mkdtempSync } from 'node:fs';
import { spawn } from 'node:child_process';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import { tmpdir } from 'node:os';

const AQUI = dirname(fileURLToPath(import.meta.url));
export const INGENIERIA = resolve(AQUI, '..');
export const RAIZ = resolve(INGENIERIA, '..', '..');

/** Devuelve el woff2 de Poppins del peso pedido, como URL de fichero. */
export function poppins(peso) {
    const dir = join(RAIZ, 'public', 'build', 'assets');
    const encontrado = readdirSync(dir).find(
        (f) => f.startsWith(`poppins-${peso}-normal-`) && f.endsWith('.woff2')
    );
    if (!encontrado) {
        throw new Error(
            `No está el Poppins de peso ${peso} en public/build/assets. Ejecuta antes: npm run build`
        );
    }
    return pathToFileURL(join(dir, encontrado)).href;
}

export function logoUrl() {
    return pathToFileURL(join(RAIZ, 'public', 'img', 'logo-asobares.png')).href;
}

/**
 * Hoja de estilo común del expediente. Sale del Manual de Marca de Asobares
 * Colombia (Quida Studio) y del `tokens.css` del sitio, para que el papel y la
 * plataforma se lean con la misma voz.
 *
 * En papel el rojo es la variante 700 y no el Pub Red puro: sobre blanco el
 * #EE4137 no alcanza AA en texto. El puro se reserva para filetes y remates,
 * que no son texto.
 */
export function estiloBase() {
    return `
@font-face { font-family: Poppins; font-weight: 400; font-style: normal; font-display: block; src: url("${poppins(400)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 500; font-style: normal; font-display: block; src: url("${poppins(500)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 600; font-style: normal; font-display: block; src: url("${poppins(600)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 700; font-style: normal; font-display: block; src: url("${poppins(700)}") format("woff2"); }

:root {
  --rojo: #b71f18;
  --rojo-puro: #ee4137;
  --negro: #0b090a;
  --tinta: #1b191a;
  --suave: #3d393b;
  --apagado: #575254;
  --linea: rgb(11 9 10 / 0.13);
  --papel-alt: #f5f3f4;
}

* { box-sizing: border-box; }

body {
  font-family: Poppins, system-ui, sans-serif;
  font-size: 10pt;
  line-height: 1.55;
  letter-spacing: 0;
  color: var(--tinta);
  margin: 0;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

/* El tracking y la interlínea van por tamaño, nunca uno solo para toda la
   escala: los titulares piden letra apretada e interlínea corta; el cuerpo,
   lo contrario. */
h1 {
  font-size: 20pt;
  font-weight: 700;
  line-height: 1.12;
  letter-spacing: -0.022em;
  color: var(--negro);
  margin: 0 0 1.5mm;
}
h2 {
  font-size: 11pt;
  font-weight: 600;
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--negro);
  margin: 6mm 0 2mm;
  page-break-after: avoid;
}
h3 {
  font-size: 9.5pt;
  font-weight: 600;
  line-height: 1.35;
  letter-spacing: -0.004em;
  margin: 4mm 0 1.5mm;
  page-break-after: avoid;
}
p { margin: 0 0 2.6mm; }
strong { font-weight: 600; color: var(--negro); }
small { font-size: 8.4pt; letter-spacing: 0.012em; color: var(--apagado); }
code {
  font-family: "Cascadia Mono", Consolas, ui-monospace, monospace;
  font-size: 8.6pt;
  background: var(--papel-alt);
  border: 1px solid var(--linea);
  border-radius: 3px;
  padding: 0.3mm 1.1mm;
}

/* --- Membrete --- */
.membrete {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10mm;
  border-bottom: 2px solid var(--rojo-puro);
  padding-bottom: 3.5mm;
  margin-bottom: 6mm;
}
.membrete img { width: 44mm; }
.membrete .ref {
  text-align: right;
  font-size: 7.6pt;
  line-height: 1.5;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--apagado);
}
.titulo-sub {
  font-size: 10.5pt;
  font-weight: 500;
  color: var(--rojo);
  letter-spacing: -0.008em;
  margin: 0 0 5mm;
}

/* --- Ficha de datos --- */
dl.ficha {
  display: grid;
  /* Las columnas de etiqueta son max-content: una etiqueta larga ensancha su
     columna en vez de partirse en dos renglones, que es lo que descuadraba la
     ficha entera. Los dos 1fr se reparten lo que sobra. */
  grid-template-columns: max-content 1fr max-content 1fr;
  gap: 1.9mm 4mm;
  margin: 0 0 5mm;
  padding: 3.5mm 4mm;
  background: var(--papel-alt);
  border-radius: 4px;
  font-size: 9.2pt;
}
dl.ficha dt {
  font-weight: 500;
  font-size: 7.4pt;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--apagado);
  align-self: center;
  white-space: nowrap;
}
dl.ficha dd { margin: 0; }

/* Fila de casillas en línea: cada opción es indivisible, y la fila envuelve
   por opciones enteras en vez de dejar una casilla huérfana de su texto. */
.opciones-linea {
  display: flex;
  flex-wrap: wrap;
  gap: 2mm 7mm;
  align-items: baseline;
  margin: 0 0 4mm;
  font-size: 9.4pt;
}
.opciones-linea > span { white-space: nowrap; }
.opciones-linea .titulo { font-weight: 600; color: var(--negro); }

/* --- Tablas --- */
table { width: 100%; border-collapse: collapse; margin: 0 0 4mm; font-size: 9pt; line-height: 1.4; }
th {
  text-align: left;
  font-weight: 600;
  font-size: 7.8pt;
  letter-spacing: 0.055em;
  text-transform: uppercase;
  color: var(--apagado);
  border-bottom: 1.5px solid var(--negro);
  padding: 1.8mm 2.2mm;
  vertical-align: bottom;
}
td { padding: 2mm 2.2mm; border-bottom: 1px solid var(--linea); vertical-align: top; }
tbody tr:nth-child(even) { background: rgb(245 243 244 / 0.55); }
/* Fila para rellenar a mano: alta, para que quepa la letra de una persona. */
tr.vacia td { height: 9mm; }

ul, ol { margin: 0 0 2.6mm; padding-left: 5.5mm; }
li { margin-bottom: 1.1mm; }

/* --- Casillas y renglones para rellenar --- */
.casilla {
  display: inline-block;
  width: 3.6mm;
  height: 3.6mm;
  border: 1.2px solid var(--negro);
  border-radius: 1px;
  margin-right: 1.6mm;
  vertical-align: -0.4mm;
}
.opciones { display: flex; gap: 9mm; margin: 1mm 0 3mm; font-size: 9.4pt; }
.renglones { margin: 0 0 4mm; }
.renglones div { border-bottom: 1px solid var(--linea); height: 7mm; }

/* --- Aviso --- */
.nota {
  margin: 0 0 4mm;
  padding: 2.8mm 3.5mm;
  background: rgb(238 65 55 / 0.055);
  border-left: 3px solid var(--rojo-puro);
  border-radius: 0 4px 4px 0;
  font-size: 8.8pt;
  line-height: 1.5;
}

/* --- Firmas --- */
.firmas {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14mm;
  margin-top: 14mm;
  page-break-inside: avoid;
}
/* Tres firmantes: la dirección y los dos practicantes. El equipo de práctica
   son dos personas y ambas responden por el proyecto, así que ninguna acta
   puede salir con una sola línea del lado de la universidad. */
.firmas.tres {
  grid-template-columns: repeat(3, 1fr);
  gap: 7mm;
}
.firmas.tres .firma { padding-top: 14mm; }
.firmas.tres .firma .nombre { font-size: 9pt; }
.firmas.tres .firma .cargo { font-size: 7.6pt; }
.firma { padding-top: 16mm; border-top: 1px solid var(--negro); }
.firma .nombre { font-weight: 600; font-size: 9.6pt; color: var(--negro); }
.firma .cargo { font-size: 8.2pt; line-height: 1.45; color: var(--apagado); letter-spacing: 0.008em; }

.pie-legal {
  margin-top: 9mm;
  padding-top: 3mm;
  border-top: 1px solid var(--linea);
  font-size: 7.6pt;
  line-height: 1.5;
  letter-spacing: 0.012em;
  color: var(--apagado);
}
.salto { page-break-before: always; }
`;
}

const NAVEGADORES = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    'C:/Program Files/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
];

function buscarNavegador() {
    const encontrado = NAVEGADORES.find((r) => existsSync(r));
    if (!encontrado) {
        throw new Error('No se encontró Chrome ni Edge para imprimir el PDF.');
    }
    return encontrado;
}

/** Espera a que el puerto de depuración conteste, sin dormir a ciegas. */
async function esperarDepurador(puerto, intentos = 120) {
    for (let n = 0; n < intentos; n++) {
        try {
            const r = await fetch(`http://127.0.0.1:${puerto}/json/version`);
            if (r.ok) {
                return;
            }
        } catch {
            /* todavía no levanta */
        }
        await new Promise((r) => setTimeout(r, 150));
    }
    throw new Error('El navegador no abrió el puerto de depuración.');
}

/** Cliente mínimo del protocolo DevTools sobre el WebSocket nativo de Node. */
function conectar(url) {
    const ws = new WebSocket(url);
    let siguiente = 1;
    const pendientes = new Map();
    const oyentes = new Map();

    ws.addEventListener('message', (ev) => {
        const msg = JSON.parse(ev.data);
        if (msg.id && pendientes.has(msg.id)) {
            const { resolver, rechazar } = pendientes.get(msg.id);
            pendientes.delete(msg.id);
            msg.error ? rechazar(new Error(msg.error.message)) : resolver(msg.result);
        } else if (msg.method && oyentes.has(msg.method)) {
            oyentes.get(msg.method).forEach((f) => f(msg.params));
        }
    });

    return {
        lista: new Promise((res, rej) => {
            ws.addEventListener('open', res, { once: true });
            ws.addEventListener('error', rej, { once: true });
        }),
        enviar(method, params = {}) {
            const id = siguiente++;
            return new Promise((resolver, rechazar) => {
                pendientes.set(id, { resolver, rechazar });
                ws.send(JSON.stringify({ id, method, params }));
            });
        },
        al(method, fn) {
            if (!oyentes.has(method)) {
                oyentes.set(method, []);
            }
            oyentes.get(method).push(fn);
        },
        cerrar: () => ws.close(),
    };
}

/**
 * Imprime uno o varios documentos con un solo arranque del navegador.
 *
 * @param {{html: string, salida: string, pie?: string}[]} trabajos
 */
export async function imprimir(trabajos, { puerto = 9333 } = {}) {
    const perfil = mkdtempSync(join(tmpdir(), 'asobares-pdf-'));
    const proceso = spawn(
        buscarNavegador(),
        [
            '--headless=new',
            `--remote-debugging-port=${puerto}`,
            `--user-data-dir=${perfil}`,
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-gpu',
            '--allow-file-access-from-files',
            ...(process.env.ASOBARES_PDF_SIN_CAJA ? ['--no-sandbox', '--disable-dev-shm-usage'] : []),
            'about:blank',
        ],
        { stdio: 'ignore' }
    );

    const escritos = [];

    try {
        await esperarDepurador(puerto);

        for (const trabajo of trabajos) {
            const temporal = mkdtempSync(join(tmpdir(), 'asobares-doc-'));
            const rutaHtml = join(temporal, 'documento.html');
            writeFileSync(rutaHtml, trabajo.html, 'utf8');

            const objetivo = await fetch(`http://127.0.0.1:${puerto}/json/new?about:blank`, {
                method: 'PUT',
            }).then((r) => r.json());

            const cdp = conectar(objetivo.webSocketDebuggerUrl);
            await cdp.lista;
            await cdp.enviar('Page.enable');

            const cargada = new Promise((res) => cdp.al('Page.loadEventFired', res));
            await cdp.enviar('Page.navigate', { url: pathToFileURL(rutaHtml).href });
            await cargada;

            // Las fuentes y las imágenes tienen que estar decodificadas antes de
            // imprimir, o salen huecos en blanco. `load` por sí solo no basta.
            await cdp.enviar('Runtime.evaluate', {
                expression: `Promise.all([
                    document.fonts.ready,
                    ...[...document.images].map(i => i.decode().catch(() => null)),
                ])`,
                awaitPromise: true,
            });

            const { data } = await cdp.enviar('Page.printToPDF', {
                printBackground: true,
                preferCSSPageSize: true,
                displayHeaderFooter: Boolean(trabajo.pie),
                headerTemplate: '<div></div>',
                footerTemplate: trabajo.pie ?? '<div></div>',
                marginTop: 0.71,
                marginBottom: 0.79,
                marginLeft: 0.63,
                marginRight: 0.63,
            });

            writeFileSync(trabajo.salida, Buffer.from(data, 'base64'));
            escritos.push(trabajo.salida);
            cdp.cerrar();
        }
    } finally {
        proceso.kill();
    }

    return escritos;
}

/** Plantilla de pie con número de página. */
export function pieConPaginacion(izquierda) {
    return `<div style="font-family:Poppins,sans-serif;font-size:7pt;color:#575254;
        width:100%;padding:0 16mm;display:flex;justify-content:space-between;
        letter-spacing:0.02em;">
        <span>${izquierda}</span>
        <span>P&aacute;gina <span class="pageNumber"></span> de <span class="totalPages"></span></span>
    </div>`;
}

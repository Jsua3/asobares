/**
 * Exporta `docs/ingenieria/manual-de-usuario.md` a PDF con la identidad de marca
 * del gremio.
 *
 *     node docs/ingenieria/herramientas/manual-a-pdf.mjs
 *
 * Sin dependencias de npm a propósito: `package.json` es del sitio, no de la
 * documentación, y CLAUDE.md prohíbe tocarlo. El conversor de Markdown de abajo
 * cubre exactamente el subconjunto que usa el manual —encabezados, tablas,
 * citas, listas anidadas, imágenes con pie— y nada más; si el manual empieza a
 * usar otra sintaxis, se amplía aquí.
 *
 * El PDF lo imprime el Chrome/Edge que ya está instalado, conducido por el
 * protocolo DevTools sobre el WebSocket nativo de Node. Es más código que
 * `--print-to-pdf`, y hace falta: solo `Page.printToPDF` acepta plantilla de
 * pie, que es lo que pone el número de página y la fecha en cada hoja.
 */

import { readFileSync, writeFileSync, existsSync, readdirSync, mkdirSync } from 'node:fs';
import { spawn } from 'node:child_process';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import { mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';

const AQUI = dirname(fileURLToPath(import.meta.url));
const INGENIERIA = resolve(AQUI, '..');
const RAIZ = resolve(INGENIERIA, '..', '..');

const ENTRADA = join(INGENIERIA, 'manual-de-usuario.md');
const SALIDA = join(INGENIERIA, 'Manual de usuario - Panel ASOBARES Quindio.pdf');

/* ---------------------------------------------------------------------------
 * 1. Markdown -> HTML
 * ------------------------------------------------------------------------- */

/** Escapa lo que el navegador leería como marcado. */
function escapar(texto) {
    return texto
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

/**
 * Marcado de una sola línea. El orden importa: el código va primero para que
 * un asterisco dentro de `backticks` no se lea como énfasis.
 */
function enLinea(texto) {
    const codigos = [];
    let t = escapar(texto).replace(/`([^`]+)`/g, (_, c) => {
        codigos.push(c);
        return `\u0000${codigos.length - 1}\u0000`;
    });

    t = t
        .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (_, alt, src) => `<img src="${src}" alt="${alt}">`)
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[^*])\*([^*]+)\*/g, '$1<em>$2</em>');

    return t.replace(/\u0000(\d+)\u0000/g, (_, i) => `<code>${codigos[i]}</code>`);
}

/** Convierte una fila `| a | b |` en celdas. */
function celdas(linea) {
    return linea
        .trim()
        .replace(/^\|/, '')
        .replace(/\|$/, '')
        .split('|')
        .map((c) => c.trim());
}

function convertir(markdown) {
    const lineas = markdown.split(/\r?\n/);
    const salida = [];
    let i = 0;

    // Se saltan el título y el subtítulo: viven en la portada, no en el cuerpo.
    while (i < lineas.length && !/^\*\*Versión:/.test(lineas[i])) {
        i++;
    }
    while (i < lineas.length && !/^---\s*$/.test(lineas[i])) {
        i++;
    }
    i++;

    while (i < lineas.length) {
        const linea = lineas[i];

        if (!linea.trim()) {
            i++;
            continue;
        }

        // Separador: se usa como salto de página, que es lo que separa los
        // capítulos del manual.
        if (/^---+\s*$/.test(linea.trim())) {
            salida.push('<div class="salto"></div>');
            i++;
            continue;
        }

        // Encabezados
        const enc = linea.match(/^(#{2,4})\s+(.*)$/);
        if (enc) {
            const nivel = enc[1].length;
            const texto = enLinea(enc[2]);
            const id = enc[2]
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            salida.push(`<h${nivel} id="${id}">${texto}</h${nivel}>`);
            i++;
            continue;
        }

        // Tabla
        if (/^\|/.test(linea) && /^\|[\s:|-]+\|$/.test((lineas[i + 1] ?? '').trim())) {
            const encabezado = celdas(linea);
            i += 2;
            const filas = [];
            while (i < lineas.length && /^\|/.test(lineas[i])) {
                filas.push(celdas(lineas[i]));
                i++;
            }
            const th = encabezado.map((c) => `<th>${enLinea(c)}</th>`).join('');
            const tr = filas
                .map((f) => `<tr>${f.map((c) => `<td>${enLinea(c)}</td>`).join('')}</tr>`)
                .join('');
            salida.push(`<table><thead><tr>${th}</tr></thead><tbody>${tr}</tbody></table>`);
            continue;
        }

        // Cita / aviso
        if (/^>\s?/.test(linea)) {
            const cuerpo = [];
            while (i < lineas.length && /^>\s?/.test(lineas[i])) {
                cuerpo.push(lineas[i].replace(/^>\s?/, ''));
                i++;
            }
            const texto = cuerpo.join(' ').trim();
            // El emoji de cabecera decide el color del aviso.
            let clase = 'nota';
            if (texto.startsWith('⚠️')) {
                clase = 'nota aviso';
            } else if (texto.startsWith('🔒')) {
                clase = 'nota candado';
            } else if (texto.startsWith('📸')) {
                clase = 'nota camara';
            }
            salida.push(`<blockquote class="${clase}">${enLinea(texto)}</blockquote>`);
            continue;
        }

        // Imagen suelta: se emparejan con el pie en cursiva que viene debajo.
        const img = linea.match(/^!\[([^\]]*)\]\(([^)]+)\)\s*$/);
        if (img) {
            const src = pathToFileURL(join(INGENIERIA, img[2])).href;
            let pie = '';
            let j = i + 1;
            while (j < lineas.length && !lineas[j].trim()) {
                j++;
            }
            const posible = (lineas[j] ?? '').trim();
            if (/^\*[^*].*\*$/.test(posible)) {
                pie = enLinea(posible.replace(/^\*|\*$/g, ''));
                i = j + 1;
            } else {
                i++;
            }
            salida.push(
                `<figure><img src="${src}" alt="${escapar(img[1])}">` +
                    (pie ? `<figcaption>${pie}</figcaption>` : '') +
                    '</figure>'
            );
            continue;
        }

        // Listas, con un nivel de anidamiento (el manual no usa más).
        if (/^(\d+\.|[-*])\s+/.test(linea)) {
            const ordenada = /^\d+\./.test(linea);
            const etiqueta = ordenada ? 'ol' : 'ul';
            const items = [];
            while (i < lineas.length && /^(\s*)(\d+\.|[-*])\s+/.test(lineas[i])) {
                const sangria = lineas[i].match(/^(\s*)/)[1].length;
                const contenido = lineas[i].replace(/^\s*(\d+\.|[-*])\s+/, '');
                if (sangria >= 2 && items.length) {
                    items[items.length - 1].hijos.push(contenido);
                } else {
                    items.push({ texto: contenido, hijos: [] });
                }
                i++;
            }
            const html = items
                .map((it) => {
                    const hijos = it.hijos.length
                        ? `<ul>${it.hijos.map((h) => `<li>${enLinea(h)}</li>`).join('')}</ul>`
                        : '';
                    return `<li>${enLinea(it.texto)}${hijos}</li>`;
                })
                .join('');
            salida.push(`<${etiqueta}>${html}</${etiqueta}>`);
            continue;
        }

        // Párrafo: se juntan las líneas hasta el primer hueco.
        const parrafo = [];
        while (
            i < lineas.length &&
            lineas[i].trim() &&
            !/^(#{2,4}\s|\||>|!\[|---+\s*$|(\d+\.|[-*])\s)/.test(lineas[i])
        ) {
            parrafo.push(lineas[i].trim());
            i++;
        }
        if (parrafo.length) {
            const texto = parrafo.join(' ');
            // Los pies de figura sueltos y la firma final van en cursiva.
            const clase = /^\*[^*].*\*$/.test(texto) ? ' class="cursiva"' : '';
            salida.push(`<p${clase}>${enLinea(texto)}</p>`);
        }
    }

    return salida.join('\n');
}

/* ---------------------------------------------------------------------------
 * 2. Envoltorio: portada, índice y hoja de estilo
 * ------------------------------------------------------------------------- */

/** Devuelve el woff2 de Poppins del peso pedido, como URL de fichero. */
function poppins(peso) {
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

function indice(html) {
    const capitulos = [...html.matchAll(/<h2 id="([^"]+)">(.*?)<\/h2>/g)];
    const filas = capitulos
        .map(([, id, titulo]) => {
            const limpio = titulo.replace(/<[^>]+>/g, '');
            const numero = limpio.match(/^(\d+)\.\s*(.*)$/);
            return numero
                ? `<li><span class="indice-n">${numero[1]}</span><span class="indice-t">${numero[2]}</span></li>`
                : `<li><span class="indice-n"></span><span class="indice-t">${limpio}</span></li>`;
        })
        .join('');
    return `<ol class="indice">${filas}</ol>`;
}

function documento(cuerpo, meta) {
    const logo = pathToFileURL(join(RAIZ, 'public', 'img', 'logo-asobares.png')).href;

    return `<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Manual de usuario — Panel ASOBARES Quindío</title>
<style>
/* Poppins local: el mismo woff2 que sirve el sitio, para que el PDF y la
   plataforma se lean con la misma voz. */
@font-face { font-family: Poppins; font-weight: 400; font-style: normal; font-display: block; src: url("${poppins(400)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 500; font-style: normal; font-display: block; src: url("${poppins(500)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 600; font-style: normal; font-display: block; src: url("${poppins(600)}") format("woff2"); }
@font-face { font-family: Poppins; font-weight: 700; font-style: normal; font-display: block; src: url("${poppins(700)}") format("woff2"); }

:root {
  /* Manual de Marca de Asobares Colombia. En papel se usa la variante 700 del
     rojo y no el Pub Red puro: sobre blanco el #EE4137 no alcanza AA en texto. */
  --rojo: #b71f18;
  --rojo-puro: #ee4137;
  --negro: #0b090a;
  --tinta: #1b191a;
  --suave: #3d393b;
  --apagado: #575254;
  --linea: rgb(11 9 10 / 0.13);
  --papel-alt: #f5f3f4;
}

@page { size: A4; margin: 18mm 16mm 20mm; }

* { box-sizing: border-box; }

body {
  font-family: Poppins, system-ui, sans-serif;
  /* Tracking y leading van por tamaño, nunca uno solo para toda la escala:
     el cuerpo pide interlínea holgada y letra sin apretar; los titulares, al
     revés. */
  font-size: 10.5pt;
  line-height: 1.62;
  letter-spacing: 0;
  color: var(--tinta);
  margin: 0;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

/* --- Portada --- */
.portada {
  height: 253mm;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  page-break-after: always;
}
.portada-logo { width: 62mm; }
.portada-centro { padding-bottom: 24mm; }
.portada h1 {
  font-size: 34pt;
  font-weight: 700;
  line-height: 1.04;
  letter-spacing: -0.025em;
  margin: 0 0 6mm;
  color: var(--negro);
}
.portada .sub {
  font-size: 15pt;
  font-weight: 500;
  line-height: 1.25;
  letter-spacing: -0.012em;
  color: var(--rojo);
  margin: 0 0 10mm;
}
.portada .regla { width: 28mm; height: 3px; background: var(--rojo-puro); margin-bottom: 9mm; }
.portada dl { margin: 0; font-size: 10pt; line-height: 1.75; }
.portada dt {
  font-weight: 500;
  font-size: 7.5pt;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--apagado);
  margin-top: 4mm;
}
.portada dd { margin: 0; color: var(--tinta); }
.portada-pie { font-size: 8.5pt; letter-spacing: 0.01em; color: var(--apagado); border-top: 1px solid var(--linea); padding-top: 4mm; }

/* --- Índice --- */
.indice-hoja { page-break-after: always; }
.indice-hoja h2 { margin-top: 0; }
ol.indice { list-style: none; margin: 0; padding: 0; }
ol.indice li {
  display: flex;
  gap: 6mm;
  align-items: baseline;
  padding: 2.6mm 0;
  border-bottom: 1px solid var(--linea);
  font-size: 11pt;
}
.indice-n { color: var(--rojo); font-weight: 700; min-width: 7mm; font-variant-numeric: tabular-nums; }
.indice-t { letter-spacing: -0.005em; }

/* --- Jerarquía --- */
h2 {
  font-size: 19pt;
  font-weight: 700;
  line-height: 1.14;
  letter-spacing: -0.021em;
  color: var(--negro);
  margin: 0 0 5mm;
  padding-bottom: 2.5mm;
  border-bottom: 2px solid var(--rojo-puro);
  page-break-after: avoid;
}
h3 {
  font-size: 12.5pt;
  font-weight: 600;
  line-height: 1.3;
  letter-spacing: -0.012em;
  color: var(--negro);
  margin: 7mm 0 2.5mm;
  page-break-after: avoid;
}
h4 {
  font-size: 10.5pt;
  font-weight: 600;
  line-height: 1.4;
  letter-spacing: -0.006em;
  margin: 5mm 0 2mm;
  page-break-after: avoid;
}
p { margin: 0 0 3.2mm; }
p.cursiva { font-style: italic; color: var(--apagado); font-size: 9.5pt; letter-spacing: 0.004em; }
strong { font-weight: 600; color: var(--negro); }
a { color: var(--rojo); text-decoration: none; }
code {
  font-family: "Cascadia Mono", Consolas, ui-monospace, monospace;
  font-size: 9pt;
  background: var(--papel-alt);
  border: 1px solid var(--linea);
  border-radius: 3px;
  padding: 0.4mm 1.2mm;
  letter-spacing: 0;
}

ul, ol { margin: 0 0 3.2mm; padding-left: 6mm; }
li { margin-bottom: 1.4mm; }
li > ul { margin-top: 1.4mm; margin-bottom: 0; }

/* --- Tablas --- */
table {
  width: 100%;
  border-collapse: collapse;
  margin: 0 0 4.5mm;
  font-size: 9.3pt;
  line-height: 1.45;
  page-break-inside: avoid;
}
th {
  text-align: left;
  font-weight: 600;
  font-size: 8.2pt;
  letter-spacing: 0.055em;
  text-transform: uppercase;
  color: var(--apagado);
  border-bottom: 1.5px solid var(--negro);
  padding: 2mm 2.4mm;
  vertical-align: bottom;
}
td { padding: 2.1mm 2.4mm; border-bottom: 1px solid var(--linea); vertical-align: top; }
tbody tr:nth-child(even) { background: rgb(245 243 244 / 0.6); }

/* --- Avisos --- */
blockquote.nota {
  margin: 0 0 4mm;
  padding: 3mm 4mm;
  background: var(--papel-alt);
  border-left: 3px solid var(--apagado);
  border-radius: 0 4px 4px 0;
  font-size: 9.6pt;
  line-height: 1.55;
  page-break-inside: avoid;
}
blockquote.aviso { background: rgb(238 65 55 / 0.06); border-left-color: var(--rojo-puro); }
blockquote.candado { background: rgb(11 9 10 / 0.045); border-left-color: var(--negro); }
blockquote.camara { background: rgb(11 9 10 / 0.035); border-left-color: var(--apagado); }

/* --- Figuras --- */
figure { margin: 0 0 5mm; page-break-inside: avoid; }
figure img {
  width: 100%;
  border: 1px solid var(--linea);
  border-radius: 5px;
  /* Las capturas son de una interfaz clara: una sombra mínima las despega del
     papel igual que en el sitio despega la tarjeta del fondo. */
  box-shadow: 0 1px 2px rgb(11 9 10 / 0.05), 0 3px 8px rgb(11 9 10 / 0.05);
}
figcaption {
  margin-top: 1.8mm;
  font-size: 8.6pt;
  line-height: 1.4;
  letter-spacing: 0.01em;
  color: var(--apagado);
  font-style: italic;
}

.salto { page-break-before: always; }
</style>
</head>
<body>

<section class="portada">
  <img class="portada-logo" src="${logo}" alt="ASOBARES Capítulo Quindío">
  <div class="portada-centro">
    <div class="regla"></div>
    <h1>Manual de usuario</h1>
    <p class="sub">Panel de administración<br>Plataforma Web ASOBARES Capítulo Quindío</p>
    <dl>
      <dt>Versión</dt><dd>${meta.version}</dd>
      <dt>Fecha</dt><dd>${meta.fecha}</dd>
      <dt>Dirigido a</dt><dd>${meta.destinatario}</dd>
    </dl>
  </div>
  <p class="portada-pie">Entregable de la Fase 4 del cronograma firmado por la dirección ejecutiva.<br>
  No exige conocimientos técnicos: si sabe usar el correo, sabe usar esto.</p>
</section>

<section class="indice-hoja">
  <h2>Contenido</h2>
  ${indice(cuerpo)}
</section>

${cuerpo}

</body>
</html>`;
}

/* ---------------------------------------------------------------------------
 * 3. Impresión con Chrome por el protocolo DevTools
 * ------------------------------------------------------------------------- */

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
async function esperarDepurador(puerto, intentos = 100) {
    for (let n = 0; n < intentos; n++) {
        try {
            const r = await fetch(`http://127.0.0.1:${puerto}/json/version`);
            if (r.ok) {
                return await r.json();
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

async function imprimir(rutaHtml, rutaPdf, meta) {
    const perfil = mkdtempSync(join(tmpdir(), 'asobares-pdf-'));
    const puerto = 9333;
    const navegador = buscarNavegador();

    const proceso = spawn(
        navegador,
        [
            '--headless=new',
            `--remote-debugging-port=${puerto}`,
            `--user-data-dir=${perfil}`,
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-gpu',
            '--allow-file-access-from-files',
            'about:blank',
        ],
        { stdio: 'ignore' }
    );

    try {
        await esperarDepurador(puerto);

        const objetivo = await fetch(`http://127.0.0.1:${puerto}/json/new?about:blank`, {
            method: 'PUT',
        }).then((r) => r.json());

        const cdp = conectar(objetivo.webSocketDebuggerUrl);
        await cdp.lista;

        await cdp.enviar('Page.enable');
        const cargada = new Promise((res) => cdp.al('Page.loadEventFired', res));
        await cdp.enviar('Page.navigate', { url: pathToFileURL(rutaHtml).href });
        await cargada;

        // Las fuentes y las once capturas tienen que estar decodificadas antes
        // de imprimir, o salen huecos en blanco. `document.fonts.ready` y el
        // `decode()` de cada imagen lo garantizan; `load` solo no basta.
        await cdp.enviar('Runtime.evaluate', {
            expression: `Promise.all([
                document.fonts.ready,
                ...[...document.images].map(i => i.decode().catch(() => null)),
            ])`,
            awaitPromise: true,
        });

        const pie = `<div style="font-family:Poppins,sans-serif;font-size:7pt;color:#575254;
            width:100%;padding:0 16mm;display:flex;justify-content:space-between;
            letter-spacing:0.02em;">
            <span>Manual de usuario · Panel ASOBARES Quind&iacute;o · ${meta.version}</span>
            <span>P&aacute;gina <span class="pageNumber"></span> de <span class="totalPages"></span></span>
        </div>`;

        const { data } = await cdp.enviar('Page.printToPDF', {
            printBackground: true,
            preferCSSPageSize: true,
            displayHeaderFooter: true,
            headerTemplate: '<div></div>',
            footerTemplate: pie,
            marginTop: 0.71,
            marginBottom: 0.79,
            marginLeft: 0.63,
            marginRight: 0.63,
        });

        writeFileSync(rutaPdf, Buffer.from(data, 'base64'));
        cdp.cerrar();
    } finally {
        proceso.kill();
    }
}

/* ---------------------------------------------------------------------------
 * 4. Orquestación
 * ------------------------------------------------------------------------- */

const markdown = readFileSync(ENTRADA, 'utf8');

const meta = {
    version: (markdown.match(/\*\*Versión:\*\*\s*([^·\n]+)/) ?? [, '1.1'])[1].trim(),
    fecha: (markdown.match(/\*\*Fecha:\*\*\s*([^\n]+)/) ?? [, ''])[1].trim(),
    destinatario: (markdown.match(/\*\*Dirigido a:\*\*\s*([^\n]+)/) ?? [, ''])[1].trim(),
};

const html = documento(convertir(markdown), meta);

const temporal = mkdtempSync(join(tmpdir(), 'asobares-manual-'));
const rutaHtml = join(temporal, 'manual.html');
writeFileSync(rutaHtml, html, 'utf8');

if (!existsSync(dirname(SALIDA))) {
    mkdirSync(dirname(SALIDA), { recursive: true });
}

await imprimir(rutaHtml, SALIDA, meta);

const kb = Math.round(readFileSync(SALIDA).length / 1024);
console.log(`PDF escrito: ${SALIDA} (${kb} KB)`);
// El HTML intermedio se deja anunciado: es por donde se revisa la maqueta
// cuando algo sale torcido en el PDF.
console.log(`HTML intermedio: ${rutaHtml}`);

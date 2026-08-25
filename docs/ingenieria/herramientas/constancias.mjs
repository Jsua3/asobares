/**
 * Genera los tres formatos de constancia del expediente:
 *
 *   1. Acta de aprobación del diseño de la interfaz  (hito de la Semana 2)
 *   2. Constancia de capacitación                    (Semana 8)
 *   3. Retroalimentación del empresario y registro de hallazgos (Semanas 7–8)
 *
 *     node docs/ingenieria/herramientas/constancias.mjs
 *
 * Los tres son FORMATOS PARA DILIGENCIAR, no actas de hechos ya ocurridos.
 * Ninguno afirma que algo se aprobó, se dictó o se revisó: eso lo escribe y lo
 * firma quien corresponda el día que pase. Un documento que dé por cierto lo
 * que todavía no ha ocurrido no es evidencia, es un problema.
 */

import { join } from 'node:path';
import { estiloBase, logoUrl, imprimir, pieConPaginacion, INGENIERIA } from './imprimir.mjs';

const LOGO = logoUrl();

function documento({ titulo, subtitulo, referencia, cuerpo, estiloExtra = '' }) {
    return `<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>${titulo}</title>
<style>
@page { size: A4; margin: 18mm 16mm 20mm; }
${estiloBase()}
${estiloExtra}
</style>
</head>
<body>
<header class="membrete">
  <img src="${LOGO}" alt="ASOBARES Capítulo Quindío">
  <div class="ref">${referencia}</div>
</header>
<h1>${titulo}</h1>
<p class="titulo-sub">${subtitulo}</p>
${cuerpo}
</body>
</html>`;
}

const RENGLONES = (n) => `<div class="renglones">${'<div></div>'.repeat(n)}</div>`;

const PIE_LEGAL = `<p class="pie-legal">
Plataforma Web Oficial de ASOBARES Capítulo Quindío · Práctica empresarial, Universidad Alexander von Humboldt ·
Documento rector: Cronograma Sitio Web 2026, firmado por la dirección ejecutiva · Fecha límite de la práctica: 22 de septiembre de 2026.<br>
Este formato se diligencia y se firma el día de la sesión. Una vez firmado se anexa al expediente del proyecto en <code>docs/ingenieria/</code>.
</p>`;

/* ===========================================================================
 * 1. Acta de aprobación del diseño de la interfaz
 * ========================================================================= */

const acta = documento({
    referencia: 'Acta 01 · Hito de la Semana 2<br>Cronograma Sitio Web 2026<br>Fecha de emisión: 19 de agosto de 2026',
    titulo: 'Acta de aprobación del diseño de la interfaz',
    subtitulo: 'Hito contractual de la Semana 2 · Plataforma Web ASOBARES Capítulo Quindío',
    cuerpo: `
<dl class="ficha">
  <dt>Fecha de la sesión</dt><dd>_______________________</dd>
  <dt>Lugar</dt><dd>_______________________</dd>
  <dt>Semana</dt><dd>Semana 2 · 3 al 7 de agosto de 2026</dd>
  <dt>Modalidad</dt><dd>_______________________</dd>
</dl>

<h2>1. Objeto</h2>
<p>Dejar constancia escrita de la revisión y la decisión de la dirección ejecutiva sobre el <strong>diseño de la interfaz</strong> de la Plataforma Web Oficial de ASOBARES Capítulo Quindío, que el cronograma firmado fija como <strong>hito de aprobación de la Semana 2</strong>.</p>

<div class="nota">
<strong>Por qué se levanta esta acta ahora.</strong> El diseño se construyó en la semana que le correspondía y el desarrollo continuó sobre él. Lo que nunca quedó por escrito fue la aprobación. Este documento cierra esa brecha documental; no reabre decisiones de diseño ya ejecutadas ni retrasa el cronograma.
</div>

<h2>2. Lo que se somete a aprobación</h2>

<h3>2.1 Identidad visual</h3>
<table>
  <thead><tr><th style="width:34%">Elemento</th><th>Lo aplicado</th><th style="width:26%">Fuente</th></tr></thead>
  <tbody>
    <tr><td>Paleta principal</td><td>Pub Red <code>#EE4137</code> · Pub Black <code>#0B090A</code> · Ambient White <code>#F5F3F4</code></td><td rowspan="3">Manual de Marca de Asobares Colombia (Quida Studio)</td></tr>
    <tr><td>Paleta secundaria</td><td>Pub Grey <code>#282628</code> · Wine <code>#A4161A</code> · Ambient Purple <code>#C05299</code> · Ambient Rose <code>#EA698B</code></td></tr>
    <tr><td>Tipografía</td><td>Poppins, en los pesos 300 a 900</td></tr>
    <tr><td>Logotipo</td><td>Se usa completo y sin filtros. La única variante admitida es la versión en blanco, para fondos rojos o fotografía</td><td>Prohibición expresa del manual de reorganizar, deformar o recolorear la marca</td></tr>
    <tr><td>Motivo gráfico</td><td>Trama de puntos en rojo de marca, como textura de fondo</td><td>Piezas del gremio (cronograma y TED gremial)</td></tr>
  </tbody>
</table>

<h3>2.2 Decisiones de diseño que exceden lo pedido y conviene ratificar</h3>
<ul>
  <li><strong>Dos temas, claro y oscuro</strong>, con el tema gobernado por la elección del visitante y no por su sistema operativo. La elección se recuerda y vale también para el panel de administración.</li>
  <li><strong>El rojo de texto es la variante 700</strong> (<code>#B71F18</code>) y no el Pub Red puro: sobre el fondo claro del manual, el <code>#EE4137</code> no alcanza el contraste mínimo exigido para texto. El Pub Red puro se conserva para filetes, remates y superficies, que no son texto.</li>
  <li><strong>Mobile-first</strong>, por mandato del cronograma.</li>
  <li><strong>Accesibilidad</strong>: contraste AA en texto, foco siempre visible, y respeto a las preferencias de movimiento reducido, transparencia reducida y contraste alto del sistema operativo del visitante.</li>
</ul>

<h3>2.3 Estructura del sitio público sometida a revisión</h3>
<p>Inicio · Directorio de asociados · Abre tu negocio (guía normativa por municipio) · Empleo · Artistas · Proveedores · Eventos · Boletín · Quiénes somos · Afíliate · Contacto · Mi cuenta.</p>

<h2>3. Evidencia adjunta</h2>
<table>
  <thead><tr><th style="width:46%">Documento</th><th>Qué acredita</th></tr></thead>
  <tbody>
    <tr><td><code>docs/ingenieria/diagramas/</code></td><td>Siete diagramas UML y BPMN con sus fuentes editables</td></tr>
    <tr><td><code>docs/ingenieria/manual-de-usuario.md</code> y su PDF</td><td>Once capturas del panel real</td></tr>
    <tr><td><code>docs/ingenieria/medicion-de-rendimiento.md</code></td><td>78 mediciones sobre navegador real: la portada pinta en 972 ms contra un techo contractual de 2.500 ms</td></tr>
    <tr><td><code>docs/ingenieria/matriz-de-pruebas.md</code></td><td>Trazabilidad de los requisitos contratados contra las pruebas que los verifican</td></tr>
  </tbody>
</table>

<h2>4. Observaciones de la dirección ejecutiva</h2>
${RENGLONES(5)}

<h2>5. Decisión</h2>
<div class="opciones">
  <span><span class="casilla"></span> Aprobado</span>
  <span><span class="casilla"></span> Aprobado con las observaciones anotadas</span>
  <span><span class="casilla"></span> No aprobado</span>
</div>
<p><small>Si la decisión es «aprobado con observaciones», las del punto 4 se incorporan y se verifican en la Semana 7 (pruebas globales). Si es «no aprobado», se indica en el punto 4 qué debe cambiar y en qué plazo.</small></p>

<div class="firmas tres">
  <div class="firma">
    <div class="nombre">Natalia Gutiérrez</div>
    <div class="cargo">Directora ejecutiva · ASOBARES Capítulo Quindío<br>Tutora empresarial · aprueba el diseño</div>
  </div>
  <div class="firma">
    <div class="nombre">Juan José Sua Gómez</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Arquitectura, panel y pasarela</div>
  </div>
  <div class="firma">
    <div class="nombre">Ingrid Montoya Warski</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Interfaz, directorio y eventos</div>
  </div>
</div>

${PIE_LEGAL}
`,
});

/* ===========================================================================
 * 2. Constancia de capacitación
 * ========================================================================= */

const capacitacion = documento({
    referencia: 'Acta 02 · Semana 8<br>Cronograma Sitio Web 2026<br>Fecha de emisión: 19 de agosto de 2026',
    titulo: 'Constancia de capacitación',
    subtitulo: 'Panel de administración · Plataforma Web ASOBARES Capítulo Quindío',
    cuerpo: `
<dl class="ficha">
  <dt>Fecha</dt><dd>_______________________</dd>
  <dt>Lugar</dt><dd>_______________________</dd>
  <dt>Hora de inicio</dt><dd>_______________________</dd>
  <dt>Hora de cierre</dt><dd>_______________________</dd>
  <dt>Modalidad</dt><dd>_______________________</dd>
  <dt>Dicta</dt><dd>Juan José Sua Gómez e Ingrid Montoya Warski</dd>
</dl>

<h2>1. Objeto</h2>
<p>Dejar constancia de la sesión de capacitación al personal de ASOBARES Capítulo Quindío en el uso del panel de administración de la plataforma, exigida por la <strong>Semana 8</strong> del cronograma firmado por la dirección ejecutiva.</p>

<h2>2. Contenido impartido</h2>
<table>
  <thead><tr><th style="width:7%">Sí</th><th style="width:43%">Tema</th><th>Qué se practica</th></tr></thead>
  <tbody>
    <tr><td><span class="casilla"></span></td><td>Acceso y segundo factor</td><td>Entrar al panel y recibir el código por correo</td></tr>
    <tr><td><span class="casilla"></span></td><td>Los dos tipos de cuenta</td><td>Qué puede hacer la Dirección y qué la Secretaría</td></tr>
    <tr><td><span class="casilla"></span></td><td>Publicar un asociado</td><td>Contacto público frente a contacto interno, y la autorización del propietario</td></tr>
    <tr><td><span class="casilla"></span></td><td>Publicar un evento</td><td>Aforo, valor e inscripciones</td></tr>
    <tr><td><span class="casilla"></span></td><td>Mantener la guía normativa</td><td>Trámites por municipio y formatos descargables</td></tr>
    <tr><td><span class="casilla"></span></td><td>Aprobar lo que redactó otra persona</td><td>Aprobar y publicar, o devolver con motivo</td></tr>
    <tr><td><span class="casilla"></span></td><td>Moderar las bolsas</td><td>Vacantes, artistas y proveedores</td></tr>
    <tr><td><span class="casilla"></span></td><td>Atender mensajes y PQR</td><td>Radicado y constancia de respuesta</td></tr>
    <tr><td><span class="casilla"></span></td><td>Cartera y pagos</td><td>Importar el archivo de la contadora y consultar transacciones</td></tr>
    <tr><td><span class="casilla"></span></td><td>Configuración y bitácora</td><td>Ajustes del sitio y quién hizo qué</td></tr>
  </tbody>
</table>

<h2>3. Criterio de aprobación</h2>
<div class="nota">
El criterio no es que la sesión ocurra ni que el manual exista. Es que, <strong>al terminar, cada asistente publique por su cuenta y sin ayuda un asociado, un evento y una noticia.</strong> Lo que falle en esa prueba señala qué hay que reescribir en el manual, no qué hay que repetirle al asistente.
</div>

<table>
  <thead>
    <tr>
      <th style="width:28%">Asistente</th><th style="width:20%">Cargo</th>
      <th style="width:9%">Asociado</th><th style="width:9%">Evento</th><th style="width:9%">Noticia</th>
      <th>Firma</th>
    </tr>
  </thead>
  <tbody>
    <tr class="vacia"><td></td><td></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr class="vacia"><td></td><td></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr class="vacia"><td></td><td></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr class="vacia"><td></td><td></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr class="vacia"><td></td><td></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
  </tbody>
</table>

<h2>4. Material entregado</h2>
<ul>
  <li><span class="casilla"></span> Manual de usuario del panel, en PDF, con las once capturas del panel real.</li>
  <li><span class="casilla"></span> Credenciales de acceso propias de cada asistente, con su segundo factor dado de alta.</li>
  <li><span class="casilla"></span> Dirección del sitio y del panel: _______________________________________</li>
  <li><span class="casilla"></span> Persona de contacto para dudas posteriores: _______________________________________</li>
</ul>

<h2>5. Lo que quedó sin resolver en la sesión</h2>
<p><small>Lo que aquí se anote se corrige en el manual antes de la entrega final.</small></p>
${RENGLONES(4)}

<div class="firmas tres">
  <div class="firma">
    <div class="nombre">Juan José Sua Gómez</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Dicta la capacitación</div>
  </div>
  <div class="firma">
    <div class="nombre">Ingrid Montoya Warski</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Dicta la capacitación</div>
  </div>
  <div class="firma">
    <div class="nombre">Natalia Gutiérrez</div>
    <div class="cargo">Directora ejecutiva · ASOBARES Capítulo Quindío<br>Recibe la capacitación y el material</div>
  </div>
</div>

${PIE_LEGAL}
`,
});

/* ===========================================================================
 * 3. Retroalimentación del empresario y registro de hallazgos
 * ========================================================================= */

const retroalimentacion = documento({
    referencia: 'Formato 03 · Semanas 7 y 8<br>Cronograma Sitio Web 2026<br>Fecha de emisión: 19 de agosto de 2026',
    titulo: 'Retroalimentación del empresario',
    subtitulo: 'Revisión de la plataforma y registro de hallazgos · ASOBARES Capítulo Quindío',
    cuerpo: `
<dl class="ficha">
  <dt>Fecha de la revisión</dt><dd>_______________________</dd>
  <dt>Quién revisa</dt><dd>_______________________</dd>
  <dt>Versión revisada</dt><dd>_______________________</dd>
  <dt>Duración</dt><dd>_______________________</dd>
</dl>

<div class="opciones-linea">
  <span class="titulo">Cómo se revisó:</span>
  <span><span class="casilla"></span>demostración en vivo</span>
  <span><span class="casilla"></span>recorrido grabado</span>
  <span><span class="casilla"></span>por su cuenta, desde el sitio publicado</span>
  <span><span class="casilla"></span>otra: _______________</span>
</div>

<div class="nota">
<strong>Este formato existe por dos razones a la vez.</strong> El cronograma nombra la corrección de errores reportados por la tutora como el contenido de la Semana 7, y el curso de práctica evalúa la retroalimentación del empresario como criterio de corte. Un «está muy bonito» dicho en una reunión no sirve para ninguna de las dos: hace falta que quede escrito y fechado.
</div>

<h2>1. Valoración por módulo</h2>
<p><small>Marque solo lo que alcanzó a revisar. Un módulo sin marcar se lee como «no revisado», no como «correcto».</small></p>
<table>
  <thead>
    <tr>
      <th style="width:30%">Módulo</th>
      <th style="width:9%">Sirve</th><th style="width:13%">Sirve, con reparos</th><th style="width:9%">No sirve</th>
      <th>Comentario</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Directorio de asociados</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Abre tu negocio (guía normativa)</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Bolsa de empleo</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Directorio de artistas</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Bolsa de proveedores</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Eventos e inscripciones</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Cartera del afiliado y pagos</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Panel de administración</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
    <tr><td>Uso desde el celular</td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td><td></td></tr>
  </tbody>
</table>

<div class="salto"></div>

<h2>2. Registro de hallazgos</h2>
<p><small>Un hallazgo por fila. <strong>Impide usar</strong> = no se puede completar la tarea. <strong>Estorba</strong> = se puede, pero cuesta o confunde. <strong>Detalle</strong> = mejora deseable.</small></p>
<table>
  <thead>
    <tr>
      <th style="width:6%">N.º</th><th style="width:18%">Dónde</th>
      <th style="width:40%">Qué ocurre y qué esperaba que ocurriera</th>
      <th style="width:16%">Gravedad</th><th>Estado</th>
    </tr>
  </thead>
  <tbody>
    ${Array.from({ length: 9 }, (_, i) => `<tr class="vacia"><td>${i + 1}</td><td></td><td></td><td></td><td></td></tr>`).join('\n    ')}
  </tbody>
</table>

<h2>3. Insumos que el gremio debe entregar</h2>
<p><small>Sin estos datos hay módulos que se publican vacíos. Conviene ponerles fecha comprometida aquí y no volver a pedirlos de palabra.</small></p>
<table>
  <thead><tr><th style="width:52%">Insumo</th><th style="width:22%">Responsable</th><th>Fecha comprometida</th></tr></thead>
  <tbody>
    <tr class="vacia"><td>Base de los asociados con sus autorizaciones de publicación</td><td></td><td></td></tr>
    <tr class="vacia"><td>Listas de chequeo por entidad y municipio (guía normativa)</td><td></td><td></td></tr>
    <tr class="vacia"><td>Lista vigente de aliados con su beneficio</td><td></td><td></td></tr>
    <tr class="vacia"><td>Textos definitivos de «Quiénes somos»</td><td></td><td></td></tr>
    <tr class="vacia"><td>Documentos para activar la pasarela de pagos en producción</td><td></td><td></td></tr>
    <tr class="vacia"><td>Correo y medio de pago institucionales para el servidor</td><td></td><td></td></tr>
  </tbody>
</table>

<h2>4. Concepto general de la dirección ejecutiva</h2>
${RENGLONES(6)}

<div class="firmas tres">
  <div class="firma">
    <div class="nombre">Natalia Gutiérrez</div>
    <div class="cargo">Directora ejecutiva · ASOBARES Capítulo Quindío<br>Tutora empresarial · emite la retroalimentación</div>
  </div>
  <div class="firma">
    <div class="nombre">Juan José Sua Gómez</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Recibe la retroalimentación</div>
  </div>
  <div class="firma">
    <div class="nombre">Ingrid Montoya Warski</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Recibe la retroalimentación</div>
  </div>
</div>

${PIE_LEGAL}
`,
});

/* ------------------------------------------------------------------------- */

const trabajos = [
    {
        html: acta,
        salida: join(INGENIERIA, 'constancias', 'Acta 01 - Aprobacion del diseno de la interfaz.pdf'),
        pie: pieConPaginacion('Acta 01 · Aprobaci&oacute;n del dise&ntilde;o · ASOBARES Quind&iacute;o'),
    },
    {
        html: capacitacion,
        salida: join(INGENIERIA, 'constancias', 'Acta 02 - Constancia de capacitacion.pdf'),
        pie: pieConPaginacion('Acta 02 · Constancia de capacitaci&oacute;n · ASOBARES Quind&iacute;o'),
    },
    {
        html: retroalimentacion,
        salida: join(INGENIERIA, 'constancias', 'Formato 03 - Retroalimentacion del empresario.pdf'),
        pie: pieConPaginacion('Formato 03 · Retroalimentaci&oacute;n del empresario · ASOBARES Quind&iacute;o'),
    },
];

const { mkdirSync } = await import('node:fs');
mkdirSync(join(INGENIERIA, 'constancias'), { recursive: true });

const escritos = await imprimir(trabajos);
for (const ruta of escritos) {
    console.log('PDF escrito:', ruta);
}

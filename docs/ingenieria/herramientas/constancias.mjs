/**
 * Genera los cinco formatos de constancia del expediente:
 *
 *   1. Acta de aprobación del diseño de la interfaz  (hito de la Semana 2)
 *   2. Constancia de capacitación                    (Semana 8)
 *   3. Retroalimentación del empresario y registro de hallazgos (Semanas 7–8)
 *   4. Ampliación de alcance                         (revisión del 28 ago 2026)
 *   5. Ampliación de alcance: cifras del gremio en la portada (petición del 1 sep 2026)
 *
 *     node docs/ingenieria/herramientas/constancias.mjs            # los cinco
 *     node docs/ingenieria/herramientas/constancias.mjs "Acta 05"  # solo el que contenga ese texto
 *
 * Los cinco son FORMATOS PARA DILIGENCIAR, no actas de hechos ya ocurridos.
 * Ninguno afirma que algo se aprobó, se dictó o se revisó: eso lo escribe y lo
 * firma quien corresponda el día que pase. Un documento que dé por cierto lo
 * que todavía no ha ocurrido no es evidencia, es un problema.
 *
 * El cuarto se numera **04** y no 03 a propósito: el 03 ya lo ocupa el formato
 * de retroalimentación, y dos referencias con el mismo número en un expediente
 * que se entrega a la universidad y al gremio es un problema de trazabilidad,
 * no un detalle de nomenclatura.
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

/* ===========================================================================
 * 4. Ampliación de alcance
 * ========================================================================= */

const ampliacion = documento({
    referencia: 'Acta 04 · Ampliación de alcance<br>Revisión del gremio del 28 de agosto de 2026<br>Fecha de emisión: 30 de agosto de 2026',
    titulo: 'Acta de ampliación de alcance',
    subtitulo: 'Decisión sobre lo pedido en la revisión del 28 de agosto · ASOBARES Capítulo Quindío',
    // Las filas de este acta son largas —una peticion citada y su costo— y sin
    // esto la tabla de decision parte un renglon por la mitad entre dos
    // paginas, dejando las casillas de decision separadas de lo que se decide.
    // En un documento que se firma, eso no es un detalle tipografico.
    estiloExtra: 'tbody tr { page-break-inside: avoid; }',
    cuerpo: `
<dl class="ficha">
  <dt>Fecha de la sesión</dt><dd>_______________________</dd>
  <dt>Lugar</dt><dd>_______________________</dd>
  <dt>Modalidad</dt><dd>_______________________</dd>
  <dt>Quién decide</dt><dd>_______________________</dd>
</dl>

<h2>1. Objeto</h2>
<p>Dejar constancia escrita de la decisión de la dirección ejecutiva sobre <strong>cuatro peticiones surgidas en la revisión del 28 de agosto de 2026</strong> que <strong>no forman parte del alcance contratado</strong>: no están en el cronograma firmado ni en la especificación de requisitos, y por tanto no se pueden construir sin una decisión expresa que las incorpore.</p>

<div class="nota">
<strong>Lo que este documento NO cubre.</strong> Los catorce señalamientos restantes de esa misma revisión son ajustes sobre módulos ya construidos —renombrar un título, ordenar un listado, retirar un dato de una ficha, corregir un texto— y <strong>se ejecutan sin acta</strong>, porque no amplían el alcance. Esta acta se limita a las cuatro peticiones de abajo, que sí lo amplían.
</div>

<h2>2. La regla que gobierna esta decisión</h2>
<p>El alcance quedó congelado el <strong>14 de agosto de 2026</strong>. Desde entonces rige el mismo criterio: la ausencia de una funcionalidad no constituye incumplimiento mientras no figure en el cronograma firmado ni en la especificación, y <strong>toda ampliación se registra por escrito antes de codificarse</strong>.</p>
<p>La fecha límite de la práctica es el <strong>22 de septiembre de 2026</strong>. El tiempo disponible es fijo y ya está comprometido con la capa visual que la propia revisión del 28 de agosto priorizó. <strong>Lo que entre aquí desplaza a otra cosa</strong>, y el punto 5 existe para dejar dicho a qué.</p>

<div class="salto"></div>

<h2>3. Lo que se somete a decisión</h2>
<p><small>Una decisión por petición. Dejar una fila sin marcar no la aplaza: la deja sin decidir, que es como se llega a la fecha límite con todo a medias.</small></p>

<table>
  <thead>
    <tr>
      <th style="width:6%">Ref.</th>
      <th style="width:30%">Lo que se pidió</th>
      <th style="width:34%">Qué implica construirlo</th>
      <th style="width:10%">Antes del 22 sep</th>
      <th style="width:10%">Fase II</th>
      <th style="width:10%">Se descarta</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>OBS3-15</td>
      <td><strong>Correo de alerta a la dirección</strong> cuando la secretaría o un pasante cambien algo, «para que sepa qué fue y no tenga que ir a abrir la página».</td>
      <td>Barato <strong>solo si el correo saliente institucional ya existe</strong>, y hoy no existe. Depende del mismo trámite que bloquea el despliegue: la cuenta y el correo del gremio. Sin eso no se puede ni demostrar.</td>
      <td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td>
    </tr>
    <tr>
      <td>OBS3-16</td>
      <td><strong>Reversión de cambios</strong> sobre la bitácora: ver todos los cambios «para que no haya excusa» y poder deshacerlos.</td>
      <td>Es lo más caro de la lista y no es un ajuste, es un módulo. <strong>La mitad ya está hecha:</strong> la bitácora del panel registra hoy quién cambió qué, cuándo y qué campos. Lo que falta es solo deshacerlo, que es la parte cara.</td>
      <td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td>
    </tr>
    <tr>
      <td>OBS3-17</td>
      <td><strong>Transparencia financiera</strong> en el portal del afiliado: estados financieros, gestión administrativa, actas e invitaciones.</td>
      <td><strong>No se puede codificar mientras el gremio no defina qué documento se publica y quién lo sube.</strong> Si la respuesta es «un PDF que sube la dirección», es una lista y un recurso del panel, no un módulo financiero — y entonces sí cabe.</td>
      <td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td>
    </tr>
    <tr>
      <td>OBS3-18</td>
      <td><strong>Destacados pagos</strong>: pauta en portada y laterales rotativos «pero con un costo».</td>
      <td>La base técnica ya existe en el panel. Lo que falta no es código: es la <strong>regla comercial</strong> —qué se cobra, a quién, por cuánto tiempo— y cómo se cobra. Eso lo define el gremio.</td>
      <td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td>
    </tr>
  </tbody>
</table>

<h2>4. Contrapropuestas del equipo</h2>
<p><small>Se ofrecen porque en dos casos hay una versión que cabe en el tiempo disponible y probablemente resuelve la necesidad real. Marcar solo si sustituyen a lo pedido.</small></p>

<table>
  <thead><tr><th style="width:8%">Acepta</th><th style="width:12%">En vez de</th><th>Propuesta</th></tr></thead>
  <tbody>
    <tr>
      <td><span class="casilla"></span></td>
      <td>OBS3-16</td>
      <td><strong>Enseñar la bitácora que ya existe</strong>, y limitar la reversión a los campos donde es barata —textos de configuración y de contenido—, nunca genérica. Deshacer cualquier cambio de cualquier tabla exige versionado completo y es trabajo de meses, no de semanas.</td>
    </tr>
    <tr>
      <td><span class="casilla"></span></td>
      <td>OBS3-17</td>
      <td><strong>Una sección de documentos del gremio</strong> en el portal del afiliado: la dirección sube un PDF con su título y su fecha, y el afiliado con sesión iniciada lo descarga. Cubre estados financieros, actas e invitaciones sin construir un módulo financiero.</td>
    </tr>
  </tbody>
</table>

<h2>5. Qué se desplaza</h2>
<p><small>A diligenciar solo si alguna petición se aprueba para antes del 22 de septiembre. El tiempo no es elástico y esta casilla evita que el desplazamiento se descubra en la última semana.</small></p>
${RENGLONES(4)}

<h2>6. Observaciones de la dirección ejecutiva</h2>
${RENGLONES(5)}

<h2>7. Constancia</h2>
<div class="nota">
Lo marcado como <strong>Fase II</strong> queda fuera del alcance de esta práctica empresarial y no constituye incumplimiento de lo contratado. Lo marcado como <strong>antes del 22 de septiembre</strong> se incorpora al alcance y desplaza lo que se anote en el punto 5. Lo <strong>descartado</strong> no se vuelve a proponer sin una nueva acta.
</div>

<div class="firmas tres">
  <div class="firma">
    <div class="nombre">Natalia Gutiérrez</div>
    <div class="cargo">Directora ejecutiva · ASOBARES Capítulo Quindío<br>Tutora empresarial · decide la ampliación</div>
  </div>
  <div class="firma">
    <div class="nombre">Juan José Sua Gómez</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Presenta el alcance y su costo</div>
  </div>
  <div class="firma">
    <div class="nombre">Ingrid Montoya Warski</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Presenta el alcance y su costo</div>
  </div>
</div>

${PIE_LEGAL}
`,
});

/* ===========================================================================
 * 5. Ampliación de alcance: la franja «El gremio en cifras» de la portada
 * ========================================================================= */

const cifrasDelGremio = documento({
    referencia: 'Acta 05 · Ampliación de alcance<br>Petición de la dirección del 1 de septiembre de 2026<br>Fecha de emisión: 1 de septiembre de 2026',
    titulo: 'Acta de ampliación de alcance: cifras del gremio en la portada',
    subtitulo: 'Decisión sobre la franja «El gremio en cifras» · ASOBARES Capítulo Quindío',
    estiloExtra: 'tbody tr { page-break-inside: avoid; }',
    cuerpo: `
<dl class="ficha">
  <dt>Fecha de la sesión</dt><dd>_______________________</dd>
  <dt>Lugar</dt><dd>_______________________</dd>
  <dt>Modalidad</dt><dd>_______________________</dd>
  <dt>Quién decide</dt><dd>_______________________</dd>
</dl>

<h2>1. Objeto</h2>
<p>Dejar constancia escrita de la decisión de la dirección ejecutiva sobre <strong>una petición del 1 de septiembre de 2026</strong> que <strong>no forma parte del alcance contratado</strong>: que la portada muestre <strong>cifras propias del capítulo</strong> —distintas de las del Observatorio Económico de la Nacional, que ya tienen su franja— tomadas del archivo que la contaduría actualiza cada quince días.</p>

<h2>2. La regla que gobierna esta decisión</h2>
<p>El alcance quedó congelado el <strong>14 de agosto de 2026</strong> y desde entonces <strong>toda ampliación se registra por escrito antes de codificarse</strong>. Esta acta se emite antes de la primera línea de código de la franja. Mientras no se firme y no se tecleen las cifras, la franja no existe para quien visita el sitio: nace vacía y vacía no se muestra.</p>

<h2>3. Lo que se somete a decisión</h2>
<table>
  <thead>
    <tr>
      <th style="width:6%">Ref.</th>
      <th style="width:30%">Lo que se pidió</th>
      <th style="width:34%">Qué implica construirlo</th>
      <th style="width:10%">Antes del 22 sep</th>
      <th style="width:10%">Fase II</th>
      <th style="width:10%">Se descarta</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="white-space:nowrap">D-25</td>
      <td><strong>Una franja «El gremio en cifras»</strong> en la portada, junto a la del Observatorio, con cifras del capítulo que cambian cada quince días según el archivo de la contadora.</td>
      <td>Se construye como <strong>cuatro cifras editables desde el panel</strong> —el número y qué significa cada una— en el mismo formulario de «Ajustes del sitio» que ya usa la oficina: se teclean en un minuto, la franja no aparece mientras estén vacías, y muestra sola la fecha de la última cifra que cambió. <strong>El sistema no lee el archivo de la contadora</strong>: es la fuente de la que la oficina copia los números. Cabe en una sesión y no desplaza nada de lo comprometido.</td>
      <td><span class="casilla"></span></td><td><span class="casilla"></span></td><td><span class="casilla"></span></td>
    </tr>
  </tbody>
</table>

<h2>4. Lo que queda fuera, y por qué</h2>
<p>Que la franja se actualice sola leyendo el archivo de la contadora es la evolución natural de esto y <strong>no entra ahora</strong>: el archivo todavía no existe con un formato acordado, y para cuatro números por quincena un importador —con sus errores por fila, sus pruebas y una cosa más que enseñar en la capacitación— cuesta más que teclearlos. Queda propuesto como <strong>Fase II</strong>, condicionado a recibir el archivo real y a que la contaduría prefiera cargarlo a escribirlo.</p>

<h2>5. Las cuatro cifras</h2>
<p><small>A diligenciar por la dirección: qué cuatro cifras se publican y de dónde sale cada una. Sin esto la franja queda vacía y no se muestra.</small></p>
${RENGLONES(4)}

<h2>6. Observaciones de la dirección ejecutiva</h2>
${RENGLONES(4)}

<h2>7. Constancia</h2>
<div class="nota">
Lo marcado como <strong>antes del 22 de septiembre</strong> se incorpora al alcance. Lo marcado como <strong>Fase II</strong> queda fuera de esta práctica empresarial y no constituye incumplimiento de lo contratado. Lo <strong>descartado</strong> no se vuelve a proponer sin una nueva acta. Las cifras publicadas son responsabilidad de quien las teclea: el sistema las muestra tal como se escriben.
</div>

<div class="firmas tres">
  <div class="firma">
    <div class="nombre">Natalia Gutiérrez</div>
    <div class="cargo">Directora ejecutiva · ASOBARES Capítulo Quindío<br>Tutora empresarial · decide la ampliación</div>
  </div>
  <div class="firma">
    <div class="nombre">Juan José Sua Gómez</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Presenta el alcance y su costo</div>
  </div>
  <div class="firma">
    <div class="nombre">Ingrid Montoya Warski</div>
    <div class="cargo">Practicante · Universidad Alexander von Humboldt<br>Presenta el alcance y su costo</div>
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
    {
        html: ampliacion,
        salida: join(INGENIERIA, 'constancias', 'Acta 04 - Ampliacion de alcance.pdf'),
        pie: pieConPaginacion('Acta 04 · Ampliaci&oacute;n de alcance · ASOBARES Quind&iacute;o'),
    },
    {
        html: cifrasDelGremio,
        salida: join(INGENIERIA, 'constancias', 'Acta 05 - Ampliacion de alcance - cifras del gremio.pdf'),
        pie: pieConPaginacion('Acta 05 · Cifras del gremio en la portada · ASOBARES Quind&iacute;o'),
    },
];

const { mkdirSync } = await import('node:fs');
mkdirSync(join(INGENIERIA, 'constancias'), { recursive: true });

// Con un argumento se imprimen solo los trabajos cuyo archivo de salida lo
// contenga; sin él, los cinco. Regenerar un PDF que no cambió lo cambia
// igual —metadatos y fecha— y ensucia el historial de git con binarios.
const filtro = process.argv[2];
const seleccionados = filtro ? trabajos.filter((t) => t.salida.includes(filtro)) : trabajos;

if (seleccionados.length === 0) {
    throw new Error(`Ningún trabajo coincide con «${filtro}».`);
}

const escritos = await imprimir(seleccionados);
for (const ruta of escritos) {
    console.log('PDF escrito:', ruta);
}

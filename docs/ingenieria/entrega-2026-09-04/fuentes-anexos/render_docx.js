// Renderiza los anexos E, F y G como .docx a partir del AST JSON (md_to_ast.py).
// Formato: carta, membrete del documento de práctica en el encabezado, Times New Roman,
// pie con «Anexo X · título · Página n de N», tablas con bordes y cabecera repetida.
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell, WidthType, BorderStyle,
  ShadingType, AlignmentType, HeadingLevel, Header, Footer, PageNumber, ImageRun, LevelFormat,
  HorizontalPositionRelativeFrom, VerticalPositionRelativeFrom, TextWrappingType, TabStopType,
  PageBreak, VerticalAlign,
} = require('docx');

const HERE = __dirname;
// Membrete del documento de práctica (misma imagen que la portada). Se busca junto a este script.
const BANNER = [path.join(__dirname, 'membrete.jpeg'), '/home/claude/work/x/word/media/image1.jpeg'].find((p) => fs.existsSync(p));
const FONT = 'Times New Roman';
const MONO = 'Consolas';
const PAGE_W = 12240, PAGE_H = 15840;
const MARGIN = { top: 2740, right: 1559, bottom: 1100, left: 1700, header: 3, footer: 500 };
const TEXT_W = PAGE_W - MARGIN.left - MARGIN.right; // 8981 DXA

const ANEXOS = {
  e: {
    letra: 'E', titulo: 'Matriz de trazabilidad de requisitos y pruebas',
    meta: [
      ['Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'],
      ['Docente asesor', 'César Augusto Granada Muñoz'],
      ['Tutora empresarial', 'Natalia Gutiérrez, directora ejecutiva de Asobares Capítulo Quindío'],
      ['Línea base de requisitos', 'ERS-ASOBARES-QUINDIO-V3.0 (RF-01 a RF-62; RNF-01 a RNF-14)'],
      ['Repositorio', 'github.com/Jsua3/asobares, rama main'],
      ['Versión', '1.1, corte al 25 de agosto de 2026 (última ejecución verificada)'],
    ],
  },
  f: {
    letra: 'F', titulo: 'Manual de usuario del panel de administración',
    meta: [
      ['Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'],
      ['Dirigido a', 'Dirección ejecutiva, secretaría y practicantes de Asobares Capítulo Quindío'],
      ['Requisito verificado', 'RNF-14, operación autónoma del panel por personal no técnico'],
      ['Capturas', 'Once capturas del panel real, tomadas el 18 de agosto de 2026 sobre la base de demostración'],
      ['Versión', '1.3, 27 de agosto de 2026'],
    ],
  },
  g: {
    letra: 'G', titulo: 'Informe de medición de rendimiento',
    meta: [
      ['Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'],
      ['Requisito verificado', 'RNF-02. Origen: cronograma firmado por la dirección ejecutiva, «La página principal no debe tardar más de 2.5 segundos en cargar»'],
      ['Fecha de la medición', '18 de agosto de 2026'],
      ['Estado del repositorio', 'Rama main, revisión 4f15d24; recursos compilados con npm run build'],
      ['Versión', '1.1, 27 de agosto de 2026'],
    ],
  },
};

const border = (color = '808080', size = 4) => ({ style: BorderStyle.SINGLE, size, color });
const cellBorders = { top: border(), bottom: border(), left: border(), right: border() };
const noBorder = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };

function runs(inlines, base = {}) {
  return inlines.map((r) => {
    const common = { font: FONT, size: base.size || 22, ...base };
    if (r.t === 'code') return new TextRun({ text: r.v, font: MONO, size: (base.size || 22) - 3 });
    if (r.t === 'em') return new TextRun({ ...common, text: r.v, italics: true });
    if (r.t === 'strong') return new TextRun({ ...common, text: r.v, bold: true });
    return new TextRun({ ...common, text: r.v });
  });
}

function para(inlines, opts = {}) {
  return new Paragraph({
    children: runs(inlines, opts.run || {}),
    alignment: opts.alignment || AlignmentType.JUSTIFIED,
    spacing: { before: opts.before ?? 0, after: opts.after ?? 120, line: 276 },
    indent: opts.indent,
    numbering: opts.numbering,
    keepNext: opts.keepNext,
    keepLines: opts.keepLines,
    shading: opts.shading,
    border: opts.border,
  });
}

function heading(text, level) {
  return new Paragraph({
    heading: level === 2 ? HeadingLevel.HEADING_2 : HeadingLevel.HEADING_3,
    children: [new TextRun({ text, font: FONT, bold: true, size: level === 2 ? 24 : 22, color: '000000' })],
    spacing: { before: level === 2 ? 280 : 200, after: 100, line: 276 },
    keepNext: true,
  });
}

function table(node) {
  const total = node.widths.reduce((a, b) => a + b, 0);
  const cols = node.widths.map((w) => Math.round((TEXT_W * w) / total));
  cols[cols.length - 1] += TEXT_W - cols.reduce((a, b) => a + b, 0);
  // Las tablas de cifras (10 a 14 filas cortas, como las de rutas del Anexo G) se mantienen enteras:
  // «conservar con el siguiente» en todas sus filas menos la última. Las demás pueden partirse; la fila de cabecera se repite.
  const chars = node.rows.reduce((a, r) => a + r.reduce((b, c) => b + c.reduce((d, x) => d + x.v.length, 0), 0), 0);
  const keepTogether = node.rows.length >= 10 && node.rows.length <= 14 && chars <= 700;
  const mk = (cells, isHeader, isLast) => new TableRow({
    tableHeader: isHeader,
    cantSplit: true,
    children: cells.map((inl, ci) => new TableCell({
      width: { size: cols[ci], type: WidthType.DXA },
      borders: cellBorders,
      shading: isHeader ? { type: ShadingType.CLEAR, fill: 'E7E6E6', color: 'auto' } : undefined,
      margins: { top: 40, bottom: 40, left: 80, right: 80 },
      verticalAlign: VerticalAlign.TOP,
      children: [new Paragraph({
        children: runs(inl, { size: 18, bold: isHeader || undefined }),
        alignment: node.align && node.align[ci] === 'right' && !isHeader ? AlignmentType.RIGHT : AlignmentType.LEFT,
        spacing: { before: 0, after: 0, line: 240 },
        keepNext: keepTogether && !isLast ? true : undefined,
      })],
    })),
  });
  return new Table({
    width: { size: TEXT_W, type: WidthType.DXA },
    columnWidths: cols,
    layout: 'fixed',
    rows: [mk(node.header, true, false), ...node.rows.map((r, i) => mk(r, false, i === node.rows.length - 1))],
  });
}

function image(node) {
  const maxW = 5.3 * 96, maxH = 3.6 * 96;           // píxeles a 96 ppp
  let w = node.w, h = node.h;
  const k = Math.min(maxW / w, maxH / h, 1);
  w = Math.round(w * k); h = Math.round(h * k);
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 120, after: 60 },
    keepNext: true,
    children: [new ImageRun({ type: 'png', data: fs.readFileSync(node.path), transformation: { width: w, height: h } })],
  });
}

function caption(text) {
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 200 },
    children: [new TextRun({ text, font: FONT, size: 19, italics: true })],
  });
}

function code(lines) {
  return lines.map((l, i) => new Paragraph({
    children: [new TextRun({ text: l, font: MONO, size: 18 })],
    spacing: { before: 0, after: i === lines.length - 1 ? 160 : 0, line: 240 },
    indent: { left: 360 },
    shading: { type: ShadingType.CLEAR, fill: 'F2F2F2', color: 'auto' },
    keepLines: true,
  }));
}

function note(inlines) {
  return new Paragraph({
    children: [new TextRun({ text: 'Nota. ', font: FONT, size: 22, bold: true }), ...runs(inlines)],
    alignment: AlignmentType.JUSTIFIED,
    spacing: { before: 60, after: 160, line: 276 },
    indent: { left: 200, right: 200 },
    shading: { type: ShadingType.CLEAR, fill: 'F2F2F2', color: 'auto' },
    border: { left: { style: BorderStyle.SINGLE, size: 18, color: '666666', space: 6 } },
  });
}

function bannerHeader() {
  const data = fs.readFileSync(BANNER);
  const w = 816, h = Math.round(321 * 816 / 1424);  // ancho de página a 96 ppp
  return new Header({
    children: [new Paragraph({
      spacing: { before: 0, after: 0, line: 14 },
      children: [new ImageRun({
        type: 'jpg', data, transformation: { width: w, height: h },
        floating: {
          horizontalPosition: { relative: HorizontalPositionRelativeFrom.PAGE, offset: 0 },
          verticalPosition: { relative: VerticalPositionRelativeFrom.PAGE, offset: 0 },
          wrap: { type: TextWrappingType.NONE },
          behindDocument: true, allowOverlap: true, lockAnchor: true,
        },
      })],
    })],
  });
}

function footer(a) {
  return new Footer({
    children: [new Paragraph({
      alignment: AlignmentType.CENTER,
      children: [
        new TextRun({ text: `Anexo ${a.letra}. ${a.titulo} · Página `, font: FONT, size: 17, color: '333333' }),
        new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 17, color: '333333' }),
        new TextRun({ text: ' de ', font: FONT, size: 17, color: '333333' }),
        new TextRun({ children: [PageNumber.TOTAL_PAGES], font: FONT, size: 17, color: '333333' }),
      ],
    })],
  });
}

function titleBlock(a) {
  const kicker = new Paragraph({
    spacing: { before: 0, after: 60 },
    children: [new TextRun({ text: 'PROYECTO DE PRÁCTICA EMPRESARIAL · PLATAFORMA WEB OFICIAL DE ASOBARES CAPÍTULO QUINDÍO', font: FONT, size: 17, color: '333333' })],
  });
  const title = new Paragraph({
    spacing: { before: 0, after: 200 },
    children: [new TextRun({ text: `ANEXO ${a.letra}. ${a.titulo}`, font: FONT, size: 30, bold: true })],
  });
  const c1 = 2300, c2 = TEXT_W - 2300;
  const meta = new Table({
    width: { size: TEXT_W, type: WidthType.DXA },
    columnWidths: [c1, c2],
    layout: 'fixed',
    rows: a.meta.map(([k, v]) => new TableRow({
      cantSplit: true,
      children: [
        new TableCell({
          width: { size: c1, type: WidthType.DXA },
          borders: { top: noBorder, left: noBorder, right: noBorder, bottom: border('BFBFBF') },
          margins: { top: 50, bottom: 50, left: 60, right: 60 },
          children: [new Paragraph({ children: [new TextRun({ text: k, font: FONT, size: 19, bold: true })], spacing: { before: 0, after: 0 } })],
        }),
        new TableCell({
          width: { size: c2, type: WidthType.DXA },
          borders: { top: noBorder, left: noBorder, right: noBorder, bottom: border('BFBFBF') },
          margins: { top: 50, bottom: 50, left: 60, right: 60 },
          children: [new Paragraph({ children: [new TextRun({ text: v, font: FONT, size: 19 })], spacing: { before: 0, after: 0 } })],
        }),
      ],
    })),
  });
  return [kicker, title, meta, new Paragraph({ spacing: { before: 0, after: 120 }, children: [] })];
}

function build(key) {
  const a = ANEXOS[key];
  const ast = JSON.parse(fs.readFileSync(path.join(HERE, `anexo_${key}.json`), 'utf8'));
  const children = [...titleBlock(a)];
  for (const n of ast) {
    switch (n.type) {
      case 'heading': children.push(heading(n.text, n.level)); break;
      case 'para': children.push(para(n.inlines)); break;
      case 'note': children.push(note(n.inlines)); break;
      case 'bullets':
        n.items.forEach((it) => children.push(para(it, { numbering: { reference: 'vinetas', level: 0 }, after: 60 })));
        children[children.length - 1] = para(n.items[n.items.length - 1], { numbering: { reference: 'vinetas', level: 0 }, after: 140 });
        break;
      case 'numbered': {
        const ref = `num${numberedCounter++}`;
        numberedRefs.push(ref);
        n.items.forEach((it, idx) => children.push(para(it, { numbering: { reference: ref, level: 0 }, after: idx === n.items.length - 1 ? 140 : 60 })));
        break;
      }
      case 'table': children.push(table(n)); children.push(new Paragraph({ spacing: { before: 0, after: 80 }, children: [] })); break;
      case 'image': children.push(image(n)); break;
      case 'caption': children.push(caption(n.text)); break;
      case 'code': children.push(...code(n.lines)); break;
    }
  }
  const numbering = {
    config: [
      { reference: 'vinetas', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 560, hanging: 280 } } } }] },
      ...numberedRefs.map((ref) => ({ reference: ref, levels: [{ level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 560, hanging: 320 } } } }] })),
    ],
  };
  const doc = new Document({
    creator: 'Juan José Sua Gómez',
    lastModifiedBy: 'Juan José Sua Gómez',
    title: `Anexo ${a.letra}. ${a.titulo}`,
    subject: 'Práctica empresarial en Asobares Capítulo Quindío, Universidad Alexander von Humboldt',
    styles: {
      default: { document: { run: { font: FONT, size: 22 } } },
      paragraphStyles: [
        { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 24, bold: true, color: '000000' }, paragraph: { spacing: { before: 280, after: 100 }, keepNext: true, outlineLevel: 1 } },
        { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 22, bold: true, color: '000000' }, paragraph: { spacing: { before: 200, after: 100 }, keepNext: true, outlineLevel: 2 } },
      ],
    },
    numbering,
    sections: [{
      properties: { page: { size: { width: PAGE_W, height: PAGE_H }, margin: MARGIN } },
      headers: { default: bannerHeader() },
      footers: { default: footer(a) },
      children,
    }],
  });
  return Packer.toBuffer(doc).then((buf) => {
    const out = path.join(HERE, `anexo_${key}.docx`);
    fs.writeFileSync(out, buf);
    console.log('escrito', out, buf.length, 'bytes');
  });
}

let numberedCounter = 0;
const numberedRefs = [];
(async () => {
  for (const key of (process.argv.slice(2).length ? process.argv.slice(2) : ['e', 'f', 'g'])) {
    numberedCounter = 0; numberedRefs.length = 0;
    await build(key);
  }
})();

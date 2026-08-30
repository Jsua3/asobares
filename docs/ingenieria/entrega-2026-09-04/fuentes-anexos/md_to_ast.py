# -*- coding: utf-8 -*-
"""Convierte los markdown curados de los anexos en un AST JSON sencillo que render_docx.js vuelve .docx."""
import json, re, sys
from pathlib import Path
from PIL import Image

HERE = Path(__file__).parent
# Capturas del panel: docs/ingenieria/capturas (dos niveles arriba de este script en el repositorio)
_CANDIDATAS = [HERE.parent.parent / 'capturas', Path('/mnt/user-data/uploads/Asobares3/docs/ingenieria/capturas')]
CAPTURAS = next((p for p in _CANDIDATAS if p.is_dir()), _CANDIDATAS[0])

WIDTHS = {
    ('RF', 'HU'): [9, 11, 23, 33, 24],
    ('RNF', 'Requisito'): [8, 20, 34, 38],
    ('Fecha', 'Entorno'): [9, 12, 8, 12, 10, 8, 12, 10, 19],
    ('Estado', 'Definición'): [18, 82],
    ('Indicador', 'Valor'): [48, 52],
    ('Requisito', 'Situación'): [22, 40, 38],
    ('Métrica', 'Qué mide'): [16, 84],
    ('Medida contractual', 'Tope'): [26, 12, 32, 30],
    ('Permiso', 'Dirección (superadministrador)'): [34, 30, 36],
    ('Grupo', 'Qué contiene'): [16, 50, 34],
    ('Ruta', 'TTFB', 7): [22, 11, 11, 13, 14, 14, 15],
    ('Ruta', 'TTFB', 8): [20, 10, 10, 10, 12, 12, 13, 13],
}


def inlines(text):
    """Texto con `código`, *cursiva* y **negrita** -> lista de runs."""
    out = []
    pos = 0
    pattern = re.compile(r'(`[^`]+`|\*\*[^*]+\*\*|\*[^*]+\*)')
    for m in pattern.finditer(text):
        if m.start() > pos:
            out.append({'t': 'text', 'v': text[pos:m.start()]})
        tok = m.group(0)
        if tok.startswith('`'):
            out.append({'t': 'code', 'v': tok[1:-1]})
        elif tok.startswith('**'):
            out.append({'t': 'strong', 'v': tok[2:-2]})
        else:
            out.append({'t': 'em', 'v': tok[1:-1]})
        pos = m.end()
    if pos < len(text):
        out.append({'t': 'text', 'v': text[pos:]})
    return out


def split_row(line):
    cells = [c.strip() for c in line.strip().strip('|').split('|')]
    return cells


def parse(md_text):
    lines = md_text.splitlines()
    nodes = []
    i = 0
    while i < len(lines):
        line = lines[i]
        s = line.strip()
        if not s:
            i += 1; continue
        if s.startswith('### '):
            nodes.append({'type': 'heading', 'level': 3, 'text': s[4:].strip()}); i += 1; continue
        if s.startswith('## '):
            nodes.append({'type': 'heading', 'level': 2, 'text': s[3:].strip()}); i += 1; continue
        if s.startswith('```'):
            buf = []; i += 1
            while i < len(lines) and not lines[i].strip().startswith('```'):
                buf.append(lines[i]); i += 1
            i += 1
            nodes.append({'type': 'code', 'lines': buf}); continue
        if s.startswith('|'):
            rows = []
            while i < len(lines) and lines[i].strip().startswith('|'):
                rows.append(lines[i]); i += 1
            header = split_row(rows[0])
            align = [('right' if re.match(r'^-+:$', a.strip()) else 'left') for a in split_row(rows[1])]
            body = [split_row(r) for r in rows[2:]]
            widths = WIDTHS.get(tuple(header[:2]) + (len(header),)) or WIDTHS.get(tuple(header[:2]))
            if widths is None:
                widths = [round(100 / len(header))] * len(header)
            nodes.append({'type': 'table', 'header': [inlines(h) for h in header],
                          'rows': [[inlines(c) for c in r] for r in body], 'widths': widths, 'align': align})
            continue
        m = re.match(r'^!\[([^\]]*)\]\(([^)]+)\)', s)
        if m:
            src = m.group(2).replace('CAPTURAS/', '')
            path = CAPTURAS / src
            with Image.open(path) as im:
                w, h = im.size
            nodes.append({'type': 'image', 'path': str(path), 'w': w, 'h': h}); i += 1; continue
        if re.match(r'^\*Figura [A-Z]?\d+\..*\*$', s):
            nodes.append({'type': 'caption', 'text': s[1:-1]}); i += 1; continue
        if re.match(r'^(-|\*) ', s):
            items = []
            while i < len(lines) and re.match(r'^\s*(-|\*) ', lines[i]):
                items.append(inlines(re.sub(r'^\s*(-|\*) ', '', lines[i]).strip())); i += 1
            nodes.append({'type': 'bullets', 'items': items}); continue
        if re.match(r'^\d+\. ', s):
            items = []
            while i < len(lines) and re.match(r'^\s*\d+\. ', lines[i]):
                items.append(inlines(re.sub(r'^\s*\d+\. ', '', lines[i]).strip())); i += 1
            nodes.append({'type': 'numbered', 'items': items}); continue
        # párrafo (puede continuar en líneas siguientes hasta línea vacía)
        buf = [s]; i += 1
        while i < len(lines) and lines[i].strip() and not re.match(r'^(#|\||!\[|```|- |\* |\d+\. )', lines[i].strip()):
            buf.append(lines[i].strip()); i += 1
        text = ' '.join(buf)
        if text.startswith('Nota. '):
            nodes.append({'type': 'note', 'inlines': inlines(text[6:])})
        else:
            nodes.append({'type': 'para', 'inlines': inlines(text)})
    return nodes


if __name__ == '__main__':
    for key in sys.argv[1:] or ['e', 'f', 'g']:
        md = (HERE / f'anexo_{key}.md').read_text(encoding='utf-8')
        ast = parse(md)
        (HERE / f'anexo_{key}.json').write_text(json.dumps(ast, ensure_ascii=False, indent=1), encoding='utf-8')
        kinds = {}
        for n in ast:
            kinds[n['type']] = kinds.get(n['type'], 0) + 1
        print(key, kinds)

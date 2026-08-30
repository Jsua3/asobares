# -*- coding: utf-8 -*-
"""Genera los anexos E, F y G en PDF a partir de los markdown curados, con el membrete del documento,
tipografía Times New Roman y pie de página numerado. Sin emojis ni símbolos."""
import subprocess, sys, os, re, base64
from pathlib import Path
from playwright.sync_api import sync_playwright

HERE = Path(__file__).parent
BANNER = Path('/home/claude/work/x/word/media/image1.jpeg')
CAPTURAS = Path('/mnt/user-data/uploads/Asobares3/docs/ingenieria/capturas')

ANEXOS = {
    'e': dict(
        letra='E', titulo='Matriz de trazabilidad de requisitos y pruebas',
        corto='Matriz de trazabilidad de requisitos y pruebas',
        meta=[
            ('Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'),
            ('Docente asesor', 'César Augusto Granada Muñoz'),
            ('Tutora empresarial', 'Natalia Gutiérrez, directora ejecutiva de Asobares Capítulo Quindío'),
            ('Línea base de requisitos', 'ERS-ASOBARES-QUINDIO-V3.0 (RF-01 a RF-62; RNF-01 a RNF-14)'),
            ('Repositorio', 'github.com/Jsua3/asobares, rama main'),
            ('Versión', '1.1, corte al 25 de agosto de 2026 (última ejecución verificada)'),
        ]),
    'f': dict(
        letra='F', titulo='Manual de usuario del panel de administración',
        corto='Manual de usuario del panel de administración',
        meta=[
            ('Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'),
            ('Dirigido a', 'Dirección ejecutiva, secretaría y practicantes de Asobares Capítulo Quindío'),
            ('Requisito verificado', 'RNF-14, operación autónoma del panel por personal no técnico'),
            ('Capturas', 'Once capturas del panel real, tomadas el 18 de agosto de 2026 sobre la base de demostración'),
            ('Versión', '1.3, 27 de agosto de 2026'),
        ]),
    'g': dict(
        letra='G', titulo='Informe de medición de rendimiento',
        corto='Informe de medición de rendimiento',
        meta=[
            ('Elaboró', 'Juan José Sua Gómez, Programa de Ingeniería de Software, Universidad Alexander von Humboldt'),
            ('Requisito verificado', 'RNF-02. Origen: cronograma firmado por la dirección ejecutiva, «La página principal no debe tardar más de 2.5 segundos en cargar»'),
            ('Fecha de la medición', '18 de agosto de 2026'),
            ('Estado del repositorio', 'Rama main, revisión 4f15d24; recursos compilados con npm run build'),
            ('Versión', '1.1, 27 de agosto de 2026'),
        ]),
}

CSS = """
@page { size: Letter; margin: 18mm 20mm 20mm 20mm; }
@page :first { margin-top: 0; }
html, body { font-family: "Times New Roman", "Liberation Serif", serif; font-size: 10.5pt; line-height: 1.32; color: #000; }
body { margin: 0; }
.banner { display: block; width: calc(100% + 40mm); margin: 0 -20mm 6mm -20mm; }
.kicker { font-size: 9.5pt; letter-spacing: 0.02em; text-transform: uppercase; color: #333; margin: 0 0 2mm; }
h1 { font-size: 16pt; font-weight: bold; margin: 0 0 4mm; line-height: 1.2; }
table.meta { border-collapse: collapse; width: 100%; margin: 0 0 7mm; font-size: 9.5pt; }
table.meta td { padding: 1.2mm 2mm; border-bottom: 1px solid #bbb; vertical-align: top; }
table.meta td:first-child { width: 38mm; font-weight: bold; }
h2 { font-size: 12pt; font-weight: bold; margin: 7mm 0 2.5mm; page-break-after: avoid; }
h3 { font-size: 10.5pt; font-weight: bold; margin: 5mm 0 2mm; page-break-after: avoid; }
p { margin: 0 0 2.6mm; text-align: justify; }
ul, ol { margin: 0 0 2.6mm; padding-left: 6mm; }
li { margin-bottom: 1mm; text-align: justify; }
table { border-collapse: collapse; width: 100%; table-layout: fixed; margin: 1.5mm 0 4mm; font-size: 8.6pt; line-height: 1.25; page-break-inside: auto; }
th, td { border: 1px solid #666; padding: 1.1mm 1.6mm; vertical-align: top; text-align: left; }
th { background: #e9e9e9; font-weight: bold; }
tr { page-break-inside: avoid; }
thead { display: table-header-group; }
table.auto { table-layout: auto; }
td[align=right], th[align=right] { text-align: right; }
code { font-family: "Liberation Mono", "Courier New", monospace; font-size: 7.8pt; overflow-wrap: anywhere; }
pre { font-family: "Liberation Mono", "Courier New", monospace; font-size: 8.5pt; background: #f2f2f2; border: 1px solid #bbb; padding: 2mm 3mm; margin: 0 0 3mm; white-space: pre-wrap; }
pre code { font-size: 8.5pt; }
img { max-width: 100%; max-height: 105mm; width: auto; height: auto; display: block; margin: 3mm auto 1.5mm; border: 1px solid #999; page-break-inside: avoid; }
p > em:only-child { display: block; text-align: center; font-size: 9.2pt; margin-top: 0; }
figure { margin: 0; }
.nota { margin: 2mm 0 3mm; padding: 2mm 3mm; border-left: 3px solid #666; background: #f5f5f5; }
"""


def md_to_html(md_path: Path) -> str:
    out = subprocess.run(['pandoc', str(md_path), '-f', 'markdown', '-t', 'html', '--wrap=none'],
                         capture_output=True, text=True, check=True)
    html = out.stdout
    html = html.replace('CAPTURAS/', CAPTURAS.as_uri() + '/')
    # párrafos que empiezan por «Nota.» -> caja sobria
    html = re.sub(r'<p>Nota\. ', '<p class="nota"><strong>Nota.</strong> ', html)
    html = re.sub(r'<figcaption[^>]*>.*?</figcaption>', '', html, flags=re.S)   # el pie lo pone la línea en cursiva
    # anchos de columna según la cabecera de cada tabla
    def colgroup(widths):
        return '<colgroup>' + ''.join(f'<col style="width:{w}%">' for w in widths) + '</colgroup>'
    def add_widths(m):
        table = m.group(0)
        head = re.search(r'<thead>.*?</thead>', table, re.S)
        cols = re.findall(r'<th[^>]*>(.*?)</th>', head.group(0), re.S) if head else []
        names = [re.sub('<[^>]+>', '', c).strip() for c in cols]
        widths = None
        if names[:2] == ['RF', 'HU']:
            widths = [9, 11, 23, 33, 24]
        elif names[:2] == ['RNF', 'Requisito']:
            widths = [8, 20, 34, 38]
        elif names[:2] == ['Fecha', 'Entorno']:
            widths = [12, 18, 6, 8, 7, 6, 8, 8, 27]
        elif names[:2] == ['Estado', 'Definición']:
            widths = [18, 82]
        elif names[:2] == ['Indicador', 'Valor']:
            widths = [48, 52]
        elif names[:2] == ['Requisito', 'Situación']:
            widths = [22, 40, 38]
        elif names[:2] == ['Métrica', 'Qué mide']:
            widths = [16, 84]
        elif names[:2] == ['Medida contractual', 'Tope']:
            widths = [26, 12, 32, 30]
        elif names[:2] == ['Permiso', 'Dirección (superadministrador)']:
            widths = [34, 30, 36]
        elif names[:2] == ['Grupo', 'Qué contiene']:
            widths = [16, 50, 34]
        table = re.sub(r'<colgroup>.*?</colgroup>', '', table, flags=re.S)   # anchos de pandoc fuera
        if widths:
            table = table.replace('<thead>', colgroup(widths) + '<thead>', 1)
        else:
            table = table.replace('<table>', '<table class="auto">', 1)
        return table
    html = re.sub(r'<table>.*?</table>', add_widths, html, flags=re.S)
    return html


def wrap(key: str, body_html: str) -> str:
    a = ANEXOS[key]
    banner_uri = 'data:image/jpeg;base64,' + base64.b64encode(BANNER.read_bytes()).decode()
    meta = ''.join(f'<tr><td>{k}</td><td>{v}</td></tr>' for k, v in a['meta'])
    return f"""<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Anexo {a['letra']}</title>
<style>{CSS}</style></head><body>
<img class="banner" src="{banner_uri}" alt="">
<p class="kicker">Proyecto de práctica empresarial · Plataforma web oficial de Asobares Capítulo Quindío</p>
<h1>ANEXO {a['letra']}. {a['titulo']}</h1>
<table class="meta">{meta}</table>
{body_html}
</body></html>"""


def render(key: str):
    a = ANEXOS[key]
    html = wrap(key, md_to_html(HERE / f'anexo_{key}.md'))
    html_path = HERE / f'anexo_{key}.html'
    html_path.write_text(html, encoding='utf-8')
    pdf_path = HERE / f'anexo_{key}.pdf'
    footer = (f'<div style="font-family: Times New Roman, serif; font-size: 8pt; color: #333; width: 100%; '
              f'padding: 0 20mm; display: flex; justify-content: space-between;">'
              f'<span>Anexo {a["letra"]}. {a["corto"]}</span>'
              f'<span>Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
    with sync_playwright() as p:
        b = p.chromium.launch()
        pg = b.new_page()
        pg.goto(html_path.as_uri())
        pg.wait_for_load_state('networkidle')
        pg.emulate_media(media='print')
        pg.pdf(path=str(pdf_path), format='Letter', print_background=True, display_header_footer=True,
               header_template='<div></div>', footer_template=footer,
               margin={'top': '18mm', 'bottom': '20mm', 'left': '20mm', 'right': '20mm'},
               prefer_css_page_size=False)
        b.close()
    return pdf_path


if __name__ == '__main__':
    keys = sys.argv[1:] or ['e', 'f', 'g']
    for k in keys:
        out = render(k)
        pages = subprocess.run(['pdfinfo', str(out)], capture_output=True, text=True).stdout
        print(out.name, [l for l in pages.splitlines() if l.startswith('Pages')])

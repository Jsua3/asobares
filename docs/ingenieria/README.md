# Documentación de ingeniería — Plataforma Web ASOBARES Capítulo Quindío

Entregables de la **Fase 4** del cronograma firmado por la dirección ejecutiva. Todo lo que hay aquí es material del expediente del proyecto: se referencia desde el documento de práctica (capítulos 5 y 7, y anexos) y se entrega al gremio junto con el código fuente.

**Última actualización:** 19 de agosto de 2026 · repositorio en `1ff87d0` más el trabajo sin confirmar de esa fecha

> **Estado vigente del proyecto:** no está en esta carpeta. Vive en `material/estado.md` (se reescribe al cerrar cada sesión), con `material/encargo.md` (lo que el producto es y sus reglas) y `material/bitacora.md` (la historia). El punto de entrada es `material/prompt-maestro-laravel-filament.md`. Las cifras de esta tabla son las del 19 de agosto.

---

## Contenido

| Archivo | Qué es | Estado |
|---|---|---|
| `Informe de cumplimiento del cronograma - ASOBARES Quindio.docx` | Informe de entrega: contrasta cada exigencia del cronograma firmado, semana a semana, con lo ejecutado. Incluye brechas con responsable y fecha, y hoja de constancia para la firma de la tutora empresarial | ✅ Completo · 20 páginas |
| `matriz-de-pruebas.md` | Trazabilidad RF-01…RF-62 y RNF-01…RNF-14 contra las pruebas que los verifican, con los huecos declarados | ✅ Completo · suite **re-ejecutada y verificada** el 19 de agosto: **747 casos · 736 pasan · 11 omitidas · 0 fallos · 2.719 aserciones**. RF-19 (calendario) y RNF-12 (foco y objetivos táctiles) pasaron de hueco declarado a **verificados** |
| `medicion-de-rendimiento.md` | Verificación de RNF-02: 78 mediciones con Chromium real sobre las 12 rutas públicas, en móvil 4G y escritorio, en los dos temas | ✅ Completo · **la portada pinta en 972 ms contra un techo de 2.500 ms** |
| `base-de-datos/` | Entregable final «base de datos exportada»: esquema y volcado completo con datos de demostración, más el inventario de las 37 tablas | ✅ Completo |
| `runbook-despliegue.md` | Riesgo R-14: el procedimiento de despliegue en Laravel Cloud — paso humano bloqueante, secuencia de comandos, tabla de variables, límite de gasto, qué hacer si falla, y la hoja de «a nombre de quién quedó todo» | ⚠️ Técnicamente listo · **falta la cuenta institucional del gremio**, que no la puede crear un agente |
| `manual-de-usuario.md` | Manual del panel para personal no técnico (RNF-14). **Es la fuente: edite este archivo, nunca el PDF** | ✅ Completo · texto, las 11 capturas y el PDF |
| `Manual de usuario - Panel ASOBARES Quindio.pdf` | El manual en el formato que pide el cronograma. 24 páginas, tipografía de marca incrustada | ✅ Completo · se regenera con `node herramientas/manual-a-pdf.mjs` |
| `constancias/` | Los cuatro formatos de firma: aprobación del diseño (hito S2), constancia de capacitación (S8), retroalimentación del empresario con registro de hallazgos (S7) y **ampliación de alcance** (decisión sobre lo pedido en la revisión del 28 de agosto) | ✅ Emitidos · **falta diligenciarlos y firmarlos** |
| `herramientas/` | Los generadores de los PDF de esta carpeta. No es documentación de entrega: es lo que la produce | ✅ Sin dependencias de npm, usan el navegador ya instalado |
| `capturas/` | Las 11 capturas del panel, tomadas sobre la base de demostración en tema claro a 1440 px | ✅ Completo · **verificado el 19 de agosto que el trabajo de interfaz no las invalida**: la escala tipográfica nueva vive en `app.css`, que `/admin` no carga |
| `diagramas/` | Diagramas UML y BPMN en PNG | ✅ Completo |
| `diagramas/fuentes/` | Fuentes PlantUML editables de cada diagrama | ✅ Completo |

## Diagramas

| Archivo | Tipo | Qué modela |
|---|---|---|
| `01-casos-de-uso` | UML 2.5.1 · casos de uso | 9 actores y 21 casos de uso de la plataforma |
| `02-diagrama-contexto` | Contexto nivel 0 | La plataforma frente a sus 8 entidades externas |
| `03-bpmn-afiliacion` | BPMN 2.0 | Proceso de afiliación de un establecimiento |
| `04-bpmn-guia-normativa` | BPMN 2.0 | Construcción y uso de la guía normativa por municipio (módulo insignia) |
| `05-proceso-del-equipo` | BPMN 2.0 con carriles | Proceso de desarrollo del equipo de práctica, por fases del cronograma |
| `05b-ciclo-semanal` | BPMN 2.0 | Subproceso que se repite cada semana: planear → construir → demo del viernes → informe |
| `06-modelo-de-datos` | UML · diagrama de clases | Las 21 entidades implementadas y sus relaciones reales |

### Cómo regenerarlos

Los PNG se generan desde las fuentes de `diagramas/fuentes/`. **Edite siempre el `.puml`, nunca el PNG.**

```bash
java -jar plantuml.jar -charset UTF-8 -tpng diagramas/fuentes/*.puml
```

Generados con **PlantUML 1.2024.8**. Estilo acordado: fondo blanco, textos en español, acento `#EE4036`.

> ⚠️ `06-modelo-de-datos` refleja el esquema **realmente implementado**, verificado contra las migraciones y las relaciones declaradas en los modelos de Eloquent. Si cambia el esquema, este diagrama queda desactualizado y hay que rehacerlo — no es documentación de intenciones, es documentación de lo que existe.

## Lo que todavía no está en esta carpeta

Ninguno de estos es deuda técnica del equipo de desarrollo: dependen del **riesgo R-14** (la cuenta institucional, pendiente de la junta) o de sentarse con una persona.

- **Verificación en dispositivos reales** (RNF-01, RNF-07) — un Android y un iOS de verdad. El rendimiento ya está medido, pero con un viewport emulado y la red estrangulada, no con un teléfono. Es contenido explícito de la Semana 7.
- **Repetir la medición de rendimiento contra el dominio real** una vez exista despliegue. La cifra actual es contra `localhost` y no incluye la latencia de red hasta un servidor.
- **Diligenciar y firmar los cuatro formatos de `constancias/`.** Los documentos existen; lo que falta es la reunión, la sesión de capacitación y las firmas. Un formato en blanco no es evidencia.
- **Decidir el Acta 04 antes de escribir una línea de sus cuatro peticiones.** Es la única constancia con consecuencia sobre el código: mientras no esté marcada y firmada, OBS3-15 a OBS3-18 no se construyen. Aceptarlas en silencio es el camino a llegar al 22 de septiembre con todo a medias.
- **El bucket de objetos.** La aplicación ya sabe usarlo y está probada contra un almacén compatible con S3; lo que falta es crearlo con la cuenta del gremio. Ver el apartado 8 del runbook, y en particular el 8.3: **la política obvia del bucket abre los formatos de la guía normativa**, y eso hay que hacerlo bien a la primera.

**Cerrado el 18 de agosto de 2026:** la base de datos exportada y la medición de rendimiento.

**Cerrado el 19 de agosto de 2026:** el manual en PDF, los tres formatos de constancia, el runbook de despliegue con la aplicación validada contra PostgreSQL 17 real, y el almacenamiento de objetos probado de punta a punta.

## Documentos relacionados fuera de esta carpeta

- `../../README.md` — documentación técnica: cómo correr el proyecto, credenciales de demostración y cinco guiones de demostración de punta a punta.
- `../../material/prompt-maestro-laravel-filament.md` — punto de entrada de toda sesión; el historial de decisiones (§15 a §31) está en `../../material/bitacora.md` y el estado vigente en `../../material/estado.md`.
- `../superpowers/` — especificaciones y planes de trabajo de agentes. **No es documentación de entrega**; no lo referencie desde los anexos del documento de práctica.

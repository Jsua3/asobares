# Documentación de ingeniería — Plataforma Web ASOBARES Capítulo Quindío

Entregables de la **Fase 4** del cronograma firmado por la dirección ejecutiva. Todo lo que hay aquí es material del expediente del proyecto: se referencia desde el documento de práctica (capítulos 5 y 7, y anexos) y se entrega al gremio junto con el código fuente.

**Última actualización:** 18 de agosto de 2026 · repositorio en `4f15d24`

---

## Contenido

| Archivo | Qué es | Estado |
|---|---|---|
| `Informe de cumplimiento del cronograma - ASOBARES Quindio.docx` | Informe de entrega: contrasta cada exigencia del cronograma firmado, semana a semana, con lo ejecutado. Incluye brechas con responsable y fecha, y hoja de constancia para la firma de la tutora empresarial | ✅ Completo · 20 páginas |
| `matriz-de-pruebas.md` | Trazabilidad RF-01…RF-62 y RNF-01…RNF-14 contra las pruebas que los verifican, con los huecos declarados | ✅ Completo · suite **re-ejecutada y verificada** el 18 de agosto (599 casos, 0 fallos, 1.699 aserciones) |
| `medicion-de-rendimiento.md` | Verificación de RNF-02: 78 mediciones con Chromium real sobre las 12 rutas públicas, en móvil 4G y escritorio, en los dos temas | ✅ Completo · **la portada pinta en 972 ms contra un techo de 2.500 ms** |
| `base-de-datos/` | Entregable final «base de datos exportada»: esquema y volcado completo con datos de demostración, más el inventario de las 37 tablas | ✅ Completo |
| `manual-de-usuario.md` | Manual del panel para personal no técnico (RNF-14) | ✅ Texto **y las 11 capturas** del panel real · falta exportarlo a PDF |
| `capturas/` | Las 11 capturas del panel, tomadas sobre la base de demostración en tema claro a 1440 px | ✅ Completo |
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

- **Exportar el manual de usuario a PDF** para la entrega formal.
- **Verificación en dispositivos reales** (RNF-01, RNF-07) — un Android y un iOS de verdad. El rendimiento ya está medido, pero con un viewport emulado y la red estrangulada, no con un teléfono. Es contenido explícito de la Semana 7.
- **Constancia de capacitación** — el criterio contractual es que el personal publique un asociado, un evento y una noticia sin ayuda.
- **Repetir la medición de rendimiento contra el dominio real** una vez exista despliegue. La cifra actual es contra `localhost` y no incluye la latencia de red hasta un servidor.

Los tres últimos dependen del **riesgo R-14** (cuenta institucional del gremio pendiente de la junta) o de agendar con el personal: no son deuda técnica del equipo de desarrollo.

**Cerrado el 18 de agosto de 2026:** la base de datos exportada y la medición de rendimiento, que hasta esa fecha figuraban aquí como pendientes.

## Documentos relacionados fuera de esta carpeta

- `../../README.md` — documentación técnica: cómo correr el proyecto, credenciales de demostración y cinco guiones de demostración de punta a punta.
- `../../material/prompt-maestro-laravel-filament.md` — historial de decisiones de arquitectura y estado del proyecto (§15 a §23).
- `../superpowers/` — especificaciones y planes de trabajo de agentes. **No es documentación de entrega**; no lo referencie desde los anexos del documento de práctica.

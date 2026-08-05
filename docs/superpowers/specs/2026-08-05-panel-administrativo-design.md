# Diseño: Panel administrativo — sistema visual, tablero, observatorio y cartera

**Fecha:** 2026-08-05
**Estado:** aprobado en conversación de diseño; pendiente plan de implementación.
**Objetivo declarado:** demo para la directiva del 22 de septiembre de 2026.

## Contexto y problema

El panel `/admin` existe y funciona: 19 recursos, 2 páginas propias (Ajustes, Bitácora), 4 widgets, roles y policies, flujo de aprobación RF-37, bitácora, MFA e importador de cartera. Corre sobre el **tema de fábrica de Filament 4** — `resources/css/` solo contiene `app.css`, no hay tema generado para el panel.

Sobre esa base, una revisión crítica encontró:

1. **El tablero está diseñado para nadie.** `ResumenDelGremio` mezcla seis tarjetas de tres oficios distintos (contenido, plata, bandeja, bolsa). Dirección y secretaría abren la misma pantalla con la misma jerarquía; `canView()` esconde tarjetas por permiso pero no reordena el trabajo de nadie.
2. **Nada del tablero es accionable.** «Mensajes nuevos: 7» no lleva a ningún lado. El trabajo diario de la secretaría es una cola de pendientes, no un marcador.
3. **Falta en el tablero justo el requisito crítico.** El flujo de aprobación RF-37 vive únicamente en la campanita de notificaciones. Lo pendiente de aprobar debería ser lo primero que se ve.
4. **Dos de las tres gráficas nacen muertas.** `InscripcionesDelMes` grafica 30 días de inscripciones con eventos ~mensuales: línea plana en cero unos 28 días de cada 30. `AsociadosPorMunicipio` son 60 afiliados que se mueven una vez al mes — es una foto, no una serie. Una gráfica vacía no es neutra: enseña que el tablero no sirve.
5. **Falta la gráfica de la «petición estrella».** El acta de la Reunión 2 es literal: *«la gente no paga porque no sabe cuánto debe, entonces todo el mundo llama a Natalia»*. Hay una tarjeta de mora, pero no hay antigüedad de cartera, tendencia ni concentración de deuda.
6. **Las gráficas repiten un error de contraste que el sitio ya corrigió.** `#EE4137` está cableado en los cuatro widgets. El propio prompt maestro documenta que el Pub Red puro no alcanza AA sobre fondo claro — por eso el token `acento` vale `#B71F18` en claro y `#F27166` en oscuro. El panel se saltó esa lección.
7. **`InscripcionesDelMes` cuenta en memoria.** `->get()->groupBy()` trae todos los modelos para agruparlos por día. Irrelevante con 8 filas semilla, incorrecto a escala.
8. **«Animaciones y transparencia» hoy no tienen dónde vivir.** Sin tema propio de Filament no hay hoja de estilos del panel donde escribirlas.

Y dos reencuadres sobre lo que se pidió:

- **«Estudio de mercado» no es el Observatorio de la Nacional.** Las cifras que trae el prompt (12,65 % del empleo, informalidad 72,82 %…) las envía Asobares Colombia; son contenido del boletín, no cálculo de la plataforma. Lo que sí es propio y nadie más en el Quindío tiene es el dato que la plataforma ya captura: demanda de cargos por área y mes, oferta de talento contra esa demanda, cobertura de proveedores, presencia por municipio. Eso alimenta el propósito #1 re-ponderado por la directiva — **representatividad gremial ante instituciones**.
- **«Tiempo real» no es un problema de base de datos.** Desde que el CSV de la contadora entra, el asociado ve el saldo nuevo y el panel refresca por polling. La demora es humana: cada cuánto ella manda el archivo y cuándo la secretaría lo sube. El acta ya lo encuadró así: *«tratarla como integración de datos, no como promesa de módulo, hasta ver ese archivo»*.

## Decisiones tomadas

| Tema | Decisión |
|---|---|
| Objetivo | Demo para la directiva del 22 sept 2026. Prioriza impacto visual y gráficas que se vean vivas; nada de lo construido debe tirarse después. |
| Alcance de pantallas | Las cuatro pantallas señaladas (Tablero, Observatorio, Cartera, Moderación) son estrella → se resuelve con **un sistema de diseño**, no con cuatro diseños sueltos. |
| Tema | **Bicromático, ambos temas pulidos al mismo nivel.** Extiende la disciplina del sitio al panel. |
| Ambición visual | **Híbrido:** Tablero y Observatorio a medida (Blade + Livewire propios); Cartera, Moderación y los otros 17 recursos con Filament pulido. No se rehace navegación ni barra lateral. |
| Ingesta de cartera | **Importador con vista previa** en dos pasos (analizar sin escribir → confirmar → aplicar). |
| Motor de base de datos | **SQLite se queda.** Se añade una corrida de la suite contra PostgreSQL como red de seguridad para el despliegue. |
| Tiempo real | Polling de Filament + Livewire. **Sin Reverb ni websockets.** |
| Semilla | Parte del entregable: sin datos con forma temporal las gráficas mienten o están vacías. |

## Principios

1. **El color vive en un solo archivo, para el sitio y para el panel.** Cero hexadecimales cableados en vistas o widgets, con prueba de guardia que lo verifique.
2. **Un KPI que no es un enlace es un número muerto.** Toda cifra del tablero lleva a su listado filtrado.
3. **La cola de pendientes le pregunta a la policy, no al rol.** Mismo principio que ya ordena las notificaciones RF-37: quien puede aprobar *ese* modelo es quien lo ve pendiente.
4. **Transparencia en el cromo, opacidad en el dato.** El vidrio va en barra superior, tarjetas KPI, modales y fondos; nunca sobre una tabla de datos. Este proyecto ya peleó una batalla de contraste documentada (1,22:1 y 2,63:1) y no se reabre.
5. **Ninguna cifra se presenta sin su n.** Un gremio que lleva porcentajes sin tamaño de muestra ante la Alcaldía pierde credibilidad una sola vez.
6. **Se reutiliza la maquinaria existente:** tokens semánticos del sitio, `EstadoPublicacion`, policies por permisos, patrón bandeja, `AccionesDeAprobacion`, `ImportadorDeCartera`.

## §1 · Capa de tokens del panel

Los tokens semánticos que hoy viven en `resources/css/app.css` (`fondo`, `superficie`, `superficie-alta`, `fuerte`, `tinta`, `suave`, `tenue`, `apagado`, `linea`, `linea-fuerte`, `acento`, `acento-fuerte`, `marca-panel`, `exito*`, `aviso*`) se extraen a **`resources/css/tokens.css`**, importado por:

- `resources/css/app.css` (sitio público), y
- `resources/css/filament/admin/theme.css` (tema nuevo, generado con `php artisan make:filament-theme`).

El refactor es un no-op visual para el sitio: se mueven las declaraciones, no los valores.

### Tokens nuevos de la capa demo

Dos recetas, porque en claro y en oscuro **no es la misma técnica**:

| Token | Oscuro | Claro |
|---|---|---|
| `--vidrio-fondo` | blanco 4–6 % | blanco 70 % |
| `--vidrio-borde` | `rgba(255,255,255,.09)` | `rgba(11,9,10,.08)` |
| `--vidrio-desenfoque` | `blur(12px)` | `blur(12px)` |
| profundidad | resplandor rojo difuso | sombra suave en capas |

En claro el vidrio se construye con **sombra y borde**, no con luz. Copiar la receta oscura produce el efecto lavado.

### Integración con Filament

Los `--primary-*` de Filament se **mapean** a los tokens; no se reemplaza su sistema. Poppins —ya cargada por Vite vía `bunny()`, pesos 300/400/500/600/700/900— pasa a ser la tipografía del panel.

### Trampas conocidas que aplican aquí

- **Chromium no reinicia una `transition` cuando lo que cambia es la custom property detrás del valor**; la propiedad se congela en el color del tema anterior. El sitio ya apaga transiciones durante el cambio (con respaldo de `setTimeout`, porque en pestaña de segundo plano no corre `requestAnimationFrame`). El panel necesita el mismo tratamiento.
- **`app.css` escanea `storage/framework/views/*.php`**: hay que `php artisan view:clear` antes de compilar para desplegar, o el tamaño del bundle depende de qué vistas estén en caché.

### Deuda que cierra

El `#EE4137` cableado en `AsociadosPorMunicipio` e `InscripcionesDelMes` desaparece.

## §2 · Componentes reutilizables

Cuatro, y nada más — son los que levantan las cuatro pantallas estrella y arrastran los 17 recursos restantes.

| Componente | Qué hace |
|---|---|
| `<x-panel.kpi>` | Valor con conteo animado, delta contra periodo anterior, chispa (sparkline) SVG en línea, superficie de vidrio, y **clic al listado filtrado**. |
| `<x-panel.vidrio>` | Contenedor de vidrio con las dos recetas por tema. |
| `<x-panel.cola>` | Fila de pendiente: icono, texto, antigüedad, botón de acción directa. |
| `<x-panel.grafica>` | Envoltorio de Chart.js que **lee los colores de los tokens** vía `getComputedStyle(document.documentElement)` y se re-renderiza al cambiar de tema. |

Sin `<x-panel.grafica>` la gráfica conserva el color del tema anterior: los colores de Chart.js se pasan en JavaScript y no participan del cascade.

Todo el movimiento va bajo `@media (prefers-reduced-motion: reduce)`.

## §3 · Tablero de mando (a medida)

Sustituye el `Dashboard::class` de fábrica por una página propia. Tres bandas, en este orden:

### Banda 1 — Acción (arriba del todo)

La cola de pendientes: «3 vacantes esperando tu aprobación», «7 mensajes sin responder», «2 PQR con más de 5 días». Cada línea es un enlace al recurso **ya filtrado**. Es lo que hoy no existe y es el trabajo diario de la secretaría.

Los conteos se resuelven **preguntando a la policy**, no al rol (principio 3): quien puede publicar *ese* modelo es quien lo ve pendiente. Así secretaría ve las bolsas, dirección ve el contenido redactado por secretaría, y nadie se ve a sí mismo.

### Banda 2 — KPIs: cuatro tarjetas, distintas por rol

| Dirección | Secretaría |
|---|---|
| Recaudo del mes (+ delta contra el mes anterior) | Pendientes de moderación |
| Cartera en mora (+ tendencia a 6 meses) | Bandeja sin responder |
| Asociados publicados (+ altas del mes) | Postulaciones de la semana |
| **Cobertura territorial** | Fichas por revisar |

**Cobertura territorial** = municipios con al menos un asociado publicado, sobre los **12 del alcance firmado** (Armenia, Calarcá, Circasia, Filandia, Salento, La Tebaida, Montenegro, Quimbaya, Córdoba, Buenavista, Pijao, Génova). Hoy la semilla cubre 8. Es un KPI de representatividad, que es el propósito #1 re-ponderado por la directiva — no una métrica de vanidad.

**Una sola página.** El set se resuelve con un método que consulta `can()`; no hay dos páginas ni roles cableados.

### Banda 3 — Gráficas

Solo las que tendrán forma con la semilla de §6. `InscripcionesDelMes` no sobrevive tal como está: se reemplaza por **recaudo mensual acumulado**, que sí tiene serie. Las agregaciones se hacen en SQL (`selectRaw` + `groupBy`), no en memoria.

## §4 · Observatorio del gremio (página nueva, a medida)

Seis visualizaciones, todas calculadas sobre datos que la plataforma ya captura:

| Visualización | Fuente | Para qué |
|---|---|---|
| Demanda laboral por área y mes | `vacantes.categoria_cargo` + `created_at` | Qué cargos pide el sector y cuándo |
| **Oferta contra demanda por área** | `aspirantes` + `postulaciones` frente a `vacantes` | **El argumento institucional**: «el sector pidió N bartenders y el banco tiene M perfiles» es una petición de programa de formación |
| Mapa de calor por municipio | `asociados`, `vacantes`, `consultas_guia` (nueva, ver abajo) | Dónde está el gremio y dónde hay movimiento |
| Composición del sector | `categorias` | Bar / gastrobar / café / discoteca |
| Cobertura de proveedores | `proveedores.categoria_proveedor` | Dónde falta oferta |
| Salud financiera del sector | `transacciones`, `carteras` | Recaudo por mes a 18 meses y evolución de la tasa de mora |

`categoria_cargo` existe precisamente para esto: `cargo` es texto libre y no agrupa.

**Sin solapamiento con el tablero:** la banda 3 del tablero muestra recaudo del **año en curso** con propósito operativo («¿vamos bien este mes?»). El observatorio muestra la serie de **18 meses** con propósito analítico («¿cómo evoluciona la salud del sector?»). Son horizontes y preguntas distintas sobre la misma tabla.

### Adición de alcance: tabla `consultas_guia`

El mapa de calor necesita saber en qué municipios la gente consulta la guía de apertura, y **hoy ese dato no se registra**: no existe tabla de visitas y `GuiaController` no deja rastro. Se añade una tabla mínima:

| Campo | Notas |
|---|---|
| `municipio_id` | FK; qué municipio se consultó |
| `requisito_apertura_id` | nullable; se llena al descargar un formato |
| `created_at` | timestamp |

**Sin IP, sin agente de usuario, sin identificador de sesión.** Es un conteo agregado y anónimo, así que no es dato personal y no abre frente de Ley 1581 — decisión deliberada, no descuido. Se escribe desde `GuiaController::index()` y `::descargarFormato()`.

Justificación: «en qué municipios la gente quiere abrir un negocio» es la señal más valiosa que la página insignia puede producir para una conversación con una alcaldía, y cuesta una migración y dos líneas.

**Salvedad honesta:** la tabla nace vacía el 5 de agosto y el sitio no está en producción, así que para el 22 de septiembre solo tendrá lo que siembre §6. En la demo debe presentarse como lo que es.

### Exportación

Botón **«Descargar informe»** que arma un PDF con la marca. Ese objeto —no la pantalla— es lo que la dirección lleva a una reunión con Alcaldía o Cámara de Comercio. Es lo que convierte el observatorio de gráfica bonita en herramienta gremial.

### Honestidad estadística (requisito, no adorno)

Cada gráfica muestra su **n** y avisa cuando la muestra es chica («12 vacantes en el periodo — muestra pequeña»). Con 60 asociados en agosto de 2026, varias de estas series son todavía ruido estadístico y la interfaz tiene que decirlo.

## §5 · Cartera con vista previa

`ImportadorDeCartera::importar($ruta)` se parte en dos:

```
analizar($ruta): PrevisualizacionDeCartera   // no toca la base de datos
aplicar(PrevisualizacionDeCartera): ResultadoDeImportacion
```

`PrevisualizacionDeCartera` clasifica cada fila en: **nuevas** · **cambios** (con su delta) · **sin cruce** por nombre · **inválidas** (con el motivo).

El modal de `ListCarteras` pasa a dos pasos: arrastrar el archivo → tabla de diff con semáforos → confirmar.

Cierra el residuo del hallazgo G5 de la auditoría: hoy una fila que no cruza por nombre se pierde en un aviso de texto posterior al guardado; con la vista previa se ve **antes** de escribir.

Se conserva sin cambios la neutralización de fórmulas de `plantillaDeCartera()` (Excel ejecuta como fórmula toda celda que empiece por `=`, `+`, `-` o `@`).

## §6 · Semilla con forma

El seeder actual produce datos planos (8 inscripciones, 7 vacantes, 6 transacciones). Contra eso no se puede diseñar una gráfica. Se necesita:

- 18 meses de transacciones con estacionalidad (pico en diciembre, valle en enero–febrero)
- Vacantes repartidas en el tiempo y por área
- Postulaciones proporcionales a las vacantes
- Cartera con historia de pagos
- `consultas_guia` con concentración creíble en Armenia y cola en los municipios pequeños

**Va antes del tablero en el orden de trabajo** (§10). Los datos siguen siendo ficticios; los institucionales siguen siendo reales.

## §7 · Base de datos

**SQLite se queda.** Con 60 asociados y cinco usuarios de panel aguanta, y las migraciones se escribieron portables a propósito.

Lo que sí se hace ahora: **añadir a la suite una corrida contra PostgreSQL**, para que el día del despliegue no aparezcan diferencias de motor. PostgreSQL se justificará por **despliegue**, no por volumen: si el hosting es Laravel Cloud, el disco es efímero y un archivo SQLite se pierde en cada despliegue.

Nada de Reverb ni websockets: el polling de Filament (`databaseNotificationsPolling('30s')`) basta para cinco usuarios, y el «tiempo real» que pide el acta ya existe desde que el archivo entra.

## §8 · Fuera de alcance

- Migrar a PostgreSQL
- Websockets / broadcasting
- Rehacer navegación o barra lateral del panel
- Cuentas propias para artistas y proveedores (la columna `user_id` sigue sin usarse)
- **Cuenta propia para la contadora** — es la respuesta correcta de producción y el siguiente paso natural, pero en una demo es un login más
- Sincronización con hoja de Google — bloqueada por información que no se tiene: nadie ha visto qué usa la contadora
- **Exportaciones CSV/Excel en recursos con datos personales** (Aspirantes, Inscripciones, Mensajes) — la auditoría lo dejó marcado como hueco sin cubrir; abrirlo sin control de acceso sería empeorarlo

## §9 · Pruebas

- Guardia de tokens extendida a las vistas del panel: falla si reaparece un color cableado
- Cada agregación del observatorio con datos conocidos → cifra esperada
- `analizar()` **no escribe nada** en la base (prueba explícita)
- `aplicar()` produce el mismo resultado que el importador anterior (no-regresión)
- KPIs por rol: dirección ve recaudo, secretaría no
- La cola de pendientes cuenta solo lo que el usuario puede aprobar — verificado contra la policy, con la frontera negativa (secretaría no ve como pendiente lo que solo dirección aprueba)
- Las gráficas se re-renderizan al cambiar de tema conservando legibilidad AA en ambos
- `consultas_guia` registra al visitar y al descargar, y **no guarda IP ni agente de usuario** (prueba explícita: la fila solo tiene municipio, requisito y fecha)

## §10 · Orden de trabajo

| Fase | Contenido |
|---|---|
| **F1** | Tokens compartidos + tema de Filament + Poppins + los cuatro componentes |
| **F2** | Tabla `consultas_guia` + semilla con forma temporal |
| **F3** | Tablero de mando |
| **F4** | Cartera con vista previa |
| **F5** | Observatorio del gremio |
| **F6** | Pulido de los 17 recursos restantes |

Cortable por abajo: si el tiempo aprieta se corta F6 y luego F5, y lo entregado sigue en pie. F1 y F2 son prerrequisitos duros de todo lo demás.

**Nota para el plan de implementación:** son seis fases con dos naturalezas distintas. Conviene partirlo en **dos planes** —F1–F3 (cimientos y tablero) y F4–F6 (cartera, observatorio y pulido)— con un punto de revisión entre ambos, en vez de un plan único de veintitantas tareas.

## Riesgos conocidos

- **Proyector.** El tema oscuro puede aplastarse en la sala de la Cámara de Comercio. Mitigación: ambos temas están pulidos y el selector ya existe; se decide el mismo día según la sala.
- **Muestra pequeña.** Varias series del observatorio son ruido estadístico en agosto de 2026. Mitigación: el aviso de n es parte del diseño, no un añadido.
- **Actualizaciones de Filament.** El enfoque híbrido no sobrescribe vistas de Filament; el riesgo se concentra en el mapeo de custom properties, que es superficie pequeña.
- **Calendario.** Son ~7 semanas hasta el 22 de septiembre y la sección 17 del prompt maestro (normatividad real por municipio, con formatos oficiales) sigue abierta y es trabajo ajeno a este diseño.

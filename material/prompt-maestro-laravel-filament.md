# PROMPT MAESTRO — Plataforma Web ASOBARES Capítulo Quindío

_Punto de entrada de toda sesión. Es corto a propósito y **no lleva ninguna cifra, fecha de entrega ni pendiente**: lo que cambia cada semana está en `estado.md`; lo que el producto es y las reglas con las que se construye, en `encargo.md`; la historia de cómo se llegó hasta aquí, en `bitacora.md`. Los tres viven junto a este archivo en `material/`. Este archivo cambia solo cuando cambia una regla universal o el protocolo, y cada cambio se anota en §6._

---

## 1. El proyecto en un minuto

Plataforma web del gremio de la vida nocturna del Quindío (ASOBARES Capítulo Quindío), construida como **práctica empresarial de dos estudiantes** de Ingeniería de Software de la CUE. Es un monolito Laravel + Filament con sitio público, panel de administración y portal del asociado; **está construido, probado, desplegado en Laravel Cloud con contenido oficial del gremio, y el cliente ya lo vio y ya reclamó**. Si abres una sesión aquí no estás arrancando nada: estás continuando un producto que sirve páginas en internet.

Tiene **dos clientes con dos calendarios**, y confundirlos es el error más caro:

- **El gremio.** Le importa lo que se ve y lo que le sirve a sus afiliados. Su documento rector es el cronograma firmado por la dirección ejecutiva, que es la product owner; el directivo del capítulo manda sobre el producto. Su fecha de entrega está en `estado.md`.
- **La universidad.** Le importa el documento de práctica, que es **individual por estudiante**, se entrega los viernes antes de las 11:59:59 pm sin excepción (tarde = 0.0) y se califica por presentación tanto como por contenido. El docente asesor no revisa código.

Un avance técnico impecable que llegue tarde al documento pierde la mayor parte del corte. Un documento perfecto sobre un sitio que el gremio no aprueba pierde al cliente. Hay que servir a los dos.

## 2. Los tres archivos, y cuándo hace falta cada uno

| Archivo | Qué es | Cómo cambia | Cuándo lo lees |
|---|---|---|---|
| `estado.md` | La foto de hoy: qué se exige y cuándo, inventario por frente con de quién depende cada cosa, registro único de decisiones pendientes, deuda diferida, cifras medidas, lo siguiente en orden | **Se reescribe entero** al cerrar cada sesión que cambie algo. Sin tachones | **Siempre, entero.** Es corto |
| `encargo.md` | Lo que el producto **es**: cliente, stack, identidad, modelo de datos, panel, sitio, pagos, seguridad, semillas, reglas de contenido, decisiones que rigen, lo que no se hace, trampas por área, guiones | **Se edita en su sitio** cuando una decisión cambia el producto, con fecha y commit en la misma línea | Solo las secciones de lo que vas a tocar (tabla de abajo) |
| `bitacora.md` | La historia: notas de versión v2–v16 y §15–§31 con su numeración intacta; desde §32, una entrada por sesión | **Solo se anexa.** Nunca se corrige una entrada anterior | Solo cuando algo no cuadra, o para escribir la lección de una sesión |

**Si vas a tocar…**

| … | Lee de `estado.md` | Lee de `encargo.md` | `bitacora.md` |
|---|---|---|---|
| Cualquier cosa | Todo | §14 Lo que no se hace | No |
| Vistas, CSS, tema, hero, portada | Frente producto | §4 Identidad · §7 Sitio público · §12 Reglas de contenido · §15 Antes de tocar: tema | Solo si una guardia falla sin motivo aparente |
| Panel, recursos, policies, aprobación | Frente producto | §6 Panel · §15: Filament, bolsas y aprobación | §16, §24.6 si vas a escribir pruebas de permisos |
| Modelo de datos, migraciones, sembradores | Frente contenido · Deuda | §5 Modelo · §10 Semillas · §15: fechas | §31 si vas a sembrar en producción |
| Formularios públicos, datos personales, fotos | Frente datos personales | **§9 Seguridad, entero, obligatorio** | §15 (bitácora), §29.4 |
| Pagos, Bold, cartera | Decisiones pendientes | §8 Pagos · §15: pagos y CSV | §15 (bitácora) |
| Despliegue, variables, correo, bucket, dominio | Frente infraestructura | §15: Cloud, más `docs/ingenieria/runbook-despliegue.md` | §20, §29, §31.6 |
| Textos, contenido, guía normativa | Frente contenido · Decisiones pendientes | §2 El cliente · §12 Reglas de contenido | §17, §31 |
| El documento de práctica | Frente académico | Nada | §23.2: lo que el docente ha exigido, textual |
| Una ampliación que alguien pidió | Decisiones pendientes | §13 Decisiones que rigen · §14 | §27.4 |

El repositorio trae además dos habilidades propias en `.claude/skills/` —`laravel-best-practices` y `tailwindcss-development`—; la segunda es de lectura obligada antes de tocar vistas.

## 3. Protocolo de sesión

**Al empezar:**

1. `GIT_OPTIONAL_LOCKS=0 git log --oneline -5`. Puede haber otra sesión trabajando en este mismo directorio: ya pasó dos veces.
2. Abre `estado.md` y compara el commit de su encabezado con `HEAD`. Si el estado está atrás de `main`, lee las entradas de `bitacora.md` posteriores a ese commit y **actualiza el estado antes de nada**.
3. Lee `estado.md` entero. Elige en qué trabajar desde su inventario y su «lo siguiente», no desde la memoria de otra sesión.
4. Con la tabla del §2, abre solo las secciones de `encargo.md` que toquen a lo que vas a hacer.
5. La bitácora, únicamente si algo no cuadra.

**Al cerrar:**

1. La lista del §5, completa.
2. Reescribe `estado.md` con el commit nuevo en el encabezado: entra lo que se abrió, sale lo que se cerró, se mueven las decisiones respondidas.
3. Anexa la entrada §n a `bitacora.md`: fecha, qué se hizo con sus commits, qué se midió, qué se aprendió, qué entró y salió del estado.
4. Si cambió una regla del producto, edita `encargo.md` en su sitio con la nota fechada.

Una sesión que no cambia nada de estado —solo lee, o solo redacta un documento externo— no toca ninguno de los tres.

## 4. Reglas que aplican siempre

Sin excepción y sin importar qué se toque. La explicación larga está donde se indica.

1. **El alcance está congelado.** La ausencia de una funcionalidad no es incumplimiento mientras no esté en el cronograma firmado ni en la ERS; toda ampliación se registra por escrito **antes** de escribirse (`docs/ingenieria/constancias/`). No se acepta nada en silencio. (`encargo.md` §13; bitácora §24.5, §26.4, §27.8)
2. **Ninguna cifra sale de una suma.** Casos de prueba, migraciones, rendimiento: se miden ejecutando, el mismo día en que se citan, y sobre clon limpio si van a un documento. «La suite está en verde» tiene fecha de caducidad. (bitácora §24.6, §28.4)
3. **Escribir la prueba no basta: rompe el código a propósito y comprueba que se pone roja.** Van doce falsos verdes en el proyecto, todos escritos por el autor del plan; ninguno se vio leyendo. Para cada aserción, pregunta qué tendría que romperse para que fallara. (bitácora v7, §24.6, §30.6)
4. **Nada que exponga datos personales se publica sin autorización del titular ni se construye sin releer `encargo.md` §9.** Las fichas nacen en borrador; las fotos con personas no suben sin autorización de imagen; `material/nuevomaterial/` está en `.gitignore` y ahí se queda; el repositorio es público.
5. **En producción solo entra contenido que salga de un documento oficial del gremio**, con su fuente y su fecha. Ningún dato inventado, ningún costo sin verificar, ningún formato de relleno. (`encargo.md` §10; bitácora §31)
6. **Todo artefacto para el gremio nombra a las dos practicantes.** El documento de práctica de la universidad es **individual**: uno por persona, sin compartir redacción. (bitácora §24.1)
7. **El sitio abierto en el navegador dice cosas que la suite no ve.** Antes de dar por hecho algo visible, mírala con los datos reales puestos: en local el directorio está lleno de demostración y en producción está vacío. (bitácora §31.5)
8. **`GIT_OPTIONAL_LOCKS=0` siempre** desde sesiones remotas: el `git status` normal deja un `index.lock` huérfano que no se puede borrar desde aquí. Los huérfanos se renombran, no se ignoran, y los borra el dueño a mano.
9. **No se crean archivos de documentación nuevos** en el repositorio salvo que se pidan: el estado va en `estado.md`, la historia en `bitacora.md`, las reglas en `encargo.md`.
10. **No se cambian las dependencias, la estructura de carpetas ni la configuración de despliegue sin aprobación.** (`CLAUDE.md`)

## 5. Antes de decir «hecho»

1. `php artisan test --compact` con filtro sobre lo que tocaste; la suite entera antes de cerrar la sesión.
2. `vendor/bin/pint --dirty --format agent` si tocaste PHP.
3. `php artisan view:clear && npm run build` si tocaste vistas o CSS.
4. `GIT_OPTIONAL_LOCKS=0 git status --short` para ver qué quedó suelto.
5. **Rompe a propósito lo que acabas de proteger y comprueba que la prueba se pone roja.**
6. Reescribe `estado.md` con el commit nuevo en el encabezado.
7. Anexa la entrada de esta sesión a `bitacora.md`.

## 6. Registro de cambios de este archivo

- **1 ago 2026** — v1: el encargo original para construir la plataforma desde cero en una carpeta vacía, 14 secciones.
- **1–31 ago 2026** — v2 a v16: el encargo se ejecutó y el mismo archivo fue acumulando el estado y la historia del proyecto (§15–§31), hasta 1.800 líneas y 32 secciones que se corregían unas a otras. Todo ese texto está íntegro en `bitacora.md`.
- **1 sep 2026** — Partido en cuatro: este punto de entrada, `estado.md`, `encargo.md` y `bitacora.md`, con una regla de mantenimiento distinta para cada uno. Diseño y decisiones en `claude/diseno-division-prompt-maestro.md` del Project de Cowork y en la entrada §32 de la bitácora. `CLAUDE.md`/`AGENTS.md` y `docs/ingenieria/README.md` apuntan aquí.

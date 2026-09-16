# Estado vigente — Plataforma Web ASOBARES Capítulo Quindío

_La foto del proyecto hoy. **Se reescribe entero** al cerrar toda sesión que cambie algo: sin tachones, sin «superado», sin «esta línea decía». Lo que se cierra sale de aquí y queda contado en `bitacora.md`; lo que se decide sale de aquí y entra en «Decisiones que rigen» de `encargo.md`. Si el commit del encabezado está atrás de `main`, lee las entradas de bitácora posteriores a él y actualiza esto **antes** de tocar nada. `tests/Feature/GuardiaDelEstadoTest.php` comprueba que el commit del encabezado exista de verdad._

---

## ⚠️ LO PRIMERO DE MAÑANA — LA GUÍA ESTÁ DESPLEGADA PERO NO SEMBRADA

> El código de la guía de los doce municipios **está en producción desde hoy**. Los datos **no**. El `deployCommand` del entorno es `php artisan migrate --force` y nada más: empujar no siembra. Comprobado a las 21:50 sobre la URL pública: la guía sigue ofreciendo **un municipio y ocho entidades**.
>
> Dos comandos, en este orden, porque el segundo necesita los cuatro municipios que crea el primero:
>
> ```
> cloud command:run <entorno> --cmd='php artisan db:seed --class=MunicipioSeeder --force' -n
> cloud command:run <entorno> --cmd='php artisan db:seed --class=RequisitoAperturaSeeder --force' -n
> ```
>
> Los dos usan `updateOrCreate` y **no borran nada**: añaden 4 municipios y 143 fichas, y actualizan las 8 de Armenia. Después, la guía tiene que dar **12 municipios y 151 fichas**, todas con «Costo por confirmar», y las cuatro locales de cada municipio diciendo «Sin verificar contra la fuente oficial».
>
> ⚠️ **El `--cmd` no admite comillas dobles**: el CLI llega al servidor por `cmd.exe` y la tokenización se parte («Too many arguments»). Van con simples, y el PHP de dentro no puede llevar literales de cadena propios.
>
> **Lo que NO se siembra, y por qué:** `SettingSeeder` —o `ContenidoOficialSeeder`, que lo arrastra—. Producción tiene **109 claves** y la rama siembra **200**: faltan 91, entre ellas todos los textos que Ingrid volvió administrables en Directorio y Guía. El coste es que `updateOrCreate` **pisa cualquier ajuste que la oficina haya editado** desde el 3 de septiembre y no hay forma de saber cuáles tocaron (D-14). Ingrid pidió expresamente no correrlo el 11 sep. Se decide con eso delante, no después.

---

## ⚠️ ARREGLO PENDIENTE — CORREO SALIENTE (SMTP). SE HACE EL DÍA QUE SE TENGA DELANTE LA CUENTA DE GOOGLE DEL GREMIO

> **Qué está roto hoy.** La aplicación no tiene por dónde mandar correo: nunca se contrató el SMTP (D-07). No sale el código del segundo factor por correo (al panel se entra con la app TOTP), ni los acuses de PQR y de postulación, ni el aviso al establecimiento, ni los correos de vacante aprobada, vacante devuelta y ficha publicada. Desde `707e21e` y `07a3033` **nada se rompe** por eso: los formularios públicos guardan y avisan que el acuse no salió, y el panel publica y avisa en amarillo que el correo no salió; cada fallo queda reportado en el registro.
>
> **Por qué no sirve lo que decía el expediente (Resend).** El DNS de `asobares.org`, medido el 1 sep 2026, tiene el correo en **Google Workspace**, SPF solo para Google y Mailgun, DKIM de Google y de Brevo, y **DMARC `p=reject`**. Un remitente `@asobares.org` que salga por Resend, Postmark o una cuenta nueva de Brevo **lo rechazan los receptores** —no va a spam, no llega— mientras la Nacional, que administra ese DNS, no añada los registros del proveedor. El capítulo no administra ese DNS. `.env.staging.example` todavía trae `smtp.resend.com`: no copiarlo.
>
> **Qué se hace, con la cuenta `asobaresquindio@asobares.org` abierta (Natalia + Sua, media hora):**
>
> 1. En la cuenta de Google: activar la **verificación en dos pasos** y crear una **contraseña de aplicación** (Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones). Si la opción no aparece, el administrador del Workspace —la Nacional— la tiene bloqueada: pasar a la opción B.
> 2. En Laravel Cloud, variables del entorno: `MAIL_MAILER=smtp` (ya está), `MAIL_SCHEME=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_USERNAME=asobaresquindio@asobares.org`, `MAIL_PASSWORD=<la contraseña de aplicación, sin espacios>`, `MAIL_FROM_ADDRESS=asobaresquindio@asobares.org`, `MAIL_FROM_NAME` con el nombre del gremio. El remitente tiene que ser el mismo buzón que autentica: Google reescribe cualquier otro.
> 3. **Redesplegar.** Una variable nueva no llega al proceso vivo hasta el siguiente despliegue.
> 4. Probar, en este orden: radicar una PQR de prueba y comprobar que el aviso dice «Te enviamos el acuse» y que el acuse llega; entrar al panel con el código por correo; postularse a una vacante de prueba y ver los dos correos; aprobar una vacante de prueba y ver el aviso verde «Vacante publicada» sin el «pero». Mirar `cloud environment:logs` buscando `TransportException`.
> 5. Actualizar `.env.staging.example` y el runbook §6.3; pasar D-07 a «Decisiones que rigen» del encargo; **borrar este bloque** del estado.
>
> **Opción B**, si Google no deja crear contraseñas de aplicación: credenciales SMTP de la cuenta de Brevo o de Mailgun de la Nacional, que ya firman o están en el SPF del dominio. **Opción C**: la Nacional añade los registros de Resend. Las dos dependen de la Nacional; la A no.
>
> El límite de Workspace por SMTP es del orden de 2.000 correos al día por buzón; el sitio manda un puñado.

---

## 0. Medición

| | |
|---|---|
| Fecha | **Lunes 15 de septiembre de 2026** (Bogotá). El despliegue es de las 21:48; la comprobación contra la URL pública, de las 21:50 |
| **Dónde vive este archivo** | En `main`, medido sobre `2d86359`, que es lo que sirve producción |
| `main` | `2d86359` · ✅ **Al día con `origin/main` y desplegado.** Hoy entraron de una vez **178 commits y 437 archivos** (+16.224 / −7.125): la capa visual de Ingrid, la guía de los doce municipios y el salto a Filament 5. El push se comprobó contra el remoto con `git ls-remote`, no por la salida del `push`. ⚠️ El hash va **primero y entre acentos graves**: `GuardiaDelEstadoTest` lee esta fila con `/^\|\s*`main`\s*\|\s*`(hash)`/`, y adornarla por delante la deja sin nada que comprobar |
| Ramas | ✅ **Nada sin fusionar de nuestro lado.** ⚠️ `origin/cierre/visual03-directorio-login` (`e703485`) **quedó huérfana**: sus tres commits posteriores al corte se reescribieron para quitarles la firma de Cursor, así que los de `main` **no son los suyos**. Si Ingrid sigue trabajando ahí y alguien vuelve a fusionar, la firma regresa y la historia se duplica. **Su rama tiene que ponerse sobre `main`.** El respaldo con la historia firmada está en la rama local `respaldo/integracion-con-firma` |
| Suite sobre lo desplegado | **1.615 casos · 1.615 pasan · 0 fallos · 8.460 aserciones · 693 s**, sobre Filament 5.8.2 y Livewire 4.4.5. La referencia previa sobre Filament 4.12.8, con la fusión y los tres arreglos ya dentro, dio **las mismas 8.460 aserciones en 967 s**: el salto no cambió ni una comprobación y la suite quedó **un 28 % más rápida**. **134 archivos de prueba · 1.251 métodos · 47 migraciones · 634 confirmaciones** |
| Quién midió | Sesión local de Claude Code con Sua, en la máquina de Sua (PHP 8.5), sobre un worktree aislado en `.claude/worktrees/visual03` |
| Producción | `https://asobares-production-0jhdcz.laravel.cloud` · ✅ **sirve `2d86359`**, en el aire **60 s después del push**. Comprobado **por contenido y no por el repositorio**, que es lo que importa porque Cloud compila sus propios activos: el HTML del panel sirve `?v=5.8.2.0` —o sea Filament 5 de verdad— y `/img/directorio/hero-directorio.png`, que no existía antes, pasó de 404 a 200. Siete rutas en **200** entre 0,77 y 1,98 s: portada, directorio, guía, empleo, aliados, `/admin/login` y `/mi-cuenta/entrar` |
| **PHP del entorno** | ✅ **Resuelto de hecho.** `composer.json` exige `^8.4.1` desde el cambio de Ingrid y el despliegue **construyó sin error**, así que el entorno corre 8.4 o superior. Era el riesgo que no se podía comprobar desde esta máquina —el CLI de Cloud no está en el PATH— y lo respondió el propio despliegue |
| **Expediente** | Al día: esta foto y la **§55 de `bitacora.md`**, que cuenta la fusión, la guía y el salto en ocho apartados. ⚠️ La **matriz de trazabilidad** sigue con las cifras del 8 sep: hay que rehacerla antes de citarla. ⚠️ El **Acta 09** (D-47) sigue sin emitir, y ahora tiene que cubrir también el salto de versión |

## 1. Qué se exige y cuándo

| Fecha | Qué | Quién lo exige |
|---|---|---|
| **22 sep — quedan 7 días** | **Entrega dura al gremio** | Cronograma firmado |
| **Vencido: vie 11 sep, 11:59:59 pm** | ⚠️ **Confirmar que se envió.** El expediente no registra envíos y los archivos siguen **sin versionar** en `docs/ingenieria/entrega-2026-09-04/`. **Presentación de la socialización final, 7–10 diapositivas.** Construida el 9 sep, 10 diapositivas sobre la plantilla oficial del gremio, notas del orador y el recorrido de 36 s embebido. **Falta que Sua la ensaye y decida si envía también el PDF** | Docente asesor (correo del 7 sep) |
| **Vencido: vie 4 sep, 11:59:59 pm** | Documento de práctica corregido según la revisión CG del 31 ago (`docs/ingenieria/Semana 7 - … correccion.docx`, `1e3b365`). **El repositorio no registra si se envió**: confirmarlo. Y sigue describiendo los RF de proveedores como públicos, sin el banco de talento (§2.5), y **describe la barra A**, que no es la desplegada | Docente asesor. Tarde = 0.0 |
| **Vencida sin fijar: era para el vie 11 de sep** | ⚠️ **La ventana se cerró y la fecha nunca se pidió.** **Segunda demostración con la capa visual levantada**, sobre la URL pública, en el teléfono del directivo (D-20). Ahora hay más que enseñar que nunca: el Directorio rediseñado y la guía de los doce **si se siembra antes** | Directivo del capítulo (`R24 04:52`) |
| 14 – 18 sep | Dominio, SSL, capacitación y Acta 02 firmada; manual actualizado | Cronograma firmado (S8) |
| Por confirmar | Fecha de cierre del corte 3 y del PDF final a `proyectosing@cue.edu.co` | Docente asesor |

## 2. Inventario por frente

### 2.1 Producto — los catorce señalamientos del gremio (acta 3, 28 ago)

| Ref. | Estado | Qué falta, y de quién depende |
|---|---|---|
| OBS3-01 a 06, 08, 09, 12, 13, 14 | ✅ Cerrados | — |
| OBS3-07 (fotos y video del gremio) | ⚠️ **Se movió hoy, y en las dos direcciones.** El video está en el sitio. **Llegaron fotos reales de siete establecimientos** (`material/sep15material/`, ignorada): Break, Donde Alejo, El Ruedo, Garden, Icónico, Indianápolis, San Basilio. Y a la vez **se publicaron cuatro superficies con imágenes generadas por IA** (ver §2.4) | Pies de foto y **autorización de imagen (D-03)**, que ahora es lo único que separa a las fotos reales de sustituir a las sintéticas |
| OBS3-10 (enlaces al trámite) | ⚠️ **Código puesto y los dos primeros enlaces buenos existen**, pero en la base, no en producción hasta sembrar. De veinticinco enlaces del archivo del gremio, **dieciocho abren la portada de la alcaldía y no el trámite**, así que no entran | Las **7 URL de trámite de Armenia** (D-04) siguen sin llegar |
| OBS3-11 | ⚠️ Código puesto (todo editable) | El **texto propio** de «Quiénes somos» y nombres/cargos (D-05) |
| OBS3-15 a 18 | ❌ Congelados | **Acta 04 sin firmar** (D-01) |

De catorce, **doce cerrados y dos vivos** (10 y 11); ninguno se cierra escribiendo código.

**La capa visual de Ingrid, desplegada hoy.** Directorio rediseñado con su hero, textos administrables de Directorio y Guía, conteos reales, «Mi Cuenta» con pantalla de acceso propia, «Abre tu negocio» administrable con capa visual y animación, permisos de la bolsa de empleo y un repaso de responsive a 390/768/1024/1280/1440. Entró con **dos conflictos y los dos eran de comentario**. **Abierto:** que Sua e Ingrid miren el panel y el Directorio **con ojos** sobre la URL pública, que es lo que ninguna prueba cubre.

**El salto de versión (hoy).** Filament **4.12.8 → 5.8.2** y, con él, Livewire **3.8.3 → 4.4.5**, Laravel **13.23 → 13.32** y hamcrest **2 → 3**. Revierte una decisión escrita del §13 del encargo —«Filament 5 se descartó por demasiado nuevo para la entrega del 22 de septiembre», 3 ago— y se tomó con las dos caras delante. La herramienta oficial de migración **no cambió ni un archivo**, y de 1.615 casos **falló uno**, que era de una guardia que medía por un rastro que Livewire dejó de dejar. **Abierto:** el panel en un teléfono real bajo la versión nueva, y el tacto del resorte del riel.

### 2.2 Contenido

| Qué | Estado | Qué falta, y de quién depende |
|---|---|---|
| **Guía normativa** | ✅ **De 1 municipio a 12 en el código; 151 fichas.** ⚠️ **En producción sigue en 1 y 8 fichas** hasta que se siembre (bloque de arriba) | Que la dirección **confirme por escrito** que esta es la versión vigente antes de publicarla como definitiva; el archivo trae las doce filas en «Pendiente» y por eso las fichas locales salen sin fecha (D-21) |
| **Los ajustes sembrados** | ⚠️ Producción tiene **109**; la rama siembra **200**. Faltan **91**, entre ellos los textos administrables nuevos de Directorio y Guía | Correr `SettingSeeder` **con visto bueno aparte, porque pisa ediciones de la oficina** (D-14). Ingrid pidió no correrlo el 11 sep |
| **Franja «El gremio en cifras»** (D-25, Acta 05) | ✅ Código en producción, vacía de fábrica | **Firmar el Acta 05**; fijar las cuatro cifras; teclearlas |
| Portada | ✅ Todo texto editable | Las 19 fotos autorizadas sin colocar |
| Aliados | ⚠️ 23 del catálogo **nacional**, ninguno del Quindío | Los 18 departamentales de las láminas 15–16 necesitan **dos** cosas: los nombres escritos y **qué le da cada uno al afiliado** (`detalle_convenio`), que la presentación no trae (D-18) |
| Beneficios e iniciativas | ✅ 5 y 5, de documento oficial | **Clasificarlos por alcance** (D-39) |
| «Quiénes somos» | ⚠️ Menos provisional desde el 10 sep: propuesta de valor, lema y presidente ya son los del gremio | Redacción propia de historia, qué hacemos y visión, más cargos y junta (D-05) |
| Directorio | ⚠️ **0 fichas publicadas** en producción, comprobado hoy. La base del gremio vive en `D:/Sua_Files/material-asobares/` (48 y 41 filas), **fuera del árbol** | Importar con `asociados:importar`; **autorización de cada titular** |
| Boletín laboral, formulario oficial de registro, certificado de afiliación | ❌ Sin publicar | D-18 |
| Cifra pública de afiliados | ⚠️ El sitio dice 60; la base 48; el directivo 60 | Natalia (D-18) |

### 2.3 Infraestructura

| Qué | Estado |
|---|---|
| Sitio | ✅ **200** sobre PostgreSQL 17, **47 migraciones**, sirviendo `main`. La migración nueva de hoy (`anade_estado_y_orden_a_municipios`) corrió sola en el despliegue |
| Despliegue de hoy | ✅ **21:48 push, 21:50 sirviendo.** 178 commits, 437 archivos, una migración, 60 s. Comprobado por contenido servido (§0) |
| **`main`** | ✅ **Al día.** Recordatorio que no caduca: **el push a `main` despliega solo**, no hay paso intermedio, y **no siembra nada** |
| Correo saliente (SMTP) | ❌ **Sin contratar: bloque de arriba** |
| Bucket | ❌ Sin crear; condiciona fotos sin moderar y formatos oficiales (D-13) |
| Dominio propio | ❌ Semana 8 (D-09) |
| Indexación | ✅ Resuelta (D-08). `SITIO_INDEXABLE` cerrada de fábrica gobierna `robots.txt` y la etiqueta del layout. Se abre el día del dominio propio poniéndola en `true` y redesplegando |
| Scheduler | ✅ Encendido el 9 sep; `schedule:list` devuelve las tres purgas en producción |
| Rendimiento contra la URL | ⚠️ Medido hoy solo por tiempo total de respuesta (0,77–1,98 s en siete rutas, en caliente). **La medición completa en frío sigue siendo la del 5 sep**: portada 2,97 s |
| Dispositivos reales (RNF-01, RNF-07) | ✅ La parte pública, cerrada el 11 sep. ⚠️ Siguen sin verse **iOS/Safari** y **el riel del panel en el teléfono** — y ahora, además, **todo el panel bajo Filament 5** |
| Repositorio | ⚠️ `Jsua3/asobares`, público. **Cero PR y cero CI.** `INGRIDMONWARTSKI` con `write`, sin segundo administrador (D-12) |

### 2.4 Datos personales

| Qué | Estado |
|---|---|
| **Imágenes generadas por IA, ahora públicas** | ⚠️ **Frente abierto desde hoy, por decisión tomada con los datos delante.** Cuatro superficies: el hero del Directorio, el de «Abre tu negocio», los tres «establecimientos» del banco visual de la portada y el respaldo de publicidad. Los originales traen manifiesto **C2PA de «OpenAI Media Service API»** con `digitalSourceType` de medio generado por algoritmo entrenado; **los WebP derivados ya no lo traen**, porque la conversión lo borra. La §5 del prompt maestro dice que en producción solo entra contenido de documento oficial del gremio. **Se sustituyen con las fotos reales que llegaron hoy en cuanto tengan autorización de imagen** |
| **Fotos de siete establecimientos reales** | ⚠️ Llegaron el 15 sep en `material/sep15material/` (ignorada; 27 archivos, 17,4 MB medidos). Hay **personas identificables**, logotipos de terceros y un certificado de afiliación | **Autorización de imagen de cada titular (D-03)** |
| **Banco de talento visible para los afiliados** | ⚠️ Los 7 perfiles registrados aceptaron con una versión anterior de la política (D-27) |
| Fichas de asociados | ✅ Nacen en borrador; **cero publicadas en producción**, comprobado hoy |
| Base de establecimientos (`.xlsx`) | ✅ **Fuera del árbol**; `DatosInternosDelAsociadoTest` en verde |
| Política de tratamiento de datos | ❌ D-19; bloquea al banco de talento |
| `nuevomaterial/`, `materialnoviembre/`, `sep15material/` | ✅ Las tres en `.gitignore`. La última se comprobó con `git check-ignore` sobre la carpeta, una foto y el Excel |
| Retención automática | ✅ Cerrada el 9 sep; los tres simulacros dieron 0, 0 y 0 porque ningún plazo ha vencido |

### 2.5 Académico

| Qué | Estado |
|---|---|
| Corte 1 | ✅ 5.0 |
| Corte 2 | ✅ Entregado a tiempo el 21 ago |
| Corte 3 (60 %) | ⚠️ Documento corregido en el repositorio. **Sin confirmación de envío.** Sigue describiendo los RF de proveedores como públicos, sin el banco de talento, y **describe la barra A** |
| Constancias | ✅ Acta 01, Formato 03, planeador. ❌ Acta 02 (S8), **Acta 04 y Acta 05 sin firmar**, **Acta 06 sin emitir** (D-26), **Acta 09 sin emitir** (D-47) |

## 3. Registro único de decisiones pendientes

Cuando una se responde, sale de aquí y entra fechada en «Decisiones que rigen» de `encargo.md`. **Hoy salieron dos**, las dos tomadas por Sua con las caras delante: **subir a Filament 5** revirtiendo lo escrito el 3 de agosto, y **desplegar con las imágenes generadas por IA dentro**. Las dos quedan en `encargo.md` §13 y contadas en la bitácora §55.

| ID | Decisión | Dueño | Pedida | Respondida |
|---|---|---|---|---|
| **D-48** | **La rama de Ingrid, huérfana.** Sus tres commits posteriores al corte se reescribieron sin la firma de Cursor, así que `origin/cierre/visual03-directorio-login` ya no es lo que hay en `main`. Tiene que rehacer su rama sobre `main` antes de seguir, o la próxima fusión duplica historia y devuelve la firma | Ingrid + Sua | **15 sep** | — |
| **D-49** | **Las fotos reales contra las sintéticas.** Llegaron siete establecimientos de verdad y en producción hay cuatro superficies generadas por IA. Sustituirlas exige autorización de imagen y decidir qué foto va en qué hueco | Natalia + Ingrid | **15 sep** | — |
| **D-21** | **Municipios 2 a 12 de la guía** | Natalia | 1 sep | **Materia prima entregada el 15 sep** y código hecho. Falta que la dirección **confirme por escrito** que es la versión vigente: el archivo trae las doce filas en «Pendiente» |
| **D-47** | **Acta 09 — ampliación de alcance de la capa visual**, y ahora también del salto de versión. El §9 del encargo exige registrar por escrito toda ampliación **antes** de codificarla | Sua | 9 sep | — |
| **D-46** | **Los dos tokens compartidos de la capa visual** (`--asb-apagado` y `--asb-accion`): cambian cómo se ven Afiliación, Publicidad, Directorio y Mi Cuenta, que son suyos. El 4,5:1 de RNF-12 no es negociable | Ingrid | 9 sep | — |
| **D-44** | **¿Cuál es la cuota vigente?** La cartera implica **$70.000/mes**; el formulario oficial dice **$30.000 los dos primeros meses y $50.000 desde el tercero**. Nada se siembra ni se publica hasta que Natalia lo diga por escrito | Natalia | 9 sep | — |
| **D-45** | **Los 41 nombres de la cartera contra la base de asociados**: hoy no cruza ninguno por *slug* | Natalia + Sua | 9 sep | — |
| **D-38** | **La lista real de la auditoría funcional**: 2 FAIL y 3 NOT TESTED sin nombrar | Ingrid | 8 sep | — |
| **D-39** | **Clasificar los cinco beneficios por alcance** | Natalia | 8 sep | — |
| **D-40** | **Aprobar los perfiles del banco de talento**, que nacen pendientes | Natalia + secretaría | 8 sep | — |
| **D-41** | **El reparto que propone el plan de trabajo invierte el registrado** | Sua + Ingrid | 8 sep | — |
| **D-31** | **`prefers-reduced-transparency` en un equipo real**: Playwright acepta la emulación y no la aplica | Sua | 5 sep | — |
| **D-32** | **Idiomas como subsistema propio.** Ampliación de alcance: acta antes de codificar | Natalia + Sua | 3 sep | — |
| D-26 | **Acta 06** de la ampliación de las bolsas; retirar o reubicar `docs/ingenieria/decisiones/` | Sua + Ingrid | 3 sep | — |
| D-27 | **Política de tratamiento y los 7 perfiles** que aceptaron con otra versión | Natalia + aliado jurídico | 3 sep | — |
| D-28 | **Alta de credenciales de afiliado**: sin ella nadie ve proveedores ni banco de talento | Natalia + Sua | 3 sep | — |
| D-29 | **`hero_frase_corta`** y la **banda de tres videos** que promete piezas inexistentes | Ingrid | 3 sep | — |
| D-01 | Firma del **Acta 04** y del **Acta 05** con sus cuatro cifras | Natalia + directivo | 30 ago / 1 sep | — |
| D-03 | **Pies de foto y autorización de imagen**. El 15 sep crece otra vez: siete establecimientos con personas identificables | Natalia | 26 ago | Autorización de las 19 del gremio, 1 sep ✅ · el resto: — |
| D-04 | **Las 7 URL de trámite** de Armenia (OBS3-10) | Natalia / Alcaldía | 28 ago | — |
| D-05 | **Texto propio de «Quiénes somos»**; nombres y cargos | Natalia + Nacional | 5 ago / 28 ago | Materia prima completa el 9 sep; falta aprobación |
| D-06 | **Logos** de aliados en buena resolución y cuáles aplican en el Quindío | Natalia | 31 ago | Institucional resuelto el 9 sep |
| D-07 | **SMTP con el correo del gremio** (bloque de arriba) | Natalia + Sua | 15 ago | Buzón conocido; falta la credencial |
| D-09 | **Dominio propio** | Natalia | 5 ago | — |
| D-10 | **Pasarela**: «solo Bold» por escrito; PSE o QR | Natalia + contadora | 28 ago | — |
| D-11 | **Cartera**: mecanismo de actualización del archivo de la contadora | Luisa + Natalia | 28 ago | Archivo recibido el 9 sep |
| D-12 | **Titularidad de la infraestructura**: facturación, Natalia miembro, segundo admin en GitHub | Sua + Natalia | 30 ago | — |
| D-13 | **Bucket y fotos pendientes** | Sua | 31 ago | — |
| D-14 | **Marca de procedencia en el contenido sembrado**, y qué ajustes tocó la oficina | Sua | 1 sep | — |
| D-18 | **Confirmaciones del plan del material**: condiciones de los 18 aliados departamentales | Natalia | 26 ago | Aliados de hecho 9 sep ⚠️ · condiciones: — |
| D-19 | **Política de tratamiento de datos** (ver D-27) | Natalia / aliado jurídico | 5 ago | — |
| D-20 | **Fecha de la segunda demostración** | Directivo + Natalia | 28 ago | — |
| D-43 | **¿Vuelve la campana del panel?** | Sua + Natalia | 9 sep | — |

## 4. Deuda diferida a propósito

No se «arregla de paso»:

- **El filtro de municipios del Directorio lista todos, tengan o no fichas.** Con la siembra de la guía pasa de ocho a **doce opciones**, y cuatro de ellas —Buenavista, Córdoba, Génova y Pijao— tienen **cero establecimientos**. Está anotado en el sembrador y se arregla allí, no en la guía, que sí filtra bien.
- **La guardia del hero medía el marcado con una expresión regular** y se rompió en cuanto el contenedor ganó un atributo. Ya está sobre XPath, pero **quedan más guardias de ese tipo en la suite**: cualquier `assertMatchesRegularExpression` sobre HTML es candidata al mismo fallo.
- **Ninguna prueba ve una imagen generada por IA.** La procedencia se comprueba leyendo los bytes del **original** —el WebP ya perdió el manifiesto— y eso no está automatizado. Si entra otra tanda, se mira a mano.
- **`hamcrest` subió a 3.0.0** con el salto. No lo usa ninguna prueba nuestra directamente; entra por Mockery.
- **Del panel en el teléfono:** el tacto del resorte solo lo cierra un aparato de verdad; se ajusta con `ARRASTRE` y `AMORTIGUACION`.
- **De la barra pública B:** la transición de `gap` cuesta un reflow por fotograma durante 620 ms; `$rol`/`$prefijoRol` son dos `match` que recalculan lo mismo.
- **Preexistente:** `consultaSistema.addEventListener('change', aplicarTema)` pasa el evento como `preferenciaForzada` (funciona por accidente de la comparación); tabular hacia el header `sticky` estando desplazado devuelve la página al tope (Chromium).
- **El correo de ficha de bolsa publicada enlaza a `/proveedores`**, que ya no nombra al proveedor. Se arregla cuando haya SMTP.
- `@alpinejs/collapse` importado sin consumidor. El consecutivo de PQR bajo concurrencia falla cerrado. No existe `lang/` (D-32).
- Cuatro `index.lock.huerfano*` y `.git/huerfanos-cowork-2026-09-01/` en `.git/`: **los borra Sua a mano**.
- **El worktree `.claude/worktrees/visual03` y su servidor en el 8124 siguen levantados**, y `.claude/launch.json` en la raíz tiene una entrada de más sin commitear. Se retiran al cerrar la revisión.

## 5. Cifras medidas del árbol

Medidas el **15 de septiembre de 2026 sobre `2d86359`**, que es lo desplegado. **Vuelve a medirlas antes de citarlas** en un documento.

| Cifra | Valor | Comando |
|---|---|---|
| Confirmaciones | **634** | `git rev-list --count main` |
| Migraciones | **47** | `ls database/migrations/*.php` |
| Modelos | **24** | `ls app/Models/*.php` |
| Sembradores | **21** | `ls database/seeders/*.php` |
| Fábricas | **19** | `ls database/factories/*.php` |
| Archivos de prueba | **134** | `find tests -name '*Test.php'` |
| Métodos de prueba | **1.251** | `grep -rhE '^\s*public function test_' tests` |
| Vistas Blade | **102** | `find resources/views -name '*.blade.php'` |
| Componentes públicos | **24** | `ls resources/views/components/publico/*.blade.php` |
| Panel | **21** recursos · **6** páginas · **22** policies · **16** widgets | `ls app/Filament/…`, `ls app/Policies/*.php` |
| Comandos de Artisan propios | **6** | `ls app/Console/Commands/*.php` |
| Enums | **21** | `ls app/Enums/*.php` |
| Controladores públicos | **19** | `ls app/Http/Controllers/Publico/*.php` |
| Middleware propio | **3** | `ls app/Http/Middleware/*.php` |
| Archivos de configuración | **18** | `ls config/*.php` |
| Rutas GET propias | **96** | `php artisan route:list --method=GET --except-vendor --json` |
| **Suite completa** | **1.615 casos · 1.615 pasan · 0 fallos · 8.460 aserciones · 693 s** (Filament 5.8.2) | `php artisan test --compact` |
| Referencia previa, Filament 4.12.8 | 1.615 casos · 1.615 pasan · 0 fallos · **8.460 aserciones** · 967 s | `php artisan test --compact` |
| Ajustes que siembra `SettingSeeder` | **200** en la rama · **109** en producción | reflexión sobre `SettingSeeder::ajustes()` |
| Permisos que siembra `RolYPermisoSeeder` | **88**, y **no cambia** en esta fusión: no se repite el P0 del 10 sep | `git diff main HEAD -- database/seeders/RolYPermisoSeeder.php` |
| Fichas de la guía | **151** en la rama (12 municipios, 0 costos, procedencia más larga 191 de 255) · **8** en producción hasta sembrar | `php artisan tinker` + `curl` sobre la URL pública |
| Material del gremio del 15 sep | **27 archivos · 17,4 MB** | `Get-ChildItem -Recurse -File` |
| Despliegue de hoy | push 21:48 · sirviendo 21:50 · **60 s** · 178 commits · 437 archivos · 1 migración | `git ls-remote` + `curl` al activo nuevo |

## 6. Lo siguiente, en orden

1. **Sembrar la guía en producción** — el bloque del principio. Sin eso el trabajo insignia de hoy está desplegado y no se ve.
2. **Decirle a Ingrid que su rama quedó huérfana** (D-48) antes de que escriba una línea más sobre ella. Y de paso, lo que quedó pendiente de decirle desde el 10 sep: que se desplegó lo que ella estaba revisando, que dos de los cinco hallazgos que le describí no eran lo que le conté, y que la validación de la consolidación citaba 425 pruebas cuando la suite pasa de 1.600.
3. **Mirar el panel con ojos, bajo Filament 5**, Sua e Ingrid. Es lo único que ninguna prueba cubre y acaba de cambiar de versión mayor. Entrar con la app de autenticación; el correo no sale hasta que haya SMTP.
4. **Emitir el Acta 09** (D-47), que ahora cubre la capa visual **y** el salto de versión: el §9 del encargo exige el registro por escrito y en los dos casos llegó después del código.
5. **Confirmar los dos envíos a la universidad** (4 y 11 sep). Si no salieron, es 0.0 y hay que hablar con el docente hoy.
6. **Fijar la demo 2** (D-20) con la guía ya sembrada y el Directorio nuevo delante.
7. **Una sola reunión con Natalia** con la tabla del §3 impresa: D-01, D-03, D-04 a D-12, D-18 a D-21, D-27 a D-29, D-39, D-40, D-44, D-45 y la nueva **D-49**. Si está la cuenta de Google del gremio, se hace ahí mismo el SMTP.
8. **Sustituir las imágenes generadas por IA** por las fotos reales en cuanto haya autorización (D-49 + D-03).
9. **Rehacer la matriz de trazabilidad**, que sigue en las cifras del 8 sep, y corregir el documento de práctica, que describe la barra A.
10. Semana 8: dominio y SSL, manual actualizado y en PDF, capacitación y Acta 02, traspaso de cuentas (D-12), acuerdo de soporte (DPV-13).

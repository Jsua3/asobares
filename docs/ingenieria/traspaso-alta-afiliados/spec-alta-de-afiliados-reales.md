# Spec — Alta de afiliados reales con contraseña provisional

- **Fecha:** miércoles 16 de septiembre de 2026 (Bogotá)
- **Base:** `main` en `6d43b91` (= `origin/main`)
- **Estado:** diseño aprobado por Sua en el chat, en tres partes. Este archivo lo deja escrito para revisarlo antes del plan.
- **Dónde vive:** en el scratchpad de la sesión, **no en el repositorio**: `CLAUDE.md` y el prompt maestro (§4.9) prohíben crear documentación nueva que no se pida, y el repositorio es público.

---

## 1. Objetivo

1. Cargar en producción las fichas de los establecimientos afiliados reales, a partir de la base del gremio.
2. Darle a cada dueño acceso a `/mi-cuenta` con una contraseña genérica que está obligado a cambiar, sin que mientras tanto esa contraseña compartida abra datos personales de terceros.
3. Dar de alta la cuenta real del super admin.

## 2. Decisiones tomadas

| # | Decisión | Quién | Cómo se tomó |
|---|---|---|---|
| D1 | Los afiliados empiezan con **una contraseña genérica**, que el gremio les comunica, y se les pide cambiarla | Sua | Instrucción en el chat |
| D2 | Mientras la contraseña sea provisional: **aviso tocable y secciones de terceros cerradas** | Sua | Eligió la opción recomendada entre tres (las otras: bloqueo total, solo aviso) |
| D3 | Las fichas y las cuentas entran a producción **desde el panel** | Sua | Eligió la opción recomendada entre tres (las otras: comando contra la base de producción, a mano) |
| D4 | Super admin: la cuenta Gmail de la página. **La contraseña propuesta en el chat no cumple la política** (no tiene símbolo) y además quedó escrita en el chat: Sua elige otra y la escribe él mismo en el panel | Sua | Aprobado con el diseño |
| D5 | **Crear cuentas y escribir contraseñas en producción lo hace una persona**, no la sesión: el super admin (importación) y Sua (cuenta de dirección) | Regla de la sesión | Aprobado con el diseño |
| D6 | Sin cartera cargada, Mi Cuenta **no afirma «Estás al día»** | Sua | Aprobado con el diseño (parte 2) |

## 3. Hechos medidos que condicionan el diseño

### 3.1 El archivo

`D:\Sua_Files\Downloads\Base_de_datos_Cap__Quindio_actualizada FINAL.xlsx` · 51.745 B · fuera del árbol, y ahí se queda.

- Hoja «Base de Datos 2025». Banner en la fila 1 y **cabecera en la fila 6**, el mismo formato que ya lee `ImportadorDeAsociados`.
- **66 filas de datos.** Las columnas son las que mapea el importador, más una **columna 13 sin encabezado** que trae «ok»/«Ok» o «pdte» en 13 filas. Nadie ha dicho qué significa, y el importador la ignora.
- **Sin columna de categoría.**
- Simulación con las reglas del importador y la regla de cuentas de §4.2, medida el 16 sep, **suponiendo una base sin fichas ni cuentas de afiliado**, que es la situación de producción:

| Medida | Valor |
|---|---|
| Filas rechazadas por municipio | **4**: fila 57 ([establecimiento], «Providencia», que no es ninguno de los 12) y filas 70, 71 y 72 ([establecimiento], [establecimiento], [establecimiento], sin municipio) |
| Fichas que se crearían | **61** (una fusión: [establecimiento] aparece en las filas 13 y 46) |
| Fichas sin correo | 22 |
| Fichas con correo inválido | 2 (filas 29 y 30) |
| Correos válidos distintos | 36 |
| **Cuentas sin ambigüedad** | **35** |
| Correos compartidos por dos establecimientos | 1 ([establecimiento] e [establecimiento]) |
| Documentos (NIT o CC) repetidos entre filas | 5 pares (13/46, 15/44, 35/36, 37/61, 38/60) |

### 3.2 Producción (medido el 16 sep, solo lectura)

- Una instancia `App`: `scalingType` none, **`minReplicas` 1 y `maxReplicas` 1**, scheduler encendido.
- Según `estado.md` (15 sep): `deployCommand` = `php artisan migrate --force`, **0 fichas publicadas** y **0 carteras**.

### 3.3 El código en `main`

- `asobares:crear-usuario` **no vincula** la cuenta con un establecimiento. `SesionAsociadoController` rechaza a un asociado sin `asociado_id`.
- `/mi-cuenta` **no tiene** «olvidé mi contraseña» ni cambio de contraseña con sesión. La única puerta es `/mi-cuenta/contrasena/{token}`, cuyo enlace sale por correo, y no hay SMTP.
- `MiCuentaController::index` fabrica una `Cartera` en cero si no existe, así que la vista dice **«Estás al día»**. `pagarMensualidad` con cartera nula responde **«Tu cuenta ya está al día.»**
- `AuthenticateSession` está añadido al grupo `web` (`bootstrap/app.php`).
- **No existe `lang/`**: toda regla de validación sin mensaje propio se imprime como su clave (`validation.min.string`).
- Política de contraseñas del proyecto: `Password::min(12)->mixedCase()->numbers()->symbols()`. `CrearUsuarioDelPanel::CLAVE_PUBLICADA` = la del demo.
- `UserPolicy::create` exige `crear_usuario`, que solo tiene `super_admin`. `AsociadoPolicy::create` exige `crear_asociado`.
- `User` declara `#[Fillable(['name','email','password','asociado_id'])]`: cualquier otro atributo dentro de un `create`/`updateOrCreate` **se descarta en silencio**.
- `AjustesQueSirvenParaAlgoTest`: todo ajuste sembrado tiene que leerlo alguien.
- Los límites de peticiones de Mi Cuenta viven en `AppServiceProvider` (`'mi-cuenta-entrar' => 5`, …). La bitácora de sesión usa `activity('sesion')` en el mismo proveedor.
- La rama huérfana de Ingrid (`a6ebcca`, D-48) **no toca** Mi Cuenta, usuarios, rutas ni middleware.

---

## 4. Diseño

### 4.1 Esquema

- Migración `2026_09_16_…_anade_contrasena_provisional_a_users`: columna **`users.contrasena_provisional`**, booleana, `default false`, con `down()`.
- `User`: casteo a booleano. **Se asigna suelta, nunca dentro de `create`**, por el `#[Fillable]`.
- **Regla:** toda contraseña que pone alguien distinto del titular de una cuenta de rol `asociado` deja la marca en `true`. Solo el titular la apaga, desde `/mi-cuenta/seguridad`.

### 4.2 Importación desde el panel (parte 1)

**Acción** `importar` en la cabecera de `ListAsociados`, con el molde de `ListCarteras::importar`.

- **Visible y autorizada** solo si `can('create', Asociado::class)` **y** `can('create', User::class)`, que hoy es solo `super_admin`. **Sin permiso nuevo**, así que no hace falta correr `RolYPermisoSeeder` en producción.
- **Formulario del modal:**
  - `archivo`: un solo `.xlsx`, guardado en el disco privado.
  - `categoria_por_defecto`: selección obligatoria del catálogo. Si una fila trae su propia columna «Categoría», gana la de la fila, como ya hace el importador.
  - `crear_cuentas`: casilla.
  - `contrasena_generica` con su confirmación: obligatoria si `crear_cuentas`. Pasa por la política del proyecto y **rechaza la contraseña del demo**. Los mensajes de validación van escritos en español.
- **Ejecución**, en **una transacción** que envuelve los dos pasos:
  1. `ImportadorDeAsociados::importar($ruta, $categoria)`, **sin autorización de datos** y sin cambiar ninguna de sus reglas: borrador, actualiza por slug, datos internos, nunca publica.
  2. Si `crear_cuentas`: `AltaDeCuentasDeAfiliados` sobre **las fichas que tocó esta carga**.
  3. En un `finally`: borrar el archivo del disco privado, haya salido bien o mal.
  4. Notificación **persistente** con el resumen del importador (creadas, actualizadas, errores por fila) y el de las cuentas (creadas y cada ficha que se quedó sin cuenta, con su motivo).
- **Límite conocido:** la subida de Livewire guarda el temporal en una petición y lo procesa en otra. Funciona porque producción tiene **una réplica**. Si algún día escala, esto necesita el bucket (D-13). Se deja dicho en el código y en el encargo.

**`ResultadoDeCargaDeAsociados`** gana la lista de **ids de las fichas creadas o actualizadas** en la carga. Se anota en las dos ramas de `procesarFila`.

**`AltaDeCuentasDeAfiliados`** (servicio nuevo): recibe las fichas y la contraseña, y devuelve su propio resultado. Para cada ficha, con `correo = minúsculas(recortar(correo_interno))`, **en este orden**:

| Caso | Qué hace | Motivo en el resumen |
|---|---|---|
| Correo vacío | No crea | «sin correo» |
| Correo inválido según `FILTER_VALIDATE_EMAIL`, el mismo criterio de la simulación | No crea | «correo inválido» |
| El mismo correo en dos o más fichas de esta carga | No crea **ninguna** | «correo compartido con: …» |
| La ficha ya tiene alguna cuenta vinculada | No toca nada | «ya tenía cuenta» |
| Existe un usuario con ese correo (comparado en minúsculas) de rol `super_admin` o `subadmin` | No toca nada | «el correo es del equipo del gremio» |
| Existe un usuario con ese correo, de cualquier otro tipo | No toca nada | «el correo ya tiene cuenta» |
| Ninguno de los anteriores | **Crea** la cuenta: `name` = representante o, si falta, nombre de la ficha; `email` en minúsculas; contraseña con `Hash`; `asociado_id`; después, sueltos, `email_verified_at = now()` y `contrasena_provisional = true`; rol `asociado` | — |

**Una cuenta existente nunca se toca**: ni contraseña, ni vínculo, ni rol, ni marca. Reimportar el archivo corregido no le devuelve la genérica a quien ya la cambió.

**`asociados:importar` (el comando) no cambia.**

### 4.3 Portal del afiliado (parte 2)

**Middleware nuevo** `ExigirContrasenaPropia`, con alias `contrasena.propia`.

- Si `contrasena_provisional` → redirige a `mi-cuenta.seguridad` con un `aviso`: la sección se abre cuando cambie la contraseña provisional.
- **Se aplica a:** `mi-cuenta.proveedores.index`, `mi-cuenta.artistas.index`, `mi-cuenta.aspirantes.index`, **todas** las `mi-cuenta.vacantes.*` (8 rutas) y `mi-cuenta.postulaciones.gestionar`.
- **No se aplica a:** `mi-cuenta.index` (estado de cuenta y convenios), `mi-cuenta.pagar`, `mi-cuenta.fotos.*`, `mi-cuenta.seguridad*` y `mi-cuenta.salir`.
- La bolsa se cierra entera porque las postulaciones se ven dentro de cada vacante y cerrar una vacante no pasa por moderación.

**Aviso tocable** (componente `x-publico.mi-cuenta.aviso-contrasena`).

- Se pinta arriba de `mi-cuenta/index` y `mi-cuenta/fotos/index` cuando la marca está puesta. Las secciones cerradas no llegan a pintarse, y la pantalla de seguridad lleva su propia explicación.
- **Todo el aviso es un enlace** a `mi-cuenta.seguridad`, con nombre accesible y área táctil de al menos 44 px.
- Textos por `ajuste()` con respaldo.

**Pantalla `/mi-cuenta/seguridad`.** Controlador `SeguridadDeLaCuentaController` y vista `publico/mi-cuenta/seguridad.blade.php`.

- `GET mi-cuenta.seguridad`: formulario con **`current_password`, `password` y `password_confirmation`**. Con la marca puesta, explica por qué tiene que cambiarla.
  - Los nombres no son de estilo: son los que el manejador de excepciones de Laravel **no devuelve a la sesión** al fallar la validación (`$dontFlash`). Con nombres propios, la contraseña viajaría a la tabla de sesiones y el componente `campo` la volvería a pintar en el HTML. *(Ajuste del 16 sep, hallado al planificar.)*
- `PUT mi-cuenta.seguridad.actualizar`, con límite de peticiones propio (`'mi-cuenta-seguridad' => 5` en `AppServiceProvider`).
  - **Reglas:** `current_password` obligatoria y validada contra la actual; `password` obligatoria, `confirmed`, la política del proyecto y distinta de la actual. **Un mensaje en español por cada regla**, con las claves que de verdad usa la regla `Password`: `password.password.symbols`, no `password.symbols`.
  - **Al guardar:** cierra las demás sesiones de la cuenta (mecanismo documentado de Laravel sobre `AuthenticateSession`), guarda el nuevo hash, rota `remember_token`, apaga `contrasena_provisional` y anota `activity('sesion')` «cambió su contraseña» **sin ningún dato de la contraseña**. Redirige a `mi-cuenta.index` con `exito`.
- Botón **«Seguridad»** fijo en la navegación de `mi-cuenta/index`.
- **Por qué cerrar las otras sesiones:** si alguien entró con la genérica antes que el dueño, su sesión sobreviviría al cambio y, al apagarse la marca, se le abrirían las secciones cerradas.

**Sin cartera cargada.**

- `index`: si el asociado no tiene fila de cartera, bloque «estado de cuenta todavía no cargado», **sin** «Estás al día» y **sin** «Pagar ahora». Textos por `ajuste()`.
- `pagarMensualidad`: con cartera nula redirige con un `aviso` que dice que el estado de cuenta no está cargado, no «al día».
- `index` pinta también la sesión `aviso` (hoy solo pinta `exito` y `error`).

### 4.4 Panel (parte 3)

- **Usuarios, crear y editar:** si se escribió una contraseña y el rol que queda es `asociado` → `contrasena_provisional = true`, en el guardado de la página.
- **Usuarios, tabla:** columna «Contraseña provisional» y filtro, para que la oficina vea a quién le falta cambiarla.
- **Usuarios, ayuda del campo contraseña:** se ajusta a lo que ahora pasa de verdad con un afiliado.
- **`asobares:crear-usuario --rol=asociado`:** deja la marca en `true`.
- **`asobares:crear-usuario`, mensajes:** guarda sus mensajes como `clave.mixed`, `clave.symbols`… y la regla `Password` falla con `password.mixed` y `password.symbols`. Así que, probablemente, una contraseña sin símbolo imprime la clave cruda, que es justo el caso de la contraseña propuesta en el chat. Se corrige a `clave.password.*` **solo si una prueba lo ve rojo antes**. *(Ajuste del 16 sep, hallado al planificar.)*

### 4.5 Textos

- **Siete claves nuevas** en `SettingSeeder`, grupo `mi_cuenta`, cada una leída en su vista con `ajuste('clave', 'respaldo')`, como exige `AjustesQueSirvenParaAlgoTest`:
  - `mi_cuenta_seguridad_titulo`, `mi_cuenta_seguridad_texto` y `mi_cuenta_seguridad_provisional_texto`;
  - `mi_cuenta_aviso_provisional_titulo` y `mi_cuenta_aviso_provisional_texto`;
  - `mi_cuenta_sin_cartera_titulo` y `mi_cuenta_sin_cartera_texto`.
- **Los mensajes de sesión** (sección cerrada, contraseña cambiada, pagar sin cartera) y **la ayuda de la política** bajo el campo van escritos en el controlador y en la vista, igual que los que ya existen («Tu cuenta ya está al día.», la ayuda de `establecer-contrasena`). *(Ajuste del 16 sep: el spec los ponía también como ajustes.)*
- En producción se ve el respaldo hasta que alguien corra `ContenidoOficialSeeder`. **No hace falta** correrlo para este trabajo.

---

## 5. Errores

| Situación | Qué pasa |
|---|---|
| Archivo ilegible o sin cabecera | Error general en la notificación, no se crea nada, el archivo se borra |
| Contraseña genérica que no cumple o es la del demo | Error de validación en el modal, no se procesa nada |
| Filas con error (municipio, nombre, categoría) | Se reportan con su número de fila y el resto entra |
| Excepción inesperada en las cuentas | Deshace **todo** (fichas y cuentas), notificación de error, `report()`, archivo borrado |
| Cambio de contraseña inválido | Errores en español en el formulario, nada cambia |
| Demasiados intentos de cambio | 429 del límite de peticiones, igual que el resto de Mi Cuenta |
| Equipo del gremio en `/mi-cuenta/seguridad` | La pantalla de «sesión equivocada» de siempre (`rol.asociado`) |

## 6. Pruebas

TDD. Cada aserción **se ve roja rompiendo el cableado a propósito**: regla 3 del prompt maestro y la memoria de mutación por comportamiento. Las aserciones sobre HTML van sobre el árbol (`xpathDe()`), no sobre cadenas.

- **Importación desde el panel:**
  - Solo la ve el super admin: la secretaría y el asociado no.
  - Crea fichas y cuentas.
  - Exige la contraseña si se piden cuentas y rechaza la del demo y una sin símbolo.
  - Sin la casilla, no crea cuentas.
  - Borra el archivo en éxito y en error.
  - Si las cuentas revientan, deshace todo.
- **`AltaDeCuentasDeAfiliados`:** un caso por cada fila de la tabla de §4.2; el hash de una cuenta existente **no cambia**; correo en minúsculas; rol, vínculo, marca y `email_verified_at`.
- **Resultado del importador:** trae los ids tocados en creación y en actualización.
- **Middleware:**
  - Con la marca, **cada** ruta cerrada redirige. Es una prueba por ruta, con proveedor de datos: quitar el middleware de una sola ruta tiene que poner roja una prueba.
  - Sin la marca, entra.
  - Las abiertas siguen abiertas con la marca.
- **Aviso:** está con la marca y no está sin ella; su `href` es `mi-cuenta.seguridad`.
- **Seguridad de la cuenta:**
  - Invitado → redirige al formulario de entrada.
  - Actual incorrecta, sin confirmación, sin símbolo e igual a la actual: cada una con su mensaje en español, sin claves `validation.*`.
  - Éxito: el hash cambia, la marca se apaga, la bitácora anota sin la contraseña y la otra sesión queda cerrada.
  - El límite de peticiones responde.
- **Sin cartera:** no dice «Estás al día» ni ofrece pagar; `pagar` no responde «al día».
- **Panel, Usuarios:** poner contraseña a un asociado lo marca; a un super admin no; columna y filtro.
- **`CrearUsuarioDelPanelTest`:** `--rol=asociado` marca.
- **Antes de fusionar:** suite completa, `pint --dirty` y el ensayo de §7.2.

## 7. Orden de operación y responsables

1. **Sua, antes del código:** registrar la ampliación por escrito (regla 1 del prompt maestro). La pantalla de seguridad y las secciones cerradas son funcionalidad nueva. Puede ir en el Acta 09 pendiente (D-47) o en una propia. Avisarle a Ingrid, que el 11 sep pidió no abrir frentes.
2. **La sesión:**
   - Llevar el árbol de `guia/doce-municipios` a `main` (avance rápido, comprobado sin choques) y abrir rama.
   - Construir con pruebas.
   - **Ensayo local** con el archivo real sobre **una base SQLite vacía** en el scratchpad, con `migrate` y `ContenidoOficialSeeder`, igual que producción, y una contraseña desechable. No se usa la base de desarrollo: sus asociados y usuarios de demostración pueden coincidir por nombre y cambiar las cifras. Tiene que dar **61 fichas y 35 cuentas**, igual que §3.1. La base del ensayo se borra al terminar.
3. **La sesión, con visto bueno explícito de Sua:** fusionar a `main` y empujar. El despliegue y la migración corren solos; no hay permisos que sembrar. Comprobar **contra lo servido**, no contra el repositorio.
4. **Sua, en el panel de producción** con la cuenta de dirección actual:
   - Crear la cuenta de dirección nueva, con una contraseña que cumpla la política.
   - Entrar con ella y registrar la app de autenticación.
   - **Solo cuando esa entre**, decidir qué pasa con la cuenta vieja. Antes, la sesión mide en solo lectura qué cuentas hay.
   - Si Sua ya no tuviera acceso a la cuenta actual, el camino es `asobares:crear-usuario` por `command:run` con la variable de entorno, y se ve en ese momento.
5. **El super admin:** Asociados → Importar base del gremio, con el archivo (idealmente ya corregido por el gremio) y la contraseña genérica escrita por él. Guardar el resumen.
6. **Verificación:**
   - **La sesión**, en solo lectura en producción: fichas en borrador, **0 publicadas**, cuentas de rol asociado con la marca, equipo del gremio intacto.
   - **Sua:** entra con una cuenta de afiliado, ve el aviso y las secciones cerradas, **sin cambiar** la contraseña.
7. **El gremio:** a cada afiliado, su correo y la contraseña genérica, por su canal.

## 8. Fuera de alcance y riesgos que quedan dichos

- **Cartera real:** no entra (D-44 cuota, D-45 cruce de nombres).
- **Publicar fichas:** exige la autorización de cada titular (§9 del encargo). Todo entra en borrador.
- **SMTP (D-07)** y, con él, «olvidé mi contraseña». Nota: la cuenta Gmail de la página podría servir de remitente con una contraseña de aplicación. Es decisión de Natalia y Sua, no de este trabajo.
- **Una cuenta, un establecimiento:** un dueño con dos locales necesita dos correos o que el gremio elija uno.
- **Mayúsculas en el correo al entrar:** en PostgreSQL el `=` distingue mayúsculas. Las cuentas se crean en minúsculas y el campo es `type="email"`, que en el teléfono no pone mayúscula inicial. No se toca el login: la propiedad «sin fuga por tiempo» de RF-42 no se reabre por esto.
- **La ventana de la genérica:** hasta que cada dueño la cambie, quien sepa la genérica y el correo de un afiliado ve el estado de cuenta y los convenios de ese afiliado. Es lo que D2 acepta; la columna del panel sirve para perseguir a los que faltan.
- **La subida por el panel depende de una sola réplica** (§4.2).
- **Lo que el gremio tiene que corregir en el archivo:**
  - los 4 municipios;
  - los 22 correos que faltan y los 2 inválidos;
  - [establecimiento] repetido;
  - el correo compartido;
  - los 5 documentos repetidos;
  - la categoría;
  - qué significa la columna 13.
- **`establecer-contrasena` (flujo de Ingrid) pasa `[]` como mensajes a `validate()`** y no hay `lang/`: es probable que una confirmación mal tecleada ahí muestre `validation.confirmed`. **Sin comprobar** y fuera de este trabajo. Queda como tarea aparte.

## 9. Expediente al cerrar

- `estado.md` reescrito y entrada nueva en `bitacora.md`.
- `encargo.md`: §5 (`users.contrasena_provisional`), §6 (importación desde el panel, alta de afiliados, límite de una réplica) y §13 (D1, D2, D3, D5 fechadas).
- **Solo conteos: ni un correo, ni un nombre de afiliado, ni un documento.** El expediente es público.

## 10. Addendum — 17 de septiembre de 2026 (no reescribe lo de arriba)

Las secciones 1–9 son el diseño del 16 sep, **incluido D1 (contraseña genérica)**. No se tachan.

**Qué cambió después, sin borrar D1:** Sua eligió «una por afiliado» y descarga de accesos por la Dirección. Eso está implementado en `ingrid/cierre-alta-real` (`cc72655`, `4b14b40`, `a3bb65d`). El encargo §13 del 17 sep es la regla vigente del producto en esa rama.

**Ensayo aislado confirmado (solo conteos):** 61 fichas creadas, 1 actualizada, 4 errores (filas 57, 70, 71, 72 — municipio fuera del catálogo), 35 cuentas. Rollback validado.

**El acta de la ampliación se registra después del código**, como el Acta 06. Aún no está emitida.

**Producción:** esta rama **no** está en `main`, **no** está desplegada, **no** se importó el Excel real y **no** se generaron accesos reales.

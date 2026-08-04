# Diseño: Bolsas de ASOBARES (empleo, artistas, proveedores)

**Fecha:** 2026-08-04
**Estado:** aprobado en conversación de diseño; pendiente plan de implementación.

## Contexto y problema

Hoy las tres bolsas funcionan al revés de lo que necesita el gremio:

- Las vacantes las crea el personal interno en `/admin` "a nombre de" un asociado; el asociado no publica ni edita nada (`/mi-cuenta` es solo lectura + pago).
- El flujo de aprobación existente (`borrador → pendiente_aprobacion → publicado`, `FlujoDeAprobacionObserver`) está invertido respecto al requisito: la secretaría (`subadmin`) redacta y no puede aprobar; solo `super_admin` aprueba y recibe las notificaciones.
- Postularse a una vacante concreta es solo un deep link de WhatsApp: no queda en base de datos, no notifica a nadie y sin número no hay botón. El formulario "Déjanos tu perfil" guarda aspirantes nunca ligados a una vacante, sin notificación y con correos duplicables.
- Artistas y proveedores son fichas sin dueño cargadas a mano; "quiero ser proveedor" cae como texto libre en la bandeja de contacto.
- Las vacantes no tienen ciclo de vida: sin fecha de cierre ni caducidad.

## Decisiones tomadas

| Tema | Decisión |
|---|---|
| Alcance | Empleo completo con autoservicio del asociado; artistas y proveedores entran por solicitud pública estructurada (sin cuenta aún), con el modelo preparado para darles cuenta después. |
| Portal del asociado | Sección "Mis vacantes" dentro de `/mi-cuenta` (Blade propio del sitio). Los asociados siguen sin acceso a `/admin`. |
| Postulación | Sin cuenta: formulario en el detalle de cada vacante, ligado a esa vacante. |
| Notificación de postulación | Correo al asociado dueño; la secretaría consulta en su bandeja del panel sin recibir avisos. |
| Ediciones | Editar una vacante publicada la despublica (vuelve a `pendiente_aprobacion`) hasta re-aprobación. Sin modelo de revisiones. |
| Ciclo de vida | Cierre manual por el asociado + fecha límite con expiración automática (obligatoria para empleos momentáneos). |
| Banco de talento | El formulario genérico "Déjanos tu perfil" se mantiene, deduplicado por correo y con estados de gestión. |
| Retención | Purga automática: postulaciones a los 6 meses del cierre de su vacante; perfiles del banco a los 12 meses sin actualización. Plazos configurables. |
| Alcance de aprobación de secretaría | Solo bolsas (`publicar_vacante`, `publicar_artista`, `publicar_proveedor`). El resto del contenido lo sigue aprobando el admin. |

## Principios

1. **Quien publica es dueño y único editor de su contenido.** Secretaría y administración moderan (aprobar, devolver con motivo; `super_admin` puede eliminar contenido indebido), pero no editan vacantes ajenas.
2. **Nadie aprueba lo que él mismo redacta.** Secretaría aprueba lo que publican terceros; el admin aprueba lo que redacta secretaría, y puede aprobar bolsas como respaldo.
3. **Consultar es público; publicar exige cuenta; postularse no exige cuenta.**
4. **Se reutiliza la maquinaria existente**: enum `EstadoPublicacion`, trait `EsPublicable`, `FlujoDeAprobacionObserver`, policies por permisos `{accion}_{recurso}`, patrón bandeja, honeypot + throttle, consentimiento habeas data.

## Modelo de datos

### `vacantes` (cambios)

- `+ fecha_limite` (date, nullable). Obligatoria si `tipo = momentaneo`; opcional para el resto. Vencida la fecha, la vacante sale del muro vía scope (patrón `vigente()` de proveedores; sin cron).
- `+ cerrada_at` (timestamp, nullable). Cierre manual del asociado, sin aprobación. Reabrir limpia la fecha (el contenido aprobado no cambió); solo tiene efecto si la vacante sigue publicada y no vencida.
- `+ motivo_devolucion` (text, nullable). Lo escribe secretaría al devolver; el asociado lo ve en `/mi-cuenta`; se limpia al reenviar.
- `+ categoria_cargo` (string casteada a enum `CargoDelSector`: `administracion`, `cocina`, `barra`, `servicio`, `seguridad`, `aseo`, `otros`). El campo libre `cargo` se mantiene como título del puesto. La categoría alimenta el filtro del muro y el cruce futuro con el banco de talento.
- Enum `TipoVacante` gana el caso `Momentaneo = 'momentaneo'` (label "Momentáneo (evento de una o dos noches)").
- Sin cambios de FK: `asociado_id` sigue obligatorio.

### `postulaciones` (tabla nueva)

- `vacante_id` FK → vacantes, `cascadeOnDelete`.
- `nombre`, `correo`, `telefono` (nullable), `experiencia` (text corto).
- `acepta_datos` (boolean), `consentimiento_at` (timestamp) — mismo patrón de aspirantes.
- `estado` (string casteada a enum `EstadoDeGestion`: `nuevo`, `contactado`, `descartado`; default `nuevo`).
- Único compuesto `(vacante_id, correo)`: reenviar el formulario a la misma vacante no duplica.

### `aspirantes` (queda solo como banco de talento)

- Se elimina `vacante_id` (hoy siempre es null; la relación con vacantes pasa a `postulaciones`).
- `correo` único: el envío hace `updateOrCreate` por correo (re-sella consentimiento y actualiza el perfil).
- `+ estado` (enum `EstadoDeGestion`, default `nuevo`).
- `+ categoria_cargo` (enum `CargoDelSector`); `cargo_interes` libre se mantiene como detalle.

### `artistas` y `proveedores`

- `+ user_id` (FK nullable → users, `nullOnDelete`): preparación para cuentas propias en fase futura; ningún código la usa aún.
- `+ acepta_datos` + `consentimiento_at` en ambas (la solicitud pública captura datos personales).
- `artistas + correo` (nullable): para avisar al solicitante el resultado (proveedores ya lo tiene).
- Las fichas siguen siendo editables por secretaría mientras no tengan dueño con cuenta — **excepción documentada** al principio 1.

### Enums nuevos

- `CargoDelSector` (string-backed, español, `HasLabel`): categorías de cargo del sector nocturno.
- `EstadoDeGestion` (string-backed, `HasLabel` + `HasColor`): `nuevo` (gris) → `contactado` (verde) → `descartado` (rojo). Compartido por postulaciones y aspirantes.

## Flujos

### Publicar y moderar una vacante

1. El asociado crea la vacante en "Mis vacantes". El controlador guarda con `estado = pendiente_aprobacion`; el observer queda como defensa en profundidad (un asociado nunca tiene `publicar_vacante`).
2. Notificación de pendiente a **quienes pueden publicar ese modelo** (ver Notificaciones): para bolsas, secretaría y admin.
3. Secretaría **aprueba** (→ `publicado`; correo al asociado) o **devuelve con motivo obligatorio** (→ `borrador` + `motivo_devolucion`; correo al asociado). El asociado corrige y reenvía (→ `pendiente_aprobacion`, motivo limpiado).
4. **Editar una vacante publicada la devuelve a `pendiente_aprobacion`** (despublicada mientras tanto) y repite el ciclo.
5. Cerrar (`cerrada_at = now()`) y reabrir no pasan por aprobación.
6. El muro público muestra solo: `publicado` + `cerrada_at` null + (`fecha_limite` null o >= hoy). Scope `vigente()` en el modelo.

### Postulación

1. Nueva ruta pública `GET /empleo/{vacante}` (binding por id): detalle de la vacante con formulario de postulación. Solo visible si la vacante está publicada y vigente; si no, 404. Incluye JSON-LD `JobPosting` (componente `json-ld` existente). El botón de WhatsApp se mantiene como canal secundario cuando hay número.
2. `POST /empleo/{vacante}/postular` (throttle 6/min + honeypot + consentimiento obligatorio): crea la postulación y **envía correo a los usuarios del asociado dueño** con los datos básicos. Rechaza vacantes no vigentes y duplicados por `(vacante_id, correo)` con mensaje amable.
3. El asociado ve y gestiona sus postulaciones (marcar contactado/descartado) en "Mis vacantes". Secretaría las consulta en la bandeja `/admin/postulaciones` sin recibir notificación.

### Banco de talento

`POST /empleo/perfil` se conserva con `updateOrCreate` por correo, categoría de cargo y estados de gestión en la bandeja de aspirantes.

### Solicitudes de artistas y proveedores

1. CTA "Inscríbete en la bolsa" en `/artistas` y `/proveedores` → formulario público estructurado con los campos de la ficha + consentimiento + honeypot + throttle. Artistas: foto opcional con la misma validación estricta de imágenes del panel (JPG/PNG/WebP, máx 5MB); los huérfanos los limpia `LimpiezaDeArchivosObserver`.
2. El envío crea la ficha (`Artista`/`Proveedor`) directamente en `pendiente_aprobacion` y notifica a secretaría.
3. Secretaría revisa, completa/corrige si hace falta (fichas sin dueño) y aprueba; al publicarse se avisa por correo al solicitante si dejó correo. La devolución de solicitudes se gestiona por contacto directo (sin portal donde mostrar motivo).

## Permisos y policies

- `RolYPermisoSeeder`: `subadmin` gana `publicar_vacante`, `publicar_artista`, `publicar_proveedor` (ningún otro `publicar_*`). Se añade `postulacion` a BANDEJAS (`ver/editar/eliminar`; subadmin sin eliminar, como las demás).
- `VacantePolicy` deja de heredar el mapeo plano de `PoliticaDeContenido` donde haga falta:
  - `create`: solo usuarios con rol `asociado` y `asociado_id` vinculado.
  - `update` y cerrar/reabrir: solo usuarios del asociado dueño (`user->asociado_id === vacante->asociado_id`). El staff del panel **no** crea ni edita vacantes.
  - `view/viewAny`: staff con `ver_vacante` (moderación y supervisión) y el dueño en su cuenta.
  - `delete`: solo `super_admin` (contenido indebido).
  - `publicar`: permiso `publicar_vacante` (secretaría y admin).
- `PostulacionPolicy`: patrón bandeja (`create() => false`; nacen del formulario público). El asociado dueño gestiona el estado desde `/mi-cuenta` (autorización por propiedad de la vacante, no por permiso de panel).
- Rutas de `/mi-cuenta/vacantes/*` detrás del middleware `rol.asociado` existente + policy de propiedad.

## Notificaciones

| Evento | Destinatario | Canal |
|---|---|---|
| Contenido pendiente de aprobación | Usuarios con el permiso `publicar_{recurso}` del modelo (bolsas → secretaría y admin; resto → solo admin, sin lógica especial) | Notificación de BD de Filament (como hoy) |
| Vacante aprobada / devuelta (con motivo) | Usuarios del asociado dueño | Correo |
| Nueva postulación | Usuarios del asociado dueño | Correo |
| Ficha de artista/proveedor publicada | Correo del solicitante (si existe) | Correo |

El cambio en `FlujoDeAprobacionObserver` es puntual: en vez de notificar a `super_admin` fijo, notifica a los usuarios que pasan la policy `publicar` del modelo (vía permiso `publicar_{recurso}`).

## Retención de datos (Ley 1581)

- Comando `bolsas:depurar` en el scheduler diario:
  - Elimina postulaciones cuya vacante cerró o venció hace más de `config('bolsas.retencion_postulaciones_meses')` (default 6).
  - Elimina aspirantes del banco sin actualización hace más de `config('bolsas.retencion_aspirantes_meses')` (default 12).
- Nuevo `config/bolsas.php` con ambos plazos.
- La purga registra conteos en la bitácora (activitylog).

## Cambios por superficie

**Sitio público**
- `/empleo`: tarjetas enlazan al detalle; filtros por `categoria_cargo` (select del enum) y municipio; formulario de banco de talento al final. Se elimina la query muerta `$cargos`.
- `/empleo/{vacante}` (nueva): detalle + formulario de postulación + JSON-LD.
- `/artistas` y `/proveedores`: CTA y formulario de solicitud. `/proveedores` gana paginación (hoy `->get()` sin límite).

**`/mi-cuenta` (asociado)**
- Nueva sección "Mis vacantes": listado con estado, motivo de devolución y conteo de postulaciones; crear, editar, cerrar, reabrir; detalle con postulaciones y cambio de estado de gestión.

**`/admin` (panel)**
- `VacanteResource`: sin Create/Edit para el staff; lista de solo lectura con filtros, acción "Aprobar y publicar" y "Devolver" **con motivo obligatorio** (modal), `aprobarEnLote` en la toolbar, y vista de detalle con sus postulaciones. Columna `asociado.id` → `asociado.nombre`.
- Nueva bandeja `PostulacionResource` (ver/gestionar estado/eliminar; sin crear).
- `AspiranteResource`: sin select de vacante, con estado de gestión; se elimina el `CreateAction` muerto de `ListAspirantes`.
- `ArtistaResource` y `ProveedorResource`: CRUD del staff se mantiene (fichas sin dueño); columnas `municipio.id` → `municipio.nombre`; `aprobarEnLote` en toolbar.

## Testing

Se crean las factories hoy inexistentes que los modelos ya declaran: `VacanteFactory`, `AspiranteFactory`, `ArtistaFactory`, `ProveedorFactory`, más `PostulacionFactory`. Tests feature (PHPUnit) para:

- Ciclo de vacante: asociado crea → cae pendiente; secretaría aprueba → publicada + correo; devuelve → borrador + motivo + correo; reenvío limpia motivo; editar publicada la despublica.
- Autorización: un asociado no ve/edita vacantes de otro; staff no crea ni edita vacantes; secretaría aprueba vacantes/artistas/proveedores pero **no** noticias; asociado no accede a `/admin` ni el staff a `/mi-cuenta` (ya cubierto, se extiende).
- Ciclo de vida: cerrada o vencida no aparece en el muro, su detalle da 404 y no acepta postulaciones; reabrir funciona solo si vigente.
- Postulación: crea registro ligado + correo al asociado; duplicado por (vacante, correo) rechazado; honeypot y consentimiento; estados de gestión.
- Banco de talento: `updateOrCreate` por correo re-sella consentimiento; sin duplicados.
- Solicitudes: crean ficha en pendiente + notifican; no visibles públicamente hasta aprobar; validación de foto de artista.
- Retención: `bolsas:depurar` elimina lo vencido y respeta lo vigente.
- Muro y filtros de `/empleo` (categoría, municipio).

## Fuera de alcance (explícito)

- Cuentas y autogestión para artistas y proveedores (solo queda `user_id` nullable preparado).
- Monetización de proveedores (`visible_hasta` sigue manual).
- Versionado/revisiones de ediciones (editar despublica; decisión consciente).
- CV adjunto (PDF) en postulaciones.
- Matching automático aspirante↔vacante (facilitado por `categoria_cargo`, no implementado).
- Estado de postulación consultable por el aspirante (no tiene cuenta).

# Runbook de despliegue — Laravel Cloud
## Plataforma Web ASOBARES Capítulo Quindío

**Versión:** 1.0 · **Fecha:** 19 de agosto de 2026
**Dirigido a:** quien ejecute el despliegue, haya vivido o no la sesión que preparó esto.
**Riesgo del expediente:** R-14 (hosting). La parte técnica está cerrada; la parte humana **no**.

> **Léase entero antes de escribir un solo comando.** Los apartados 1, 2 y 8 son los que
> deciden si el despliegue sale bien o se cae en la demostración ante la tutora.

---

## 0. En una frase

Todo lo que un agente podía dejar listo está listo: el código ya no se rompe en PostgreSQL,
la coraza de configuración ya cubre el entorno remoto, existe `.env.staging.example` con
las variables exactas, y una suite (`tests/Feature/ConfiguracionDeDespliegueTest.php`)
vigila que nadie lo deshaga. **Falta un trámite humano que ningún agente debe hacer**, y
queda un problema abierto que hay que decidir antes de enseñarle la URL a nadie: el
almacenamiento de archivos (apartado 8).

---

## 1. El paso bloqueante — lo hace una persona, no un agente

Nada de este documento arranca sin esto.

### 1.1 La cuenta nace institucional

Es la mitad del veredicto que eligió Laravel Cloud, no un detalle administrativo. El modo
de muerte conocido es: cuenta y tarjeta del practicante → la demostración sale bien →
nadie migra lo que funciona → la práctica termina y las llaves se van con ella → al primer
incidente el gremio reconstruye desde cero.

| Cosa | A nombre de quién |
|---|---|
| Correo de la cuenta y de facturación | `asobaresquindio@asobares.org` — **no** el personal del desarrollador |
| Medio de pago | Del gremio. Si por urgencia se usa uno personal, queda anotado en el apartado 12 con fecha de traspaso |
| Repositorio `Jsua3/asobares` | Vive en cuenta personal de GitHub. Recomendado moverlo a una organización, o al menos añadir un segundo administrador del gremio |

Si el gremio no aporta correo y medio de pago institucionales en dos semanas, **el problema
no es de proveedor sino de gobierno**: se escala a la junta como riesgo del proyecto. No se
resuelve desplegando con cuenta personal.

### 1.2 El comando que sí es humano

En una terminal **interactiva** del dueño (abre el navegador y pide autorizar):

```powershell
& "$env:APPDATA\Composer\vendor\bin\cloud.bat" auth
```

El token queda en `~\.config\cloud\config.json`. A partir de ahí un agente puede continuar.

**Estado a 19 de agosto de 2026:** el binario `cloud.bat` está instalado
(`laravel/cloud-cli`, en `%APPDATA%\Composer\vendor\bin`), pero **no está autenticado**:
la carpeta `~\.config\cloud\` no existe. Ese es todo el bloqueo.

Un agente **no** debe registrar la cuenta, **no** debe introducir datos de pago y **no**
debe autenticarse por el dueño.

---

## 2. Comprobación de que se puede continuar

```sh
cloud auth -n
cloud application:list --json -n
```

Si el primero falla, se ha saltado el apartado 1.

> **Regla del CLI, no negociable:** `-n` en **todos** los comandos (si falta, la
> herramienta se queda esperando una respuesta que nadie va a dar). Nunca `-q` ni
> `--silent`. Y **nunca se inventa la firma de un comando**: se descubre con
> `cloud <comando> -h` justo antes de usarlo. Las versiones del CLI cambian opciones.

---

## 3. Antes de subir nada: el árbol tiene que estar sano

```sh
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

Y las migraciones contra PostgreSQL de verdad, no contra SQLite. Con el contenedor local:

```sh
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=5433 DB_DATABASE=asobares_staging \
DB_USERNAME=asobares DB_PASSWORD=asobares php artisan migrate:fresh --seed --force
```

Las 33 migraciones y los sembradores están verificados contra PostgreSQL 17.11.

⚠️ **Desactualizado desde el 31 de agosto de 2026.** Hoy son **39 migraciones**: las seis
posteriores —dos de finales de agosto y **cuatro del 31**— **no** se han verificado contra
PostgreSQL real. Una de ellas hace un `UPDATE … LIKE '%Cámara de Comercio%'` con tilde y otra
recorre `custom_properties` escribiendo JSON. Hasta que se repita la corrida del §14, esta
frase vale para las 33 primeras y para ninguna más.

---

## 4. Crear la infraestructura

```sh
cloud ship -h        # primero descubrir las opciones
cloud ship -n        # aplicación + entorno + Postgres serverless
cloud deploy:monitor -n
```

Región: **US East**. Plan **Starter** (US$5/mes con US$5 de uso incluidos, primer mes
gratis).

Sobre el marco legal: EE. UU. tiene nivel adecuado de protección para la Ley 1581 de 2012
(Circular 005/2017 de la SIC), así que la transferencia es legal. Lo que sigue pendiente
—y no es técnico— es **quién firma como responsable del tratamiento**.

---

## 5. Los comandos de construcción y de despliegue

Se configuran en el entorno. Los dos son literales y **ninguno de los tres pasos del
segundo es opcional**:

**Construcción (build):**

```sh
composer install --no-dev --optimize-autoloader && npm ci && npm run build
```

**Despliegue (deploy):**

```sh
php artisan migrate --force && php artisan storage:link && php artisan optimize
```

Por qué cada uno:

- `npm run build` — `/public/build` está en `.gitignore` y el panel usa `viteTheme`. Sin
  esto el panel sale sin estilos y sin la tipografía Poppins.
- `storage:link` — **es obligatorio y hay que escribirlo aquí**. Sólo vive dentro del
  script `setup` de `composer.json`, que Cloud no ejecuta; `post-autoload-dump` sólo corre
  `package:discover` y `filament:upgrade`. `/public/storage` está en `.gitignore`. Sin el
  enlace, todo lo que se sirve por `/storage` da 404: galerías de asociados, portadas de
  eventos, logos de aliados y fotos de artistas.
- `migrate --force` — sin `--force` se queda pidiendo confirmación a nadie.

---

## 6. Las variables del entorno

El fichero que se copia y se pega es **`.env.staging.example`**, en la raíz del repositorio.
Está versionado, cada variable que se aparta del perfil de demostración lleva su porqué
encima, y sólo tiene cuatro huecos marcados con `<...>`.

```sh
cloud environment:variables -h
cloud environment:variables -n --force
```

### 6.1 Tabla de variables — sólo las que importan

Las que valen lo mismo que en el demo no están aquí; están en el fichero.

| Variable | Valor remoto | Qué pasa si se olvida |
|---|---|---|
| `APP_ENV` | `staging` | En `production` los sembradores de demostración se niegan en bloque y el sitio sale vacío. Ojo: `staging` **ya no es blando**, la coraza cubre todo lo que no sea local ni testing |
| `APP_KEY` | La genera Cloud | Reutilizar la local significa que quien tenga el repositorio descifra cookies y datos cifrados |
| `APP_DEBUG` | `false` | La aplicación **se niega a arrancar**. Con `true`, la página de error publica cabeceras, cuerpo de la petición y variables de entorno |
| `APP_URL` | `https://…` | Se rellena tras el `ship`, con lo que devuelva |
| **`TRUSTED_PROXIES`** | `*` | **Bloqueante.** Todas las peticiones parecen venir del balanceador: los límites por IP colapsan en un solo cubo, y las URLs se generan en `http` — incluida la `callback_url` que se le manda a Bold |
| **`LOG_STACK`** | `stderr` | **Bloqueante.** Con `single` el registro muere en un fichero del disco efímero que `cloud environment:logs` no lee. Ahí mueren también los códigos MFA del panel (apartado 10) |
| `LOG_LEVEL` | `info` | Con `debug`, un entorno expuesto vuelca consultas y sus parámetros |
| **`DB_CONNECTION`** | `pgsql` | **Bloqueante.** Cloud inyecta host, puerto, base, usuario y clave, pero **no** esta. El defecto silencioso es `sqlite`: la aplicación busca un fichero en el disco efímero y el sitio entero da 500 con las variables de Postgres correctas al lado |
| `DB_SSLMODE` | `require` | `prefer` acepta una conexión sin cifrar si el servidor no la ofrece |
| `SESSION_SECURE_COOKIE` | `true` | `config/session.php` sólo la marca sola cuando `APP_ENV=production`, y aquí es `staging`. Sin ella la cookie del panel viaja también por http |
| `CACHE_STORE` | `database` | Tiene que ser **compartida**: los ajustes del sitio se memorizan con `rememberForever` y se invalidan por evento de modelo. Una caché por instancia se desincroniza y unas instancias sirven ajustes viejos |
| `APP_MAINTENANCE_DRIVER` | `cache` (+ `APP_MAINTENANCE_STORE=database`) | Con `file`, `php artisan down` deja en mantenimiento **una** instancia y las demás siguen sirviendo |
| `QUEUE_CONNECTION` | `sync` | Decisión, no descuido — ver 6.2 |
| `QUEUE_CONVERSIONS_BY_DEFAULT` | `false` | El seguro de lo anterior — ver 6.2 |
| `MAIL_MAILER` | `smtp` | `log` **impide arrancar** (escribiría las PQR con datos del ciudadano en el registro). `resend`, `postmark` y `ses` **revientan en ejecución**: sus paquetes no están en `vendor/` — ver 6.3 |
| `PAYMENT_DRIVER` | `bold` | La pasarela simulada se niega a existir fuera de local y testing — ver 6.4 |
| `FILESYSTEM_DISK` / `MEDIA_DISK` | `public` | **Ver el apartado 8, que es el problema abierto de este despliegue** |

### 6.2 La trampa de la cola

`sync` es lo correcto: no hay `app/Jobs/`, ningún `ShouldQueue`, y los cinco envíos de
correo son síncronos. Queda anotado como **decisión** porque el día que alguien la cambie
a `database` «para hacerlo bien» sin levantar antes un `background-process` en Cloud,
`spatie/laravel-medialibrary` empezará a encolar sus conversiones (su configuración trae
`queue_conversions_by_default` en `true`) y las miniaturas de la galería dejarán de
generarse: sin error, sin aviso, sin miniaturas. `QUEUE_CONVERSIONS_BY_DEFAULT=false` es
el seguro contra eso.

### 6.3 El correo: sólo SMTP

Laravel Cloud **no incluye correo saliente**. El único transporte instalado que funciona
sin tocar dependencias es `smtp`. `MAIL_MAILER=resend`, `postmark` o `ses` fallan en
tiempo de ejecución: en `vendor/symfony/` sólo está `mailer`, sin `postmark-mailer` ni
`amazon-mailer`, y no está `resend/resend-laravel`. `config/services.php` declara las
llaves pero ningún transporte las consume.

Resend, Postmark y Brevo publican todos endpoint SMTP, así que se contrata cualquiera con
la cuenta del gremio y se rellenan `MAIL_HOST`, `MAIL_USERNAME` y `MAIL_PASSWORD`. Con
correo real configurado, los códigos MFA llegan de verdad y no hace falta el camino de
emergencia del apartado 10.

### 6.4 Los pagos no se demuestran, y es a propósito

Se despliega con `PAYMENT_DRIVER=bold` y sin llaves de Bold, así que el enlace de pago
falla adrede. Lo que **ya no pasa** es que ese fallo se vea como una página 500 pelada:
`EventoController::inscribir` y `MiCuentaController::pagarMensualidad` lo capturan, lo
registran y devuelven un mensaje que se entiende. Ambas rutas están cubiertas por
`tests/Feature/FlujoDePagoTest.php`.

---

## 7. El límite de gasto — antes de exponer la URL

Se fija en el panel de Cloud, **antes** del primer despliegue público.

- Techo: **~US$10/mes**. Ese número, y no el piso de US$5, es el que se le presenta a la
  junta.
- Desde junio de 2026 los límites de gasto **pausan** el cómputo en vez de facturar de más.

Verificación (importes en centavos):

```sh
cloud usage --json -n
```

---

## 8. ⚠️ EL ALMACENAMIENTO ES EFÍMERO ⚠️

> **Esto no está resuelto. Léalo antes de pedirle a la tutora que suba nada.**

### 8.1 Qué pasa exactamente

`config/filesystems.php` define los dos discos que usa la aplicación con driver `local`,
sobre el disco de la máquina:

| Disco | Ruta real | Quién escribe ahí |
|---|---|---|
| `public` | `storage/app/public` | `SubidaSegura.php:42` (las cinco imágenes del panel), la galería de asociados (`Asociado.php:100`, `useDisk('public')`) |
| `local` | `storage/app/private` | `SubidaSegura.php:66` — los **PDF oficiales de la guía normativa**, que sirve `GuiaController.php:69` |

En un entorno serverless ese disco es **efímero**: no sobrevive a un despliegue y no se
comparte entre instancias. Consecuencias concretas, no teóricas:

1. La tutora sube la portada de un evento desde el panel. Se ve. Al siguiente despliegue
   **desaparece** y queda una imagen rota.
2. Con más de una instancia sirviendo, quien suba un archivo lo verá y otro visitante no,
   según a qué instancia lo mande el balanceador. Intermitente, que es lo peor de
   diagnosticar.
3. Los formatos PDF de la guía normativa —el módulo insignia— caen en el mismo saco.

El contenido de las semillas **no** se pierde, porque viaja dentro del sembrador y se
regenera en cada `db:seed`. Lo que se pierde es todo lo que se suba a mano.

### 8.2 Cómo se resuelve — el lado de la aplicación ya está hecho y probado

El driver **está instalado** (`league/flysystem-aws-s3-v3`) y los dos discos de objetos
están definidos y verificados contra un almacén compatible con S3 (MinIO), el 19 de agosto:

| Comprobación | Resultado |
|---|---|
| Escribir y leer en el disco público | ✅ |
| Escribir y leer en el disco privado | ✅ |
| URL pública de una portada, por HTTP anónimo | ✅ `200` |
| PDF de la guía por URL directa al bucket | ✅ `403` |
| Descarga del PDF **por `GuiaController`** | ✅ `200`, `application/pdf`, 2.639 bytes, nombre limpio, `nosniff` |

El día que exista el bucket, todo el cambio son dos variables:

```sh
DISCO_PUBLICO=s3
DISCO_PRIVADO=s3-privado
```

más las seis `AWS_*` que devuelve `cloud bucket:create --json -n`.

> ⚠️ **No basta con `FILESYSTEM_DISK=s3` ni con `MEDIA_DISK=s3`**, aunque sea lo primero que
> uno escribe. Los puntos de subida nombran su disco de forma explícita —tienen que
> hacerlo, porque la diferencia entre los dos es de seguridad y no de configuración— y
> `Asociado::registerMediaCollections()` llama a `useDisk()`, que pisa a `MEDIA_DISK`.
> Quien cambie sólo esas dos variables verá que **nada se mueve** y no sabrá por qué.
> Lo vigila `tests/Feature/AlmacenamientoTest.php`.

### 8.3 ⚠️ La política del bucket es la frontera de seguridad, y la natural está mal

Esto es lo único de este apartado que **no se puede delegar en la aplicación**, y es donde
se va a equivocar quien cree el bucket.

La forma evidente de abrir un bucket para que se vean las imágenes es conceder
`s3:GetObject` sobre todo:

```json
"Resource": ["arn:aws:s3:::<bucket>/*"]
```

Medido: con esa política, **los formatos oficiales de la guía normativa se descargan por URL
directa**, devolviendo `200` sin pasar por `GuiaController` — que es el único sitio donde se
comprueba que el requisito esté publicado. Es exactamente el agujero que se cerró cuando
esos PDF salieron del disco `public`, reabierto por la puerta de atrás.

La política correcta acota al prefijo público:

```json
{
  "Version": "2012-10-17",
  "Statement": [{
    "Effect": "Allow",
    "Principal": {"AWS": ["*"]},
    "Action": ["s3:GetObject"],
    "Resource": ["arn:aws:s3:::<bucket>/publico/*"]
  }]
}
```

Con ella, medido: lo público responde `200` y lo privado `403`.

Los dos prefijos —`publico/` y `privado/`— los fija `config/filesystems.php` con el `root` de
cada disco, y `AlmacenamientoTest` afirma que siguen siendo distintos: si alguien los iguala,
ninguna política puede volver a separarlos y el control vuelve a ser decorativo sin que nada
se ponga en rojo.

**Después de crear el bucket, compruébelo con dos `curl`**, no lo dé por bueno:

```sh
curl -o /dev/null -w "%{http_code}\n" "<AWS_URL>/publico/<cualquier-imagen>"   # espera 200
curl -o /dev/null -w "%{http_code}\n" "<AWS_URL>/privado/formatos/<un-pdf>"    # espera 403
```

### 8.4 Qué pasa si no se resuelve

Se puede llegar a la demostración del 22 de septiembre con el disco local **si y sólo si**
se acepta y se comunica esto:

- Nadie sube archivos definitivos desde el panel hasta que exista el bucket.
- Todo lo que se suba en pruebas se da por perdido.
- Si en la demostración hay que enseñar una imagen subida a mano, se sube **el mismo día**
  y no se despliega nada entre la subida y la demostración.

Si esas tres condiciones no se pueden garantizar, el bucket deja de ser fase 2 y pasa a ser
bloqueante.

---

## 9. ⚠️ `db:seed` NO es seguro contra una base con datos reales ⚠️

Hasta este trabajo, **de los veinte sembradores sólo `UsuarioSeeder` se negaba a correr en
producción**. Los otros diecinueve corrían sin freno con `db:seed --force`:

- `AsociadoSeeder` publica establecimientos inventados **en el directorio público real**.
- `MensajeSeeder` inserta PQR ficticias con nombre, correo y teléfono, y **consume
  radicados del consecutivo anual de verdad** (`PQR-2026-0001`, `-0002`…), así que la
  numeración oficial del gremio queda corrida.
- `TransaccionSeeder` inserta pagos en estado *aprobado* que la conciliación y el widget de
  recaudo van a sumar como ingresos.

Ahora `DatabaseSeeder` se niega en bloque cuando `APP_ENV=production`, y
`ConfiguracionDeDespliegueTest` lo vigila. **Aun así, la regla operativa no cambia:**

> Se siembra **una sola vez**, sobre una base vacía, en el entorno `staging` y antes de que
> nadie del gremio haya escrito un dato de verdad. A partir de ese momento, `db:seed` no se
> vuelve a ejecutar nunca contra ese entorno.

La razón de que el entorno se llame `staging` y no `production` es precisamente poder
sembrar los datos de demostración. El día que la plataforma tenga datos reales del gremio,
el entorno pasa a `production` y esa puerta se cierra sola.

---

## 10. Sembrar, humo y entrar al panel

### 10.1 Semilla (una sola vez — leer el apartado 9)

```sh
cloud command:run <entorno> --cmd='php artisan db:seed --force' -n
```

### 10.2 Comprobación de que la configuración llegó

```sh
cloud command:run <entorno> --cmd='php artisan about' -n
cloud tinker <entorno> -n --code='dump(config("app.env"), config("app.debug"), config("session.secure"), config("database.default"), config("pagos.driver"), config("logging.channels.stack.channels"));'
```

Esperado, en ese orden: `staging` · `false` · `true` · `pgsql` · `bold` · `["stderr"]`.
Cualquier otra cosa se corrige antes de seguir.

### 10.3 Humo de rutas

Contra la URL que devolvió el `ship`:

```
/            /directorio      /directorio?q=BAR      /eventos       /empleo
/guia        /noticias        /contacto              /up            /admin/login
/mi-cuenta/entrar
```

**La tercera es la importante.** Valida el arreglo del buscador: `q=BAR` y `q=bar` tienen
que devolver **exactamente lo mismo**. Si `bar` en minúsculas devuelve menos resultados,
el arreglo de `Asociado::scopeBuscarPorNombre` no llegó al despliegue.

### 10.4 Los códigos MFA sin proveedor de correo

El panel exige segundo factor de forma obligatoria. Si todavía no hay SMTP contratado
(apartado 6.3), el código se lee del registro:

```sh
cloud environment:logs -n
```

**Esto sólo funciona con `LOG_STACK=stderr`.** Con `single` el código se escribe en
`storage/logs/laravel.log`, un fichero del disco efímero que ese comando no lee, y el
acceso al panel deja de ser demostrable. Es el camino de emergencia, no el destino: en
cuanto haya SMTP, el código llega al correo y esto se olvida.

---

## 11. Cuando algo falla

| Síntoma | Causa casi segura | Arreglo |
|---|---|---|
| Todo el sitio da 500 y las variables de la base parecen correctas | Falta `DB_CONNECTION=pgsql`: la aplicación cayó al defecto `sqlite` | Añadir la variable y redesplegar |
| El sitio carga pero **todas** las imágenes dan 404 | No se ejecutó `storage:link` | Comprobar que el comando de despliegue tiene los tres pasos del apartado 5 |
| El panel sale sin estilos ni Poppins | Falta `npm run build` en el comando de construcción | Apartado 5 |
| La aplicación no arranca y el error nombra `APP_DEBUG` o `MAIL_MAILER` | La coraza está haciendo su trabajo | Poner `APP_DEBUG=false` / `MAIL_MAILER=smtp`. **No** se desactiva la coraza |
| Las URLs salen en `http` y los límites por IP se disparan con poco tráfico | Falta `TRUSTED_PROXIES=*` | Apartado 6.1 |
| El buscador del directorio encuentra menos de lo que debería | Se revirtió el `whereLike(caseSensitive: false)` | `php artisan test --filter=ConfiguracionDeDespliegue` |
| No hay forma de leer el código MFA | `LOG_STACK` quedó en `single` | Apartado 10.4 |
| Una imagen subida ayer ya no está | Es el apartado 8, funcionando como se explicó | No es un fallo que se arregle redesplegando |
| El despliegue falla y no se ve por qué | — | `cloud deploy:monitor -n`; si el mismo error se repite tras un intento de arreglo, **parar y preguntar** en vez de insistir |

Diagnóstico general: leer la salida, mirar el estado con `<recurso>:list --json -n` o
`<recurso>:get --json -n`, y si es error de autenticación, `cloud auth -n`.

---

## 12. A nombre de quién quedó todo

**Rellenar el día del despliegue.** Este apartado es el que impide que las llaves se vayan
con la práctica; si se queda vacío, el riesgo R-14 no está cerrado por mucho que el sitio
funcione.

| Campo | Valor |
|---|---|
| Fecha del despliegue | _(pendiente)_ |
| URL del entorno | _(pendiente)_ |
| Nombre del entorno en Cloud | _(pendiente)_ |
| Correo de la cuenta de Laravel Cloud | _(pendiente — debe ser `asobaresquindio@asobares.org`)_ |
| Medio de pago | _(pendiente — del gremio; si es personal, anotar de quién y fecha comprometida de traspaso)_ |
| Límite de gasto configurado | _(pendiente — objetivo ~US$10/mes)_ |
| Proveedor SMTP contratado | _(pendiente — si no hay, los códigos MFA se leen por el apartado 10.4)_ |
| ¿Bucket de objetos creado? | _(pendiente — si no, rige el apartado 8.3)_ |
| Segundo administrador del repositorio | _(pendiente)_ |
| Quién firma como responsable del tratamiento (Ley 1581) | _(pendiente)_ |

---

## 13. Cuándo se reabre la decisión de proveedor

No antes. Los tres falsadores acordados:

1. Dos facturas seguidas por encima de ~US$15/mes con el límite de gasto puesto y sin
   crecimiento de tráfico → reevaluar una PaaS genérica con Dockerfile.
2. Otra caída total de Laravel Cloud que afecte a aplicaciones (la única seria hasta la
   fecha: 20 de febrero de 2026, 3 h 15 min).
3. El gremio no aporta correo ni medio de pago institucionales en dos semanas → es un
   problema de gobierno, se escala a la junta, **no** se resuelve desplegando con cuenta
   personal.

---

## 14. Barrido SQLite → PostgreSQL

El demo corre sobre SQLite y el hosting sobre PostgreSQL. Esto es lo que se buscó, una
familia a una, y lo que salió. Todo lo marcado como *medido* se comprobó contra un
PostgreSQL 17.11 real con el esquema migrado y los datos de demostración sembrados.

### 14.1 Arreglado

| Qué | Qué pasaba |
|---|---|
| **`LIKE` del buscador del directorio** | En SQLite `LIKE` es insensible a mayúsculas para ASCII; en PostgreSQL es **sensible**. Medido: `like '%Bar%'` → 6 filas, `like '%bar%'` → **4**, `ilike '%bar%'` → **10**. La única búsqueda de texto del sitio público habría encontrado cuatro de cada diez establecimientos, en silencio, hasta que alguien lo notara en la demostración. Resuelto con `whereLike(caseSensitive: false)`, que la gramática de Laravel traduce a `ilike` en Postgres y a `like` en SQLite |
| **Secuencia de identidad de `aspirantes`** | Insertar `id` explícito no adelanta la secuencia en Postgres. Medido: con filas de id 5 y 9, el siguiente `nextval` devolvía **1**; con el `setval` devuelve 10. Sólo muerde al importar el volcado y migrar encima, pero cuesta cuatro líneas |

### 14.2 Comprobado y sano — no se toca

- **Funciones de fecha.** Ya estaban aisladas por driver (`to_char` / `date_format` /
  `strftime`) en `RecaudoMensual` y en `MetricasDelObservatorio::expresionMes`.
- **`GROUP BY` por alias del `SELECT`.** PostgreSQL admite nombres de columna de salida en
  `GROUP BY`; medido con una y con dos claves de agrupación.
- **JSON.** ⚠️ **Dejó de ser cierto el 31 de agosto de 2026, y costó un defecto.** Esta
  entrada decía que las columnas `json` se usaban sólo por el cast `array` y que no había
  `whereJsonContains` ni operadores `->` en SQL. OBS3-13 metió uno, y estaba **roto en
  PostgreSQL**: `whereNot(whereJsonContains(…, true))` emite
  `not (("custom_properties"->'aprobada')::jsonb @> ?)`, y sobre una fila **sin la clave** el
  `->` da NULL, `NULL @> 'true'` da NULL, `not NULL` da NULL y el `WHERE` descarta la fila —
  justo la foto que nadie había marcado, que es la que más falta hacía moderar. En SQLite no
  se reproduce, así que la suite pasaba verde. Arreglado en `ModerarFotos::consultaBase()`
  añadiendo la rama de la clave ausente, y vigilado por `ColaDeFotosTest`, que afirma sobre
  la **SQL generada** por cada gramática siguiendo el §15.
  **Alcance medido:** `ModerarFotos.php` es hoy el ÚNICO sitio del árbol con
  `whereJsonContains` (grep sobre todo el repositorio salvo `vendor/`), así que no hace falta
  volver a barrer entero — pero la afirmación de que «no hay JSON en SQL» ya no se puede
  reutilizar sin comprobarla.
- **Tipos de vuelta.** Medido: `count(*)` llega como `int`, los booleanos como `bool`, los
  `decimal` como cadena — igual que en SQLite tras los casts —, y `sum()` sobre conjunto
  vacío da 0 en ambos. Todos los consumidores castean explícitamente.
- **Longitud de identificadores.** PostgreSQL trunca a 63 caracteres; el índice más largo
  del esquema mide 49.
- **`->after()` en migraciones.** Es una pista sólo de MySQL: Postgres y SQLite la ignoran
  y añaden al final. Ningún `SELECT *` del código depende del orden. *Efecto colateral que
  sí importa:* el volcado de la base de Cloud tendrá las columnas en orden distinto al de
  `docs/ingenieria/base-de-datos/`, y quien los compare creerá que hay divergencia de
  esquema. No la hay.
- **Buscador del panel.** Filament fuerza `lower()` sobre pgsql en su expresión de
  búsqueda, así que las tablas del panel no tienen el problema del directorio.
- **`distinct()` con `orderBy`.** En los dos usos la columna ordenada está en el `SELECT`,
  que es lo que PostgreSQL exige.
- **Precisión de marcas de tiempo.** Ambos motores guardan al segundo con el mismo formato.

### 14.3 Reportado y **no** arreglado — decisión pendiente

**1. El consecutivo de PQR puede fallar bajo concurrencia en PostgreSQL.**

`Mensaje::generarRadicado()` dice «se bloquea la tabla», pero `lockForUpdate()` bloquea
*filas*, no la tabla, y no puede bloquear una fila que todavía no existe. En SQLite la
transacción de escritura serializa la base entera y el problema no se ve nunca. En
PostgreSQL, con aislamiento *read committed*, dos envíos simultáneos pueden leer el mismo
último radicado y calcular el mismo siguiente.

**Falla cerrado**, que es lo que salva el caso: hay índice único sobre `radicado`
(`mensajes_radicado_unique`, verificado en el motor real), así que el segundo envío recibe
un error en vez de un radicado duplicado. Nadie se queda con dos PQR con el mismo número.

Probabilidad real en un gremio con este volumen: muy baja. Arreglo, si algún día se quiere:
un *advisory lock* de Postgres o `LOCK TABLE … IN EXCLUSIVE MODE`, aislado por driver
exactamente igual que las expresiones de fecha. No se hizo aquí para no meter un cambio de
comportamiento en la parte del expediente de PQR sin decisión del dueño.

**2. El orden alfabético cambiará, y el contenedor local no lo reproduce.**

El PostgreSQL de pruebas de esta máquina es `postgres:17-alpine`. Alpine usa *musl*, donde
la colación se comporta como orden de bytes: medido, `'a' < 'B'` es falso y el orden por
defecto es idéntico a `COLLATE "C"` — es decir, **igual que SQLite**. El PostgreSQL de
Cloud será casi con seguridad glibc o ICU, donde el orden es sensible al idioma:
`Café de los Andes` y `Café Palma de Cera` cambiarán de sitio, y las minúsculas dejarán de
ir todas después de las mayúsculas.

No es un fallo y no rompe nada, pero **parecerá** un fallo, y el contenedor local **no
sirve para anticiparlo**. Afecta al directorio, a los municipios, a las categorías y a los
géneros musicales. Si el orden del directorio importa de verdad, se fija con una colación
explícita en la consulta; hoy no hay motivo para hacerlo.

**3. Los acentos, para que nadie persiga un fantasma.**

Medido: `ilike` de PostgreSQL **sí** pliega mayúsculas acentuadas (`'MERLÍN' ilike
'%merlín%'` → verdadero), y el `LIKE` de SQLite **no** (→ falso). O sea que, tras el
arreglo, el buscador desplegado encuentra *más* que el de desarrollo. Lo que no hace
ninguno de los dos es ignorar la tilde: escribir «merlin» no encuentra «MERLÍN». Eso es una
limitación conocida, no una regresión del despliegue.

---

## 15. Qué vigila la suite, para que esto no se deshaga

`tests/Feature/ConfiguracionDeDespliegueTest.php` afirma, entre otras cosas:

- Que fuera de `local`/`testing` se fuerza `https`, se marca la cookie `Secure`, y la
  aplicación **se niega a arrancar** con `APP_DEBUG=true` o con `MAIL_MAILER=log`.
- Que en `local` y en `testing` esa coraza no estorba.
- Que el buscador del directorio emite `ilike` con la gramática de PostgreSQL y `like` con
  la de SQLite. **Esto se afirma sobre la SQL generada y no sobre el resultado**, porque la
  suite corre en SQLite, donde el defecto no se reproduce: una prueba de comportamiento
  sola saldría verde con el código roto.
- Que los datos de demostración no entran con `APP_ENV=production`, ni siquiera con
  `--force`.
- Que `.env.example` y `.env.staging.example` declaran las variables que el código lee.
- Que este runbook sigue avisando de lo que muerde.

Si alguna de esas pruebas se pone en rojo, no se «arregla la prueba»: se ha roto algo que
se rompe en producción.

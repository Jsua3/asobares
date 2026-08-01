# Plataforma web — ASOBARES Capítulo Quindío

Prototipo funcional de la plataforma web del gremio de la vida nocturna del Quindío:
sitio público en Blade + Tailwind con identidad propia, y panel de administración a la
medida en Filament.

No es un mockup. Todo el contenido sale de la base de datos, los formularios guardan de
verdad, el flujo de aprobación se hace cumplir en el modelo y los pagos recorren el mismo
camino que recorrerían con Bold en producción.

---

## Qué incluye

**Sitio público**

| Ruta | Qué es |
|---|---|
| `/` | Inicio con cifras del Observatorio, destacados, beneficios y aliados |
| `/quienes-somos` | Manifiesto del gremio, visión a 10 años, barreras del sector, líneas de trabajo y las 5 iniciativas en marcha |
| `/directorio` · `/directorio/{slug}` | Establecimientos con filtros compartibles por URL, vista de mapa y ficha con JSON-LD |
| `/abre-tu-negocio` | **La guía normativa por municipio**: checklist, costos y formatos descargables |
| `/empleo` | Muro de vacantes + formulario de aspirante |
| `/artistas` · `/artistas/{slug}` | DJs, bandas y solistas con tarifa y video embebido |
| `/proveedores` | Bolsa por categoría, filtrada por vigencia pagada |
| `/eventos` · `/eventos/{slug}` | Solo eventos del gremio, con inscripción y pago |
| `/boletin` · `/boletin/{slug}` | Boletín de baja frecuencia |
| `/afiliate` · `/contacto` | Formularios con habeas data; las PQR generan radicado |
| `/mi-cuenta` | Estado de cartera del afiliado y detalle privado de convenios |
| `/politica-de-datos` | Política conforme a la Ley 1581 de 2012 |

**Panel `/admin`** — 18 recursos en español agrupados en Contenido, Bolsas, Bandejas,
Gremio y Configuración; flujo de aprobación con notificaciones; importador CSV de cartera;
bitácora legible; MFA; y dashboard con 6 indicadores.

---

## Requisitos

- **PHP 8.3 o superior** con las extensiones `intl`, `gd`, `exif`, `fileinfo`, `mbstring`,
  `openssl`, `curl`, `zip`, `sqlite3` y `pdo_sqlite`.
  `intl` la exige Filament y `gd` las conversiones de imagen: sin ellas la instalación falla.
- **Composer 2**
- **Node 20 o superior** con npm

> En el equipo donde se construyó, el PHP de `php.new` no sirve: es un build estático sin
> `intl` ni `gd`. Se usa el build oficial de `windows.php.net` instalado en
> `C:\Users\Predator\.config\php85`.

---

## Cómo correrlo

```bash
composer setup
```

Ese único comando instala dependencias, crea el `.env`, genera la llave, enlaza `storage`,
migra, siembra los datos de demostración y compila los assets. Después:

```bash
composer run dev
```

Queda en **http://localhost:8000**. Si prefieres solo el servidor: `php artisan serve`.

> **Si ves la página de bienvenida de Laravel en vez del sitio**, el puerto 8000 lo tiene
> otro proyecto. `php artisan serve` avisa en qué puerto quedó, pero es fácil pasarlo por
> alto: fíjate en la línea `INFO Server running on [...]` y abre esa dirección exacta.
> Para forzar otro puerto: `php artisan serve --port=8765`.
>
> Para ver quién ocupa el 8000 en Windows:
> `Get-NetTCPConnection -LocalPort 8000 -State Listen`

---

## Credenciales de la demostración

Las imprime el propio seeder al terminar.

| Rol | Correo | Contraseña | Entra por |
|---|---|---|---|
| `super_admin` (dirección) | `direccion@asobaresquindio.test` | `Asobares2026*` | `/admin` |
| `subadmin` (secretaría) | `oficina@asobaresquindio.test` | `Asobares2026*` | `/admin` |
| `asociado` (dueño) | `asociado@asobaresquindio.test` | `Asobares2026*` | `/mi-cuenta` |

El usuario asociado está vinculado a **Bruma Gastrobar**, que tiene **3 meses de mora**
a propósito, para el guion de cartera.

---

## Guion 1 — El flujo de aprobación (RF-37)

Es el requisito central: **la secretaría redacta, solo la dirección publica**, y la regla
vive en el modelo, no en el formulario.

1. Entra a `/admin` como **`oficina@asobaresquindio.test`** (secretaría).
2. Ve a **Contenido → Asociados** y crea uno nuevo. En «Publicación», escoge
   **Publicado** y guarda. Fíjate en la ayuda del campo: te avisa que quedará pendiente.
3. Mira la lista: el registro quedó en **Pendiente de aprobación**, no en Publicado.
   El observer lo interceptó al guardar.
4. Comprueba que no salió al sitio: abre `/directorio` en otra pestaña — no está.
5. Cierra sesión y entra como **`direccion@asobaresquindio.test`** (dirección).
   Arriba a la derecha, la campana tiene una **notificación** con acción «Revisar».
6. En la fila del asociado, usa **«Aprobar y publicar»**. Vuelve a `/directorio`: ya aparece.

Para verlo del otro lado: **Configuración → Bitácora** registra quién hizo cada cosa y cuándo.

> Que esto no se pueda burlar manipulando el formulario está cubierto por
> `tests/Feature/FlujoDeAprobacionTest.php`.

---

## Guion 2 — Pago simulado de un evento

1. Ve a `/eventos` y abre **ExpoBar Quindío 2026** (tiene precio de $30.000).
2. Llena el formulario de inscripción, marca la autorización de datos y envía.
3. Caes en `/pago-simulado/{referencia}`: la pantalla con la marca del gremio y el
   selector **PSE / Tarjeta**.
4. Pulsa **Pagar $30.000**. La inscripción queda **Confirmada** y la transacción **Aprobada**.
5. Prueba también **«Simular pago rechazado»** con otra inscripción: la transacción queda
   rechazada y la inscripción **sin confirmar**.
6. Verifícalo en el panel: **Gremio → Transacciones** (solo lectura) y
   **Bandejas → Inscripciones**.

---

## Guion 3 — Estado de cartera del afiliado

El caso que pidió la directiva: *«la gente no paga porque no sabe cuánto debe, entonces
todo el mundo llama a Natalia»*.

1. Entra a `/mi-cuenta` como **`asociado@asobaresquindio.test`**.
2. Verás **«Debes 3 meses · $150.000»** en rojo, con el botón **Pagar ahora**.
3. Baja un poco: ahí está el **detalle de los convenios**, que en el sitio público no
   aparece por ningún lado.
4. Pulsa **Pagar ahora** → escoge PSE → **Pagar**.
5. Vuelve a `/mi-cuenta`: ahora dice **«Estás al día»** y el movimiento aparece en el
   historial.

---

## Guion 4 — Importar el CSV de la contadora

1. En `/admin`, ve a **Gremio → Cartera**.
2. Pulsa **«Descargar plantilla»**: baja un CSV con el estado actual y las columnas exactas.
3. Ábrelo, cambia algunos valores y guárdalo.
4. Pulsa **«Importar CSV de la contadora»**, sube el archivo y confirma.
5. Sale un resumen: cuántos estados de cuenta se actualizaron y, si algo falló, **qué fila
   y por qué**. Una fila mala nunca aborta el resto.

**Formato esperado:**

```csv
establecimiento,saldo_pendiente,meses_mora,ultimo_pago
"Bruma Gastrobar",150000,3,2026-05-01
"La Cava del Yipao",0,0,2026-08-01
```

El importador tolera encabezados con tildes y mayúsculas (`Último Pago`), montos con
formato colombiano (`$1.250.000`) y fechas en `AAAA-MM-DD` o `DD/MM/AAAA`.

---

## Guion 5 — PQR con radicado

1. Ve a `/contacto`, escoge **PQR**, llena el formulario y envía.
2. En pantalla aparece el radicado, con formato **`PQR-2026-0001`**, consecutivo y sin saltos.
3. El acuse de recibo se envía por correo. En el demo el mailer es `log`: búscalo en
   `storage/logs/laravel.log`.
4. En el panel, **Bandejas → Mensajes y PQR**: ahí está, con su radicado. La acción
   **«Marcar respondido»** pide una nota que queda como constancia.

---

## Activar Bold de verdad

La integración está implementada según la documentación pública de
[developers.bold.co](https://developers.bold.co), pero **sin credenciales**. Para activarla:

```env
PAYMENT_DRIVER=bold
BOLD_API_KEY=tu_llave_de_identidad
BOLD_SECRET=tu_llave_secreta
BOLD_SANDBOX=true
BOLD_API_URL=https://integrations.api.bold.co
```

Después registra en el panel de Bold la URL del webhook:

```
https://tu-dominio.com/webhooks/bold
```

`PasarelaBold` crea el link de pago por API y verifica la **firma HMAC-SHA256** del
encabezado `x-bold-signature` **antes** de leer el cuerpo de la notificación. Si la firma no
cuadra, la petición se rechaza con 401 y queda en el log. No hace falta tocar ninguna otra
parte del código: el resto del sistema solo conoce la interfaz `PasarelaDePago`.

---

## Pruebas

```bash
php artisan test
```

**183 pruebas, 299 aserciones.** Cubren:

- El flujo de aprobación, incluido que un subadmin no publique ni manipulando el formulario.
- El **login real del panel** (formulario Livewire, no `actingAs`) y que el modelo `User`
  cumpla los tres contratos de MFA que el panel declara.
- Un **barrido completo del panel**: listado, formulario de creación y formulario de edición
  con un registro real de *cada* recurso, más el escritorio, los ajustes, la bitácora, el
  perfil y todos los widgets. Los recursos se descubren solos, así que uno nuevo entra a la
  prueba sin tocar nada.
- Las **acciones invocadas de verdad**: aprobar, devolver a borrador, marcar respondido,
  guardar ajustes, y la creación por el formulario real de Filament (donde se comprueba que
  la secretaría termina en «pendiente» aunque escoja «Publicado»).
- Todas las rutas públicas y del panel, con sus permisos por rol.
- Que la ficha pública no filtre representante, correo ni notas internas del asociado.
- Que una vacante no publicada no aparezca en `/empleo`.
- Habeas data obligatorio y honeypot en los formularios públicos.
- Radicados de PQR consecutivos y acuse por correo.
- Inscripción → pago → confirmación, y mi-cuenta → pago → cartera al día.
- Idempotencia de las confirmaciones de pago y verificación de la firma HMAC de Bold.
- Importación de CSV con encabezados y montos en formato colombiano.
- Extracción del ID de YouTube, rechazando dominios falsos.

Formato de código:

```bash
vendor/bin/pint
```

---

## Identidad de marca

Todo sale del **Manual de Marca de Asobares Colombia** (re-styling de Quida Studio) y del kit
de logo del capítulo, que están en `material/`. Nada de esto se inventó.

**Paleta principal**

| Nombre | HEX | Uso |
|---|---|---|
| Pub Red | `#EE4137` | Único acento. `marca-500` en Tailwind |
| Pub Black | `#0B090A` | Fondo del sitio. `noche-950` |
| Ambient White | `#F5F3F4` | Texto. `noche-50` |

**Paleta secundaria** — Pub Grey `#282628` (rampa de neutros), Wine `#A4161A`,
Ambient Purple `#C05299`, Ambient Rose `#EA698B`. Disponibles como `vino`, `purpura` y `rosa`.

**Tipografía** — **Poppins** en Light (300), Medium (500), Bold (700) y Black (900), los
cuatro pesos que documenta el manual. Se sirve auto-alojada por Vite, sin llamadas a Google.
Rage Italic y Patrick Hand quedan fuera: el manual las reserva para activaciones puntuales.

**Logo** — se usa el archivo oficial completo, sin recolorear ni reorganizar, como exige el
manual. El componente `<x-publico.logo>` es el único punto que lo dibuja.

| Archivo | Cuándo |
|---|---|
| `public/img/logo-asobares.svg` | Por defecto (rojo sobre fondo oscuro) |
| `public/img/logo-asobares-blanco.png` | Fondos rojos o fotografías |
| `public/img/monograma-asobares.png` | Símbolo «ab» suelto |
| `public/img/favicon.png` | Monograma sobre Pub Black |

**Motivos gráficos** — la clase `.trama-puntos` reproduce la trama de puntos rojos de las
piezas del gremio, y `.antetitulo` el versalitas espaciado que usan en sus antetítulos.

---

## De dónde sale el contenido institucional

Ni un texto del sitio se inventó. Todo viene de `material/`:

| Fuente | Qué aporta |
|---|---|
| Manual de Marca de Asobares Colombia | Paleta, tipografía y normas de uso del logo |
| Kit de logo del capítulo | El logo oficial con «Capítulo Quindío» |
| **TED gremial** | El manifiesto («Nos conocen por la rumba…»), la visión a 10 años, las dos barreras del sector y las **5 iniciativas con su estado** |
| Plan Estratégico | Las tres líneas de trabajo y sus programas |
| Cronograma del sitio web | Objetivos, fases y requisitos de rendimiento y seguridad |

Las **iniciativas** son una entidad propia (`iniciativas`), no texto quemado, porque cambian
de estado con el tiempo: **En formulación → Escalando → En ejecución**. La dirección las
reordena y les cambia el estado desde **Contenido → Iniciativas del gremio**, y pasan por el
mismo flujo de aprobación que el resto.

El manifiesto, la visión y las barreras viven en el grupo **Manifiesto del gremio** de la
página de Ajustes.

---

## Decisiones que conviene saber

**Laravel 13, no 12.** El instalador oficial ya no entrega Laravel 12. El conflicto real no
era la versión de Laravel sino **Livewire**: Filament 4 exige `livewire/livewire ^3.5` y el
starter kit de Livewire de Laravel 13 trae Livewire 4. Por eso el proyecto se creó **sin
starter kit** (`--no-authentication`) y el login de `/mi-cuenta` se escribió a mano con la
identidad oscura de la marca. **No instales el starter kit de Livewire ni subas a Livewire 4
mientras el panel sea Filament 4.**

**Filament 4, no 5.** Filament 5 salió el 31 de julio de 2026. Se descartó por ser demasiado
nuevo para un demo con fecha de entrega.

**`estado` en requisitos y aliados.** El modelo de datos original no se los daba, pero la
sección 6 del documento de requisitos pide que también pasen por aprobación. Se les agregó.
En aliados, `estado` (aprobado por la dirección) y `activo` (se muestra hoy en el carrusel)
son cosas distintas: para salir al sitio hacen falta las dos.

**`SEED_GALERIA=false` en las pruebas.** Las galerías generan 36 conversiones webp por
siembra. Es lo correcto para el demo, pero en la suite multiplicaba el tiempo por veinte
(270 s → 11 s) sin verificar nada nuevo.

---

## Pendientes conocidos

1. **Los datos de los asociados son ficticios.** Faltan el archivo real de los ~60
   afiliados y sus autorizaciones de publicación (habeas data). El propietario decide qué
   se publica de su establecimiento: eso hay que recogerlo antes de cargar nada real.
2. **La guía normativa cubre 3 municipios de 12.** Están Armenia, Salento y Filandia con
   sus 6 entidades cada uno. Faltan los otros nueve y validar con cada secretaría los
   costos y formatos, que hoy son de ejemplo.
3. **Los formatos oficiales son PDFs generados localmente**, no los documentos reales de
   Bomberos y Policía. Natalia ya los está solicitando.
4. **Bold sin credenciales.** Falta el papeleo (RUT, cámara de comercio, representante
   legal, datos de la cuenta de Itaú) para abrir la cuenta y probar en sandbox.
5. **El asociado todavía no edita su propia ficha.** La sesión y los roles ya están
   construidos para soportarlo; falta la interfaz.
6. **Recordatorios automáticos de cartera.** La directiva los pidió; hoy el afiliado ve su
   estado pero no recibe aviso. Requiere decidir el canal (correo o WhatsApp) y la cadencia.
7. **Sin pruebas de navegador.** La cobertura llega hasta los componentes Livewire; los
   flujos que dependen de JavaScript en el navegador (mapa Leaflet, menú móvil) y la subida
   real de archivos (portadas, galería, CSV de cartera) se verificaron a mano. El importador
   de cartera sí tiene pruebas del servicio, pero no de la acción de Filament que lo llama.
8. **Falta medir el rendimiento real en móvil 4G.** El objetivo de home < 2,5 s se atacó
   por diseño (webp, lazy loading, fuentes auto-alojadas, sin librerías pesadas), pero no
   se ha corrido Lighthouse.

---

## Estructura

```
app/
├── Enums/            13 enums del dominio (estados, tipos, métodos de pago)
├── Filament/         18 recursos, 2 páginas y 4 widgets del panel
├── Http/             Controladores públicos, form requests, middleware
├── Models/           19 modelos + trait EsPublicable
├── Observers/        FlujoDeAprobacionObserver (RF-37)
├── Pagos/            PasarelaDePago, PasarelaSimulada, PasarelaBold
├── Policies/         Permisos por recurso, con `publicar` aparte de `editar`
├── Services/         ImportadorDeCartera, RegistroDePagos
└── Support/          Helpers de vista, Formulario, VideoDeYoutube
database/
├── migrations/       20 tablas, portables a PostgreSQL
└── seeders/          Contenido del gremio + generadores locales de PNG y PDF
resources/views/
├── components/       Layout, navbar, footer, mapa, campos de formulario
└── publico/          Las páginas del sitio
```

---

## Notas de producción

- La base es **SQLite** para que el demo arranque sin configurar nada. Las migraciones no
  usan nada específico del motor: pasar a **PostgreSQL** es cambiar el `.env`.
- `QUEUE_CONNECTION=sync` en el demo para que las conversiones de imagen y las
  notificaciones corran en línea. En producción conviene una cola real.
- `MAIL_MAILER=log`: ningún correo sale de verdad. Quedan en `storage/logs/laravel.log`.
- Candidato de hosting evaluado: **Laravel Cloud** (pago mensual, condición de la dirección).

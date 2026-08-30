## Presentación

Este manual describe cómo usar el panel de administración de la plataforma web de Asobares Capítulo Quindío. Está dirigido a la dirección ejecutiva, a la secretaría y a los practicantes del capítulo, y no exige conocimientos técnicos.

Las once imágenes son capturas del panel real, tomadas el 18 de agosto de 2026 sobre la base de datos de demostración, en tema claro y a 1440 píxeles de ancho. Ninguna contiene datos de personas reales: todos los establecimientos y nombres que aparecen son ficticios.

## 1. Antes de empezar

### 1.1 Cómo entrar

1. Abra la dirección `/admin` en el navegador.
2. Escriba su correo y su contraseña.
3. El sistema le pedirá un código de seis dígitos que llega a su correo. Escríbalo.

![Pantalla de acceso al panel](CAPTURAS/01-inicio-de-sesion.png)

*Figura F1. Pantalla de acceso al panel.*

El código por correo no es opcional. Es una exigencia de seguridad del proyecto: protege los datos personales de los asociados, de los candidatos a empleo y de quienes escriben al gremio. Si nunca ha configurado el segundo factor, el sistema lo guía la primera vez.

Nota. Si no le llega el código, revise la carpeta de correo no deseado. Si sigue sin llegar, avise a quien administre la plataforma: puede ser que el servicio de correo no esté configurado en ese servidor, y existe otra forma de obtenerlo.

### 1.2 Los dos tipos de cuenta

| Permiso | Dirección (superadministrador) | Secretaría (subadministrador) |
|---|---|---|
| Ver todo el panel | Sí | Sí, salvo Cartera, Transacciones y Usuarios |
| Redactar contenido | Sí | Sí |
| Publicar en el sitio | Sí | No: queda pendiente de aprobación |
| Aprobar lo que redactó otra persona | Sí | Solo en las Bolsas |
| Cambiar la configuración del sitio | Sí | No |

Esta separación es deliberada y es uno de los requisitos centrales del proyecto: la secretaría redacta y la dirección publica. No es una restricción del formulario que se pueda saltar; está implementada en el servidor. Si la secretaría marca «Publicado» y guarda, el registro queda igualmente en «Pendiente de aprobación».

### 1.3 Cómo está organizado el menú

![Menú lateral completo, con la sesión de la Dirección](CAPTURAS/02-menu-lateral.png)

*Figura F2. Menú lateral completo, con la sesión de la Dirección.*

| Grupo | Qué contiene | Para qué |
|---|---|---|
| Contenido | Asociados, Eventos, Noticias, Iniciativas, Requisitos de apertura | Lo que se ve en el sitio público |
| Gremio | Aliados, Beneficios, Cartera, Transacciones | Afiliación, convenios y recaudo |
| Bolsas | Vacantes, Artistas, Proveedores, Banco de talento | Lo que publican terceros y el gremio modera |
| Bandejas | Inscripciones, Mensajes y PQR, Postulaciones | Lo que llega desde el sitio y hay que atender |
| Configuración | Municipios, Categorías, Usuarios, Ajustes del sitio, Bitácora | Los cimientos; se modifican poco |

Regla práctica: si se pregunta dónde está algo, piense en si es algo que usted publica (Contenido), algo que le llega (Bandejas) o algo que otro publicó y usted revisa (Bolsas).

## 2. Publicar un asociado nuevo

Es la tarea más frecuente, porque el gremio crece mes a mes.

1. Contenido, Asociados, Crear.
2. Llene el nombre, el municipio y la categoría (bar, gastrobar, café, discoteca).
3. Suba la foto de portada. No se preocupe por el tamaño ni el formato: el sistema convierte y optimiza las imágenes.
4. Distinga los dos bloques de contacto. El contacto público aparece en la ficha que ve todo el mundo; el contacto interno solo lo ve el equipo del gremio y nunca aparece en el sitio.
5. En «Publicación», escoja el estado y guarde.

![Formulario de asociado: arriba lo que ve el público, abajo los datos internos del gremio](CAPTURAS/03-asociado-publico-vs-interno.png)

*Figura F3. Formulario de asociado: arriba lo que ve el público, abajo los datos internos del gremio.*

Nota. El propietario decide qué se publica de su establecimiento. Es un acuerdo con la directiva y una obligación legal: antes de publicar datos de un establecimiento hay que tener su autorización. El bloque de contacto interno existe para guardar lo que el propietario no autorizó mostrar.

Si usted es secretaría, al guardar el registro queda en «Pendiente de aprobación» y la dirección recibe un aviso. Es el comportamiento esperado, no un error.

## 3. Publicar un evento y recibir inscripciones

1. Contenido, Eventos, Crear.
2. Fecha, hora, lugar y descripción.
3. Aforo: si escribe un número, el sistema cierra las inscripciones al llenarse y avisa a quien llegue tarde. Si lo deja vacío, no hay límite.
4. Valor: si el evento tiene costo, quien se inscriba pasa por la pasarela de pago. Si es gratuito, se inscribe directamente.

![Formulario de evento, con el bloque de inscripción, cupos y precio](CAPTURAS/04-evento-aforo-y-valor.png)

*Figura F4. Formulario de evento, con el bloque de inscripción, cupos y precio.*

Las inscripciones llegan a Bandejas, Inscripciones, con el estado de pago de cada una.

Nota. Solo se publican eventos del gremio (ferias, congresos y capacitaciones propias). Es una regla editorial acordada con la directiva: los eventos de bares individuales no se publican y los eventos nacionales se enlazan al registro de Asobares Colombia.

## 4. Mantener la guía normativa

Es el módulo más valioso del sitio y el que más se desactualiza si nadie lo cuida.

1. Contenido, Requisitos de apertura.
2. Cada trámite pertenece a un municipio y lleva entidad responsable, pasos, costo aproximado, fuente, fecha de verificación, vigencia y el formato descargable.
3. Suba el formato oficial como archivo adjunto.

![Ficha de un trámite de la guía normativa con su formato descargable](CAPTURAS/05-requisito-con-formato.png)

*Figura F5. Ficha de un trámite de la guía normativa con su formato descargable.*

Al reemplazar un formato antiguo por uno nuevo, los enlaces que ya circulan siguen funcionando. Un trámite cuya vigencia venció deja de mostrarse en el sitio de forma automática.

### 4.1 Cómo saber si la guía se usa

Observatorio, Consultas de la guía muestra cuántas personas consultaron cada municipio y cuántos formatos se descargaron. Ese registro no guarda ningún dato de quién consultó; es un conteo anónimo. Sirve para saber qué municipio priorizar y para tener una cifra concreta que presentar a una alcaldía.

## 5. Aprobar lo que redactó otra persona

Solo dirección.

Cuando la secretaría redacta algo, usted recibe un aviso en la campana de la parte superior derecha.

1. Pulse la campana y luego «Revisar».
2. O entre al Escritorio: la primera banda muestra todo lo pendiente, ordenado, con lo más antiguo marcado como urgente.
3. En la fila del registro, use «Aprobar y publicar» o «Devolver a borrador».

![Tablero: la banda de pendientes solo muestra lo que le corresponde aprobar a quien entró](CAPTURAS/06-escritorio-pendientes.png)

*Figura F6. Tablero: la banda de pendientes solo muestra lo que le corresponde aprobar a quien entró.*

![Solo la fila pendiente ofrece «Aprobar y publicar»; las ya publicadas solo se pueden devolver](CAPTURAS/07-acciones-aprobar-devolver.png)

*Figura F7. Solo la fila pendiente ofrece «Aprobar y publicar»; las ya publicadas solo se pueden devolver.*

Si devuelve algo, el motivo es obligatorio: quien lo redactó recibe ese motivo y puede corregir sin adivinar.

## 6. Moderar las bolsas

Las vacantes, los artistas y los proveedores no los crea el gremio: los publica quien tiene la necesidad, y el gremio aprueba o devuelve.

- Bolsas, Vacantes. Las publica el asociado desde su portal. Usted aprueba o devuelve con motivo. Puede aprobar varias a la vez seleccionándolas.
- Bolsas, Artistas y Proveedores. Entran por un formulario público. Al aprobar, el sistema avisa por correo a quien se inscribió.
- Bandejas, Postulaciones. Los candidatos a las vacantes. Aquí solo se consultan: quien gestiona los candidatos es el establecimiento dueño de la vacante, no el gremio.

![Aprobación en lote: seleccione las vacantes y abra las acciones](CAPTURAS/08-vacantes-aprobacion-en-lote.png)

*Figura F8. Aprobación en lote: seleccione las vacantes y abra las acciones.*

Nota. Ni la secretaría ni la dirección editan la vacante de un establecimiento. Aprobarla o devolverla, sí; cambiarle el texto, no. El contenido es del asociado.

Los datos personales de las bolsas se eliminan automáticamente: las postulaciones y los perfiles del banco de talento se borran al vencer el plazo de conservación, como exige la Ley 1581 de 2012. Cada eliminación queda anotada en la Bitácora.

## 7. Atender mensajes y PQR

Bandejas, Mensajes y PQR reúne todo lo que llega por el sitio: contacto general, solicitudes de afiliación, postulaciones de aliados y PQR.

Las PQR reciben un radicado automático con el formato `PQR-2026-0001`, consecutivo y sin saltos, y el remitente recibe acuse por correo. Esto importa porque las PQR tienen plazos legales de respuesta y el radicado es la prueba de la fecha.

Al responder, use «Marcar respondido»: pide una nota que queda como constancia de qué se contestó y cuándo.

![Bandeja de mensajes con el panel de filtros abierto](CAPTURAS/09-mensajes-filtro-por-tipo.png)

*Figura F9. Bandeja de mensajes con el panel de filtros abierto.*

## 8. Cartera y pagos

Solo dirección.

### 8.1 Subir el estado de cuenta del mes

1. Gremio, Cartera, «Descargar plantilla». Descarga un archivo con el estado actual y las columnas exactas.
2. Entregue el archivo a la contadora o llénelo usted.
3. «Importar CSV de la contadora», suba el archivo y confirme.
4. Aparece un resumen de cuántos estados se actualizaron. Si una fila viene mal, el sistema indica cuál y por qué, y las demás sí entran. Un error no invalida toda la carga.

![La importación aplica las filas correctas y explica, una por una, las que rechazó](CAPTURAS/10-importacion-con-fila-rechazada.png)

*Figura F10. La importación aplica las filas correctas y explica, una por una, las que rechazó.*

El importador tolera lo que llega en la práctica: encabezados con tildes y mayúsculas, montos como `$1.250.000` y fechas en cualquiera de los dos formatos comunes.

Cargada la cartera, el asociado entra a `/mi-cuenta`, ve cuántos meses debe y por qué valor, y paga allí mismo. Ese fue el objetivo planteado por la dirección ejecutiva: que el asociado sepa cuánto debe sin tener que llamar a la oficina.

### 8.2 Consultar los pagos

Gremio, Transacciones, en solo lectura: referencia, fecha, valor y estado. No se editan a mano a propósito: un pago es un hecho registrado por la pasarela, no un dato editable.

## 9. Configuración

Solo dirección. Se modifica poco.

- Ajustes del sitio: datos de contacto, redes sociales y textos que aparecen en todo el sitio. Cambiarlos aquí los cambia en todas partes. Ningún dato del sitio está escrito en el código; fue un requisito explícito del cronograma.
- Usuarios: dar de alta a quien entra al panel y con qué rol. Las contraseñas deben ser robustas; el sistema rechaza las débiles.
- Municipios y Categorías: las listas de las que se alimentan los formularios.
- Bitácora: quién hizo qué y cuándo. Consúltela cuando algo cambió y no sepa quién lo cambió.

![Bitácora: quién hizo qué y cuándo](CAPTURAS/11-bitacora.png)

*Figura F11. Bitácora: quién hizo qué y cuándo.*

## 10. Preguntas frecuentes

**Publiqué algo y no aparece en el sitio.** Si usted es secretaría, quedó pendiente de aprobación: avise a la dirección. Si es dirección, revise que el estado sea «Publicado» y no «Borrador».

**Me equivoqué al publicar.** Cambie el estado a «Borrador» y guarde. Sale del sitio de inmediato.

**Se me perdió un mensaje.** No se pierde: todo lo que entra por el sitio queda en Bandejas. Use los filtros por tipo y por fecha. Tenga en cuenta que los mensajes antiguos ya respondidos se eliminan por la política de datos.

**¿Puedo borrar un asociado?** Puede, pero casi siempre es mejor despublicarlo: pasarlo a «Borrador» lo saca del sitio y conserva su historial de pagos. Borrarlo elimina también lo asociado a él.

**¿Cómo cambio el tema claro u oscuro?** En la parte superior derecha. Su elección se recuerda y aplica también al sitio público.

## 11. Vigencia de este manual

Las capturas corresponden a la versión del panel publicada en el repositorio al 18 de agosto de 2026. Cualquier cambio de interfaz obliga a repetir la captura correspondiente. La versión de referencia de este manual se mantiene en el archivo `docs/ingenieria/manual-de-usuario.md` del repositorio, junto a las capturas; cuando cambie la interfaz se actualiza allí y se vuelve a generar este anexo.

El criterio contractual de este entregable no es que el manual exista, sino que, al terminar la sesión de capacitación de la semana 8, el personal del gremio publique un asociado, un evento y una noticia sin ayuda. Lo que falle en esa sesión es lo que habrá que reescribir aquí. La constancia de esa sesión es el Acta 02.

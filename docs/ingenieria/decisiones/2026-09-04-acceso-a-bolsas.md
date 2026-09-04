# Proveedores y banco de talento pasan a ser beneficio de afiliado

**4 de septiembre de 2026** · Persona 2 (Ingrid) · rama `p2/acceso-asociados`, commit `f2092c5`

## La decision

Los datos de contacto de los proveedores son la contraprestacion de la cuota, no
contenido de vitrina. Lo mismo con los perfiles del banco de talento. Los dos pasan
detras de la sesion del afiliado.

**La bolsa de empleo no se cierra.** Se evaluo y se descarto: quien busca trabajo
tiene que poder ver la vacante para postularse, y ahi esta el valor para el
establecimiento. Cerrarla habria dejado sin uso el flujo de postulaciones y sus
correos, construido el 31 de agosto.

**`/proveedores` tampoco se cierra.** Sigue publica e indexable, pero sin un solo
contacto: explica que es la bolsa, cuenta cuantos proveedores hay por categoria y
ofrece las dos salidas (afiliarse, o inscribirse como proveedor). Cerrar la URL
entera habria mandado a un login seco a quien llega desde un buscador y habria
sacado del indice una seccion que hoy trae visitas, a cambio de nada que la
separacion no de igual.

## Que cambio

| Antes | Ahora |
|---|---|
| `/proveedores` listaba nombres, WhatsApp y correos | `/proveedores` es presentacion sin contactos; el directorio real vive en `/mi-cuenta/proveedores` |
| Los perfiles de aspirantes solo se veian en el panel | `/mi-cuenta/aspirantes` los muestra a los establecimientos afiliados |
| `/empleo` publico | sin cambios |
| Panel de administracion | sin cambios: `Aspirantes`, `Proveedors` y `Postulaciones` ya existian |

## Decisiones que conviene no revertir sin pensarlo

**El banco de talento es de lectura.** El campo `estado` del aspirante sigue siendo
de la secretaria. Si cada establecimiento pudiera moverlo, dos bares se pisarian el
seguimiento del mismo candidato sin enterarse. Los que el gremio descarto no se
listan: descartar y seguir apareciendo es no haber descartado nada.

**El equipo del gremio no entra por `/mi-cuenta`.** `AsegurarRolAsociado` exige rol
asociado *y* ficha vinculada. La direccion y la secretaria tienen el panel, que es
mas completo. El middleware no se toco.

## Pendiente

1. **Credenciales de los afiliados.** No existe registro publico de cuentas: las
   crea el panel. Al 31 de agosto habia 24 asociados en la base de desarrollo y un
   solo usuario con `asociado_id`. Sin resolver el alta de credenciales para los
   establecimientos de la base del gremio, la seccion no la ve nadie. Es una
   conversacion con Natalia, no una tarea de codigo.
2. **Politica de tratamiento de datos.** El formulario de aspirante ya avisa que el
   perfil sera visible para los establecimientos afiliados. Falta que el gremio
   revise si la politica publicada cubre ese uso y si hay que versionarla:
   `consentimiento_politica` guarda la version con la que cada persona acepto, y los
   7 perfiles ya registrados aceptaron con la anterior.
3. **Correo de aprobacion de proveedor.** `FichaDeBolsaPublicada` enlaza a
   `/proveedores` diciendo que ya esta publicado, y esa pagina ya no lo nombra. Hay
   que reescribir ese texto.
4. **Documentacion.** Los RF de proveedores estan descritos como publicos en la
   matriz de trazabilidad y en el documento de practica.

## Sin verificar

El codigo se escribio desde Cowork, que no tiene PHP ni Composer: **nada de esto se
ejecuto**. Antes de fusionar hay que correr `php artisan migrate` (5 migraciones
pendientes desde el 31 de agosto), `php artisan test` y `npm run dev`. Sin las
extensiones `intl` y `gd` la suite arroja fallos que no son del codigo.

## Frontera con Persona 1

`routes/web.php` es zona compartida y este cambio lo toca. No hubo migraciones ni
cambios en `composer.json` ni en `.env`, asi que no invade el terreno de Persona 1,
pero conviene que lo revise por PR antes de que entre a `main`.

# Guía normativa de Armenia — contenido oficial del gremio

**Fuente:** `material/REQUISITOS APERTURA - ARMENIA.docx`, entregado por ASOBARES Capítulo Quindío el 20 de agosto de 2026.
**Campaña de la que sale:** «BLINDEMOS TU NEGOCIO ARMENIA» — Jornada de Sensibilización y Acompañamiento Preventivo, en articulación con la **Alcaldía de Armenia**.
**Destinatario de este documento:** Ingrid Montoya Warski (Persona 2 · guía normativa).
**Preparado por:** Juan José Sua Gómez (Persona 1) — transcripción y estructura, sin tocar `RequisitoAperturaSeeder.php`.

---

## 1. Por qué este documento existe y no es un commit

La guía normativa es del bloque de la Persona 2. El seeder `database/seeders/RequisitoAperturaSeeder.php` es justo el archivo que vas a estar editando esta semana, así que sembrarlo yo garantizaría un conflicto de Git en el peor sitio posible. Lo que hago aquí es dejarlo **transcrito, estructurado y listo para pegar**: la parte que sí es mía —que el dato exista en el repositorio y no en el correo de alguien— queda hecha, y el último paso, que es de contenido, lo das tú.

También importa el estado en que quede: `RequisitoApertura` tiene `estado` con valor `borrador` por defecto y el comentario de la migración lo dice sin rodeos — *«Información legal sensible: pasa por aprobación de la dirección»*. Este contenido lo mandó el gremio, pero **conviene que Natalia confirme por escrito que es la versión vigente antes de publicarlo**: es información que un empresario va a usar para decidir si abre o no.

---

## 2. Lo que el documento oficial corrige y añade sobre lo sembrado

El seeder ya tenía un bloque `'armenia'` con cinco trámites redactados durante la construcción del demo. La comparación con el documento real:

| Trámite | Estado frente a lo sembrado |
|---|---|
| Matrícula mercantil (Cámara de Comercio) | ✅ Ya estaba. El documento añade el dato duro: **renovación antes del 31 de marzo de cada año** |
| Uso de suelos | ✅ Ya estaba. El documento añade **dos fuentes distintas** (Planeación municipal emite concepto, Curaduría emite documento oficial) y una verificación que no estaba: **que la actividad económica del RUT coincida con el concepto de uso de suelo** |
| Bomberos / seguridad humana | ✅ Ya estaba. El documento precisa el nombre: **Cuerpo Oficial de Bomberos de Armenia**, y su checklist: extintores vigentes, señalización de evacuación, **luces de emergencia** y botiquín |
| Derechos de autor | ✅ Ya estaba (Sayco y Acinpro). El documento confirma el beneficio del gremio: **revisión de tarifa y descuentos con marcas aliadas para afiliados** |
| **Concepto sanitario** | 🆕 **Nuevo y con procedimiento concreto**: se pide por correo a `servicioalcliente@armenia.gov.co` con asunto **VISITA SANITARIA**, adjuntando nombre, dirección y RUT |
| **Intensidad auditiva (CRQ)** | 🆕 **No existía.** Corporación Autónoma Regional del Quindío. Es el trámite que más cierra bares y no estaba en la guía |
| **Notificación a la policía** | 🆕 **No existía**, y trae una condición que nadie habría adivinado: **solo aplica a establecimientos abiertos después de 2019** |

Resultado: de 5 trámites sembrados a **7 reales**, y cuatro de los cinco que ya estaban ganan datos que solo tiene el gremio.

---

## 3. Los siete trámites, en el formato del seeder

Listo para pegar dentro de `'armenia' => [ … ]`. El orden es el del documento oficial, que es también el orden en que se hacen.

```php
'armenia' => [
    [
        'entidad' => 'Alcaldía de Armenia — Planeación municipal y Curaduría',
        'descripcion' => 'El certificado de uso de suelos autoriza que en esa dirección pueda funcionar un bar o una discoteca. Son dos puertas distintas: Planeación municipal emite el concepto y la Curaduría ciudadana el documento oficial. Consúltalo ANTES de firmar el arriendo.',
        'checklist' => [
            'Concepto de uso de suelo emitido por Planeación municipal',
            'Documento oficial expedido por la Curaduría ciudadana',
            'Verificar que la actividad económica del RUT coincida con el concepto de uso de suelo',
        ],
        'enlace_externo' => 'https://armenia.gov.co',
        'orden' => 1,
    ],
    [
        'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
        'descripcion' => 'La matrícula mercantil es el registro legal del establecimiento. Debe estar renovada antes del 31 de marzo de cada año.',
        'checklist' => [
            'Formulario RUES diligenciado (persona natural o jurídica)',
            'Cédula del propietario o del representante legal',
            'RUT expedido por la DIAN',
            'Consulta previa de homonimia del nombre comercial',
            'Renovación al día: vence el 31 de marzo de cada año',
        ],
        'enlace_externo' => 'https://camaraarmenia.org.co',
        'orden' => 2,
    ],
    [
        'entidad' => 'Secretaría de Salud Municipal — Concepto sanitario',
        'descripcion' => 'Es la visita de los inspectores de salud. Se solicita por correo a servicioalcliente@armenia.gov.co con el asunto VISITA SANITARIA, adjuntando nombre del establecimiento, dirección y RUT. El requisito se cumple con el acta de visita con concepto favorable o, mientras llega, con la solicitud radicada.',
        'checklist' => [
            'Solicitud enviada a servicioalcliente@armenia.gov.co — asunto: VISITA SANITARIA',
            'Adjuntar nombre del establecimiento, dirección y RUT',
            'Certificado vigente de control de plagas',
            'Certificados de manipulación de alimentos (si aplica)',
            'Condiciones locativas de los baños en regla',
            'Acta de visita con concepto favorable, o la solicitud de visita radicada',
        ],
        'orden' => 3,
    ],
    [
        'entidad' => 'Cuerpo Oficial de Bomberos de Armenia',
        'descripcion' => 'Certificado de seguridad humana y contra incendios emitido por el Cuerpo Oficial de Bomberos de Armenia.',
        'checklist' => [
            'Extintores vigentes y con recarga certificada',
            'Señalización de rutas de evacuación',
            'Luces de emergencia',
            'Botiquín de primeros auxilios dotado',
        ],
        // El formato de solicitud de visita que ya genera el seeder sigue sirviendo.
        'orden' => 4,
    ],
    [
        'entidad' => 'Corporación Autónoma Regional del Quindío (CRQ) — Intensidad auditiva',
        'descripcion' => 'Cumplimiento de los niveles de decibeles permitidos. La medición se solicita a la entidad por correo electrónico. Asegúrate de que el establecimiento tenga el aislamiento acústico necesario para no generar impacto sobre la vecindad.',
        'checklist' => [
            'Solicitud de medición de intensidad auditiva enviada a la CRQ',
            'Aislamiento acústico verificado',
            'Medición dentro de los niveles de decibeles permitidos',
        ],
        'enlace_externo' => 'https://crq.gov.co',
        'orden' => 5,
    ],
    [
        'entidad' => 'Sayco y Acinpro — Derechos de autor',
        'descripcion' => 'Comprobante de pago por la comunicación pública de música. Si eres afiliado a ASOBARES Quindío, el gremio puede revisar tu tarifa y darte acceso a descuentos con marcas aliadas.',
        'checklist' => [
            'Formulario de declaración del establecimiento',
            'Comprobante de pago por comunicación pública de música',
            'Consultar con ASOBARES la revisión de tarifa para afiliados',
        ],
        'orden' => 6,
    ],
    [
        'entidad' => 'Policía Nacional — Notificación de apertura',
        'descripcion' => 'Solicitud escrita a la estación de policía correspondiente notificando la apertura de un establecimiento de comercio en la zona. Solo aplica a establecimientos abiertos después del año 2019.',
        'checklist' => [
            'Solicitud escrita dirigida a la estación de policía de la zona',
            'Aplica únicamente si el establecimiento abrió después de 2019',
        ],
        'orden' => 7,
    ],
],
```

---

## 4. El texto de cierre del documento, que conviene no perder

El documento termina con un párrafo que fija el tono de toda la guía y que merece salir en la página, no quedarse en el `.docx`:

> Esta jornada se realiza en articulación con la Alcaldía de Armenia y las autoridades competentes. El propósito de ASOBARES es **preventivo y educativo**: buscamos que cada empresario conozca sus deberes y derechos antes de las jornadas de control. Nuestro objetivo no es sancionar, sino sensibilizar para que el comercio nocturno de Armenia sea ejemplo de orden, seguridad y cumplimiento legal.

Es exactamente el argumento de valor del módulo. Cabe como encabezado de `/abre-tu-negocio` o como nota al pie de la guía de Armenia.

---

## 5. Lo que sigue faltando

- **Los otros 11 municipios.** Este documento es solo de Armenia. El catálogo tiene 8 municipios sembrados y el Quindío tiene 12; la guía tiene contenido para 3. Con este documento, Armenia pasa de contenido inventado a contenido oficial — los demás siguen igual.
- **Los formatos oficiales descargables.** El campo `adjunto` de `requisitos_apertura` hoy apunta a PDF de ejemplo generados por el seeder. El documento del gremio no trae los formatos reales de las entidades; hay que pedirlos o enlazarlos.
- **La confirmación de vigencia.** Antes de publicar, que Natalia deje por escrito que esta es la versión vigente. La migración ya obliga a pasar por aprobación (`estado` nace en `borrador`); esto es para que la aprobación tenga en qué apoyarse.

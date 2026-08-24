# Diseño: Vigencia y procedencia de la guía normativa (RF-60)

**Fecha:** 2026-08-24
**Estado:** aprobado en conversación de diseño; pendiente plan de implementación.
**Bloque:** Persona 1 (migraciones y esquema). La superficie pública de la guía es de la Persona 2; este diseño la toca lo mínimo y no altera su seeder.
**Cierra:** RF-60 — *«Normativa vigente y decretos transitorios por municipio»*.

## Contexto y problema

La guía normativa por municipio es el producto insignia del sitio. La Reunión 2 lo dijo sin rodeos: *«ningún gremio la tiene; es donde caen los negocios y los cierran»*. Es información que un empresario va a usar para decidir si abre o no.

Y hoy se publica **sin procedencia y sin fecha**. La tabla `requisitos_apertura` tiene entidad, descripción, checklist, costo, adjunto y estado de publicación. No tiene una sola columna que diga *cuándo se comprobó esto* ni *contra qué*. Consecuencias concretas:

1. **Un lector no puede distinguir** un trámite verificado contra la Alcaldía de uno que inventó el sembrador. Los 18 registros que existen hoy son de la segunda clase: escritos a mano durante la construcción del demo, sin contrastar con ninguna fuente.
2. **No hay dónde poner la fuente oficial que ya llegó.** El 20 de agosto el gremio entregó `REQUISITOS APERTURA - ARMENIA.docx` —campaña «Blindemos tu Negocio», en articulación con la Alcaldía— y quedó transcrito en `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md`. Son siete trámites reales contra los cinco inventados. Se puede sembrar el contenido, pero no se puede fechar: no hay columna.
3. **Nada caduca.** El requisito contratado nombra explícitamente los *decretos transitorios*, que son la clase de norma que sí tiene fecha de muerte —una restricción horaria por tres meses, un decreto de alcaldía por temporada—. Hoy un decreto vencido se seguiría leyendo como vigente indefinidamente.

RF-60 es, junto con RF-05, uno de los **dos únicos requisitos funcionales que la matriz de pruebas lista sin ninguna cobertura**. No es mantenimiento: es un hueco contratado.

## Decisiones tomadas

| Tema | Decisión | Quién |
|---|---|---|
| Alcance | **Asimétrico.** `verificado_el` es informativo y no despublica nada; `vigente_hasta` sí saca la ficha del sitio al vencer. | Dueño |
| Fichas sin fecha | **Se publican con marca honesta** «Sin verificar contra la fuente oficial». No se apagan. | Dueño |
| Rancidez | **Umbral fijo en el modelo + filtro en el panel.** El sitio sólo muestra la fecha y deja juzgar al lector. | Dueño |
| Autoridad | **Sin permiso nuevo.** El flujo de aprobación existente ya cubre el caso; se le añade una prueba que lo fije. | Diseño |
| Datos existentes | **Sin relleno retroactivo.** Los 18 quedan en `NULL`. | Dueño |

## Principios

**Una fecha inventada es peor que ninguna fecha.** Todo el valor de esto es que «verificado el 20 de agosto» sea cierto. Rellenar retroactivamente los 18 registros sembrados con una fecha plausible destruiría el mecanismo el mismo día que se construye.

**Declarar la carencia, no esconderla.** Es el patrón que el proyecto ya usa en el Observatorio («aún sin muestra suficiente») y en la propia matriz de pruebas, que enumera sus huecos. Una ficha sin verificar lo dice en su cara.

**El control va en la puerta, no en la vista.** Es la lección del §8.3 del runbook de despliegue: la política obvia del bucket dejaba los formatos de la guía descargables por URL directa, saltándose el controlador. Aquí el riesgo es idéntico y se trata igual.

---

## §1 · Esquema

Migración nueva, aditiva, sin tocar datos.

```php
Schema::table('requisitos_apertura', function (Blueprint $table): void {
    $table->date('verificado_el')->nullable()->after('estado');
    $table->string('verificado_con')->nullable()->after('verificado_el');
    $table->date('vigente_hasta')->nullable()->after('verificado_con');

    $table->index('vigente_hasta');
});
```

**`date` y no `timestamp`.** Verificar es un acto con día, no con instante: la Alcaldía y la Cámara fechan por día. Contrasta a propósito con `autorizacion_datos_at`, que sí es `timestamp` porque la Ley 1581 pregunta el momento exacto del consentimiento. Son dos preguntas distintas y merecen dos tipos distintos.

**`verificado_con` es texto libre, no una relación.** Es procedencia en prosa: *«Documento oficial Alcaldía de Armenia, campaña Blindemos tu Negocio, entregado al gremio el 20 ago 2026»*. Misma forma y misma razón que `autorizacion_datos_origen`, que es el precedente sentado el 23 de agosto para los asociados. Modelar las fuentes como tabla sería inventar un catálogo que nadie pidió.

**El índice va sólo en `vigente_hasta`.** Entra en la consulta pública en cada visita a la guía y en cada generación del sitemap. `verificado_el` sólo se filtra desde el panel, sobre una tabla de decenas de filas.

> ⚠️ **En SQLite `after()` es decorativo:** las columnas aterrizan al final de la tabla. Es inocuo y ya pasó con la ficha comercial del asociado; se anota para que nadie lo lea como fallo de la migración.

## §2 · Modelo

Casts nuevos: `verificado_el` y `vigente_hasta` a `'date'`.

```php
/**
 * Cuánto dura la tranquilidad de una verificación.
 *
 * Doce meses porque los trámites de apertura se mueven al ritmo de los
 * acuerdos municipales y de las tarifas anuales —la matrícula mercantil se
 * renueva antes del 31 de marzo de cada año—, así que un año es el ciclo
 * natural en el que algo cambia sin que nadie avise. No es una norma: es un
 * criterio del gremio, y por eso vive aquí con su razón al lado y no en un
 * ajuste que nadie va a mirar.
 */
public const int MESES_HASTA_REVISION = 12;
```

Cuatro predicados:

| Método | Verdad |
|---|---|
| `estaVerificado()` | `verificado_el !== null` |
| `necesitaRevision()` | sin verificar **o** verificado hace **más** de `MESES_HASTA_REVISION` |
| `esTransitorio()` | `vigente_hasta !== null` |
| `haCaducado()` | `vigente_hasta !== null` **y** `vigente_hasta < hoy` |

`necesitaRevision()` junta «sin verificar» y «rancio» a propósito. Son estados distintos para el lector —y la vista los distingue— pero son **la misma pila de trabajo** para la oficina, y el filtro del panel existe para atacar esa pila.

**El borde de los doce meses es estricto.** A los doce meses exactos la ficha todavía no necesita revisión; la necesita al día siguiente. Se declara aquí porque «más de un año» y «un año o más» son dos reglas distintas y ambas se leen igual en prosa.

Y los tres campos entran en el `logOnly` de la bitácora, junto a `entidad` y `estado`. Cambiar una fecha de verificación es afirmar autoridad sobre información legal: tiene que quedar quién y cuándo.

## §3 · El scope, y sus cuatro puertas

Un scope nuevo que **se compone** con el existente. `publicado()` viene del trait `EsPublicable` y lo usan el panel y el observer: no se toca.

```php
/** Un requisito sin fecha de vencimiento es permanente; uno con fecha vive hasta su último día, inclusive. */
public function scopeVigente(Builder $query): Builder
{
    return $query->where(fn (Builder $q) => $q
        ->whereNull('vigente_hasta')
        ->orWhere('vigente_hasta', '>=', now()->toDateString()));
}
```

> ⚠️ **El paréntesis no es cosmético.** Sin el grupo, el `orWhere` se suelta y anula el `publicado()` que lo precede: la guía empezaría a servir borradores. Necesita prueba propia.

Se aplica en **cuatro** sitios. Tres son fáciles de olvidar y cada uno falla distinto:

| Puerta | Archivo | Si se olvida |
|---|---|---|
| La lista de trámites | `GuiaController::index` | Un decreto vencido se lee como vigente |
| El selector de municipios | `GuiaController::index` (`whereHas`) | Un municipio con todo caducado sale en el selector con la guía vacía |
| **La descarga del formato** | `GuiaController::descargarFormato` | **El PDF del decreto vencido se sigue bajando por URL directa** |
| El sitemap | `SitemapController` (`whereHas`) | Google recibe como URL de valor una guía vacía |

La tercera es la que importa: es exactamente la familia de agujero del §8.3 del runbook —un control que vive en la vista y no en la puerta—. `descargarFormato()` ya comprueba `estaPublicado()`; gana `&& ! $requisito->haCaducado()`.

**Borde con prueba propia:** «vigente hasta el 30 de noviembre» **incluye** el 30. El scope compara `>=`, no `>`. Es un carácter que cambia el comportamiento durante un día entero, y el tipo de cosa que sólo se descubre en producción.

**Zona horaria:** `config('app.timezone')` es `America/Bogota`, así que `now()` no adelanta el vencimiento. Verificado, no supuesto.

## §4 · Panel

Sección nueva en el formulario, *«Verificación y vigencia»*, después de *«Publicación»*:

- `verificado_el` — DatePicker. Texto de ayuda: *«El día en que alguien contrastó este trámite contra la entidad. Déjalo vacío si nadie lo ha hecho: el sitio lo dirá.»*
- `verificado_con` — TextInput. *«Con qué. Un documento, un acta, un correo de la entidad.»*
- `vigente_hasta` — DatePicker. *«Sólo para decretos transitorios. Vacío significa permanente. Al pasar la fecha, el trámite deja de mostrarse en el sitio.»*

En la tabla: columna `verificado_el` con color (verde vigente / ámbar rancio / gris sin verificar), columna `vigente_hasta`, y dos filtros — **Necesita revisión** y **Caducados**.

**Sin permiso nuevo.** `FlujoDeAprobacionObserver::saving` ya cruza la frontera en los dos sentidos: si la secretaría edita un requisito **publicado**, el registro cae a `pendiente_aprobacion` y la dirección lo revisa. Declarar «verifiqué esto contra la Alcaldía» es una afirmación de autoridad, y ya pasa por la puerta correcta. Lo que falta es que esa protección deje de ser incidental: hoy ninguna prueba la ejercita sobre este campo, y una guarda que nadie comprueba se rompe el día que alguien añade un `saltaFlujoDeAprobacion`.

## §5 · Sitio público

En la línea de resumen de cada `<details>`, junto al costo y al conteo de requisitos, exactamente una marca:

```
✓ Verificado el 20 de agosto de 2026
⚠ Sin verificar contra la fuente oficial
```

Y, sólo si es transitorio, una segunda:

```
Vigente hasta el 30 de noviembre de 2026
```

En el cuerpo desplegado, la procedencia en pequeño bajo la descripción: *«Fuente: Documento oficial de la Alcaldía de Armenia»*. Es el dato que convierte la marca en algo comprobable.

Un trámite caducado **no se renderiza nunca**: ya salió en la consulta. La vista no tiene rama para él, y eso es deliberado — si aparece, es que una de las cuatro puertas del §3 se dejó abierta.

Colores desde los tokens semánticos existentes. Cero hexadecimales en la vista, que ya lo vigila una guardia.

## §6 · Datos existentes y el seeder

La migración es aditiva y nullable: los 18 registros sembrados quedan en `NULL` y se muestran honestamente como **sin verificar**. **No hay relleno retroactivo**, y esa ausencia es la decisión, no un olvido.

`RequisitoAperturaSeeder.php` **no se toca**: es del bloque de la Persona 2 y es el archivo que ella está editando esta semana. Lo que sí se actualiza es `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md`, el documento que se le dejó listo para pegar, añadiendo a los siete trámites de Armenia sus dos campos ya rellenos:

```php
'verificado_el' => '2026-08-20',
'verificado_con' => 'Documento oficial de la Alcaldía de Armenia, campaña «Blindemos tu Negocio», entregado al gremio el 20 de agosto de 2026',
```

Así el primer contenido verificado del proyecto entra fechado desde el primer día, sin que ella tenga que averiguar la forma.

## §7 · Verificación

| Frente | Qué se fija |
|---|---|
| Esquema | Las tres columnas existen, son nullable, `verificado_el` y `vigente_hasta` castean a fecha |
| Modelo | Los cuatro predicados; el umbral en sus tres bordes (11 meses no, **12 exactos tampoco**, 13 sí) |
| Bitácora | Cambiar `verificado_el` deja rastro con autor en el registro de actividad |
| Scope | Incluye `NULL`; incluye futuro; **incluye hoy**; excluye ayer; y **no rompe el `publicado()` que lo precede** |
| `GuiaController` | Un caducado no sale en la lista; un municipio con todo caducado no sale en el selector |
| Descarga | **El formato de un caducado responde 404** |
| Sitemap | No anuncia el municipio cuya guía entera caducó |
| Vista | Las tres marcas, cada una con contraprueba — una vista que no renderizara nada pasaría las tres en verde |
| Panel | Los dos filtros; y que la secretaría editando `verificado_el` sobre un publicado lo devuelve a `pendiente_aprobacion` |
| Matriz | **RF-60 pasa de ❌ a ✅** |

Toda prueba nueva pasa por el filtro que la §6 de la matriz declara: mutar el código a propósito y comprobar que la prueba se entera. De los defectos atrapados en este proyecto, cinco fueron pruebas en falso verde que pasaban con el error reintroducido.

## §8 · Orden de ejecución

1. Migración y casts del modelo.
2. Los cuatro predicados, la constante y los tres campos en el `logOnly` de la bitácora.
3. El scope `vigente()`, con su prueba del paréntesis antes que nada más.
4. Las cuatro puertas, empezando por `descargarFormato()` — es la que tiene consecuencia de seguridad.
5. Panel: formulario, columnas, filtros.
6. Vista pública: las tres marcas.
7. El documento de Ingrid con los dos campos.
8. Matriz de pruebas: RF-60 y las cifras de la suite.

## §9 · Lo que este diseño NO hace

- **No verifica ningún trámite.** Crea el sitio donde consta que alguien lo hizo. Los 18 registros siguen sin verificar hasta que una persona los contraste; ocho municipios del Quindío siguen sin guía y cuatro sin existir siquiera como registro.
- **No despublica por rancidez.** Se rechazó a propósito: el módulo insignia podría vaciarse solo justo cuando termine la práctica el 22 de septiembre.
- **No añade un catálogo de fuentes** ni un historial de verificaciones. `verificado_el` guarda la última, no la serie. Si alguna vez hace falta la serie, la bitácora de `spatie/activitylog` ya registra los cambios del modelo — basta con añadir los tres campos a su `logOnly`, que sí entra en este trabajo.
- **No toca el seeder de la Persona 2.**

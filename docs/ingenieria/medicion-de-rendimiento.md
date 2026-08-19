# Medición de rendimiento — Plataforma Web ASOBARES Capítulo Quindío

**Versión:** 1.0 · **Fecha de la medición:** 18 de agosto de 2026 · **Elaboró:** Juan José Sua Gómez (práctica empresarial, Universidad Alexander von Humboldt)
**Requisito verificado:** RNF-02 · **Origen contractual:** cronograma firmado por la dirección ejecutiva, «Rendimiento y Optimización Web»: *«La página principal no debe tardar más de 2.5 segundos en cargar»*
**Estado del repositorio:** `main` en `4f15d24` · assets compilados con `npm run build`

---

## 1. Veredicto

**El requisito se cumple, y con margen.** La portada pinta su elemento principal en **0,97 segundos** sobre una conexión móvil 4G emulada, contra un techo contractual de 2,5 segundos. Ninguna de las doce rutas públicas lo incumple, ni en móvil ni en escritorio, ni en tema claro ni en oscuro.

| Medida contractual | Techo | Resultado (portada, móvil 4G) | Margen |
|---|---|---|---|
| Carga de la página principal | 2.500 ms | **972 ms** (LCP) · 1.803 ms (carga completa) | 61 % por debajo en LCP; 28 % en carga completa |

La ruta más lenta de todo el sitio es `/boletin`, con **2.132 ms** de LCP en móvil 4G: cumple, pero es la que menos margen tiene y la primera que hay que vigilar si crece el contenido.

Hasta esta medición, el cumplimiento de RNF-02 era una afirmación sin respaldo: el proyecto tenía marcado responsive pero **ninguna cifra medida**. Este documento cierra ese hueco, que la matriz de pruebas declaraba como el número uno de su lista.

---

## 2. Método

- **Herramienta:** `playwright-cli` sobre **Chromium real**, controlado por CDP. No es una simulación de laboratorio sintético: es el motor de un navegador cargando el sitio servido por la aplicación.
- **Servidor:** la propia aplicación (`php artisan serve`, puerto 8123) con la base de datos de demostración recién sembrada (`migrate:fresh --seed`) y los assets compilados en modo producción.
- **Caché desactivada** en cada medición (`Network.setCacheDisabled`). Todas las cifras son de **primera visita**, que es el caso peor y el que importa para un visitante nuevo.
- **Dos perfiles:**
  - **Móvil 4G** — viewport 390 × 844 px, con la red estrangulada a **9 Mbps de bajada, 1,5 Mbps de subida y 170 ms de latencia**. Es el perfil que manda, porque el cronograma fija que más del 80 % de los usuarios entra por celular.
  - **Escritorio** — viewport 1280 × 800 px, sin estrangular.
- **Tres corridas por combinación**, y lo que se reporta es la **mediana**. Se incluye además el peor LCP de las tres para que se vea la dispersión.
- **Los dos temas:** la portada se midió también en oscuro, porque el sitio es bicromático y había que descartar que el tema costara tiempo.
- **Total: 78 mediciones, 0 errores.**

**Qué significa cada columna**

| Métrica | Qué mide |
|---|---|
| TTFB | Tiempo hasta el primer byte: cuánto tarda el servidor en responder |
| FCP | *First Contentful Paint*: cuándo aparece el primer contenido en pantalla |
| **LCP** | *Largest Contentful Paint*: cuándo termina de pintarse el elemento principal. **Es la métrica que representa «cargó» para un usuario** y contra la que se contrasta el techo de 2,5 s |
| Carga | Evento `load` completo, con todos los recursos secundarios |
| Peticiones / KB | Número de recursos y peso transferido real |

---

## 3. Resultados — móvil 4G (perfil que manda)

Medianas de tres corridas, en milisegundos.

| Ruta | TTFB | FCP | **LCP** | LCP peor | Carga | Peticiones | KB |
|---|---:|---:|---:|---:|---:|---:|---:|
| **`/` (portada)** | 393 | 972 | **972** | 976 | 1.803 | 16 | 361 |
| `/` (tema oscuro) | 86 | 980 | **980** | 980 | 1.783 | 16 | 361 |
| `/quienes-somos` | 423 | 976 | 976 | 988 | 1.807 | 15 | 298 |
| `/abre-tu-negocio` | 438 | 960 | 960 | 972 | 1.806 | 15 | 308 |
| `/empleo` | 80 | 976 | 976 | 984 | 1.751 | 15 | 298 |
| `/proveedores` | 432 | 960 | 960 | 972 | 1.801 | 15 | 298 |
| `/afiliate` | 422 | 960 | 960 | 964 | 1.798 | 15 | 283 |
| `/contacto` | 82 | 968 | 968 | 984 | 1.764 | 23 | 282 |
| `/politica-de-datos` | 111 | 976 | 976 | 984 | 1.392 | 14 | 274 |
| `/artistas` | 121 | 980 | 1.816 | 1.828 | 1.813 | 18 | 398 |
| `/directorio` | 81 | 980 | 1.840 | 1.860 | 1.829 | 19 | 479 |
| `/eventos` | 81 | 984 | 1.840 | 1.848 | 1.838 | 18 | 394 |
| `/boletin` | 429 | 956 | **2.132** | 2.136 | 1.802 | 19 | 438 |
| **Peor valor del sitio** | 438 | 984 | **2.132** | 2.136 | 1.838 | 23 | 479 |

## 4. Resultados — escritorio

| Ruta | TTFB | FCP | **LCP** | Carga | Peticiones | KB |
|---|---:|---:|---:|---:|---:|---:|
| **`/` (portada)** | 135 | 592 | **592** | 1.083 | 21 | 574 |
| `/` (tema oscuro) | 128 | 580 | **580** | 1.081 | 21 | 574 |
| `/quienes-somos` | 379 | 748 | 748 | 1.172 | 15 | 298 |
| `/abre-tu-negocio` | 389 | 712 | 712 | 1.174 | 15 | 308 |
| `/empleo` | 120 | 576 | 576 | 1.078 | 15 | 298 |
| `/proveedores` | 428 | 724 | 724 | 1.183 | 15 | 298 |
| `/afiliate` | 374 | 728 | 728 | 1.190 | 15 | 283 |
| `/contacto` | 128 | 568 | 568 | 1.071 | 23 | 282 |
| `/politica-de-datos` | 66 | 520 | 520 | 713 | 14 | 274 |
| `/artistas` | 72 | 520 | 1.032 | 1.026 | 23 | 570 |
| `/eventos` | 69 | 520 | 1.036 | 1.027 | 18 | 394 |
| `/boletin` | 122 | 568 | 1.080 | 1.074 | 21 | 517 |
| `/directorio` | 129 | 584 | 1.088 | 1.085 | 27 | 823 |
| **Peor valor del sitio** | 428 | 748 | **1.088** | 1.190 | 27 | 823 |

---

## 5. Lectura de los resultados

**El tema no cuesta nada.** Portada en claro 972 ms, en oscuro 980 ms; en escritorio 592 y 580. La diferencia está dentro del ruido de medición. El sistema de tokens semánticos no impone penalización de rendimiento, que era la duda razonable al hacer el sitio bicromático.

**Hay dos familias de rutas y se distinguen limpiamente.** Las páginas de texto pintan su elemento principal en cuanto llega el HTML y la hoja de estilos (LCP ≈ FCP ≈ 0,96 s en móvil). Las páginas con rejilla de imágenes —directorio, artistas, eventos, boletín— tienen el LCP entre 1,8 y 2,1 s, porque su elemento principal **es una imagen** que todavía viaja por la red 4G. Es el comportamiento esperado y sigue dentro del techo, pero explica dónde está el margen que queda.

**`/directorio` es la ruta más pesada del sitio** (479 KB en móvil, 823 KB en escritorio, 27 peticiones). La diferencia con las demás es Leaflet y las teselas de OpenStreetMap. Es un coste aceptado por el requisito de mapas del cronograma, pero conviene saber que ahí está concentrado.

**`/boletin` es la que hay que vigilar.** 2.132 ms deja 368 ms de margen contra el techo contractual. Si el boletín crece en imágenes o el gremio publica portadas más pesadas que las de relleno, es la primera ruta que lo incumpliría. Mitigación conocida y barata: `loading="lazy"` ya está puesto, así que lo que queda es acotar el tamaño de las portadas que suba la secretaría desde el panel.

**El peso total es sano.** Ninguna ruta pasa de 479 KB en móvil ni de 27 peticiones. No hay librerías pesadas, las fuentes van subconjuntadas (`poppins-400` y `poppins-600` en 10,5 KB cada una) y la hoja de estilos del sitio pesa 58 KB sin comprimir, 11 KB con gzip.

---

## 6. Qué NO prueba esta medición

Un informe de rendimiento que solo enseña lo que salió bien no sirve para gestionar. Estos son los límites de lo medido, y son reales:

1. **Está medido contra `localhost`, no contra un servidor.** El TTFB que aparece aquí es el de la aplicación resolviendo la petición en la misma máquina; **no incluye la latencia real de red hasta un servidor de producción**. Cuando el sitio se despliegue habrá que volver a medir: sobre Laravel Cloud en la región US East, la ida y vuelta desde Colombia añadirá del orden de 80–150 ms a cada petición. Con 972 ms de LCP y un techo de 2.500, el margen absorbe eso holgadamente, pero la cifra definitiva es la que se tome contra el dominio real. **Esto depende del riesgo R-14** (cuenta institucional pendiente de la junta), no del equipo de desarrollo.
2. **No hay dispositivos reales.** El perfil móvil es un viewport de 390 px con la red estrangulada, no un teléfono. La CPU no está estrangulada, así que un gama baja real será más lento en el trabajo de pintado. **RNF-01 y RNF-07 siguen abiertos** y son contenido explícito de la Semana 7 del cronograma.
3. **Solo Chromium.** No se midió en Safari/WebKit ni en Firefox. Para el público objetivo (Android mayoritario) es representativo, pero no es cobertura completa.
4. **Solo rutas públicas anónimas.** No se midió el panel `/admin` ni `/mi-cuenta` con sesión iniciada. El panel es una aplicación Livewire con Chart.js y su perfil de carga es distinto; el cronograma no le fija techo, pero conviene medirlo antes de la capacitación.
5. **Primera visita, caché en frío.** Es deliberado y es el caso peor. Las visitas repetidas serán sustancialmente más rápidas.

---

## 7. Cómo reproducir la medición

Con el servidor levantado en el puerto 8123 y los assets compilados:

```bash
php artisan view:clear && npm run build && php artisan migrate:fresh --seed
```

El guion de medición (`medir.js`) recorre las doce rutas públicas en los dos perfiles y los dos temas, tres veces cada combinación, con la caché desactivada, y devuelve un JSON con TTFB, FCP, LCP, DOMContentLoaded, evento `load`, número de peticiones y kilobytes transferidos por medición.

```bash
playwright-cli open
playwright-cli --raw run-code --filename=medir.js
```

> ⚠️ **`npm run build` después de `php artisan view:clear`, en ese orden.** La hoja de estilos escanea las vistas compiladas en caché, así que el tamaño del bundle depende de qué vistas estén compiladas en ese momento: sin limpiar antes, se ha visto pasar de 69 kB a 90 kB sin que nada del código cambie. Una medición hecha sobre un bundle inflado por caché no es comparable con esta.

---

*Documento elaborado como evidencia de la Fase 4 del cronograma firmado por la dirección ejecutiva de ASOBARES Capítulo Quindío. Cierra el hueco nº 1 declarado en `matriz-de-pruebas.md` §5.*

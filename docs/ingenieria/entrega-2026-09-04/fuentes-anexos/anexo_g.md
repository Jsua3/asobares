## 1. Resultado

El requisito se cumple con margen. La página principal pinta su elemento principal (LCP) en 972 ms sobre una conexión móvil 4G emulada, frente al tope contractual de 2.500 ms. Ninguna de las doce rutas públicas incumple el tope, ni en móvil ni en escritorio, ni en tema claro ni en oscuro.

| Medida contractual | Tope | Resultado (portada, móvil 4G) | Margen |
|---|---|---|---|
| Carga de la página principal | 2.500 ms | 972 ms (LCP); 1.803 ms (carga completa) | 61 % por debajo en LCP; 28 % en carga completa |

La ruta más lenta del sitio es `/boletin`, con 2.132 ms de LCP en móvil 4G: cumple, pero es la que menos margen tiene y la primera que habría que vigilar si crece su contenido.

## 2. Método

- Herramienta: `playwright-cli` sobre Chromium, controlado por el protocolo de DevTools. Se midió un navegador real cargando el sitio servido por la aplicación, no una simulación sintética.
- Servidor: la propia aplicación (`php artisan serve`, puerto 8123) con la base de datos de demostración recién sembrada (`migrate:fresh --seed`) y los recursos compilados en modo producción.
- Caché desactivada en cada medición (`Network.setCacheDisabled`). Todas las cifras corresponden a una primera visita, que es el caso más desfavorable.
- Dos perfiles. Móvil 4G: ventana de 390 por 844 píxeles, con la red limitada a 9 Mbps de bajada, 1,5 Mbps de subida y 170 ms de latencia; es el perfil de referencia, porque el cronograma indica que más del 80 % de los usuarios entra por celular. Escritorio: ventana de 1280 por 800 píxeles, sin limitación de red.
- Tres corridas por combinación; se reporta la mediana. Se incluye además el peor LCP de las tres para mostrar la dispersión.
- Los dos temas: la portada se midió también en tema oscuro para descartar que el tema tuviera costo.
- Total: 78 mediciones, 0 errores.

| Métrica | Qué mide |
|---|---|
| TTFB | Tiempo hasta el primer byte: cuánto tarda el servidor en responder. |
| FCP | *First Contentful Paint*: cuándo aparece el primer contenido en pantalla. |
| LCP | *Largest Contentful Paint*: cuándo termina de pintarse el elemento principal. Es la métrica que representa «cargó» para el usuario y contra la que se contrasta el tope de 2,5 s. |
| Carga | Evento `load` completo, con todos los recursos secundarios. |
| Peticiones y KB | Número de recursos y peso transferido. |

## 3. Resultados en móvil 4G (perfil de referencia)

Medianas de tres corridas, en milisegundos.

| Ruta | TTFB | FCP | LCP | LCP peor | Carga | Peticiones | KB |
|---|---:|---:|---:|---:|---:|---:|---:|
| `/` (portada) | 393 | 972 | 972 | 976 | 1.803 | 16 | 361 |
| `/` (tema oscuro) | 86 | 980 | 980 | 980 | 1.783 | 16 | 361 |
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
| `/boletin` | 429 | 956 | 2.132 | 2.136 | 1.802 | 19 | 438 |
| Peor valor del sitio | 438 | 984 | 2.132 | 2.136 | 1.838 | 23 | 479 |

## 4. Resultados en escritorio

| Ruta | TTFB | FCP | LCP | Carga | Peticiones | KB |
|---|---:|---:|---:|---:|---:|---:|
| `/` (portada) | 135 | 592 | 592 | 1.083 | 21 | 574 |
| `/` (tema oscuro) | 128 | 580 | 580 | 1.081 | 21 | 574 |
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
| Peor valor del sitio | 428 | 748 | 1.088 | 1.190 | 27 | 823 |

## 5. Lectura de los resultados

El tema no tiene costo. La portada carga en 972 ms en tema claro y en 980 ms en tema oscuro; en escritorio, 592 y 580 ms. La diferencia está dentro del ruido de medición, de modo que el sistema de variables semánticas de color no impone penalización de rendimiento.

Hay dos familias de rutas. Las páginas de texto pintan su elemento principal en cuanto llega el HTML y la hoja de estilos (LCP cercano al FCP, unos 960 ms en móvil). Las páginas con rejilla de imágenes (directorio, artistas, eventos y boletín) tienen el LCP entre 1,8 y 2,1 s, porque su elemento principal es una imagen que todavía viaja por la red 4G. Es el comportamiento esperado y sigue dentro del tope, pero explica dónde está el margen restante.

La ruta `/directorio` es la más pesada del sitio (479 KB en móvil, 823 KB en escritorio, 27 peticiones). La diferencia con las demás corresponde a la biblioteca de mapas y a las teselas de OpenStreetMap; es un costo aceptado por el requisito de mapas del cronograma.

La ruta `/boletin` es la que conviene vigilar: 2.132 ms dejan 368 ms de margen frente al tope. Si el boletín crece en imágenes o se publican portadas más pesadas que las de prueba, sería la primera en incumplir. La carga diferida de imágenes (`loading="lazy"`) ya está aplicada; la mitigación pendiente es acotar el tamaño de las portadas que suba la secretaría desde el panel.

El peso total es moderado. Ninguna ruta supera 479 KB en móvil ni 27 peticiones. No hay bibliotecas pesadas, las fuentes van subconjuntadas (10,5 KB cada una) y la hoja de estilos pesa 58 KB sin comprimir y 11 KB con compresión.

## 6. Alcance y límites de la medición

1. La medición se hizo contra el servidor local, no contra un servidor de producción. El TTFB reportado no incluye la latencia de red hasta el servidor. Cuando el sitio se despliegue, la medición debe repetirse: sobre un servidor en la región este de Estados Unidos, la ida y vuelta desde Colombia añadiría entre 80 y 150 ms a cada petición. Con 972 ms de LCP y un tope de 2.500 ms, el margen lo absorbe, pero la cifra definitiva es la que se tome contra el dominio real. Esto depende del riesgo R-14 (cuenta institucional de alojamiento), no del equipo de desarrollo.
2. No se usaron dispositivos reales. El perfil móvil es una ventana de 390 píxeles con la red limitada, no un teléfono, y la CPU no está limitada, por lo que un equipo de gama baja será más lento en el trabajo de pintado. Los requisitos RNF-01 y RNF-07 siguen abiertos y corresponden a la semana 7 del cronograma.
3. Solo se midió en Chromium. No se midió en Safari ni en Firefox. Para el público objetivo, mayoritariamente Android, es representativo, pero no es cobertura completa.
4. Solo se midieron rutas públicas sin sesión. No se midió el panel de administración ni el portal del asociado con sesión iniciada; el cronograma no les fija tope, pero conviene medirlos antes de la capacitación.
5. Primera visita con caché en frío. Es deliberado y es el caso más desfavorable; las visitas repetidas son más rápidas.

## 7. Reproducción de la medición

Con el servidor levantado en el puerto 8123 y los recursos compilados:

```
php artisan view:clear && npm run build && php artisan migrate:fresh --seed
playwright-cli open
playwright-cli --raw run-code --filename=medir.js
```

El guion `medir.js` recorre las doce rutas públicas en los dos perfiles y los dos temas, tres veces cada combinación, con la caché desactivada, y devuelve un archivo JSON con TTFB, FCP, LCP, `DOMContentLoaded`, evento `load`, número de peticiones y kilobytes transferidos por medición.

Nota. `npm run build` debe ejecutarse después de `php artisan view:clear`, en ese orden. La hoja de estilos se genera a partir de las vistas compiladas en caché, de modo que el tamaño del paquete depende de qué vistas estén compiladas en ese momento. Una medición hecha sobre un paquete inflado por la caché no es comparable con esta.

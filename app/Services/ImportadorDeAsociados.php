<?php

namespace App\Services;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader as LectorCsv;
use OpenSpout\Reader\XLSX\Options as OpcionesXlsx;
use OpenSpout\Reader\XLSX\Reader as LectorXlsx;
use Throwable;

/**
 * Carga la base de establecimientos que mantiene la oficina del gremio.
 *
 * Tres decisiones que no son negociables y por las que este servicio existe
 * en vez de un `updateOrCreate` suelto:
 *
 * 1. **Nada se publica al importar.** Una ficha nueva nace en borrador y una
 *    que ya existía conserva el estado que tuviera. Publicar es un acto de la
 *    dirección, no el efecto de abrir un Excel.
 * 2. **El teléfono y el correo entran como datos internos.** En la hoja son la
 *    forma de contactar al propietario, no un dato que él haya pedido
 *    publicar. La oficina decide después qué sube a la ficha pública, que es
 *    justo lo que el campo `whatsapp` ya modela.
 * 3. **La importación no otorga autorización de habeas data.** Las columnas
 *    `autorizacion_datos_*` solo se llenan si quien ejecuta el comando declara
 *    el soporte firmado. Sin eso quedan nulas, y son ellas las que dicen si la
 *    ficha puede publicarse.
 *
 * Y una concesión al mundo real: el archivo del gremio **no empieza por los
 * encabezados**. Trae un banner («BASE DE DATOS QUINDIO»), filas en blanco y
 * recién en la sexta fila la cabecera. Por eso se busca la cabecera en vez de
 * suponerla.
 */
class ImportadorDeAsociados
{
    /** Hasta dónde se busca la fila de encabezados antes de rendirse. */
    private const int FILAS_DE_CORTESIA = 25;

    /**
     * Encabezado normalizado del archivo → campo del modelo.
     *
     * Las claves salen de `normalizar()`, así que la oficina puede cambiar
     * tildes, mayúsculas y espacios sin romper la carga.
     *
     * @var array<string, string>
     */
    public const array MAPA = [
        'nombre_del_establecimiento' => 'nombre',
        'descripcion_del_establecimiento' => 'descripcion',
        'direccion' => 'direccion',
        'horario_de_atencion' => 'horario',
        'genero_musical' => 'genero_musical',
        'servicios_ofrecidos' => 'servicios',
        'perfil_instagram' => 'instagram_url',
        // Internos.
        'nombre' => 'representante',
        'nit' => 'documento',
        'telefono' => 'telefono_interno',
        'correo' => 'correo_interno',
        'menciones_adicionales' => 'notas_internas',
    ];

    /** @var list<string> */
    public const array COLUMNAS_REQUERIDAS = ['nombre_del_establecimiento', 'municipio'];

    public function importar(
        string $rutaAbsoluta,
        ?string $categoriaPorDefecto = null,
        ?string $autorizacionFecha = null,
        ?string $autorizacionOrigen = null,
    ): ResultadoDeCargaDeAsociados {
        $resultado = new ResultadoDeCargaDeAsociados;

        try {
            $filas = $this->leer($rutaAbsoluta);
        } catch (Throwable $error) {
            $resultado->agregarErrorGeneral('No se pudo leer el archivo: '.$error->getMessage());

            return $resultado;
        }

        $cabecera = $this->ubicarCabecera($filas);

        if ($cabecera === null) {
            $resultado->agregarErrorGeneral(
                'No se encontró la fila de encabezados en las primeras '.self::FILAS_DE_CORTESIA.
                ' filas. Se esperan al menos: '.implode(', ', self::COLUMNAS_REQUERIDAS).'.'
            );

            return $resultado;
        }

        [$filaDeCabecera, $encabezados] = $cabecera;

        $municipios = $this->indicePorSlug(Municipio::query()->pluck('nombre', 'id'));
        $categorias = $this->indicePorSlug(Categoria::query()->pluck('nombre', 'id'));

        $categoriaId = null;

        if ($categoriaPorDefecto !== null) {
            $categoriaId = $categorias[Str::slug($categoriaPorDefecto)] ?? null;

            if ($categoriaId === null) {
                $resultado->agregarErrorGeneral(
                    "No existe la categoría «{$categoriaPorDefecto}». Las disponibles son: ".
                    Categoria::query()->pluck('nombre')->implode(', ').'.'
                );

                return $resultado;
            }
        }

        if ($autorizacionFecha === null) {
            $resultado->agregarAviso(
                'Ninguna ficha queda con autorización de datos: se cargan como borrador y no se publican '
                .'hasta que exista el soporte firmado del titular (Ley 1581/2012).'
            );
        }

        // El archivo se aplica como un bloque: si el proceso se cae a mitad de
        // camino no pueden quedar la mitad de las fichas cargadas y la otra no.
        DB::transaction(function () use (
            $filas, $filaDeCabecera, $encabezados, $municipios, $categorias,
            $categoriaId, $autorizacionFecha, $autorizacionOrigen, $resultado
        ): void {
            foreach ($filas as $numero => $celdas) {
                if ($numero <= $filaDeCabecera) {
                    continue;
                }

                $fila = $this->asociar($encabezados, $celdas);

                if ($this->estaVacia($fila)) {
                    continue;
                }

                $this->procesarFila(
                    $fila, $numero, $municipios, $categorias,
                    $categoriaId, $autorizacionFecha, $autorizacionOrigen, $resultado
                );
            }
        });

        return $resultado;
    }

    /**
     * @param  array<string, string>  $fila
     * @param  array<string, int>  $municipios
     * @param  array<string, int>  $categorias
     */
    private function procesarFila(
        array $fila,
        int $numero,
        array $municipios,
        array $categorias,
        ?int $categoriaPorDefecto,
        ?string $autorizacionFecha,
        ?string $autorizacionOrigen,
        ResultadoDeCargaDeAsociados $resultado,
    ): void {
        $nombre = trim($fila['nombre_del_establecimiento'] ?? '');

        if ($nombre === '') {
            $resultado->agregarErrorDeFila($numero, 'La columna «Nombre del Establecimiento» viene vacía.');

            return;
        }

        $nombreMunicipio = trim($fila['municipio'] ?? '');
        $municipioId = $municipios[Str::slug($nombreMunicipio)] ?? null;

        if ($municipioId === null) {
            $resultado->agregarErrorDeFila(
                $numero,
                "«{$nombre}»: el municipio «{$nombreMunicipio}» no está en el catálogo del Quindío."
            );

            return;
        }

        // La hoja del gremio no trae categoría. O la manda el comando, o la
        // fila la declara; adivinarla por el nombre del sitio sería inventar
        // información del cliente.
        $categoriaId = isset($fila['categoria'])
            ? ($categorias[Str::slug(trim($fila['categoria']))] ?? null)
            : $categoriaPorDefecto;

        if ($categoriaId === null) {
            $resultado->agregarErrorDeFila(
                $numero,
                "«{$nombre}»: no hay categoría. Añade una columna «Categoría» al archivo o pásala con --categoria."
            );

            return;
        }

        $atributos = ['municipio_id' => $municipioId];

        foreach (self::MAPA as $columna => $campo) {
            if ($campo === 'nombre') {
                continue;
            }

            $valor = trim($fila[$columna] ?? '');

            // Una celda vacía no borra lo que ya había: el archivo del gremio
            // está incompleto a propósito (el correo falta en 15 de 41 filas) y
            // una ausencia no es una orden de borrado.
            if ($valor !== '') {
                $atributos[$campo] = $campo === 'instagram_url' ? $this->normalizarEnlace($valor) : $valor;
            }
        }

        if ($autorizacionFecha !== null) {
            $atributos['autorizacion_datos_at'] = $autorizacionFecha;
            $atributos['autorizacion_datos_origen'] = $autorizacionOrigen;
        }

        $slug = Str::slug($nombre);
        $asociado = Asociado::query()->where('slug', $slug)->first();

        if ($asociado === null) {
            Asociado::query()->create([
                ...$atributos,
                'nombre' => $nombre,
                'slug' => $slug,
                'categoria_id' => $categoriaId,
                // Nace en borrador, siempre. Publicar es decisión de la dirección.
                'estado' => EstadoPublicacion::Borrador,
            ]);

            $resultado->contarCreado();

            return;
        }

        // A una ficha que ya existía NO se le toca el estado: si la dirección
        // ya la publicó, una reimportación no puede despublicarla, y si la
        // devolvió a borrador, no puede resucitarla.
        $asociado->fill([...$atributos, 'nombre' => $nombre])->save();

        $resultado->contarActualizado();
    }

    /**
     * Lee el archivo completo a memoria como matriz de cadenas.
     *
     * Son decenas de filas, no millones: cargarlo entero permite buscar la
     * cabecera, que es lo que este archivo obliga a hacer.
     *
     * El array va **indexado por el número de fila real de la hoja**, no por
     * posición. Importa: los errores se reportan con ese número para que la
     * oficina encuentre la fila en su archivo, y el lector se salta las filas
     * vacías —el del gremio tiene cuatro antes de la cabecera—, así que la
     * posición y el número dejan de coincidir en la primera fila en blanco.
     *
     * @return array<int, list<string>>
     */
    private function leer(string $ruta): array
    {
        $extension = Str::lower(pathinfo($ruta, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xlsm'], true)) {
            return $this->leerXlsx($ruta);
        }

        $lector = LectorCsv::createFromPath($ruta, 'r');
        $filas = [];

        foreach ($lector->getRecords() as $posicion => $fila) {
            $filas[$posicion + 1] = array_map(
                static fn ($c): string => trim((string) $c),
                array_values($fila)
            );
        }

        return $filas;
    }

    /** @return array<int, list<string>> */
    private function leerXlsx(string $ruta): array
    {
        // ⚠️ Sin `SHOULD_PRESERVE_EMPTY_ROWS` el lector RENUMERA las filas: se
        // salta las vacías y entrega claves consecutivas, así que la cabecera
        // del archivo del gremio —fila 6 de la hoja, con cuatro filas en
        // blanco encima— llega como fila 2 y todos los errores se reportan
        // corridos cuatro renglones. Medido: la fila 8 salía como «Fila 4».
        // El desfase es silencioso y solo se nota cuando alguien va a buscar
        // la fila mala a su Excel y encuentra otra cosa.
        $opciones = new OpcionesXlsx;
        $opciones->SHOULD_PRESERVE_EMPTY_ROWS = true;

        $lector = new LectorXlsx($opciones);
        $lector->open($ruta);

        $filas = [];

        foreach ($lector->getSheetIterator() as $hoja) {
            foreach ($hoja->getRowIterator() as $numero => $fila) {
                $filas[(int) $numero] = array_map(
                    static fn ($celda): string => $celda instanceof \DateTimeInterface
                        ? $celda->format('Y-m-d')
                        : trim((string) $celda),
                    $fila->toArray()
                );
            }

            // Solo la primera hoja: el archivo del gremio tiene una sola y
            // recorrer las demás mezclaría años distintos sin avisar.
            break;
        }

        $lector->close();

        return $filas;
    }

    /**
     * Busca la fila que hace de encabezado y devuelve su número real.
     *
     * @param  array<int, list<string>>  $filas
     * @return array{int, list<string>}|null
     */
    private function ubicarCabecera(array $filas): ?array
    {
        foreach ($filas as $numero => $celdas) {
            if ($numero > self::FILAS_DE_CORTESIA) {
                break;
            }

            $encabezados = array_map($this->normalizar(...), $celdas);

            if (array_diff(self::COLUMNAS_REQUERIDAS, $encabezados) === []) {
                return [$numero, $encabezados];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $encabezados
     * @param  list<string>  $celdas
     * @return array<string, string>
     */
    private function asociar(array $encabezados, array $celdas): array
    {
        $fila = [];

        foreach ($encabezados as $posicion => $encabezado) {
            if ($encabezado === '') {
                continue;
            }

            $fila[$encabezado] = $celdas[$posicion] ?? '';
        }

        return $fila;
    }

    /** @param  array<string, string>  $fila */
    private function estaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim($valor) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, string>  $registros
     * @return array<string, int>
     */
    private function indicePorSlug(Collection $registros): array
    {
        $indice = [];

        foreach ($registros as $id => $nombre) {
            $indice[Str::slug($nombre)] = (int) $id;
        }

        return $indice;
    }

    /** Tolera mayúsculas, tildes y espacios en los encabezados de la oficina. */
    private function normalizar(string $texto): string
    {
        return Str::of($texto)->trim()->lower()->ascii()->replace([' ', '-', '.'], '_')->squish()->toString();
    }

    /**
     * La hoja trae el Instagram unas veces como URL y otras como arroba.
     * El campo es `instagram_url` y las vistas lo pintan como enlace: si entra
     * «@barmerlin» el enlace apunta a una ruta del propio sitio y no lleva a
     * ninguna parte.
     */
    private function normalizarEnlace(string $valor): string
    {
        $valor = trim($valor);

        if (Str::startsWith($valor, ['http://', 'https://'])) {
            return $valor;
        }

        return 'https://www.instagram.com/'.ltrim($valor, '@/');
    }
}

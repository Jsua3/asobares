# Alta de afiliados reales — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Que la dirección importe desde el panel la base real del gremio creando fichas en borrador y cuentas de `/mi-cuenta` con contraseña genérica, y que cada afiliado quede obligado a cambiarla sin que mientras tanto esa contraseña compartida abra datos de terceros.

**Architecture:**
- **Importación, en capas:** el importador existente dice qué fichas tocó; un servicio nuevo crea las cuentas con reglas de contención; un orquestador envuelve los dos pasos en una transacción; una acción de Filament en el listado de Asociados le pone la cara.
- **Portal:** la columna `users.contrasena_provisional` gobierna un middleware que cierra las secciones con datos de terceros, un aviso tocable y una pantalla nueva `/mi-cuenta/seguridad`, que es la única que apaga la marca.
- **Panel:** marca provisional toda contraseña que escribe la oficina para un afiliado.

**Tech Stack:** Laravel 13.32 · Filament 5.8.2 · Livewire 4.4 · PHPUnit 12 · spatie/laravel-permission · spatie/laravel-activitylog · OpenSpout (ya instalado) · SQLite en pruebas, PostgreSQL 17 en producción.

**Spec:** `scratchpad/spec-alta-de-afiliados-reales.md` (misma carpeta que este plan). Léelo antes de empezar.

## Global Constraints

- **Base:** `main` en `6d43b91` o posterior. **Rama de trabajo:** `afiliados/alta-real`.
- **PHP:** `/c/Users/Predator/.config/php85/php.exe` (8.5.9, con `intl` y `gd`). El `php` del PATH no sirve. **Todos los comandos desde la herramienta Bash**, en la raíz `D:\Sua_Files\IdeaProjects\Asobares3` (`cd /d/Sua_Files/IdeaProjects/Asobares3`). Tinker va por Bash: PowerShell se come las comillas.
- **Git:** siempre `GIT_OPTIONAL_LOCKS=0 git …`. **Nunca `git add -A` ni `git add .`**: `.claude/launch.json` está modificado y los archivos de la socialización están sin versionar, y ninguno de los dos entra en estos commits. Se añaden rutas explícitas.
- **Identificadores y textos en español.** Mensajes de commit en español con prefijo convencional (`feat(afiliados): …`, `test(…)`, `fix(…)`, `docs(…)`), terminados con una línea en blanco y `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`.
- **Política de contraseñas:** `Illuminate\Validation\Rules\Password::min(12)->mixedCase()->numbers()->symbols()`. Nunca aceptar `App\Console\Commands\CrearUsuarioDelPanel::CLAVE_PUBLICADA`.
- **No existe `lang/`.** Cada regla lleva su mensaje escrito a mano. La regla `Password` falla con las claves **`password.mixed`, `password.letters`, `password.numbers` y `password.symbols`** (vendor `Illuminate/Validation/Rules/Password.php`, `addFailure`), así que el mensaje propio va en `{campo}.password.symbols` o en `password.symbols`, **no** en `{campo}.symbols`. `min` sí va como `{campo}.min`.
- **En el portal, los campos de contraseña se llaman `current_password`, `password` y `password_confirmation`.** Son los que el manejador de excepciones de Laravel no devuelve a la sesión (`$dontFlash`). Con otro nombre, la contraseña viajaría a la tabla de sesiones y el componente `campo` la pintaría en el HTML.
- **`User` declara `#[Fillable(['name','email','password','asociado_id'])]`:** `contrasena_provisional` y `email_verified_at` se asignan sueltos o con `forceFill()`, nunca dentro de `create()` ni `update()`.
- **Sin permisos nuevos, sin dependencias nuevas, sin carpeta `lang/`.**
- **Datos personales:** el `.xlsx` real (`D:\Sua_Files\Downloads\Base_de_datos_Cap__Quindio_actualizada FINAL.xlsx`) nunca entra al árbol ni a una prueba. Las pruebas usan nombres y correos ficticios con dominio `.test`. Ni en el código, ni en el expediente, ni en un commit aparece un correo, un nombre o un documento de un afiliado real.
- **Pruebas:** se crean con `php artisan make:test --phpunit <Nombre>` (o `Panel/<Nombre>`) y se sobrescribe el contenido. **Regla 3 del prompt maestro:** después de ver verde, rompe a propósito el cableado que la prueba protege, comprueba que se pone roja y restaura. Cada tarea trae su lista de mutaciones.
- **Formato:** antes de cada commit con PHP, `/c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent`.
- **Edición de PHP y Blade:** con las herramientas Read/Edit/Write. Nada de scripts que normalicen finales de línea (ya mutilaron siete pruebas una vez).
- **Producción:** esta implementación **no** empuja a `main`, **no** corre nada en Laravel Cloud y **no** crea cuentas reales. Fusionar y empujar solo con el visto bueno explícito de Sua (Tarea 12).

## Mapa de archivos

| Archivo | Tarea | Responsabilidad |
|---|---|---|
| `database/migrations/2026_09_16_*_anade_contrasena_provisional_a_users.php` (nuevo) | 1 | Columna `contrasena_provisional` |
| `app/Models/User.php` | 1 | Cast y `marcarContrasenaProvisionalSiEsAfiliado()` |
| `app/Services/ResultadoDeCargaDeAsociados.php` | 2 | `anotarFicha()` y `fichasTocadas()` |
| `app/Services/ImportadorDeAsociados.php` | 2 | Anota cada ficha creada o actualizada |
| `app/Services/ResultadoDeAltaDeCuentas.php` (nuevo) | 3 | Conteo y motivos de las cuentas |
| `app/Services/AltaDeCuentasDeAfiliados.php` (nuevo) | 3 | Crea cuentas con reglas de contención |
| `app/Services/ImportacionDeLaBaseDelGremio.php` (nuevo) | 4 | Transacción: fichas y cuentas |
| `app/Filament/Resources/Asociados/Pages/ListAsociados.php` | 5 | Acción «Importar base del gremio» |
| `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php` (nuevo) | 6 | Cambio de contraseña del titular |
| `resources/views/publico/mi-cuenta/seguridad.blade.php` (nuevo) | 6 | Pantalla de seguridad |
| `routes/web.php` | 6, 7 | Rutas de seguridad y grupo cerrado |
| `app/Providers/AppServiceProvider.php` | 6 | Limitador `mi-cuenta-seguridad` |
| `app/Http/Middleware/ExigirContrasenaPropia.php` (nuevo) | 7 | Cierra secciones con la marca puesta |
| `bootstrap/app.php` | 7 | Alias `contrasena.propia` |
| `resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php` (nuevo) | 8 | Aviso tocable |
| `resources/views/publico/mi-cuenta/index.blade.php` | 8, 9 | Aviso, botón «Seguridad», `aviso` en sesión, cartera sin cargar |
| `resources/views/publico/mi-cuenta/fotos/index.blade.php` | 8 | Aviso |
| `app/Http/Controllers/Publico/MiCuentaController.php` | 9 | Cartera nula ya no es «al día» |
| `database/seeders/SettingSeeder.php` | 6, 8, 9 | Siete ajustes nuevos del grupo `mi_cuenta` |
| `app/Filament/Resources/Users/Pages/CreateUser.php`, `EditUser.php` | 10 | Marcan provisional |
| `app/Filament/Resources/Users/Tables/UsersTable.php` | 10 | Columna y filtro |
| `app/Filament/Resources/Users/Schemas/UserForm.php` | 10 | Texto de ayuda |
| `app/Console/Commands/CrearUsuarioDelPanel.php` | 10 | Marca provisional y claves de mensaje correctas |

---

### Tarea 0: Preparar el árbol

**Files:** ninguno del producto.

- [ ] **Paso 1: Puerta del alcance.** Pregúntale a Sua si la ampliación ya quedó registrada por escrito (Acta 09 u otra) y si Ingrid está avisada. **Si la respuesta es no, para aquí** y díselo. Regla 1 del prompt maestro: toda ampliación se registra antes de codificarse.

- [ ] **Paso 2: Comprobar que nadie movió `main`.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && GIT_OPTIONAL_LOCKS=0 git fetch origin && GIT_OPTIONAL_LOCKS=0 git log --oneline -3 origin/main && GIT_OPTIONAL_LOCKS=0 git status --short
```

Esperado: `origin/main` en `6d43b91` (o posterior, y en ese caso leer lo nuevo de `material/estado.md` antes de seguir). En `status`, solo ` M .claude/launch.json` y los `??` de `docs/ingenieria/entrega-2026-09-04/`.

- [ ] **Paso 3: Pasar a `main` y abrir la rama.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && GIT_OPTIONAL_LOCKS=0 git checkout main && GIT_OPTIONAL_LOCKS=0 git merge --ff-only origin/main && GIT_OPTIONAL_LOCKS=0 git checkout -b afiliados/alta-real
```

Esperado: `Switched to a new branch 'afiliados/alta-real'`. El `launch.json` modificado viaja sin conflicto (es idéntico en las dos ramas).

- [ ] **Paso 4: Instalar las dependencias de `main`.** El `vendor/` de la raíz tiene Filament 4.12.5 y `main` exige 5.8.2. Sin esto, todo lo demás corre contra la versión equivocada. En la herramienta **PowerShell**:

```powershell
$env:PATH = 'C:\Users\Predator\.config\php85;' + $env:PATH; & 'C:\Users\Predator\.config\herd-lite\bin\composer.bat' install --no-interaction
```

Después, en Bash:

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && grep -A2 '"name": "filament/filament"' vendor/composer/installed.json | grep '"version"'
```

Esperado: `"version": "v5.8.2",`.

- [ ] **Paso 5: Activos del front.** Las pruebas que pintan vistas leen el manifiesto de Vite.

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan view:clear && npm install && npm run build
```

Esperado: `✓ built in …`. Si `npm install` cambia `package-lock.json`, **no se commitea**: se revierte con `GIT_OPTIONAL_LOCKS=0 git checkout -- package-lock.json`.

- [ ] **Paso 6: Línea base de las pruebas vecinas.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='ImportacionDeAsociadosTest|InvalidacionDeSesionTest|LimitesDePeticionesTest|CrearUsuarioDelPanelTest|AccionesDelPanelTest|FormulariosPublicosTest|AjustesQueSirvenParaAlgoTest|LoginDeAsociadoTest'
```

Esperado: todo en verde. Si algo está rojo **antes** de tocar nada, para y repórtalo: no es de esta tarea.

---

### Tarea 1: La marca de contraseña provisional

**Files:**
- Create: `database/migrations/2026_09_16_<hora>_anade_contrasena_provisional_a_users.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/ContrasenaProvisionalTest.php`

**Interfaces:**
- Produces: columna `users.contrasena_provisional` (bool, default `false`), cast `boolean` y `User::marcarContrasenaProvisionalSiEsAfiliado(): void`.

- [ ] **Paso 1: Crear la prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit ContrasenaProvisionalTest --no-interaction
```

Sobrescribe `tests/Feature/ContrasenaProvisionalTest.php` con:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Una contraseña que puso alguien distinto del titular es provisional.
 *
 * La marca vive en `users.contrasena_provisional` y solo la enciende el
 * modelo, para cuentas de rol asociado: el panel y /mi-cuenta son puertas
 * distintas, y al equipo del gremio la marca no le cierra nada.
 */
class ContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    public function test_una_cuenta_nace_sin_la_marca(): void
    {
        $usuario = User::factory()->create();

        $this->assertFalse($usuario->fresh()->contrasena_provisional);
    }

    public function test_marcar_una_cuenta_de_afiliado_la_deja_provisional(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }

    /** @return array<string, array{string}> */
    public static function rolesDelEquipo(): array
    {
        return [
            'dirección' => [User::ROL_SUPER_ADMIN],
            'secretaría' => [User::ROL_SUBADMIN],
        ];
    }

    #[DataProvider('rolesDelEquipo')]
    public function test_una_cuenta_del_equipo_no_se_marca(string $rol): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertFalse($usuario->fresh()->contrasena_provisional);
    }

    /**
     * El formulario de Usuarios guarda el rol nuevo antes del gancho que
     * marca, y la instancia puede traer la relación `roles` cargada con el
     * rol viejo. La marca tiene que mirar el rol vigente.
     */
    public function test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);
        $usuario->load('roles');

        User::query()->findOrFail($usuario->id)->syncRoles([User::ROL_ASOCIADO]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ContrasenaProvisionalTest
```

Esperado: FAIL, por la columna inexistente o por `Call to undefined method … marcarContrasenaProvisionalSiEsAfiliado()`.

- [ ] **Paso 3: Crear la migración.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:migration anade_contrasena_provisional_a_users --table=users --no-interaction
```

Sobrescribe el archivo generado con:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La contraseña que puso alguien distinto del titular: la genérica de la
     * importación de la base del gremio o la que la oficina escribe en el
     * panel. Solo el titular la apaga, desde /mi-cuenta/seguridad.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('contrasena_provisional')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('contrasena_provisional');
        });
    }
};
```

- [ ] **Paso 4: El modelo.** En `app/Models/User.php`, dentro de `casts()`, añade después de `'has_email_authentication' => 'boolean',`:

```php
            'contrasena_provisional' => 'boolean',
```

Y después del método `esAsociado()`, añade:

```php
    /**
     * Toda contraseña que pone alguien distinto del titular de una cuenta de
     * afiliado nace provisional: la genérica de la importación, la que la
     * oficina escribe en el panel y la del comando de alta. Mientras lo sea,
     * /mi-cuenta cierra las secciones con datos de terceros
     * (`ExigirContrasenaPropia`).
     *
     * La relación `roles` se descarta antes de mirar el rol: quien llama puede
     * traerla cargada con el rol que tenía antes de guardar. Y la marca se
     * asigna suelta porque `#[Fillable]` la descartaría en silencio.
     */
    public function marcarContrasenaProvisionalSiEsAfiliado(): void
    {
        $this->unsetRelation('roles');

        if (! $this->hasRole(self::ROL_ASOCIADO)) {
            return;
        }

        $this->contrasena_provisional = true;
        $this->save();
    }
```

- [ ] **Paso 5: Verla pasar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ContrasenaProvisionalTest
```

Esperado: 5 casos, todos en verde.

- [ ] **Paso 6: Mutaciones.** Cada una por separado; restaura después de cada una.
  1. Quita la línea del cast → `test_marcar_una_cuenta_de_afiliado_la_deja_provisional` rojo (llega `1`, no `true`).
  2. Quita el `if (! $this->hasRole(...))` y su `return` → los dos casos de `test_una_cuenta_del_equipo_no_se_marca` rojos.
  3. Quita `$this->unsetRelation('roles');` → `test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria` rojo.

- [ ] **Paso 7: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Models/User.php database/migrations/*_anade_contrasena_provisional_a_users.php tests/Feature/ContrasenaProvisionalTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(afiliados): la cuenta de un afiliado puede quedar con contraseña provisional

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 2: El importador dice qué fichas tocó

**Files:**
- Modify: `app/Services/ResultadoDeCargaDeAsociados.php`
- Modify: `app/Services/ImportadorDeAsociados.php` (método `procesarFila`)
- Test: `tests/Feature/ImportacionDeAsociadosTest.php` (añadir métodos)

**Interfaces:**
- Produces: `ResultadoDeCargaDeAsociados::anotarFicha(int $id): void` y `ResultadoDeCargaDeAsociados::fichasTocadas(): list<int>`, sin repetidos.

- [ ] **Paso 1: Pruebas que fallan.** En `tests/Feature/ImportacionDeAsociadosTest.php`, al final de la clase (antes de la llave de cierre), añade:

```php
    // -----------------------------------------------------------------------
    // Qué fichas tocó la carga: sobre eso, y solo sobre eso, se crean cuentas
    // -----------------------------------------------------------------------

    public function test_el_resultado_nombra_las_fichas_que_creo(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta'), $this->fila('Bar Merlin')]);

        $resultado = $this->importar();

        $this->assertEqualsCanonicalizing(Asociado::query()->pluck('id')->all(), $resultado->fichasTocadas());
        $this->assertCount(2, $resultado->fichasTocadas());
    }

    public function test_el_resultado_nombra_las_que_actualizo_y_no_las_que_no_venian(): void
    {
        $ajena = Asociado::factory()->create();
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);
        $this->importar();

        $resultado = $this->importar();

        $actualizada = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();

        $this->assertSame([$actualizada->id], $resultado->fichasTocadas());
        $this->assertNotContains($ajena->id, $resultado->fichasTocadas());
    }

    public function test_una_ficha_repetida_en_el_archivo_se_nombra_una_vez(): void
    {
        $this->archivoComoElDelGremio([$this->fila('[establecimiento]'), $this->fila('[establecimiento]')]);

        $resultado = $this->importar();

        $this->assertCount(1, $resultado->fichasTocadas());
    }

    public function test_una_fila_rechazada_no_queda_entre_las_fichas_tocadas(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Bar de Afuera', 'Pereira')]);

        $resultado = $this->importar();

        $this->assertSame([], $resultado->fichasTocadas());
    }
```

- [ ] **Paso 2: Verlas fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ImportacionDeAsociadosTest
```

Esperado: las cuatro nuevas en FAIL con `Call to undefined method …fichasTocadas()`. Las anteriores siguen verdes.

- [ ] **Paso 3: El resultado.** En `app/Services/ResultadoDeCargaDeAsociados.php`, añade después de la propiedad `$avisos`:

```php
    /**
     * Ids de las fichas creadas o actualizadas en esta carga, como claves para
     * no repetir: el archivo del gremio trae una misma ficha en dos filas.
     *
     * @var array<int, true>
     */
    private array $fichasTocadas = [];
```

Y después del método `agregarAviso()`:

```php
    public function anotarFicha(int $id): void
    {
        $this->fichasTocadas[$id] = true;
    }

    /**
     * Las fichas que esta carga creó o actualizó. La importación desde el
     * panel crea cuentas solo para estas: una ficha que no venía en el
     * archivo no recibe una.
     *
     * @return list<int>
     */
    public function fichasTocadas(): array
    {
        return array_keys($this->fichasTocadas);
    }
```

- [ ] **Paso 4: El importador.** En `app/Services/ImportadorDeAsociados.php`, dentro de `procesarFila`, reemplaza:

```php
        if ($asociado === null) {
            Asociado::query()->create([
```

por:

```php
        if ($asociado === null) {
            $creado = Asociado::query()->create([
```

Reemplaza también:

```php
            $resultado->contarCreado();

            return;
        }
```

por:

```php
            $resultado->contarCreado();
            $resultado->anotarFicha($creado->id);

            return;
        }
```

Y reemplaza:

```php
        $resultado->contarActualizado();
    }
```

por:

```php
        $resultado->contarActualizado();
        $resultado->anotarFicha($asociado->id);
    }
```

- [ ] **Paso 5: Verlas pasar.** El mismo comando del paso 2. Esperado: toda la clase en verde.

- [ ] **Paso 6: Mutaciones.**
  1. Quita `$resultado->anotarFicha($creado->id);` → `test_el_resultado_nombra_las_fichas_que_creo` rojo.
  2. Quita `$resultado->anotarFicha($asociado->id);` → `test_el_resultado_nombra_las_que_actualizo…` rojo.
  3. Cambia `$this->fichasTocadas[$id] = true;` por `$this->fichasTocadas[] = $id;` y el `return` por `array_values($this->fichasTocadas)` → `test_una_ficha_repetida…` rojo.

- [ ] **Paso 7: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Services/ResultadoDeCargaDeAsociados.php app/Services/ImportadorDeAsociados.php tests/Feature/ImportacionDeAsociadosTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(importador): la carga de la base dice qué fichas creó o actualizó

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 3: Crear las cuentas de los afiliados

**Files:**
- Create: `app/Services/ResultadoDeAltaDeCuentas.php`
- Create: `app/Services/AltaDeCuentasDeAfiliados.php`
- Test: `tests/Feature/AltaDeCuentasDeAfiliadosTest.php`

**Interfaces:**
- Consumes: `User::ROL_*`, `Asociado::usuarios(): HasMany` y la columna `contrasena_provisional` (Tarea 1).
- Produces:
  - `AltaDeCuentasDeAfiliados::crear(Illuminate\Support\Collection $fichas, string $contrasenaGenerica): ResultadoDeAltaDeCuentas`.
  - Constantes de motivo `SIN_CORREO`, `CORREO_INVALIDO`, `CORREO_COMPARTIDO`, `YA_TENIA_CUENTA`, `CORREO_DEL_EQUIPO` y `CORREO_CON_CUENTA`.
  - `ResultadoDeAltaDeCuentas::creadas(): int`, `sinCuenta(): list<string>` y `resumen(): string`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit AltaDeCuentasDeAfiliadosTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use App\Services\AltaDeCuentasDeAfiliados;
use App\Services\ResultadoDeAltaDeCuentas;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Las cuentas de /mi-cuenta que nacen de la base del gremio.
 *
 * Lo que importa aquí es la contención: una cuenta que ya existe no se toca,
 * un correo ambiguo no produce cuenta y ninguna cuenta del equipo cambia.
 */
class AltaDeCuentasDeAfiliadosTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function ficha(string $nombre, ?string $correo, ?string $representante = 'Duena del Local'): Asociado
    {
        return Asociado::factory()->create([
            'nombre' => $nombre,
            'correo_interno' => $correo,
            'representante' => $representante,
        ]);
    }

    /** @param  list<Asociado>  $fichas */
    private function crear(array $fichas): ResultadoDeAltaDeCuentas
    {
        return app(AltaDeCuentasDeAfiliados::class)->crear(collect($fichas), self::GENERICA);
    }

    public function test_crea_la_cuenta_vinculada_con_su_rol_y_la_marca(): void
    {
        $ficha = $this->ficha('Bar Merlin', 'duena@merlin.test');

        $resultado = $this->crear([$ficha]);

        $usuario = User::query()->where('email', 'duena@merlin.test')->firstOrFail();

        $this->assertSame(1, $resultado->creadas());
        $this->assertSame($ficha->id, $usuario->asociado_id);
        $this->assertTrue($usuario->hasRole(User::ROL_ASOCIADO));
        $this->assertTrue($usuario->contrasena_provisional);
        $this->assertNotNull($usuario->email_verified_at);
        $this->assertSame('Duena del Local', $usuario->name);
        $this->assertTrue(Hash::check(self::GENERICA, $usuario->password));
    }

    public function test_el_correo_se_guarda_en_minusculas_y_sin_espacios(): void
    {
        $this->crear([$this->ficha('Bar Merlin', '  Duena@Merlin.TEST ')]);

        $this->assertSame(1, User::query()->where('email', 'duena@merlin.test')->count());
    }

    public function test_sin_representante_la_cuenta_lleva_el_nombre_del_establecimiento(): void
    {
        $this->crear([$this->ficha('Bar Merlin', 'duena@merlin.test', representante: null)]);

        $this->assertSame('Bar Merlin', User::query()->where('email', 'duena@merlin.test')->value('name'));
    }

    /** @return array<string, array{string|null, string}> */
    public static function correosQueNoDanCuenta(): array
    {
        return [
            'vacío' => [null, AltaDeCuentasDeAfiliados::SIN_CORREO],
            'sin dominio completo' => ['fernanda@hotmail', AltaDeCuentasDeAfiliados::CORREO_INVALIDO],
            'sin arroba' => ['diana.gomez.bar', AltaDeCuentasDeAfiliados::CORREO_INVALIDO],
        ];
    }

    #[DataProvider('correosQueNoDanCuenta')]
    public function test_un_correo_vacio_o_invalido_no_da_cuenta_y_lo_dice(?string $correo, string $motivo): void
    {
        $resultado = $this->crear([$this->ficha('Bar Merlin', $correo)]);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(["«Bar Merlin»: {$motivo}"], $resultado->sinCuenta());
    }

    public function test_un_correo_repetido_en_dos_fichas_no_crea_ninguna_de_las_dos(): void
    {
        $resultado = $this->crear([
            $this->ficha('Coffee Azul', 'dueno@azul.test'),
            $this->ficha('Terraza Azul', 'DUENO@azul.test'),
        ]);

        $this->assertSame(0, User::query()->count());
        $this->assertSame([
            '«Coffee Azul»: '.AltaDeCuentasDeAfiliados::CORREO_COMPARTIDO.' «Terraza Azul»',
            '«Terraza Azul»: '.AltaDeCuentasDeAfiliados::CORREO_COMPARTIDO.' «Coffee Azul»',
        ], $resultado->sinCuenta());
    }

    public function test_una_ficha_que_ya_tiene_cuenta_no_recibe_otra(): void
    {
        $ficha = $this->ficha('Bar Merlin', 'nuevo@merlin.test');
        $dueno = User::factory()->create(['email' => 'anterior@merlin.test', 'asociado_id' => $ficha->id]);
        $dueno->syncRoles([User::ROL_ASOCIADO]);

        $resultado = $this->crear([$ficha]);

        $this->assertSame(0, User::query()->where('email', 'nuevo@merlin.test')->count());
        $this->assertSame(['«Bar Merlin»: '.AltaDeCuentasDeAfiliados::YA_TENIA_CUENTA], $resultado->sinCuenta());
    }

    /** @return array<string, array{string}> */
    public static function rolesDelEquipo(): array
    {
        return [
            'dirección' => [User::ROL_SUPER_ADMIN],
            'secretaría' => [User::ROL_SUBADMIN],
        ];
    }

    #[DataProvider('rolesDelEquipo')]
    public function test_el_correo_de_alguien_del_equipo_no_se_toca(string $rol): void
    {
        $equipo = User::factory()->create(['email' => 'oficina@gremio.test', 'password' => 'Clave-Del-Equipo-2026!']);
        $equipo->syncRoles([$rol]);
        $hashAntes = $equipo->fresh()->password;

        $resultado = $this->crear([$this->ficha('Bar Merlin', 'OFICINA@gremio.test')]);

        $equipo->refresh();

        $this->assertSame($hashAntes, $equipo->password);
        $this->assertTrue($equipo->hasRole($rol));
        $this->assertNull($equipo->asociado_id);
        $this->assertFalse($equipo->contrasena_provisional);
        $this->assertSame(['«Bar Merlin»: '.AltaDeCuentasDeAfiliados::CORREO_DEL_EQUIPO], $resultado->sinCuenta());
    }

    /**
     * Reimportar el archivo corregido no puede devolverle la genérica a quien
     * ya la cambió, aunque su correo aparezca en otra ficha.
     */
    public function test_una_cuenta_existente_nunca_recupera_la_generica(): void
    {
        $otraFicha = Asociado::factory()->create();
        $dueno = User::factory()->create(['email' => 'duena@merlin.test', 'password' => 'Su-Clave-Propia-2026!', 'asociado_id' => $otraFicha->id]);
        $dueno->syncRoles([User::ROL_ASOCIADO]);
        $hashAntes = $dueno->fresh()->password;

        $resultado = $this->crear([$this->ficha('Bar Merlin', 'duena@merlin.test')]);

        $dueno->refresh();

        $this->assertSame($hashAntes, $dueno->password);
        $this->assertSame($otraFicha->id, $dueno->asociado_id);
        $this->assertFalse($dueno->contrasena_provisional);
        $this->assertSame(['«Bar Merlin»: '.AltaDeCuentasDeAfiliados::CORREO_CON_CUENTA], $resultado->sinCuenta());
    }

    public function test_las_cuentas_nuevas_comparten_un_solo_hash(): void
    {
        $this->crear([$this->ficha('Bar Uno', 'uno@bar.test'), $this->ficha('Bar Dos', 'dos@bar.test')]);

        $hashes = User::query()->pluck('password')->unique();

        $this->assertCount(1, $hashes);
        $this->assertTrue(Hash::check(self::GENERICA, $hashes->first()));
    }

    public function test_el_resumen_cuenta_las_creadas_y_las_que_no(): void
    {
        $resultado = $this->crear([$this->ficha('Bar Uno', 'uno@bar.test'), $this->ficha('Bar Dos', null)]);

        $this->assertSame('1 cuenta creada · 1 ficha sin cuenta.', $resultado->resumen());
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=AltaDeCuentasDeAfiliadosTest
```

Esperado: FAIL con `Class "App\Services\AltaDeCuentasDeAfiliados" not found`.

- [ ] **Paso 3: El resultado.** Crea `app/Services/ResultadoDeAltaDeCuentas.php`:

```php
<?php

namespace App\Services;

/**
 * Qué cuentas de afiliado creó una importación y qué fichas se quedaron sin
 * la suya, con el motivo. No lleva correos ni contraseñas: lo lee la
 * dirección en una notificación del panel.
 */
class ResultadoDeAltaDeCuentas
{
    private int $creadas = 0;

    /** @var list<string> */
    private array $sinCuenta = [];

    public function contarCreada(): void
    {
        $this->creadas++;
    }

    public function agregarSinCuenta(string $establecimiento, string $motivo): void
    {
        $this->sinCuenta[] = "«{$establecimiento}»: {$motivo}";
    }

    public function creadas(): int
    {
        return $this->creadas;
    }

    /** @return list<string> */
    public function sinCuenta(): array
    {
        return $this->sinCuenta;
    }

    public function resumen(): string
    {
        $creadas = $this->creadas === 1 ? '1 cuenta creada' : "{$this->creadas} cuentas creadas";
        $sin = count($this->sinCuenta);

        if ($sin === 0) {
            return "{$creadas}.";
        }

        return "{$creadas} · {$sin} ".($sin === 1 ? 'ficha sin cuenta' : 'fichas sin cuenta').'.';
    }
}
```

- [ ] **Paso 4: El servicio.** Crea `app/Services/AltaDeCuentasDeAfiliados.php`:

```php
<?php

namespace App\Services;

use App\Models\Asociado;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SensitiveParameter;

/**
 * Crea las cuentas de /mi-cuenta de los afiliados que trae la base del
 * gremio, con la contraseña genérica que escribe la dirección y marcadas
 * como provisionales.
 *
 * Tres reglas que no son de estilo:
 *
 * 1. **Una cuenta que ya existe no se toca**: ni su contraseña, ni su vínculo,
 *    ni su rol, ni su marca. Volver a importar el archivo corregido no puede
 *    devolverle la genérica a quien ya la cambió.
 * 2. **Ante la duda, no se crea.** Un correo repetido en dos fichas del mismo
 *    archivo no dice de cuál local es el dueño, y una cuenta se vincula a un
 *    solo establecimiento: se reporta y decide el gremio.
 * 3. **El hash se calcula una vez.** Es la misma contraseña para todos, así
 *    que repetir el hash no protege nada y multiplica el tiempo de la
 *    petición del panel. Deja de ser la misma en cuanto cada dueño la cambia.
 */
class AltaDeCuentasDeAfiliados
{
    public const string SIN_CORREO = 'sin correo';

    public const string CORREO_INVALIDO = 'correo inválido';

    public const string CORREO_COMPARTIDO = 'el mismo correo está también en';

    public const string YA_TENIA_CUENTA = 'ya tenía cuenta';

    public const string CORREO_DEL_EQUIPO = 'el correo es de una cuenta del equipo del gremio';

    public const string CORREO_CON_CUENTA = 'el correo ya tiene una cuenta';

    /** @param  Collection<int, Asociado>  $fichas */
    public function crear(Collection $fichas, #[SensitiveParameter] string $contrasenaGenerica): ResultadoDeAltaDeCuentas
    {
        $resultado = new ResultadoDeAltaDeCuentas;
        $hash = Hash::make($contrasenaGenerica);

        $correos = $fichas->mapWithKeys(fn (Asociado $ficha): array => [$ficha->id => $this->normalizar($ficha->correo_interno)]);

        $fichasPorCorreo = $fichas
            ->filter(fn (Asociado $ficha): bool => $this->esValido($correos[$ficha->id]))
            ->groupBy(fn (Asociado $ficha): string => $correos[$ficha->id]);

        foreach ($fichas as $ficha) {
            $correo = $correos[$ficha->id];

            if ($correo === '') {
                $resultado->agregarSinCuenta($ficha->nombre, self::SIN_CORREO);

                continue;
            }

            if (! $this->esValido($correo)) {
                $resultado->agregarSinCuenta($ficha->nombre, self::CORREO_INVALIDO);

                continue;
            }

            $conElMismoCorreo = $fichasPorCorreo->get($correo);

            if ($conElMismoCorreo->count() > 1) {
                $otras = $conElMismoCorreo
                    ->reject(fn (Asociado $otra): bool => $otra->is($ficha))
                    ->map(fn (Asociado $otra): string => "«{$otra->nombre}»")
                    ->implode(', ');

                $resultado->agregarSinCuenta($ficha->nombre, self::CORREO_COMPARTIDO.' '.$otras);

                continue;
            }

            if ($ficha->usuarios()->exists()) {
                $resultado->agregarSinCuenta($ficha->nombre, self::YA_TENIA_CUENTA);

                continue;
            }

            $existente = User::query()->whereRaw('LOWER(email) = ?', [$correo])->first();

            if ($existente !== null) {
                $motivo = $existente->hasAnyRole([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN])
                    ? self::CORREO_DEL_EQUIPO
                    : self::CORREO_CON_CUENTA;

                $resultado->agregarSinCuenta($ficha->nombre, $motivo);

                continue;
            }

            // `forceFill` y no `create`: `#[Fillable]` descartaría en silencio
            // la marca y la verificación. El cast `hashed` reconoce el hash
            // ya calculado y no lo vuelve a calcular.
            $usuario = new User;
            $usuario->forceFill([
                'name' => filled($ficha->representante) ? $ficha->representante : $ficha->nombre,
                'email' => $correo,
                'password' => $hash,
                'asociado_id' => $ficha->id,
                'email_verified_at' => now(),
                'contrasena_provisional' => true,
            ])->save();
            $usuario->assignRole(User::ROL_ASOCIADO);

            $resultado->contarCreada();
        }

        return $resultado;
    }

    private function normalizar(?string $correo): string
    {
        return Str::lower(trim((string) $correo));
    }

    private function esValido(string $correo): bool
    {
        return $correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

- [ ] **Paso 5: Verla pasar.** El mismo comando del paso 2. Esperado: 14 casos en verde.

- [ ] **Paso 6: Mutaciones.**
  1. Quita el bloque del correo compartido (`if ($conElMismoCorreo->count() > 1) {…}`) → `test_un_correo_repetido…` rojo.
  2. Quita el bloque `if ($ficha->usuarios()->exists())` → `test_una_ficha_que_ya_tiene_cuenta…` rojo.
  3. Quita el bloque `if ($existente !== null)` → los dos casos del equipo y `test_una_cuenta_existente…` rojos (revienta el índice único o cambia la cuenta).
  4. Cambia el ternario de `$motivo` para que siempre sea `CORREO_CON_CUENTA` → los casos del equipo rojos.
  5. Cambia `'password' => $hash` por `'password' => $contrasenaGenerica` → `test_las_cuentas_nuevas_comparten_un_solo_hash` rojo.
  6. Quita `Str::lower(` (deja `trim((string) $correo)`) → `test_el_correo_se_guarda_en_minusculas…` rojo.
  7. Quita `'contrasena_provisional' => true,` → `test_crea_la_cuenta_vinculada…` rojo.

- [ ] **Paso 7: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Services/ResultadoDeAltaDeCuentas.php app/Services/AltaDeCuentasDeAfiliados.php tests/Feature/AltaDeCuentasDeAfiliadosTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(afiliados): las cuentas de la base del gremio nacen sin tocar las que existen

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 4: La importación completa, en una transacción

**Files:**
- Create: `app/Services/ImportacionDeLaBaseDelGremio.php`
- Test: `tests/Feature/ImportacionDeLaBaseDelGremioTest.php`

**Interfaces:**
- Consumes: `ImportadorDeAsociados::importar(string, ?string, ?string, ?string): ResultadoDeCargaDeAsociados` (con `fichasTocadas()`, Tarea 2) y `AltaDeCuentasDeAfiliados::crear()` (Tarea 3).
- Produces: `ImportacionDeLaBaseDelGremio::importar(string $ruta, string $categoriaPorDefecto, ?string $contrasenaGenerica): array{carga: ResultadoDeCargaDeAsociados, cuentas: ResultadoDeAltaDeCuentas|null}`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit ImportacionDeLaBaseDelGremioTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\User;
use App\Services\AltaDeCuentasDeAfiliados;
use App\Services\ImportacionDeLaBaseDelGremio;
use App\Services\ResultadoDeAltaDeCuentas;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Tests\TestCase;

/**
 * Fichas y cuentas de la base del gremio, como un solo bloque: si las cuentas
 * revientan, no queda ni una ficha nueva.
 */
class ImportacionDeLaBaseDelGremioTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        $this->archivo = tempnam(sys_get_temp_dir(), 'asobares').'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    /** @param  list<list<string>>  $filas */
    private function archivoComoElDelGremio(array $filas): string
    {
        $escritor = new Writer;
        $escritor->openToFile($this->archivo);

        $escritor->addRow(Row::fromValues(['BASE DE DATOS QUINDIO']));

        foreach ([1, 2, 3, 4] as $vacia) {
            $escritor->addRow(Row::fromValues(['']));
        }

        $escritor->addRow(Row::fromValues([
            'Nombre del Establecimiento', 'Nombre', 'Descripción del establecimiento', 'NIT',
            'Dirección', 'Municipio', 'Telefono', 'Correo', 'Horario de Atención',
            'Genero Musical', 'Servicios ofrecidos', 'Perfil Instagram', '', 'Menciones adicionales',
        ]));

        foreach ($filas as $fila) {
            $escritor->addRow(Row::fromValues($fila));
        }

        $escritor->close();

        return $this->archivo;
    }

    /** @return list<string> */
    private function fila(string $nombre, string $correo, string $municipio = 'Armenia'): array
    {
        return [$nombre, 'Duena del Local', 'Descripción', '900123456', 'Calle 1', $municipio, '3001234567', $correo, '', '', '', '', '', ''];
    }

    public function test_importa_las_fichas_y_crea_las_cuentas_pedidas(): void
    {
        $ruta = $this->archivoComoElDelGremio([
            $this->fila('Bar Uno', 'uno@bar.test'),
            $this->fila('Bar Dos', 'dos@bar.test'),
        ]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertSame(2, $resultado['carga']->creados());
        $this->assertSame(2, $resultado['cuentas']?->creadas());
        $this->assertSame(2, User::role(User::ROL_ASOCIADO)->where('contrasena_provisional', true)->count());
    }

    public function test_sin_contrasena_no_crea_cuentas(): void
    {
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', null);

        $this->assertNull($resultado['cuentas']);
        $this->assertSame(1, Asociado::query()->count());
        $this->assertSame(0, User::query()->count());
    }

    public function test_solo_crea_cuentas_para_las_fichas_del_archivo(): void
    {
        Asociado::factory()->create(['correo_interno' => 'ajena@bar.test']);
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertSame(0, User::query()->where('email', 'ajena@bar.test')->count());
        $this->assertSame(1, User::query()->where('email', 'uno@bar.test')->count());
    }

    public function test_una_fila_rechazada_no_recibe_cuenta(): void
    {
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar de Afuera', 'afuera@bar.test', 'Pereira')]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertTrue($resultado['carga']->tieneErrores());
        $this->assertSame(0, User::query()->count());
    }

    public function test_si_las_cuentas_revientan_no_queda_ninguna_ficha_nueva(): void
    {
        $this->app->instance(AltaDeCuentasDeAfiliados::class, new class extends AltaDeCuentasDeAfiliados
        {
            public function crear(Collection $fichas, string $contrasenaGenerica): ResultadoDeAltaDeCuentas
            {
                throw new RuntimeException('Falla simulada a mitad de las cuentas');
            }
        });

        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        try {
            app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);
            $this->fail('La falla de las cuentas tenía que subir.');
        } catch (RuntimeException) {
            // Esperado.
        }

        $this->assertSame(0, Asociado::query()->count());
        $this->assertSame(0, User::query()->count());
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ImportacionDeLaBaseDelGremioTest
```

Esperado: FAIL con `Class "App\Services\ImportacionDeLaBaseDelGremio" not found`.

- [ ] **Paso 3: El orquestador.** Crea `app/Services/ImportacionDeLaBaseDelGremio.php`:

```php
<?php

namespace App\Services;

use App\Models\Asociado;
use Illuminate\Support\Facades\DB;
use SensitiveParameter;

/**
 * La importación de la base del gremio desde el panel: fichas y, si la
 * dirección lo pide, cuentas de acceso a /mi-cuenta.
 *
 * Todo en una transacción. Si las cuentas revientan a mitad de camino no
 * pueden quedar fichas nuevas sin las cuentas que se pidieron, ni la mitad de
 * las cuentas creadas: el archivo corregido se vuelve a subir entero.
 */
class ImportacionDeLaBaseDelGremio
{
    public function __construct(
        private ImportadorDeAsociados $importador,
        private AltaDeCuentasDeAfiliados $altaDeCuentas,
    ) {}

    /**
     * @return array{carga: ResultadoDeCargaDeAsociados, cuentas: ResultadoDeAltaDeCuentas|null}
     */
    public function importar(string $ruta, string $categoriaPorDefecto, #[SensitiveParameter] ?string $contrasenaGenerica): array
    {
        return DB::transaction(function () use ($ruta, $categoriaPorDefecto, $contrasenaGenerica): array {
            $carga = $this->importador->importar($ruta, $categoriaPorDefecto);

            if ($contrasenaGenerica === null) {
                return ['carga' => $carga, 'cuentas' => null];
            }

            $fichas = Asociado::query()
                ->whereKey($carga->fichasTocadas())
                ->orderBy('nombre')
                ->get();

            return ['carga' => $carga, 'cuentas' => $this->altaDeCuentas->crear($fichas, $contrasenaGenerica)];
        });
    }
}
```

- [ ] **Paso 4: Verla pasar.** El mismo comando del paso 2. Esperado: 5 casos en verde.

- [ ] **Paso 5: Mutaciones.**
  1. Quita el `DB::transaction(` (llama al cierre directamente) → `test_si_las_cuentas_revientan…` rojo.
  2. Cambia `->whereKey($carga->fichasTocadas())` por nada (todas las fichas) → `test_solo_crea_cuentas_para_las_fichas_del_archivo` rojo.

- [ ] **Paso 6: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Services/ImportacionDeLaBaseDelGremio.php tests/Feature/ImportacionDeLaBaseDelGremioTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(afiliados): fichas y cuentas de la base del gremio entran como un solo bloque

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 5: La acción «Importar base del gremio» en el panel

**Files:**
- Modify: `app/Filament/Resources/Asociados/Pages/ListAsociados.php`
- Test: `tests/Feature/Panel/ImportarBaseDelGremioTest.php`

**Interfaces:**
- Consumes: `ImportacionDeLaBaseDelGremio::importar()` (Tarea 4) y `CrearUsuarioDelPanel::CLAVE_PUBLICADA`.
- Produces: la acción `importar` en `ListAsociados`, con los campos `archivo`, `categoria`, `crear_cuentas`, `contrasena_generica` y `contrasena_generica_confirmation`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit Panel/ImportarBaseDelGremioTest --no-interaction
```

Sobrescribe `tests/Feature/Panel/ImportarBaseDelGremioTest.php` con:

```php
<?php

namespace Tests\Feature\Panel;

use App\Console\Commands\CrearUsuarioDelPanel;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\User;
use App\Services\ImportacionDeLaBaseDelGremio;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * La acción del panel con la que la dirección carga la base del gremio en
 * producción. Las reglas de fondo están probadas en los servicios; aquí se
 * prueba la puerta: quién la ve, qué contraseña acepta, que el archivo no se
 * queda en el disco y que un fallo se dice.
 */
class ImportarBaseDelGremioTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    /** Ruta del estado del formulario de la acción montada, en el MessageBag de Livewire. */
    private const string CAMPO_CONTRASENA = 'mountedActions.0.data.contrasena_generica';

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        $this->archivo = tempnam(sys_get_temp_dir(), 'asobares').'.xlsx';

        // Las subidas de otras pruebas se quedan en el disco temporal de
        // Livewire: se vacía para que «no quedó nada» mida solo esta prueba.
        FileUploadConfiguration::storage()->deleteDirectory(FileUploadConfiguration::directory());
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    private function usuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /** @param  list<list<string>>  $filas */
    private function subida(array $filas): UploadedFile
    {
        $escritor = new Writer;
        $escritor->openToFile($this->archivo);
        $escritor->addRow(Row::fromValues(['BASE DE DATOS QUINDIO']));

        foreach ([1, 2, 3, 4] as $vacia) {
            $escritor->addRow(Row::fromValues(['']));
        }

        $escritor->addRow(Row::fromValues([
            'Nombre del Establecimiento', 'Nombre', 'Descripción del establecimiento', 'NIT',
            'Dirección', 'Municipio', 'Telefono', 'Correo', 'Horario de Atención',
            'Genero Musical', 'Servicios ofrecidos', 'Perfil Instagram', '', 'Menciones adicionales',
        ]));

        foreach ($filas as $fila) {
            $escritor->addRow(Row::fromValues($fila));
        }

        $escritor->close();

        return UploadedFile::fake()->createWithContent('base.xlsx', (string) file_get_contents($this->archivo));
    }

    /** @return list<string> */
    private function fila(string $nombre, string $correo): array
    {
        return [$nombre, 'Duena del Local', 'Descripción', '900123456', 'Calle 1', 'Armenia', '3001234567', $correo, '', '', '', '', '', ''];
    }

    public function test_la_direccion_ve_la_accion(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)->assertActionVisible('importar');
    }

    /** La secretaría crea asociados pero no usuarios: no puede repartir accesos. */
    public function test_la_secretaria_no_ve_la_accion(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUBADMIN));

        Livewire::test(ListAsociados::class)->assertActionHidden('importar');
    }

    public function test_importa_fichas_en_borrador_y_crea_cuentas_provisionales(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test'), $this->fila('Bar Dos', 'dos@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => self::GENERICA,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame(2, Asociado::query()->where('estado', EstadoPublicacion::Borrador)->count());
        $this->assertSame(2, User::role(User::ROL_ASOCIADO)->where('contrasena_provisional', true)->count());
    }

    public function test_sin_marcar_la_casilla_no_crea_cuentas(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(1, Asociado::query()->count());
        $this->assertSame(0, User::role(User::ROL_ASOCIADO)->count());
    }

    /** @return array<string, array{string, string}> */
    public static function contrasenasQueNoSirven(): array
    {
        return [
            'la del demo' => [CrearUsuarioDelPanel::CLAVE_PUBLICADA, 'Esa es la contraseña del demo, publicada en el repositorio. Elige otra.'],
            'sin símbolo' => ['CordilleraQuindio2026', 'La contraseña necesita al menos un símbolo.'],
            'sin número' => ['Cordillera-Quindio!', 'La contraseña necesita al menos un número.'],
            'sin mayúscula' => ['cordillera-quindio-2026!', 'La contraseña necesita al menos una mayúscula y una minúscula.'],
            'corta' => ['Corta-1!a', 'La contraseña necesita al menos 12 caracteres.'],
        ];
    }

    #[DataProvider('contrasenasQueNoSirven')]
    public function test_la_contrasena_generica_tiene_que_cumplir_la_politica(string $clave, string $mensaje): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => $clave,
                'contrasena_generica_confirmation' => $clave,
            ])
            ->assertHasFormErrors(['contrasena_generica']);

        // Si esta aserción no encuentra la clave, imprime
        // array_keys($componente->errors()->toArray()) para ver la ruta real.
        $this->assertContains($mensaje, $componente->errors()->get(self::CAMPO_CONTRASENA));
        $this->assertSame(0, Asociado::query()->count());
    }

    public function test_la_confirmacion_tiene_que_coincidir(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => 'Otra-Distinta-2026!',
            ])
            ->assertHasFormErrors(['contrasena_generica']);

        $this->assertContains('La confirmación no coincide.', $componente->errors()->get(self::CAMPO_CONTRASENA));
    }

    public function test_el_archivo_subido_no_se_queda_en_el_disco(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame([], FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::directory()));
    }

    public function test_si_la_importacion_revienta_lo_dice_y_borra_el_archivo(): void
    {
        $this->app->instance(ImportacionDeLaBaseDelGremio::class, new class extends ImportacionDeLaBaseDelGremio
        {
            public function __construct() {}

            public function importar(string $ruta, string $categoriaPorDefecto, ?string $contrasenaGenerica): array
            {
                throw new RuntimeException('Falla simulada');
            }
        });

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertNotified('La importación no se aplicó');

        $this->assertSame([], FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::directory()));
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=ImportarBaseDelGremioTest
```

Esperado: FAIL. La visibilidad reporta la acción `importar` inexistente, y las demás no la pueden llamar.

- [ ] **Paso 3: La acción.** Sobrescribe `app/Filament/Resources/Asociados/Pages/ListAsociados.php` con:

```php
<?php

namespace App\Filament\Resources\Asociados\Pages;

use App\Console\Commands\CrearUsuarioDelPanel;
use App\Filament\Resources\Asociados\AsociadoResource;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\User;
use App\Services\ImportacionDeLaBaseDelGremio;
use App\Services\ResultadoDeAltaDeCuentas;
use App\Services\ResultadoDeCargaDeAsociados;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ListAsociados extends ListRecords
{
    protected static string $resource = AsociadoResource::class;

    /** Líneas del resumen que caben en la notificación antes de contar el resto. */
    private const int LINEAS_DEL_RESUMEN = 40;

    /**
     * Marca el listado para el patrón visual operativo (`.asb-operativo`).
     * No cambia consultas, filtros, acciones ni permisos.
     *
     * @var array<string, string>
     */
    protected array $extraBodyAttributes = [
        'class' => 'asb-operativo',
    ];

    protected function getHeaderActions(): array
    {
        return [
            $this->accionImportarBase(),
            CreateAction::make(),
        ];
    }

    /**
     * Carga la base de establecimientos del gremio y, si se pide, crea las
     * cuentas de /mi-cuenta con una contraseña genérica que escribe quien
     * importa.
     *
     * Solo la ve quien puede crear asociados Y usuarios —hoy, la dirección—,
     * sin permiso nuevo: un permiso nuevo no llega a producción por desplegar
     * y la acción nacería en 403.
     *
     * ⚠️ Livewire guarda el archivo en una petición y la acción lo procesa en
     * otra. Funciona porque producción corre en UNA réplica con disco local;
     * si el entorno escala a más, hace falta el bucket (D-13).
     */
    private function accionImportarBase(): Action
    {
        return Action::make('importar')
            ->label('Importar base del gremio')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn (): bool => self::puedeImportar())
            ->modalHeading('Importar la base del gremio')
            ->modalDescription('Las fichas entran en borrador y nunca se publican solas. Una cuenta que ya existe no se toca.')
            ->modalSubmitActionLabel('Importar')
            ->schema([
                FileUpload::make('archivo')
                    ->label('Base de datos (.xlsx)')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->maxSize(4096)
                    ->storeFiles(false),
                Select::make('categoria')
                    ->label('Categoría para las filas que no traen una')
                    ->options(fn (): array => Categoria::query()->orderBy('nombre')->pluck('nombre', 'nombre')->all())
                    ->required(),
                Checkbox::make('crear_cuentas')
                    ->label('Crear cuentas de acceso a Mi Cuenta')
                    ->helperText('Todas con la contraseña genérica de abajo. Cada afiliado queda obligado a cambiarla.')
                    ->live(),
                TextInput::make('contrasena_generica')
                    ->label('Contraseña genérica')
                    ->password()
                    ->revealable()
                    ->visible(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->required(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->confirmed()
                    ->notIn([CrearUsuarioDelPanel::CLAVE_PUBLICADA])
                    ->rule(Password::min(12)->mixedCase()->numbers()->symbols())
                    // La regla Password falla con `password.symbols` y
                    // compañía, no con `symbols`: sin esas claves exactas, y
                    // sin `lang/`, el panel pintaría la clave cruda.
                    ->validationMessages([
                        'required' => 'Escribe la contraseña genérica.',
                        'confirmed' => 'La confirmación no coincide.',
                        'not_in' => 'Esa es la contraseña del demo, publicada en el repositorio. Elige otra.',
                        'min' => 'La contraseña necesita al menos 12 caracteres.',
                        'password.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                        'password.numbers' => 'La contraseña necesita al menos un número.',
                        'password.symbols' => 'La contraseña necesita al menos un símbolo.',
                    ]),
                TextInput::make('contrasena_generica_confirmation')
                    ->label('Confirma la contraseña genérica')
                    ->password()
                    ->visible(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->required(fn (Get $get): bool => (bool) $get('crear_cuentas'))
                    ->validationMessages(['required' => 'Confirma la contraseña genérica.'])
                    ->dehydrated(false),
            ])
            ->action(function (array $data): void {
                $archivo = $data['archivo'] ?? null;
                $archivo = is_array($archivo) ? reset($archivo) : $archivo;

                if (! $archivo instanceof TemporaryUploadedFile) {
                    Notification::make()->title('No se recibió ningún archivo')->danger()->send();

                    return;
                }

                try {
                    $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar(
                        (string) $archivo->getRealPath(),
                        (string) $data['categoria'],
                        ($data['crear_cuentas'] ?? false) ? (string) $data['contrasena_generica'] : null,
                    );
                } catch (Throwable $error) {
                    report($error);

                    Notification::make()
                        ->title('La importación no se aplicó')
                        ->body('No quedó ninguna ficha ni cuenta a medias. El detalle quedó en el registro.')
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                } finally {
                    // Datos personales de terceros: el archivo no se queda en
                    // el disco ni cuando la importación sale bien.
                    $archivo->delete();
                }

                $this->notificarResultado($resultado['carga'], $resultado['cuentas']);
            });
    }

    private static function puedeImportar(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && $usuario->can('create', Asociado::class)
            && $usuario->can('create', User::class);
    }

    private function notificarResultado(ResultadoDeCargaDeAsociados $carga, ?ResultadoDeAltaDeCuentas $cuentas): void
    {
        $titulo = 'Fichas: '.$carga->resumen();

        if ($cuentas !== null) {
            $titulo .= ' Cuentas: '.$cuentas->resumen();
        }

        $lineas = [...$carga->errores(), ...($cuentas?->sinCuenta() ?? [])];

        $notificacion = Notification::make()->title($titulo)->persistent();

        if ($lineas === []) {
            $notificacion->success()->send();

            return;
        }

        $visibles = array_slice($lineas, 0, self::LINEAS_DEL_RESUMEN);
        $restantes = count($lineas) - count($visibles);

        if ($restantes > 0) {
            $visibles[] = "…y {$restantes} más.";
        }

        $notificacion->warning()->body(implode("\n", $visibles))->send();
    }
}
```

- [ ] **Paso 4: Verla pasar.** El mismo comando del paso 2. Esperado: 11 casos en verde.
  - Si falla `acceptedFileTypes` con el archivo falso, imprime el MIME que ve Livewire con `dump($archivo->getMimeType())` dentro de la acción, compáralo con lo medido en la máquina (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`) y corrige la **prueba**, no la lista de tipos.
  - Si falla la ruta `mountedActions.0.data.contrasena_generica`, corrige la constante `CAMPO_CONTRASENA` con la ruta real.

- [ ] **Paso 5: Mutaciones.**
  1. Cambia `self::puedeImportar()` por `true` → `test_la_secretaria_no_ve_la_accion` rojo.
  2. Quita `$archivo->delete();` → `test_el_archivo_subido_no_se_queda_en_el_disco` y `test_si_la_importacion_revienta…` rojos.
  3. Quita `->notIn([...])` → el caso «la del demo» rojo.
  4. Cambia `'password.symbols'` por `'symbols'` → el caso «sin símbolo» rojo (sale la clave cruda).
  5. Cambia el tercer argumento por `(string) $data['contrasena_generica']` sin mirar la casilla → `test_sin_marcar_la_casilla_no_crea_cuentas` rojo (o error por clave inexistente).
  6. Quita el `catch` (deja `try`/`finally`) → `test_si_la_importacion_revienta…` rojo.

- [ ] **Paso 6: La vecindad.** Esta vista ya tenía pruebas de acciones.

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='AccionesDelPanelTest|ImportarBaseDelGremioTest'
```

Esperado: verde.

- [ ] **Paso 7: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Filament/Resources/Asociados/Pages/ListAsociados.php tests/Feature/Panel/ImportarBaseDelGremioTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(panel): la dirección importa la base del gremio y crea las cuentas desde Asociados

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 6: La pantalla `/mi-cuenta/seguridad`

**Files:**
- Create: `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php`
- Create: `resources/views/publico/mi-cuenta/seguridad.blade.php`
- Modify: `routes/web.php`
- Modify: `app/Providers/AppServiceProvider.php` (`LIMITES_POR_MINUTO`)
- Modify: `database/seeders/SettingSeeder.php` (tres claves)
- Test: `tests/Feature/SeguridadDeLaCuentaTest.php`
- Modify test: `tests/Feature/LimitesDePeticionesTest.php`

**Interfaces:**
- Consumes: `users.contrasena_provisional` (Tarea 1).
- Produces:
  - Ruta `mi-cuenta.seguridad` (GET) y `mi-cuenta.seguridad.actualizar` (PUT, `throttle:mi-cuenta-seguridad`).
  - Sesión `aviso` pintada en la vista.
  - Ajustes `mi_cuenta_seguridad_titulo`, `mi_cuenta_seguridad_texto` y `mi_cuenta_seguridad_provisional_texto`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit SeguridadDeLaCuentaTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * El titular cambia su contraseña desde /mi-cuenta/seguridad: es la única
 * puerta que apaga la marca de provisional.
 *
 * SOBRE `forgetGuards()`: como en `InvalidacionDeSesionTest`, el contenedor no
 * se reconstruye entre peticiones de una prueba y el guard conservaría el
 * usuario con el hash viejo. `forgetGuards()` reproduce lo que en producción
 * hace cada petición nueva; sin él, las pruebas de sesión darían falsos verdes.
 */
class SeguridadDeLaCuentaTest extends TestCase
{
    use RefreshDatabase;

    private const string ACTUAL = 'Provisional-Quindio-2026!';

    private const string NUEVA = 'Cordillera-Propia-2026#';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function afiliado(bool $provisional = true): User
    {
        $usuario = User::factory()->create([
            'email' => 'duena@merlin.test',
            'password' => self::ACTUAL,
            'asociado_id' => Asociado::factory()->publicado()->create()->id,
        ]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        return $usuario->fresh();
    }

    /** Por la puerta de verdad: el hash solo queda en la sesión si la petición pasa por el middleware. */
    private function entrar(User $usuario): void
    {
        $this->post(route('mi-cuenta.entrar.post'), [
            'email' => $usuario->email,
            'password' => self::ACTUAL,
        ])->assertRedirect(route('mi-cuenta.index'));

        $this->get(route('mi-cuenta.index'))->assertOk();
    }

    /** @return array<string, string> */
    private function datosValidos(): array
    {
        return [
            'current_password' => self::ACTUAL,
            'password' => self::NUEVA,
            'password_confirmation' => self::NUEVA,
        ];
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    public function test_un_invitado_va_a_la_puerta_de_mi_cuenta(): void
    {
        $this->get(route('mi-cuenta.seguridad'))->assertRedirect(route('mi-cuenta.entrar'));
    }

    public function test_el_equipo_del_gremio_no_usa_esta_pantalla(): void
    {
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($direccion)->get(route('mi-cuenta.seguridad'))->assertForbidden();
    }

    /**
     * Los nombres no son de estilo: son los que Laravel no devuelve a la
     * sesión al fallar la validación (`$dontFlash`).
     */
    public function test_la_pantalla_pide_la_actual_la_nueva_y_su_confirmacion(): void
    {
        $html = $this->actingAs($this->afiliado())->get(route('mi-cuenta.seguridad'))->assertOk()->getContent();
        $xpath = $this->xpathDe($html);

        $formulario = '//form[@method="POST"][@action="'.route('mi-cuenta.seguridad.actualizar').'"]';

        $this->assertSame(1, $xpath->query($formulario)->length);
        $this->assertSame(1, $xpath->query($formulario.'//input[@name="_method"][@value="PUT"]')->length);

        foreach (['current_password', 'password', 'password_confirmation'] as $campo) {
            $this->assertSame(1, $xpath->query($formulario.'//input[@type="password"][@name="'.$campo.'"]')->length, "Falta el campo {$campo}.");
        }
    }

    public function test_con_la_marca_explica_por_que_hay_que_cambiarla(): void
    {
        $this->actingAs($this->afiliado(provisional: true))
            ->get(route('mi-cuenta.seguridad'))
            ->assertSeeText('Entraste con la contraseña provisional');
    }

    public function test_sin_la_marca_no_habla_de_contrasena_provisional(): void
    {
        $this->actingAs($this->afiliado(provisional: false))
            ->get(route('mi-cuenta.seguridad'))
            ->assertDontSeeText('Entraste con la contraseña provisional');
    }

    /** @return array<string, array{array<string, string>, string, string}> */
    public static function cambiosQueNoSirven(): array
    {
        return [
            'actual equivocada' => [['current_password' => 'Otra-Clave-2026!', 'password' => self::NUEVA, 'password_confirmation' => self::NUEVA], 'current_password', 'Esa no es tu contraseña actual.'],
            'confirmación distinta' => [['current_password' => self::ACTUAL, 'password' => self::NUEVA, 'password_confirmation' => 'Distinta-2026#xy'], 'password', 'La confirmación no coincide con la contraseña nueva.'],
            'sin símbolo' => [['current_password' => self::ACTUAL, 'password' => 'CordilleraPropia2026', 'password_confirmation' => 'CordilleraPropia2026'], 'password', 'La contraseña nueva necesita al menos un símbolo.'],
            'sin mayúscula' => [['current_password' => self::ACTUAL, 'password' => 'cordillera-propia-2026#', 'password_confirmation' => 'cordillera-propia-2026#'], 'password', 'La contraseña nueva necesita al menos una mayúscula y una minúscula.'],
            'sin número' => [['current_password' => self::ACTUAL, 'password' => 'Cordillera-Propia-Quindio#', 'password_confirmation' => 'Cordillera-Propia-Quindio#'], 'password', 'La contraseña nueva necesita al menos un número.'],
            'corta' => [['current_password' => self::ACTUAL, 'password' => 'Corta-1#a', 'password_confirmation' => 'Corta-1#a'], 'password', 'La contraseña nueva necesita al menos 12 caracteres.'],
            'igual a la actual' => [['current_password' => self::ACTUAL, 'password' => self::ACTUAL, 'password_confirmation' => self::ACTUAL], 'password', 'La contraseña nueva tiene que ser distinta de la actual.'],
        ];
    }

    /** @param  array<string, string>  $datos */
    #[DataProvider('cambiosQueNoSirven')]
    public function test_un_cambio_que_no_sirve_se_explica_en_espanol_y_no_cambia_nada(array $datos, string $campo, string $mensaje): void
    {
        $usuario = $this->afiliado();
        $hashAntes = $usuario->password;

        $this->actingAs($usuario)
            ->from(route('mi-cuenta.seguridad'))
            ->put(route('mi-cuenta.seguridad.actualizar'), $datos)
            ->assertRedirect(route('mi-cuenta.seguridad'))
            ->assertSessionHasErrors([$campo => $mensaje]);

        $usuario->refresh();

        $this->assertSame($hashAntes, $usuario->password);
        $this->assertTrue($usuario->contrasena_provisional);
    }

    public function test_un_error_no_devuelve_ninguna_contrasena_a_la_sesion(): void
    {
        $this->actingAs($this->afiliado())
            ->from(route('mi-cuenta.seguridad'))
            ->put(route('mi-cuenta.seguridad.actualizar'), [
                'current_password' => 'Otra-Clave-2026!',
                'password' => self::NUEVA,
                'password_confirmation' => self::NUEVA,
            ]);

        $viajaron = array_intersect_key(
            session()->getOldInput(),
            array_flip(['current_password', 'password', 'password_confirmation'])
        );

        $this->assertSame([], $viajaron);
    }

    public function test_cambiarla_guarda_la_nueva_y_apaga_la_marca(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos())
            ->assertRedirect(route('mi-cuenta.index'))
            ->assertSessionHas('exito');

        $usuario->refresh();

        $this->assertTrue(Hash::check(self::NUEVA, $usuario->password));
        $this->assertFalse($usuario->contrasena_provisional);
    }

    public function test_la_bitacora_anota_el_cambio_sin_la_contrasena(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());

        $registro = Activity::query()
            ->where('log_name', 'sesion')
            ->where('description', 'cambió su contraseña')
            ->first();

        $this->assertNotNull($registro);
        $this->assertSame($usuario->id, (int) $registro->causer_id);
        $this->assertStringNotContainsString(self::NUEVA, (string) json_encode($registro->properties));
        $this->assertStringNotContainsString(self::ACTUAL, (string) json_encode($registro->properties));
    }

    /** La contraprueba de la siguiente: quien la cambió sigue dentro. */
    public function test_quien_la_cambio_sigue_dentro(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());
        $this->app['auth']->forgetGuards();

        $this->get(route('mi-cuenta.index'))->assertOk();
        $this->assertAuthenticatedAs($usuario->fresh());
    }

    /**
     * Otra sesión abierta con la contraseña vieja es una sesión que guarda el
     * hash viejo: se le devuelve ese hash y tiene que caer. Es el caso de
     * quien entró con la genérica antes que el dueño.
     */
    public function test_una_sesion_abierta_con_la_contrasena_vieja_se_cierra(): void
    {
        $usuario = $this->afiliado();
        $this->entrar($usuario);
        $hashViejo = session('password_hash_web');
        $this->assertNotNull($hashViejo);

        $this->put(route('mi-cuenta.seguridad.actualizar'), $this->datosValidos());
        $this->app['auth']->forgetGuards();

        $this->withSession(['password_hash_web' => $hashViejo])
            ->get(route('mi-cuenta.index'))
            ->assertRedirect(route('mi-cuenta.entrar'));

        $this->assertGuest();
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=SeguridadDeLaCuentaTest
```

Esperado: FAIL con `Route [mi-cuenta.seguridad] not defined`.

- [ ] **Paso 3: El controlador.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:controller Publico/SeguridadDeLaCuentaController --no-interaction
```

Sobrescribe `app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php` con (sin `extends Controller`, igual que sus vecinos de `Publico`):

```php
<?php

namespace App\Http\Controllers\Publico;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * El titular de una cuenta de afiliado cambia su contraseña desde /mi-cuenta.
 *
 * Es la única puerta que apaga `contrasena_provisional`: la genérica de la
 * importación y la que escribe la oficina en el panel las conoce alguien más.
 *
 * Tres cosas que no son de estilo:
 *
 * - **Los campos se llaman `current_password`, `password` y
 *   `password_confirmation`.** Son los que Laravel no devuelve a la sesión al
 *   fallar la validación; con otro nombre la contraseña viajaría a la tabla
 *   de sesiones y el componente `campo` la pintaría en el HTML.
 * - **Cada regla lleva su mensaje escrito.** No hay `lang/`, y la regla
 *   Password falla con `password.symbols` y compañía: sin la clave exacta se
 *   imprime la clave cruda.
 * - **Las demás sesiones se cierran solas.** `AuthenticateSession` (grupo
 *   `web`) guarda en cada sesión el hash con el que se entró y lo compara en
 *   cada petición: al cambiarlo, las sesiones abiertas con la contraseña vieja
 *   caen en su siguiente petición, y la de quien la cambió guarda el hash
 *   nuevo al terminar esta. Quien entró con la genérica antes que el dueño no
 *   sobrevive al cambio.
 */
class SeguridadDeLaCuentaController
{
    public function editar(Request $request): View
    {
        return view('publico.mi-cuenta.seguridad', [
            'usuario' => $request->user(),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.required' => 'Escribe tu contraseña actual.',
            'current_password.current_password' => 'Esa no es tu contraseña actual.',
            'password.required' => 'Escribe la contraseña nueva.',
            'password.confirmed' => 'La confirmación no coincide con la contraseña nueva.',
            'password.different' => 'La contraseña nueva tiene que ser distinta de la actual.',
            'password.min' => 'La contraseña nueva necesita al menos 12 caracteres.',
            'password.password.mixed' => 'La contraseña nueva necesita al menos una mayúscula y una minúscula.',
            'password.password.numbers' => 'La contraseña nueva necesita al menos un número.',
            'password.password.symbols' => 'La contraseña nueva necesita al menos un símbolo.',
        ]);

        /** @var User $usuario */
        $usuario = $request->user();

        // Sueltos y no en `update()`: `#[Fillable]` descartaría la marca en
        // silencio. El cast `hashed` calcula el hash de la contraseña.
        $usuario->password = $datos['password'];
        $usuario->remember_token = Str::random(60);
        $usuario->contrasena_provisional = false;
        $usuario->save();

        $request->session()->regenerate();

        activity('sesion')
            ->causedBy($usuario)
            ->performedOn($usuario)
            ->event('updated')
            ->log('cambió su contraseña');

        return redirect()
            ->route('mi-cuenta.index')
            ->with('exito', 'Listo: tu contraseña quedó cambiada. Ya puedes entrar a todas las secciones de Mi Cuenta.');
    }
}
```

- [ ] **Paso 4: La vista.** Crea `resources/views/publico/mi-cuenta/seguridad.blade.php`:

```blade
<x-layouts.publico titulo="Seguridad de la cuenta — ASOBARES Quindío"
                   descripcion="Cambia la contraseña de tu acceso a Mi Cuenta.">

    <div class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">{{ ajuste('mi_cuenta_seguridad_titulo', 'Seguridad de la cuenta') }}</h1>
            <p class="mt-1.5 text-sm text-tenue">
                @if ($usuario->contrasena_provisional)
                    {{ ajuste('mi_cuenta_seguridad_provisional_texto', 'Entraste con la contraseña provisional que el gremio les entregó a los afiliados. Cámbiala por una que solo conozcas tú: hasta entonces, las secciones con datos de otras personas siguen cerradas.') }}
                @else
                    {{ ajuste('mi_cuenta_seguridad_texto', 'Cambia tu contraseña cuando quieras. Al guardarla se cierran las sesiones que tengas abiertas en otros equipos.') }}
                @endif
            </p>
        </header>

        @if (session('aviso'))
            <x-publico.alerta tipo="aviso" class="mt-8">{{ session('aviso') }}</x-publico.alerta>
        @endif

        {{-- Los nombres de los campos son los que Laravel no devuelve a la
             sesión tras un error: ver SeguridadDeLaCuentaController. --}}
        <form method="POST" action="{{ route('mi-cuenta.seguridad.actualizar') }}" class="tarjeta mt-8 space-y-5 p-7">
            @csrf
            @method('PUT')

            <x-publico.campo nombre="current_password" etiqueta="Contraseña actual" tipo="password" requerido />
            <x-publico.campo nombre="password" etiqueta="Contraseña nueva" tipo="password" requerido
                             ayuda="Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos." />
            <x-publico.campo nombre="password_confirmation" etiqueta="Confirma la contraseña nueva" tipo="password" requerido />

            <x-publico.boton class="w-full">
                Guardar contraseña
            </x-publico.boton>
        </form>
    </div>
</x-layouts.publico>
```

- [ ] **Paso 5: Las rutas.** En `routes/web.php`, añade la importación en orden alfabético, después de `use App\Http\Controllers\Publico\ProveedorController;`:

```php
use App\Http\Controllers\Publico\SeguridadDeLaCuentaController;
```

Y, dentro del grupo `Route::middleware(['auth', 'rol.asociado'])`, justo después de:

```php
    Route::delete('/mi-cuenta/fotos/{media}', [MisFotosController::class, 'destroy'])
        ->middleware('throttle:mi-cuenta-fotos-borrar')
        ->name('mi-cuenta.fotos.destroy');
```

añade:

```php

    // Seguridad de la cuenta: el titular cambia su contraseña. Es la única
    // puerta que apaga la marca de provisional, así que queda fuera de las
    // secciones que esa marca cierra.
    Route::get('/mi-cuenta/seguridad', [SeguridadDeLaCuentaController::class, 'editar'])->name('mi-cuenta.seguridad');
    Route::put('/mi-cuenta/seguridad', [SeguridadDeLaCuentaController::class, 'actualizar'])
        ->middleware('throttle:mi-cuenta-seguridad')
        ->name('mi-cuenta.seguridad.actualizar');
```

- [ ] **Paso 6: El limitador.** En `app/Providers/AppServiceProvider.php`, dentro de `LIMITES_POR_MINUTO`, después de `'mi-cuenta-contrasena' => 5,` añade:

```php
        'mi-cuenta-seguridad' => 5,
```

En `tests/Feature/LimitesDePeticionesTest.php`, dentro de `rutasLimitadas()`, después de la entrada `'definir la contraseña' => …` añade:

```php
            'cambiar la contraseña con sesión' => ['PUT', 'mi-cuenta.seguridad.actualizar', [], 5, true],
```

- [ ] **Paso 7: Los ajustes.** En `database/seeders/SettingSeeder.php`, justo después de la línea:

```php
            $this->largo('mi_cuenta_pago_ayuda', 'Si no coincide con tus registros, escríbenos a', 'mi_cuenta', 'Ayuda bajo estado de cuenta'),
```

añade:

```php
            $this->texto('mi_cuenta_seguridad_titulo', 'Seguridad de la cuenta', 'mi_cuenta', 'Seguridad: título'),
            $this->largo('mi_cuenta_seguridad_texto', 'Cambia tu contraseña cuando quieras. Al guardarla se cierran las sesiones que tengas abiertas en otros equipos.', 'mi_cuenta', 'Seguridad: texto'),
            $this->largo('mi_cuenta_seguridad_provisional_texto', 'Entraste con la contraseña provisional que el gremio les entregó a los afiliados. Cámbiala por una que solo conozcas tú: hasta entonces, las secciones con datos de otras personas siguen cerradas.', 'mi_cuenta', 'Seguridad: texto con contraseña provisional'),
```

- [ ] **Paso 8: Verla pasar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='SeguridadDeLaCuentaTest|LimitesDePeticionesTest|AjustesQueSirvenParaAlgoTest|InvalidacionDeSesionTest'
```

Esperado: verde. Si algún caso de `cambiosQueNoSirven` muestra `validation.password.*` en lugar del mensaje, la clave del mensaje está mal. Corrige la clave en el controlador: `password.password.symbols` es la forma específica del campo y `password.symbols` la general, y las dos funcionan.

- [ ] **Paso 9: Mutaciones.**
  1. Cambia `'password.password.symbols'` por `'password.symbols.x'` → el caso «sin símbolo» rojo.
  2. Renombra en la vista `nombre="password"` a `nombre="contrasena"` → `test_la_pantalla_pide…` rojo.
  3. Quita `$usuario->contrasena_provisional = false;` → `test_cambiarla_guarda_la_nueva_y_apaga_la_marca` rojo.
  4. Quita `$usuario->password = $datos['password'];` → `test_cambiarla…` y `test_una_sesion_abierta_con_la_contrasena_vieja_se_cierra` rojos.
  5. Quita el bloque `activity('sesion')…` → `test_la_bitacora…` rojo.
  6. Quita `'different:current_password'` → el caso «igual a la actual» rojo.
  7. Quita `->middleware('throttle:mi-cuenta-seguridad')` → el caso nuevo de `LimitesDePeticionesTest` rojo.

- [ ] **Paso 10: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Http/Controllers/Publico/SeguridadDeLaCuentaController.php resources/views/publico/mi-cuenta/seguridad.blade.php routes/web.php app/Providers/AppServiceProvider.php database/seeders/SettingSeeder.php tests/Feature/SeguridadDeLaCuentaTest.php tests/Feature/LimitesDePeticionesTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(mi-cuenta): el afiliado cambia su contraseña y las sesiones viejas se cierran

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 7: Las secciones con datos de terceros se cierran

**Files:**
- Create: `app/Http/Middleware/ExigirContrasenaPropia.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/SeccionesCerradasConContrasenaProvisionalTest.php`

**Interfaces:**
- Consumes: `users.contrasena_provisional` (Tarea 1) y la ruta `mi-cuenta.seguridad` (Tarea 6).
- Produces: el alias `contrasena.propia` y la sesión `aviso` en la redirección.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit SeccionesCerradasConContrasenaProvisionalTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Mientras la contraseña sea provisional, las secciones con datos de otras
 * personas mandan a /mi-cuenta/seguridad. Una prueba por ruta: sacar una sola
 * del grupo cerrado tiene que poner roja una prueba.
 */
class SeccionesCerradasConContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    /** @return array{0: User, 1: Vacante, 2: Postulacion} */
    private function escenario(bool $provisional): array
    {
        $asociado = Asociado::factory()->publicado()->create();
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        $vacante = Vacante::factory()->for($asociado)->publicado()->create();
        $postulacion = Postulacion::factory()->for($vacante)->create();

        return [$usuario->fresh(), $vacante, $postulacion];
    }

    /**
     * Método, nombre de ruta y qué parámetro lleva. Los parámetros apuntan a
     * registros reales del afiliado: así la prueba no depende de si el
     * middleware corre antes o después de resolver la ruta.
     *
     * @return array<string, array{string, string, string}>
     */
    public static function seccionesCerradas(): array
    {
        return [
            'proveedores' => ['GET', 'mi-cuenta.proveedores.index', ''],
            'artistas' => ['GET', 'mi-cuenta.artistas.index', ''],
            'banco de talento' => ['GET', 'mi-cuenta.aspirantes.index', ''],
            'mis vacantes' => ['GET', 'mi-cuenta.vacantes.index', ''],
            'crear vacante' => ['GET', 'mi-cuenta.vacantes.crear', ''],
            'guardar vacante' => ['POST', 'mi-cuenta.vacantes.store', ''],
            'editar vacante' => ['GET', 'mi-cuenta.vacantes.editar', 'vacante'],
            'actualizar vacante' => ['PUT', 'mi-cuenta.vacantes.update', 'vacante'],
            'cerrar vacante' => ['POST', 'mi-cuenta.vacantes.cerrar', 'vacante'],
            'reabrir vacante' => ['POST', 'mi-cuenta.vacantes.reabrir', 'vacante'],
            'ver vacante y sus postulaciones' => ['GET', 'mi-cuenta.vacantes.show', 'vacante'],
            'gestionar una postulación' => ['PATCH', 'mi-cuenta.postulaciones.gestionar', 'postulacion'],
        ];
    }

    private function url(string $ruta, string $parametro, Vacante $vacante, Postulacion $postulacion): string
    {
        return match ($parametro) {
            'vacante' => route($ruta, $vacante),
            'postulacion' => route($ruta, $postulacion),
            default => route($ruta),
        };
    }

    #[DataProvider('seccionesCerradas')]
    public function test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad(string $metodo, string $ruta, string $parametro): void
    {
        [$usuario, $vacante, $postulacion] = $this->escenario(provisional: true);

        $this->actingAs($usuario)
            ->call($metodo, $this->url($ruta, $parametro, $vacante, $postulacion))
            ->assertRedirect(route('mi-cuenta.seguridad'))
            ->assertSessionHas('aviso');
    }

    #[DataProvider('seccionesCerradas')]
    public function test_sin_la_marca_la_seccion_no_manda_a_seguridad(string $metodo, string $ruta, string $parametro): void
    {
        [$usuario, $vacante, $postulacion] = $this->escenario(provisional: false);

        $respuesta = $this->actingAs($usuario)->call($metodo, $this->url($ruta, $parametro, $vacante, $postulacion));

        $this->assertNotSame(route('mi-cuenta.seguridad'), $respuesta->headers->get('Location'));
    }

    /** @return array<string, array{string}> */
    public static function seccionesAbiertas(): array
    {
        return [
            'estado de cuenta y convenios' => ['mi-cuenta.index'],
            'mis fotos' => ['mi-cuenta.fotos.index'],
            'seguridad' => ['mi-cuenta.seguridad'],
        ];
    }

    #[DataProvider('seccionesAbiertas')]
    public function test_con_la_contrasena_provisional_siguen_abiertas(string $ruta): void
    {
        [$usuario] = $this->escenario(provisional: true);

        $this->actingAs($usuario)->get(route($ruta))->assertOk();
    }

    public function test_pagar_sigue_abierto_con_la_contrasena_provisional(): void
    {
        [$usuario] = $this->escenario(provisional: true);

        $this->actingAs($usuario)->post(route('mi-cuenta.pagar'))->assertRedirect(route('mi-cuenta.index'));
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=SeccionesCerradasConContrasenaProvisionalTest
```

Esperado: los 12 casos de `test_con_la_contrasena_provisional_la_seccion_manda_a_seguridad` en FAIL. El resto, verde.

- [ ] **Paso 3: El middleware.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:middleware ExigirContrasenaPropia --no-interaction
```

Sobrescribe `app/Http/Middleware/ExigirContrasenaPropia.php` con:

```php
<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mientras la contraseña de un afiliado sea provisional —la genérica de la
 * importación o la que le puso la oficina—, las secciones con datos de otras
 * personas siguen cerradas: banco de talento, proveedores, artistas y la
 * bolsa de empleo con sus postulaciones.
 *
 * La genérica la conocen todos los afiliados que la recibieron: con ella y el
 * correo de un vecino, cualquiera entraría a su cuenta. Lo que queda abierto
 * es lo que no expone a terceros: su estado de cuenta, los convenios y sus
 * fotos. Se cierra en la ruta y no escondiendo botones, para que escribir la
 * URL a mano tampoco sirva.
 */
class ExigirContrasenaPropia
{
    public function handle(Request $request, Closure $siguiente): Response
    {
        $usuario = $request->user();

        if ($usuario instanceof User && $usuario->contrasena_provisional) {
            return redirect()
                ->route('mi-cuenta.seguridad')
                ->with('aviso', 'Esa sección se abre cuando cambies tu contraseña provisional por una tuya.');
        }

        return $siguiente($request);
    }
}
```

- [ ] **Paso 4: El alias.** En `bootstrap/app.php`, añade después de `use App\Http\Middleware\CabecerasDeSeguridad;`:

```php
use App\Http\Middleware\ExigirContrasenaPropia;
```

Y reemplaza:

```php
        $middleware->alias([
            'rol.asociado' => AsegurarRolAsociado::class,
        ]);
```

por:

```php
        $middleware->alias([
            'rol.asociado' => AsegurarRolAsociado::class,
            'contrasena.propia' => ExigirContrasenaPropia::class,
        ]);
```

- [ ] **Paso 5: El grupo cerrado.** En `routes/web.php` (léelo primero con Read), reemplaza el bloque que va desde `    // Beneficios detrás de la sesión: los datos de contacto de proveedores,` hasta `        ->name('mi-cuenta.postulaciones.gestionar');` inclusive por:

```php
    // Las secciones con datos de otras personas: contactos de proveedores y
    // artistas, el banco de talento y las postulaciones de cada vacante.
    // Mientras la contraseña sea provisional, `contrasena.propia` las manda a
    // /mi-cuenta/seguridad (ver ExigirContrasenaPropia). La bolsa entera va
    // dentro porque las postulaciones se ven en cada vacante y cerrar una
    // vacante no pasa por moderación.
    Route::middleware('contrasena.propia')->group(function (): void {
        // Beneficios detrás de la sesión: los datos de contacto de proveedores,
        // artistas y aspirantes son la contraprestación de la cuota. La cara
        // pública de /proveedores existe, pero sin un solo contacto.
        //
        // Los artistas, con una salvedad: su ficha pública NO se vacía. El
        // escaparate --nombre, foto, género, video-- es lo que el artista busca al
        // inscribirse; lo que se muda aquí es solo el contacto.
        Route::get('/mi-cuenta/proveedores', [MisProveedoresController::class, 'index'])->name('mi-cuenta.proveedores.index');
        Route::get('/mi-cuenta/artistas', [MisArtistasController::class, 'index'])->name('mi-cuenta.artistas.index');
        Route::get('/mi-cuenta/aspirantes', [MisAspirantesController::class, 'index'])->name('mi-cuenta.aspirantes.index');

        // Bolsa de empleo: el establecimiento publica y corrige lo suyo.
        Route::get('/mi-cuenta/vacantes', [MisVacantesController::class, 'index'])->name('mi-cuenta.vacantes.index');
        Route::get('/mi-cuenta/vacantes/crear', [MisVacantesController::class, 'crear'])->name('mi-cuenta.vacantes.crear');
        Route::post('/mi-cuenta/vacantes', [MisVacantesController::class, 'store'])
            ->middleware('throttle:mi-cuenta-vacantes-crear')
            ->name('mi-cuenta.vacantes.store');
        Route::get('/mi-cuenta/vacantes/{vacante}/editar', [MisVacantesController::class, 'editar'])->name('mi-cuenta.vacantes.editar');
        Route::put('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'update'])
            ->middleware('throttle:mi-cuenta-vacantes-editar')
            ->name('mi-cuenta.vacantes.update');
        Route::post('/mi-cuenta/vacantes/{vacante}/cerrar', [MisVacantesController::class, 'cerrar'])->name('mi-cuenta.vacantes.cerrar');
        Route::post('/mi-cuenta/vacantes/{vacante}/reabrir', [MisVacantesController::class, 'reabrir'])->name('mi-cuenta.vacantes.reabrir');
        Route::get('/mi-cuenta/vacantes/{vacante}', [MisVacantesController::class, 'show'])->name('mi-cuenta.vacantes.show');
        Route::patch('/mi-cuenta/postulaciones/{postulacion}', [MisVacantesController::class, 'gestionarPostulacion'])
            ->middleware('throttle:mi-cuenta-postulaciones')
            ->name('mi-cuenta.postulaciones.gestionar');
    });
```

Comprueba que la línea siguiente sigue siendo el `});` que cierra el grupo `['auth', 'rol.asociado']`.

- [ ] **Paso 6: Verla pasar, con la vecindad.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='SeccionesCerradasConContrasenaProvisionalTest|MisVacantesTest|LimitesDePeticionesTest|AccesoDeAsociadosTest'
```

Esperado: verde. Las pruebas vecinas crean usuarios sin la marca, así que no las toca.

- [ ] **Paso 7: Mutaciones.**
  1. Saca la ruta `mi-cuenta.aspirantes.index` del grupo (déjala justo encima de `Route::middleware('contrasena.propia')`) → el caso «banco de talento» rojo.
  2. Quita `'contrasena.propia' => ExigirContrasenaPropia::class,` → rojo toda la prueba (alias inexistente).
  3. Cambia la condición por `false` → los 12 casos cerrados rojos.

- [ ] **Paso 8: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Http/Middleware/ExigirContrasenaPropia.php bootstrap/app.php routes/web.php tests/Feature/SeccionesCerradasConContrasenaProvisionalTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(mi-cuenta): con la contraseña provisional se cierran las secciones con datos de terceros

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 8: El aviso tocable y el botón «Seguridad»

**Files:**
- Create: `resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php`
- Modify: `resources/views/publico/mi-cuenta/index.blade.php`
- Modify: `resources/views/publico/mi-cuenta/fotos/index.blade.php`
- Modify: `database/seeders/SettingSeeder.php` (dos claves)
- Test: `tests/Feature/AvisoDeContrasenaProvisionalTest.php`

**Interfaces:**
- Consumes: la ruta `mi-cuenta.seguridad` (Tarea 6) y la sesión `aviso` (Tarea 7).
- Produces: el componente `<x-publico.mi-cuenta.aviso-contrasena />`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit AvisoDeContrasenaProvisionalTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El aviso de contraseña provisional: todo el aviso lleva a la pantalla de
 * seguridad, en las páginas de Mi Cuenta que siguen abiertas con la marca.
 */
class AvisoDeContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function afiliado(bool $provisional): User
    {
        $usuario = User::factory()->create(['asociado_id' => Asociado::factory()->publicado()->create()->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        return $usuario->fresh();
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    /** El aviso es el enlace a seguridad que habla de la contraseña provisional. */
    private function consultaDelAviso(): string
    {
        return '//a[@href="'.route('mi-cuenta.seguridad').'"][contains(normalize-space(.), "contraseña provisional")]';
    }

    /** @return array<string, array{string}> */
    public static function paginasConAviso(): array
    {
        return [
            'mi cuenta' => ['mi-cuenta.index'],
            'mis fotos' => ['mi-cuenta.fotos.index'],
        ];
    }

    #[DataProvider('paginasConAviso')]
    public function test_con_la_marca_el_aviso_entero_lleva_a_seguridad(string $ruta): void
    {
        $html = $this->actingAs($this->afiliado(true))->get(route($ruta))->assertOk()->getContent();

        $avisos = $this->xpathDe($html)->query($this->consultaDelAviso());

        $this->assertSame(1, $avisos->length);
        $this->assertStringContainsString('min-h-11', (string) $avisos->item(0)->getAttribute('class'), 'El aviso necesita 44 px de objetivo táctil.');
    }

    #[DataProvider('paginasConAviso')]
    public function test_sin_la_marca_no_hay_aviso(string $ruta): void
    {
        $html = $this->actingAs($this->afiliado(false))->get(route($ruta))->assertOk()->getContent();

        $this->assertSame(0, $this->xpathDe($html)->query($this->consultaDelAviso())->length);
    }

    public function test_mi_cuenta_ofrece_la_seguridad_aunque_no_haya_marca(): void
    {
        $html = $this->actingAs($this->afiliado(false))->get(route('mi-cuenta.index'))->assertOk()->getContent();

        $this->assertSame(1, $this->xpathDe($html)->query('//nav//a[@href="'.route('mi-cuenta.seguridad').'"]')->length);
    }

    public function test_mi_cuenta_pinta_el_aviso_que_llega_en_la_sesion(): void
    {
        $this->actingAs($this->afiliado(false))
            ->withSession(['aviso' => 'Mensaje de aviso de prueba'])
            ->get(route('mi-cuenta.index'))
            ->assertSeeText('Mensaje de aviso de prueba');
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=AvisoDeContrasenaProvisionalTest
```

Esperado: FAIL en los casos con marca, en el botón de seguridad y en el aviso de sesión. «sin la marca no hay aviso» sale verde porque no hay nada que ver: es la contraprueba y se mira roja en el paso 6.

- [ ] **Paso 3: El componente.** Crea `resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php`:

```blade
{{--
    Aviso de contraseña provisional, arriba de las páginas de Mi Cuenta que
    siguen abiertas mientras la marca esté puesta.

    El aviso ENTERO es el enlace, y no un botón dentro de una alerta: en el
    teléfono se toca donde sea. `min-h-11` le da los 44 px de objetivo táctil.
    No lleva `role="status"`: está ahí desde que carga la página, no es un
    mensaje que llega después.
--}}
@if (auth()->user()?->contrasena_provisional)
    <a href="{{ route('mi-cuenta.seguridad') }}"
       {{ $attributes->merge(['class' => 'flex min-h-11 items-start gap-3 rounded-xl border border-aviso-linea bg-aviso-fondo px-4 py-3.5 text-sm text-aviso-suave']) }}>
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
        </svg>
        <span class="leading-relaxed">
            <span class="block font-semibold">{{ ajuste('mi_cuenta_aviso_provisional_titulo', 'Estás usando la contraseña provisional que te dio el gremio') }}</span>
            <span class="block">{{ ajuste('mi_cuenta_aviso_provisional_texto', 'Toca aquí para cambiarla por una tuya. Mientras tanto, el banco de talento, los proveedores, los artistas y la bolsa de empleo siguen cerrados.') }}</span>
        </span>
    </a>
@endif
```

- [ ] **Paso 4: Las vistas.** En `resources/views/publico/mi-cuenta/index.blade.php` reemplaza:

```blade
        </header>

        <nav class="mt-6 flex flex-wrap gap-3">
```

por:

```blade
        </header>

        <x-publico.mi-cuenta.aviso-contrasena class="mt-6" />

        <nav class="mt-6 flex flex-wrap gap-3">
```

Reemplaza:

```blade
            <x-publico.boton variante="contorno" :href="route('mi-cuenta.aspirantes.index')">
                Banco de talento
            </x-publico.boton>
        </nav>
```

por:

```blade
            <x-publico.boton variante="contorno" :href="route('mi-cuenta.aspirantes.index')">
                Banco de talento
            </x-publico.boton>
            <x-publico.boton variante="contorno" :href="route('mi-cuenta.seguridad')">
                Seguridad
            </x-publico.boton>
        </nav>
```

Y reemplaza:

```blade
        @if (session('error'))
            <x-publico.alerta tipo="error" class="mt-8">{{ session('error') }}</x-publico.alerta>
        @endif
```

por:

```blade
        @if (session('error'))
            <x-publico.alerta tipo="error" class="mt-8">{{ session('error') }}</x-publico.alerta>
        @endif

        @if (session('aviso'))
            <x-publico.alerta tipo="aviso" class="mt-8">{{ session('aviso') }}</x-publico.alerta>
        @endif
```

En `resources/views/publico/mi-cuenta/fotos/index.blade.php` reemplaza:

```blade
        </header>

        @if (session('exito'))
```

por:

```blade
        </header>

        <x-publico.mi-cuenta.aviso-contrasena class="mt-6" />

        @if (session('exito'))
```

- [ ] **Paso 5: Los ajustes.** En `database/seeders/SettingSeeder.php`, justo después de la línea de `mi_cuenta_seguridad_provisional_texto` (Tarea 6), añade:

```php
            $this->texto('mi_cuenta_aviso_provisional_titulo', 'Estás usando la contraseña provisional que te dio el gremio', 'mi_cuenta', 'Aviso de contraseña provisional: título'),
            $this->largo('mi_cuenta_aviso_provisional_texto', 'Toca aquí para cambiarla por una tuya. Mientras tanto, el banco de talento, los proveedores, los artistas y la bolsa de empleo siguen cerrados.', 'mi_cuenta', 'Aviso de contraseña provisional: texto'),
```

- [ ] **Paso 6: Verla pasar y mirar las guardias de vistas.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan view:clear && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='AvisoDeContrasenaProvisionalTest|AjustesQueSirvenParaAlgoTest|FocoVisibleTest|ObjetivoTactilTest|TemaClaroOscuroTest|FormulariosPublicosTest|MisFotosTest'
```

Esperado: verde.

- [ ] **Paso 7: Mutaciones.**
  1. Quita `<x-publico.mi-cuenta.aviso-contrasena class="mt-6" />` de `fotos/index` → el caso «mis fotos» del aviso rojo.
  2. Cambia `@if (auth()->user()?->contrasena_provisional)` por `@if (true)` → los dos casos de `test_sin_la_marca_no_hay_aviso` rojos (contraprueba vista roja).
  3. Quita `min-h-11` del componente → rojo por el objetivo táctil.
  4. Quita el botón «Seguridad» → `test_mi_cuenta_ofrece_la_seguridad…` rojo.
  5. Quita el bloque `@if (session('aviso'))` de `index` → `test_mi_cuenta_pinta_el_aviso…` rojo.

- [ ] **Paso 8: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/mi-cuenta/aviso-contrasena.blade.php resources/views/publico/mi-cuenta/index.blade.php resources/views/publico/mi-cuenta/fotos/index.blade.php database/seeders/SettingSeeder.php tests/Feature/AvisoDeContrasenaProvisionalTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(mi-cuenta): el aviso de contraseña provisional lleva a seguridad con un toque

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 9: Mi Cuenta sin cartera cargada

**Files:**
- Modify: `app/Http/Controllers/Publico/MiCuentaController.php`
- Modify: `resources/views/publico/mi-cuenta/index.blade.php`
- Modify: `database/seeders/SettingSeeder.php` (dos claves)
- Test: `tests/Feature/MiCuentaSinCarteraTest.php`
- Modify test: `tests/Feature/FormulariosPublicosTest.php` (`test_el_asociado_al_dia_ve_el_estado_sin_deuda`)

**Interfaces:**
- Produces: la vista recibe `cartera` como `?Cartera`, y ajustes `mi_cuenta_sin_cartera_titulo` y `mi_cuenta_sin_cartera_texto`.

- [ ] **Paso 1: La prueba.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit MiCuentaSinCarteraTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Que no haya fila de cartera significa que nadie la ha cargado, no que el
 * afiliado esté al día: en producción no hay ni una cartera, y decirle «Estás
 * al día» a quien debe seis meses es afirmar algo falso con el nombre del
 * gremio encima.
 */
class MiCuentaSinCarteraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function duenio(Asociado $asociado): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    public function test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenio($asociado))
            ->get(route('mi-cuenta.index'))
            ->assertOk()
            ->assertSeeText('Tu estado de cuenta todavía no está cargado')
            ->assertDontSeeText('Estás al día')
            ->assertDontSee('Pagar ahora');
    }

    public function test_con_cartera_al_dia_sigue_diciendo_que_esta_al_dia(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 0,
            'meses_mora' => 0,
            'actualizado_at' => now(),
        ]);

        $this->actingAs($this->duenio($asociado))
            ->get(route('mi-cuenta.index'))
            ->assertOk()
            ->assertSeeText('Estás al día')
            ->assertDontSeeText('Tu estado de cuenta todavía no está cargado');
    }

    public function test_pagar_sin_cartera_no_responde_que_esta_al_dia(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenio($asociado))
            ->post(route('mi-cuenta.pagar'))
            ->assertRedirect(route('mi-cuenta.index'))
            ->assertSessionHas('aviso')
            ->assertSessionMissing('exito');
    }
}
```

- [ ] **Paso 2: Verla fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=MiCuentaSinCarteraTest
```

Esperado: FAIL en `test_sin_cartera…` (ve «Estás al día») y en `test_pagar_sin_cartera…` (llega `exito`).

- [ ] **Paso 3: El controlador.** En `app/Http/Controllers/Publico/MiCuentaController.php`, dentro de `index()`, reemplaza:

```php
        $cartera = $asociado->cartera ?? new Cartera(['saldo_pendiente' => 0, 'meses_mora' => 0]);
```

por:

```php
        // Sin fila de cartera no hay estado de cuenta que mostrar: nadie la
        // ha cargado. No es lo mismo que estar al día, y la vista lo dice.
        $cartera = $asociado->cartera;
```

Dentro de `pagarMensualidad()`, reemplaza:

```php
        if ($cartera === null || $cartera->estaAlDia()) {
            return redirect()->route('mi-cuenta.index')->with('exito', 'Tu cuenta ya está al día.');
        }
```

por:

```php
        if ($cartera === null) {
            return redirect()->route('mi-cuenta.index')->with(
                'aviso',
                'Tu estado de cuenta todavía no está cargado, así que por ahora no hay nada que pagar desde aquí.'
            );
        }

        if ($cartera->estaAlDia()) {
            return redirect()->route('mi-cuenta.index')->with('exito', 'Tu cuenta ya está al día.');
        }
```

Si `use App\Models\Cartera;` queda sin uso, Pint lo retira en el paso 7.

- [ ] **Paso 4: La vista.** En `resources/views/publico/mi-cuenta/index.blade.php` reemplaza:

```blade
            @if ($cartera->estaAlDia())
```

por:

```blade
            @if ($cartera === null)
                <div class="tarjeta mt-5 p-8">
                    <p class="font-display text-xl font-bold">{{ ajuste('mi_cuenta_sin_cartera_titulo', 'Tu estado de cuenta todavía no está cargado') }}</p>
                    <p class="mt-1.5 text-sm text-tenue">
                        {{ ajuste('mi_cuenta_sin_cartera_texto', 'La oficina del capítulo aún no ha subido tu estado de cuenta a la plataforma. Si tienes dudas sobre tus pagos, escríbenos a') }}
                        <a href="mailto:{{ ajuste('contacto_correo') }}" class="enlace-accion text-acento">{{ ajuste('contacto_correo') }}</a>.
                    </p>
                </div>
            @elseif ($cartera->estaAlDia())
```

Y reemplaza:

```blade
            @if ($cartera->actualizado_at)
```

por:

```blade
            @if ($cartera?->actualizado_at)
```

- [ ] **Paso 5: Los ajustes.** En `database/seeders/SettingSeeder.php`, justo después de la línea de `mi_cuenta_aviso_provisional_texto` (Tarea 8), añade:

```php
            $this->texto('mi_cuenta_sin_cartera_titulo', 'Tu estado de cuenta todavía no está cargado', 'mi_cuenta', 'Estado de cuenta sin cargar: título'),
            $this->largo('mi_cuenta_sin_cartera_texto', 'La oficina del capítulo aún no ha subido tu estado de cuenta a la plataforma. Si tienes dudas sobre tus pagos, escríbenos a', 'mi_cuenta', 'Estado de cuenta sin cargar: texto'),
```

- [ ] **Paso 6: La prueba que dependía del comportamiento viejo.** En `tests/Feature/FormulariosPublicosTest.php`, dentro de `test_el_asociado_al_dia_ve_el_estado_sin_deuda`, reemplaza:

```php
        $asociado = Asociado::factory()->publicado()->create(['nombre' => 'Bar Al Día']);
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
```

por:

```php
        $asociado = Asociado::factory()->publicado()->create(['nombre' => 'Bar Al Día']);
        // Al día de verdad, con su fila de cartera: sin ella Mi Cuenta dice
        // que el estado de cuenta no está cargado (MiCuentaSinCarteraTest).
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 0,
            'meses_mora' => 0,
            'actualizado_at' => now(),
        ]);
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
```

Comprueba que el archivo ya importa `use App\Models\Cartera;` (lo usa más arriba). Si no, añádelo.

- [ ] **Paso 7: Verla pasar, con la vecindad.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && /c/Users/Predator/.config/php85/php.exe artisan view:clear && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='MiCuentaSinCarteraTest|FormulariosPublicosTest|FlujoDePagoTest|LimitesDePeticionesTest|AjustesQueSirvenParaAlgoTest'
```

Esperado: verde.

- [ ] **Paso 8: Mutaciones.**
  1. Devuelve el `?? new Cartera([...])` al controlador → `test_sin_cartera…` rojo.
  2. Quita el bloque `if ($cartera === null)` de `pagarMensualidad` → `test_pagar_sin_cartera…` rojo (revienta o llega `exito`).
  3. Cambia `@if ($cartera === null)` por `@if (false)` → `test_sin_cartera…` rojo (error sobre `null`).

- [ ] **Paso 9: Commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && GIT_OPTIONAL_LOCKS=0 git add app/Http/Controllers/Publico/MiCuentaController.php resources/views/publico/mi-cuenta/index.blade.php database/seeders/SettingSeeder.php tests/Feature/MiCuentaSinCarteraTest.php tests/Feature/FormulariosPublicosTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
fix(mi-cuenta): sin cartera cargada el portal deja de decir que el afiliado está al día

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 10: Toda contraseña puesta por otro nace provisional

**Files:**
- Modify: `app/Filament/Resources/Users/Pages/CreateUser.php`
- Modify: `app/Filament/Resources/Users/Pages/EditUser.php`
- Modify: `app/Filament/Resources/Users/Tables/UsersTable.php`
- Modify: `app/Filament/Resources/Users/Schemas/UserForm.php`
- Modify: `app/Console/Commands/CrearUsuarioDelPanel.php`
- Test: `tests/Feature/Panel/ContrasenaProvisionalDesdeElPanelTest.php`
- Modify test: `tests/Feature/CrearUsuarioDelPanelTest.php`

**Interfaces:**
- Consumes: `User::marcarContrasenaProvisionalSiEsAfiliado()` (Tarea 1).

- [ ] **Paso 1: La prueba del panel.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan make:test --phpunit Panel/ContrasenaProvisionalDesdeElPanelTest --no-interaction
```

Sobrescribe con:

```php
<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Sin SMTP no hay «olvidé mi contraseña»: si un afiliado la olvida, la
 * oficina se la cambia en Usuarios. Esa contraseña la conoce la oficina, así
 * que vuelve a ser provisional.
 */
class ContrasenaProvisionalDesdeElPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion);
    }

    private function idDelRol(string $rol): int
    {
        return Role::findByName($rol)->id;
    }

    public function test_crear_un_afiliado_desde_el_panel_lo_deja_provisional(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Duena del Local',
                'email' => 'duena@merlin.test',
                'password' => 'ClaveDeLaOficina-2026!',
                'roles' => [$this->idDelRol(User::ROL_ASOCIADO)],
                'asociado_id' => Asociado::factory()->create()->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(User::query()->where('email', 'duena@merlin.test')->firstOrFail()->contrasena_provisional);
    }

    public function test_crear_a_alguien_del_equipo_no_lo_deja_provisional(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Persona de la Oficina',
                'email' => 'oficina@gremio.test',
                'password' => 'ClaveDeLaOficina-2026!',
                'roles' => [$this->idDelRol(User::ROL_SUBADMIN)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertFalse(User::query()->where('email', 'oficina@gremio.test')->firstOrFail()->contrasena_provisional);
    }

    public function test_escribirle_una_contrasena_a_un_afiliado_lo_vuelve_provisional(): void
    {
        $afiliado = User::factory()->create(['asociado_id' => Asociado::factory()->create()->id]);
        $afiliado->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(EditUser::class, ['record' => $afiliado->getRouteKey()])
            ->fillForm(['password' => 'ClaveDeLaOficina-2026!'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($afiliado->fresh()->contrasena_provisional);
    }

    public function test_editar_un_afiliado_sin_escribir_contrasena_no_lo_marca(): void
    {
        $afiliado = User::factory()->create(['asociado_id' => Asociado::factory()->create()->id]);
        $afiliado->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(EditUser::class, ['record' => $afiliado->getRouteKey()])
            ->fillForm(['name' => 'Nombre Corregido', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($afiliado->fresh()->contrasena_provisional);
    }

    /** El rol se guarda antes del gancho que marca: vale el rol nuevo. */
    public function test_pasar_a_afiliado_y_escribir_contrasena_en_el_mismo_guardado_lo_marca(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        Livewire::test(EditUser::class, ['record' => $usuario->getRouteKey()])
            ->fillForm([
                'roles' => [$this->idDelRol(User::ROL_ASOCIADO)],
                'asociado_id' => Asociado::factory()->create()->id,
                'password' => 'ClaveDeLaOficina-2026!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }

    public function test_la_tabla_filtra_a_quien_le_falta_cambiarla(): void
    {
        $provisional = User::factory()->create();
        $provisional->syncRoles([User::ROL_ASOCIADO]);
        $provisional->contrasena_provisional = true;
        $provisional->save();

        $propia = User::factory()->create();
        $propia->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(ListUsers::class)
            ->filterTable('contrasena_provisional', true)
            ->assertCanSeeTableRecords([$provisional])
            ->assertCanNotSeeTableRecords([$propia]);
    }
}
```

- [ ] **Paso 2: Las pruebas del comando.** En `tests/Feature/CrearUsuarioDelPanelTest.php`, al final de la clase, añade:

```php
    public function test_una_cuenta_de_afiliado_nace_con_la_contrasena_provisional(): void
    {
        $this->artisan('asobares:crear-usuario', ['email' => 'socio@asobaresquindio.test', '--rol' => User::ROL_ASOCIADO])
            ->expectsQuestion('Contraseña para la cuenta', self::CLAVE_BUENA)
            ->assertSuccessful();

        $this->assertTrue(User::query()->where('email', 'socio@asobaresquindio.test')->firstOrFail()->contrasena_provisional);
    }

    public function test_una_cuenta_del_equipo_no_queda_provisional(): void
    {
        $this->artisan('asobares:crear-usuario', ['email' => 'direccion@asobaresquindio.test'])
            ->expectsQuestion('Contraseña para la cuenta', self::CLAVE_BUENA)
            ->assertSuccessful();

        $this->assertFalse(User::query()->where('email', 'direccion@asobaresquindio.test')->firstOrFail()->contrasena_provisional);
    }

    /** @return array<string, array{string, string}> */
    public static function reglasConSuMensaje(): array
    {
        return [
            'sin símbolos' => ['CordilleraQuindio2026', 'al menos un símbolo'],
            'sin mayúsculas' => ['cordillera-quindio-2026!', 'una mayúscula y una minúscula'],
            'sin números' => ['Cordillera-Quindio!', 'al menos un número'],
        ];
    }

    /**
     * La regla Password falla con `password.symbols` y compañía. Un mensaje
     * guardado como `clave.symbols` no se usa nunca y el comando imprime la
     * clave cruda, justo con la contraseña más probable de un apuro: una sin
     * símbolo.
     */
    #[DataProvider('reglasConSuMensaje')]
    public function test_cada_regla_incumplida_se_explica_en_espanol(string $clave, string $mensaje): void
    {
        $this->artisan('asobares:crear-usuario', ['email' => 'debil@asobaresquindio.test'])
            ->expectsQuestion('Contraseña para la cuenta', $clave)
            ->expectsOutputToContain($mensaje)
            ->doesntExpectOutputToContain('validation.')
            ->assertFailed();
    }
```

- [ ] **Paso 3: Verlas fallar.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='ContrasenaProvisionalDesdeElPanelTest|CrearUsuarioDelPanelTest'
```

Esperado: FAIL en los casos que marcan, en el filtro (no existe) y en `test_una_cuenta_de_afiliado_nace…`.
- **Sobre `test_cada_regla_incumplida_se_explica_en_espanol`:** se espera FAIL, con `validation.password.symbols` en la salida. Si sale VERDE, las claves del comando ya funcionaban: anótalo en el reporte y **salta el paso 7**.

- [ ] **Paso 4: Las páginas.** Sobrescribe `app/Filament/Resources/Users/Pages/CreateUser.php` con:

```php
<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * La contraseña de una cuenta nueva la escribe la oficina, no su titular:
     * si la cuenta es de un afiliado, nace provisional. Corre después de
     * guardar los roles.
     */
    protected function afterCreate(): void
    {
        /** @var User $usuario */
        $usuario = $this->getRecord();
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();
    }
}
```

Sobrescribe `app/Filament/Resources/Users/Pages/EditUser.php` con:

```php
<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Si en este guardado la oficina escribió una contraseña. Vive lo que la petición. */
    private bool $seEscribioUnaContrasena = false;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * El campo solo llega cuando se escribió algo (`dehydrated(filled)` en
     * `UserForm`): corregir el nombre no toca la contraseña ni la marca.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->seEscribioUnaContrasena = filled($data['password'] ?? null);

        return $data;
    }

    /** Una contraseña que escribe la oficina la conoce la oficina: la de un afiliado vuelve a ser provisional. */
    protected function afterSave(): void
    {
        if (! $this->seEscribioUnaContrasena) {
            return;
        }

        /** @var User $usuario */
        $usuario = $this->getRecord();
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();
    }
}
```

- [ ] **Paso 5: La tabla y la ayuda.** En `app/Filament/Resources/Users/Tables/UsersTable.php`:
  - Añade `use Filament\Tables\Columns\IconColumn;` y `use Filament\Tables\Filters\TernaryFilter;`.
  - Después de la columna `asociado.nombre`, añade:

```php
                IconColumn::make('contrasena_provisional')
                    ->label('Contraseña provisional')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
```

  - En `->filters([...])`, después del `SelectFilter::make('roles')…`, añade:

```php
                TernaryFilter::make('contrasena_provisional')
                    ->label('Contraseña provisional'),
```

En `app/Filament/Resources/Users/Schemas/UserForm.php`, reemplaza:

```php
                                ? 'Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos. Entrégala como temporal y pide cambiarla al primer ingreso.'
```

por:

```php
                                ? 'Mínimo 12 caracteres, con mayúsculas, minúsculas, números y símbolos. Si la cuenta es de un afiliado queda provisional: Mi Cuenta le pide cambiarla y le cierra las secciones con datos de terceros hasta que lo haga.'
```

- [ ] **Paso 6: El comando marca.** En `app/Console/Commands/CrearUsuarioDelPanel.php`, reemplaza:

```php
        $usuario->syncRoles([$rol]);
```

por:

```php
        $usuario->syncRoles([$rol]);

        // La contraseña la escribió quien corre el comando, no el titular: si
        // la cuenta es de un afiliado, nace provisional.
        $usuario->marcarContrasenaProvisionalSiEsAfiliado();
```

- [ ] **Paso 7: El comando explica cada regla** (solo si el paso 3 lo vio rojo). En el mismo archivo, reemplaza:

```php
                'clave.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                'clave.letters' => 'La contraseña necesita al menos una letra.',
                'clave.numbers' => 'La contraseña necesita al menos un número.',
                'clave.symbols' => 'La contraseña necesita al menos un símbolo.',
```

por:

```php
                // La regla Password falla con `password.mixed` y compañía: la
                // clave del mensaje es `clave.password.mixed`, no `clave.mixed`.
                'clave.password.mixed' => 'La contraseña necesita al menos una mayúscula y una minúscula.',
                'clave.password.letters' => 'La contraseña necesita al menos una letra.',
                'clave.password.numbers' => 'La contraseña necesita al menos un número.',
                'clave.password.symbols' => 'La contraseña necesita al menos un símbolo.',
```

- [ ] **Paso 8: Verlas pasar, con la vecindad.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter='ContrasenaProvisionalDesdeElPanelTest|CrearUsuarioDelPanelTest|AccionesDelPanelTest|InvalidacionDeSesionTest|PanelAdminTest'
```

Esperado: verde.

- [ ] **Paso 9: Mutaciones.**
  1. Vacía el cuerpo de `afterCreate()` → `test_crear_un_afiliado…` rojo.
  2. En `afterSave()`, quita el `if (! $this->seEscribioUnaContrasena)` → `test_editar_un_afiliado_sin_escribir…` rojo.
  3. Quita `IconColumn`/`TernaryFilter` → `test_la_tabla_filtra…` rojo.
  4. Quita la llamada del comando → `test_una_cuenta_de_afiliado_nace…` rojo.
  5. Devuelve `'clave.password.symbols'` a `'clave.symbols'` → el caso «sin símbolos» del comando rojo.

- [ ] **Paso 10: Pint y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && GIT_OPTIONAL_LOCKS=0 git add app/Filament/Resources/Users/Pages/CreateUser.php app/Filament/Resources/Users/Pages/EditUser.php app/Filament/Resources/Users/Tables/UsersTable.php app/Filament/Resources/Users/Schemas/UserForm.php app/Console/Commands/CrearUsuarioDelPanel.php tests/Feature/Panel/ContrasenaProvisionalDesdeElPanelTest.php tests/Feature/CrearUsuarioDelPanelTest.php && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
feat(panel): la contraseña que la oficina le pone a un afiliado vuelve a ser provisional

El comando de alta también dice en español qué regla de la contraseña se
incumplió: los mensajes estaban guardados con claves que la regla no usa.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

---

### Tarea 11: Ensayo local con el archivo real

**Files:** ninguno del repositorio. Una base SQLite desechable en el scratchpad.

- [ ] **Paso 1: Base vacía, igual que producción.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && E='C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/ensayo.sqlite' && rm -f "$E" && touch "$E" && DB_CONNECTION=sqlite DB_DATABASE="$E" /c/Users/Predator/.config/php85/php.exe artisan migrate --force --no-interaction && DB_CONNECTION=sqlite DB_DATABASE="$E" /c/Users/Predator/.config/php85/php.exe artisan db:seed --class=ContenidoOficialSeeder --force --no-interaction
```

Esperado: migraciones aplicadas, incluida `anade_contrasena_provisional_a_users`, y «Contenido oficial sembrado».

- [ ] **Paso 2: La importación, con una contraseña que nadie ve.** La genera `Str::password()` en memoria y no se imprime.

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && E='C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/ensayo.sqlite' && DB_CONNECTION=sqlite DB_DATABASE="$E" /c/Users/Predator/.config/php85/php.exe artisan tinker --execute '
$r = app(App\Services\ImportacionDeLaBaseDelGremio::class)->importar("D:/Sua_Files/Downloads/Base_de_datos_Cap__Quindio_actualizada FINAL.xlsx", "Bar", Illuminate\Support\Str::password(24));
echo "fichas creadas: ".$r["carga"]->creados().PHP_EOL;
echo "fichas actualizadas: ".$r["carga"]->actualizados().PHP_EOL;
echo "filas con error: ".count($r["carga"]->errores()).PHP_EOL;
echo "cuentas creadas: ".$r["cuentas"]->creadas().PHP_EOL;
echo "fichas sin cuenta: ".count($r["cuentas"]->sinCuenta()).PHP_EOL;
echo "fichas publicadas: ".App\Models\Asociado::query()->where("estado", App\Enums\EstadoPublicacion::Publicado)->count().PHP_EOL;
echo "cuentas provisionales: ".App\Models\User::query()->where("contrasena_provisional", true)->count().PHP_EOL;
'
```

Esperado, contra la simulación del spec §3.1:

| Medida | Valor |
|---|---|
| Fichas creadas | 61 |
| Fichas actualizadas | 1 (la segunda fila de [establecimiento]) |
| Filas con error | 4 |
| Cuentas creadas | 35 |
| Fichas sin cuenta | 26 (22 sin correo, 2 inválidos, 2 compartidos) |
| Fichas publicadas | 0 |
| Cuentas provisionales | 35 |

**Si alguna cifra no coincide, para:** o el código o la simulación está mal, y hay que saber cuál antes de seguir. **No imprimas** nombres, correos ni documentos para investigar; cuenta por motivo con `array_count_values` sobre el texto que va después de `»: `.

- [ ] **Paso 3: Borrar la base del ensayo.**

```bash
rm -f 'C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/ensayo.sqlite' && ls 'C:/Users/Predator/AppData/Local/Temp/claude/D--Sua-Files-IdeaProjects-Asobares3/bc09f505-4e1a-4dbc-a814-8505db5067e2/scratchpad/' | grep -c ensayo
```

Esperado: `0`.

---

### Tarea 12: Cierre — suite, expediente y la puerta de producción

> **Nota de estado, 17 sep 2026 (no tacha los pasos):** el expediente se actualizó en documentación. Pint y `git diff --check` aprobados. Focales y ensayo aislado confirmados (ledger). **Paso 1 (suite completa) no está cerrado:** el proceso PHP terminó prematuramente; no se afirma verde total. **Paso 6 no se ejecuta:** no hay fusión ni push. El acta se registra después del código.

**Files:**
- Modify: `material/estado.md`, `material/bitacora.md`, `material/encargo.md`

- [ ] **Paso 1: Suite completa.** Tarda unos diez minutos, así que se lanza en segundo plano.

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan view:clear && /c/Users/Predator/.config/php85/php.exe artisan test --compact
```

Esperado: `0 failed`. Anota casos, aserciones y duración **tal como salen**: son las cifras que van al expediente (regla 2).

- [ ] **Paso 2: Formato y front.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe vendor/bin/pint --dirty --format agent && npm run build && GIT_OPTIONAL_LOCKS=0 git status --short
```

Esperado: Pint sin cambios. En `status`, solo `.claude/launch.json` y los archivos de la socialización. Nada más suelto.

- [ ] **Paso 3: Barrido de sondas.** Memoria del proyecto: los agentes dejan sondas en el árbol.

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && GIT_OPTIONAL_LOCKS=0 git diff main --name-only | xargs grep -nE "dd\(|dump\(|ray\(|FUGA|var_dump" ; echo "(fin del barrido)"
```

Esperado: solo `(fin del barrido)`.

- [ ] **Paso 4: Expediente, solo con conteos.**
  - **`material/encargo.md`**, editado en su sitio con fecha y commit en la misma línea:
    - **§5, fila `users`:** añadir `contrasena_provisional` y su regla.
    - **§6:** párrafo «Importador de la base del gremio» con la acción del panel, sus reglas de cuentas y el límite de una réplica. En «Alta de usuarios», la marca provisional.
    - **§9:** las secciones cerradas con contraseña provisional y los campos `current_password`/`password` que no se devuelven a la sesión.
    - **§13:** fila del 16 sep con D1 a D6 del spec.
  - **`material/bitacora.md`:** entrada nueva con el siguiente número después de la última. Qué se hizo con sus commits, las cifras del ensayo (tabla de la Tarea 11), la suite medida, las mutaciones vistas en rojo y lo aprendido: las claves de mensaje de la regla Password y el `$dontFlash`.
  - **`material/estado.md`:** reescrito entero, con el hash nuevo primero y entre acentos graves en la fila `main` (lo lee `GuardiaDelEstadoTest`).
    - D-28 sale del registro y entra en el §13 del encargo.
    - Entran, como pendientes con dueño: las correcciones del archivo que tiene que hacer el gremio (spec §8), la creación de la cuenta de dirección nueva (Sua), la importación en producción (super admin), la verificación y el reparto de credenciales (gremio).
  - **Ni un correo, ni un nombre de afiliado, ni un documento** en los tres archivos.

- [ ] **Paso 5: Verificar el expediente y commit.**

```bash
cd /d/Sua_Files/IdeaProjects/Asobares3 && /c/Users/Predator/.config/php85/php.exe artisan test --compact --filter=GuardiaDelEstadoTest && GIT_OPTIONAL_LOCKS=0 git add material/estado.md material/bitacora.md material/encargo.md && GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
docs(expediente): el alta de afiliados reales queda contada y el estado remide

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>
EOF
```

- [ ] **Paso 6: La puerta.** **No fusiones ni empujes.** Reporta a Sua:
  - las cifras de la suite y del ensayo;
  - la lista de commits (`GIT_OPTIONAL_LOCKS=0 git log --oneline main..afiliados/alta-real`);
  - el recordatorio de que el push a `main` despliega solo y que la migración corre sola en el despliegue.

  Pide el visto bueno explícito. Con él: `git checkout main && git merge --ff-only afiliados/alta-real && git push origin main`. Comprueba el push con `GIT_OPTIONAL_LOCKS=0 git ls-remote origin main`, no con la salida del `push`.

  Después comprueba contra lo servido:

  ```bash
  curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' https://asobares-production-0jhdcz.laravel.cloud/mi-cuenta/seguridad
  ```

  Esperado: `302` hacia `/mi-cuenta/entrar`. Antes del despliegue la ruta no existía y daba `404`.

  Desde ahí siguen los pasos 4 a 7 del spec §7, que hacen personas.

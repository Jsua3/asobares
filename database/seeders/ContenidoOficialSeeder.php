<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Lo único que puede correr sobre la base real del gremio.
 *
 * `DatabaseSeeder` es la demostración y se niega a correr en producción, con
 * razón: publica establecimientos inventados en el directorio, inserta PQR
 * ficticias que consumen el consecutivo anual de verdad y mete pagos en
 * estado aprobado que el widget de recaudo suma como ingresos. Pero negarse
 * dejaba un hueco: no había forma de poner en producción los catálogos y el
 * contenido institucional, que sí son ciertos y sí hacen falta, salvo
 * encadenar ocho `db:seed --class=…` a mano y acordarse del orden.
 *
 * Esto es esa forma. Los ocho que entran, y por qué cada uno:
 *
 * - `MunicipioSeeder` y `CategoriaSeeder` — el esqueleto. Los referencian
 *   asociados, artistas, proveedores, requisitos y consultas; sin ellos no se
 *   puede dar de alta nada.
 * - `BeneficioSeeder`, `AliadoSeeder` e `IniciativaSeeder` — contenido
 *   institucional, todo con documento detrás (el catálogo «Beneficios
 *   afiliados» y el TED gremial).
 * - `SettingSeeder` — el contenido editable del sitio. Sin él la portada sale
 *   en blanco, que es literalmente lo que pasaba: cada `ajuste()` devolvía
 *   vacío y el título de la página era «—».
 * - `RequisitoAperturaSeeder` — la guía normativa de Armenia, del documento
 *   oficial de la Alcaldía.
 * - `RolYPermisoSeeder` — los roles del panel. Sin él, `asobares:crear-usuario`
 *   no tiene qué asignar.
 *
 * Los doce que **no** entran, y son los que harían daño: `AsociadoSeeder`,
 * `CarteraSeeder`, `ConsultaGuiaSeeder`, `EventoSeeder`, `VacanteSeeder`,
 * `ArtistaSeeder`, `ProveedorSeeder`, `NoticiaSeeder`, `MensajeSeeder`,
 * `TransaccionSeeder` y `UsuarioSeeder`.
 *
 * Es idempotente: los ocho van por `updateOrCreate` sobre una clave natural,
 * así que volver a correrlo no duplica. ⚠️ Pero **sí sobrescribe**: si la
 * oficina corrigió el texto de un beneficio desde el panel, una resiembra se
 * lo pisa sin avisar. El esquema no marca la procedencia de una fila, así que
 * no hay forma de distinguirlo. Correr esto sobre una base con trabajo del
 * gremio encima es una decisión, no un trámite.
 */
class ContenidoOficialSeeder extends Seeder
{
    /** @var list<class-string<Seeder>> */
    public const array SEMBRADORES = [
        MunicipioSeeder::class,
        CategoriaSeeder::class,
        BeneficioSeeder::class,
        AliadoSeeder::class,
        IniciativaSeeder::class,
        SettingSeeder::class,
        RequisitoAperturaSeeder::class,
        RolYPermisoSeeder::class,
    ];

    public function run(): void
    {
        $this->call(self::SEMBRADORES);

        $this->command?->newLine();
        $this->command?->info('Contenido oficial sembrado. Lo que NO entró, a propósito:');
        $this->command?->line('  asociados, cartera, consultas, eventos, vacantes, artistas, proveedores,');
        $this->command?->line('  noticias, PQR, transacciones y las cuentas de demostración.');
        $this->command?->newLine();
        $this->command?->line('  Para dar de alta a alguien en el panel: php artisan asobares:crear-usuario');
        $this->command?->newLine();
    }
}

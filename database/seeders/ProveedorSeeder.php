<?php

namespace Database\Seeders;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bolsa de proveedores. En el demo todos están vigentes; `visible_hasta`
 * deja modelada la monetización futura (se paga por estar en la base).
 */
class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = Municipio::pluck('id', 'slug');

        $proveedores = [
            [
                'nombre' => 'Hielo Cristal del Quindío',
                'categoria_proveedor' => CategoriaProveedor::Hielo,
                'descripcion' => 'Hielo en cubo, escarcha y barra. Reparto diario en Armenia y municipios cercanos, con entregas de refuerzo los viernes y sábados.',
                'whatsapp' => '3145560012', 'correo' => 'ventas@hielocristal.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Hielo Express Eje',
                'categoria_proveedor' => CategoriaProveedor::Hielo,
                'descripcion' => 'Hielo en cubo con entrega en menos de dos horas dentro del perímetro urbano. Servicio también domingos y festivos.',
                'whatsapp' => '3106690034', 'correo' => 'pedidos@hieloexpress.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Distribuidora Licores del Eje',
                'categoria_proveedor' => CategoriaProveedor::Licores,
                'descripcion' => 'Portafolio completo de licores nacionales e importados, con cupo de crédito para establecimientos formalizados.',
                'whatsapp' => '3122278845', 'correo' => 'comercial@licoreseje.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Cervecería Artesanal Montaña',
                'categoria_proveedor' => CategoriaProveedor::Licores,
                'descripcion' => 'Cerveza artesanal en barril y botella, producida en Calarcá. Prestan grifos y CO2 en comodato.',
                'whatsapp' => '3178867701', 'correo' => 'hola@cervezamontana.test', 'municipio' => 'calarca',
            ],
            [
                'nombre' => 'Andina Abastos',
                'categoria_proveedor' => CategoriaProveedor::Alimentos,
                'descripcion' => 'Carnes, lácteos y verdura para cocinas de bar. Pedido mínimo bajo y entrega en la madrugada.',
                'whatsapp' => '3160045529', 'correo' => 'pedidos@andinaabastos.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Panadería y Repostería La Cosecha',
                'categoria_proveedor' => CategoriaProveedor::Alimentos,
                'descripcion' => 'Pan de hamburguesa, focaccia y repostería para cafés y gastrobares. Producción bajo pedido.',
                'whatsapp' => '3134432288', 'correo' => 'ventas@lacosecha.test', 'municipio' => 'circasia',
            ],
            [
                'nombre' => 'Aseo Total Quindío',
                'categoria_proveedor' => CategoriaProveedor::Aseo,
                'descripcion' => 'Insumos de aseo y desinfección grado alimentario, más el certificado de control de plagas que pide la Secretaría de Salud.',
                'whatsapp' => '3197790016', 'correo' => 'contacto@aseototal.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Vigilancia Nocturna del Café',
                'categoria_proveedor' => CategoriaProveedor::Seguridad,
                'descripcion' => 'Personal de seguridad con curso vigente para control de acceso y requisa. Servicio por turnos de fin de semana.',
                'whatsapp' => '3183312240', 'correo' => 'operaciones@vigilancianocturna.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Refrigeración y Campanas JR',
                'categoria_proveedor' => CategoriaProveedor::Mantenimiento,
                'descripcion' => '¿Quién te arregla la campana de extracción un sábado? Ellos. Mantenimiento de neveras, cuartos fríos y extracción, con servicio de urgencia.',
                'whatsapp' => '3115578823', 'correo' => 'servicio@refrijr.test', 'municipio' => 'armenia',
            ],
            [
                'nombre' => 'Sonido e Iluminación Nocturna Pro',
                'categoria_proveedor' => CategoriaProveedor::Mantenimiento,
                'descripcion' => 'Alquiler, instalación y mantenimiento de equipos de sonido e iluminación para establecimientos y eventos.',
                'whatsapp' => '3151102277', 'correo' => 'alquiler@nocturnapro.test', 'municipio' => 'montenegro',
            ],
        ];

        foreach ($proveedores as $proveedor) {
            $slug = $proveedor['municipio'];
            unset($proveedor['municipio']);

            $proveedor['slug'] = Str::slug($proveedor['nombre']);
            $proveedor['municipio_id'] = $municipios[$slug];
            $proveedor['visible_hasta'] = now()->addMonths(random_int(4, 14))->toDateString();
            $proveedor['estado'] = EstadoPublicacion::Publicado;

            Proveedor::updateOrCreate(['slug' => $proveedor['slug']], $proveedor);
        }
    }
}

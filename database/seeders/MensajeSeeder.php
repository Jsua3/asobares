<?php

namespace Database\Seeders;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use App\Models\Mensaje;
use Illuminate\Database\Seeder;

class MensajeSeeder extends Seeder
{
    public function run(): void
    {
        $mensajes = [
            [
                'tipo' => TipoMensaje::Afiliacion,
                'nombre' => 'Sandra Milena Ríos',
                'correo' => 'sandra.rios@ejemplo.test',
                'telefono' => '3145523311',
                'mensaje' => 'Buenas tardes. Tengo un gastrobar en el centro de Armenia, llevamos ocho meses abiertos y quisiera saber cómo afiliarme al gremio y qué requisitos piden.',
                'estado' => EstadoMensaje::Nuevo,
            ],
            [
                'tipo' => TipoMensaje::Afiliacion,
                'nombre' => 'Jorge Enrique Patiño',
                'correo' => 'jorge.patino@ejemplo.test',
                'telefono' => '3106645002',
                'mensaje' => 'Estoy montando un bar en Calarcá y me recomendaron afiliarme antes de abrir para que me orienten con los permisos. ¿Es posible?',
                'estado' => EstadoMensaje::EnTramite,
            ],
            [
                'tipo' => TipoMensaje::Pqr,
                'nombre' => 'Carlos Alberto Muñoz',
                'correo' => 'carlos.munoz@ejemplo.test',
                'telefono' => '3122234780',
                'mensaje' => 'Pago mi mensualidad hace cuatro meses y aún no me llega el carné de afiliado que da acceso al descuento de Sayco. Necesito saber en qué va el trámite.',
                'estado' => EstadoMensaje::Respondido,
                'nota_respuesta' => 'Se verificó el pago y se despachó el carné el 18 del mes pasado. Se envió guía de seguimiento al correo del afiliado.',
                'respondido_at' => now()->subDays(6),
            ],
            [
                'tipo' => TipoMensaje::Pqr,
                'nombre' => 'Liliana Andrea Vargas',
                'correo' => 'liliana.vargas@ejemplo.test',
                'telefono' => '3178856640',
                'mensaje' => 'La capacitación de costos del mes pasado se canceló el mismo día y nadie avisó. Perdí un turno de trabajo por asistir. Solicito que se comunique con más anticipación.',
                'estado' => EstadoMensaje::EnTramite,
            ],
            [
                'tipo' => TipoMensaje::Pqr,
                'nombre' => 'Ómar Yesid Grajales',
                'correo' => 'omar.grajales@ejemplo.test',
                'telefono' => '3160012298',
                'mensaje' => 'Quiero saber por qué el certificado de bomberos en Salento cuesta distinto al de Armenia. ¿El gremio puede gestionar una tarifa unificada?',
                'estado' => EstadoMensaje::Nuevo,
            ],
            [
                'tipo' => TipoMensaje::Proveedor,
                'nombre' => 'Distribuidora El Congelador',
                'correo' => 'ventas@elcongelador.test',
                'telefono' => '3134470012',
                'mensaje' => 'Somos distribuidores de hielo en Quimbaya y Montenegro. Queremos aparecer en la bolsa de proveedores. ¿Cuál es el costo y qué se necesita?',
                'estado' => EstadoMensaje::Nuevo,
            ],
            [
                'tipo' => TipoMensaje::Proveedor,
                'nombre' => 'Mantenimiento Industrial GT',
                'correo' => 'contacto@mantenimientogt.test',
                'telefono' => '3197723345',
                'mensaje' => 'Prestamos servicio de mantenimiento de campanas de extracción y cuartos fríos con disponibilidad de fin de semana. Nos interesa entrar a la base de proveedores del gremio.',
                'estado' => EstadoMensaje::EnTramite,
            ],
            [
                'tipo' => TipoMensaje::Aliado,
                'nombre' => 'Seguros Andinos S.A.',
                'correo' => 'alianzas@segurosandinos.test',
                'telefono' => '3183390077',
                'mensaje' => 'Queremos proponer un convenio de pólizas de responsabilidad civil para los afiliados del capítulo. ¿Con quién podemos agendar una presentación?',
                'estado' => EstadoMensaje::Nuevo,
            ],
            [
                'tipo' => TipoMensaje::Contacto,
                'nombre' => 'Paula Restrepo Hoyos',
                'correo' => 'paula.restrepo@ejemplo.test',
                'telefono' => '3115560089',
                'mensaje' => 'Soy estudiante de Administración de Turismo y estoy haciendo mi trabajo de grado sobre economía nocturna. ¿Pueden compartirme las cifras del Observatorio?',
                'estado' => EstadoMensaje::Respondido,
                'nota_respuesta' => 'Se compartió el boletín del Observatorio y se remitió a la sección pública del sitio.',
                'respondido_at' => now()->subDays(3),
            ],
            [
                'tipo' => TipoMensaje::Contacto,
                'nombre' => 'Iván Darío Ceballos',
                'correo' => 'ivan.ceballos@ejemplo.test',
                'telefono' => '3151178820',
                'mensaje' => '¿La guía de requisitos va a cubrir también a Montenegro? Estoy evaluando abrir allá el año entrante.',
                'estado' => EstadoMensaje::Nuevo,
            ],
        ];

        foreach ($mensajes as $mensaje) {
            if (Mensaje::where('correo', $mensaje['correo'])->where('mensaje', $mensaje['mensaje'])->exists()) {
                continue;
            }

            $mensaje['acepta_datos'] = true;
            $mensaje['consentimiento_at'] = now()->subDays(random_int(1, 30));

            // El radicado consecutivo es exclusivo de las PQR.
            if ($mensaje['tipo']->requiereRadicado()) {
                $mensaje['radicado'] = Mensaje::generarRadicado();
            }

            Mensaje::create($mensaje);
        }
    }
}

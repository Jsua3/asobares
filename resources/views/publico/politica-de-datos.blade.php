<x-layouts.publico titulo="Política de tratamiento de datos personales — ASOBARES Quindío"
                   descripcion="Política de tratamiento de datos personales conforme a la Ley 1581 de 2012 y el Decreto 1074 de 2015.">

    <x-publico.hero titulo="Política de tratamiento de datos personales" compacto
                    :subtitulo="'Última actualización: '.ajuste('politica_actualizacion')" />

    <article class="mx-auto max-w-3xl space-y-9 px-4 py-12 text-sm leading-relaxed text-suave sm:px-6">

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">1. Responsable del tratamiento</h2>
            <p class="mt-3">
                <strong class="text-fuerte">{{ ajuste('politica_responsable') }}</strong>, con domicilio en
                {{ ajuste('contacto_direccion') }}, {{ ajuste('contacto_ciudad') }}, es responsable del
                tratamiento de los datos personales recolectados a través de este sitio.
            </p>
            <p class="mt-3">
                Canales de atención: {{ ajuste('contacto_correo') }} · WhatsApp {{ ajuste('contacto_whatsapp_visible') }}.
            </p>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">2. Marco legal</h2>
            <p class="mt-3">
                Esta política se adopta en cumplimiento de la Ley 1581 de 2012, el Decreto 1074 de 2015 y demás
                normas que las modifiquen o reglamenten, así como del artículo 15 de la Constitución Política.
            </p>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">3. Datos que recolectamos</h2>
            <p class="mt-3">Según el formulario que diligencies, podemos recolectar:</p>
            <ul class="mt-3 space-y-2">
                @foreach ([
                    'Datos de identificación y contacto: nombre, correo electrónico y teléfono.',
                    'Datos del establecimiento: nombre comercial, dirección, municipio y actividad.',
                    'Datos laborales, cuando registras tu perfil en la bolsa de empleo: cargo de interés y experiencia.',
                    'Datos de las inscripciones a eventos y capacitaciones del gremio.',
                    'Datos de facturación y estado de cuenta, cuando eres un establecimiento afiliado.',
                ] as $dato)
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-marca-500"></span>
                        <span>{{ $dato }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3">
                No recolectamos datos sensibles ni datos de menores de edad. Si detectamos que alguno se registró
                por error, lo eliminamos.
            </p>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">4. Finalidades</h2>
            <ul class="mt-3 space-y-2">
                @foreach ([
                    'Atender solicitudes de afiliación, contacto, peticiones, quejas y reclamos.',
                    'Gestionar inscripciones a eventos y capacitaciones del gremio.',
                    'Operar la bolsa de empleo, el directorio de artistas y la bolsa de proveedores.',
                    'Informar sobre el estado de cuenta de los establecimientos afiliados y procesar sus pagos.',
                    'Enviar información institucional del gremio, cuando la persona lo haya autorizado.',
                    'Cumplir obligaciones legales y contractuales.',
                ] as $finalidad)
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-marca-500"></span>
                        <span>{{ $finalidad }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">5. Autorización</h2>
            <p class="mt-3">
                La autorización se obtiene de forma previa, expresa e informada mediante la casilla de aceptación
                que aparece en cada formulario, con enlace a esta política. El sistema registra la fecha y la hora
                exactas en que se otorgó.
            </p>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">6. Derechos del titular</h2>
            <p class="mt-3">Como titular de tus datos personales tienes derecho a:</p>
            <ul class="mt-3 space-y-2">
                @foreach ([
                    'Conocer, actualizar y rectificar tus datos.',
                    'Solicitar prueba de la autorización que otorgaste.',
                    'Ser informado sobre el uso que se ha dado a tus datos.',
                    'Presentar quejas ante la Superintendencia de Industria y Comercio.',
                    'Revocar la autorización y solicitar la supresión de tus datos, cuando no exista un deber legal o contractual que lo impida.',
                    'Acceder gratuitamente a tus datos personales.',
                ] as $derecho)
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-marca-500"></span>
                        <span>{{ $derecho }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">7. Cómo ejercer tus derechos</h2>
            <p class="mt-3">
                Escribe a <a href="mailto:{{ ajuste('contacto_correo') }}" class="text-acento underline underline-offset-2">{{ ajuste('contacto_correo') }}</a>
                o radica tu solicitud como PQR en la
                <a href="{{ route('contacto') }}" class="text-acento underline underline-offset-2">página de contacto</a>,
                donde recibirás un número de radicado.
            </p>
            <p class="mt-3">
                Las consultas se atienden en un máximo de diez (10) días hábiles y los reclamos en quince (15)
                días hábiles, prorrogables conforme a la ley.
            </p>
        </section>

        <section>
            <h2 class="font-display text-lg font-semibold text-fuerte">8. Seguridad y vigencia</h2>
            <p class="mt-3">
                Adoptamos medidas técnicas y administrativas para proteger tus datos contra acceso no autorizado,
                pérdida o alteración: cifrado de contraseñas, control de acceso por roles, segundo factor de
                autenticación para el equipo administrativo y registro de auditoría de cada cambio.
            </p>
            <p class="mt-3">
                Los datos se conservan mientras exista la relación con el gremio y durante los términos legales
                aplicables. Esta política rige desde {{ ajuste('politica_actualizacion') }}.
            </p>
        </section>
    </article>
</x-layouts.publico>

<?php

use Illuminate\Support\Facades\Schedule;

// Los datos personales de la bolsa se borran solos al vencer su plazo.
Schedule::command('bolsas:depurar')->dailyAt('03:30');

// Y los de los formularios públicos: contacto y PQR también caducan.
Schedule::command('mensajes:depurar')->dailyAt('03:45');

// Las inscripciones a eventos caducan cuando el evento pasa; el registro
// contable del pago sobrevive (transacciones.inscripcion_id es nullOnDelete).
Schedule::command('inscripciones:depurar')->dailyAt('03:50');

// Lo que se subió al panel y ninguna acción procesó —la base del gremio, la
// cartera— no espera a la purga de 24 horas de Livewire.
Schedule::command('subidas:depurar')->hourly();

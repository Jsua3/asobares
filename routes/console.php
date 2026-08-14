<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Los datos personales de la bolsa se borran solos al vencer su plazo.
Schedule::command('bolsas:depurar')->dailyAt('03:30');

// Y los de los formularios públicos: contacto y PQR también caducan.
Schedule::command('mensajes:depurar')->dailyAt('03:45');

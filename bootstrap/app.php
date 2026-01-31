<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar alias de middleware para control de roles
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withSchedule(function ($schedule): void {
        // Comandos de alertas automáticas del sistema SGD Bethel
        if (config('alerts.scheduler.licencias.habilitado', true)) {
            $schedule->command('bethel:check-licencias')
                     ->dailyAt(config('alerts.scheduler.licencias.horario', '08:00'))
                     ->onOneServer()
                     ->withoutOverlapping();
        }

        if (config('alerts.scheduler.estaciones_fa.habilitado', true)) {
            $schedule->command('bethel:check-estaciones-fa')
                     ->dailyAt(config('alerts.scheduler.estaciones_fa.horario', '09:00'))
                     ->onOneServer()
                     ->withoutOverlapping();
        }

        if (config('alerts.scheduler.incidencias_estancadas.habilitado', true)) {
            $schedule->command('bethel:check-incidencias-estancadas')
                     ->dailyAt(config('alerts.scheduler.incidencias_estancadas.horario', '10:00'))
                     ->onOneServer()
                     ->withoutOverlapping();
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

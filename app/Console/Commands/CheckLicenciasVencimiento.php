<?php

namespace App\Console\Commands;

use App\Models\Estacion;
use App\Models\User;
use App\Notifications\LicenciaProximaVencer;
use App\Notifications\LicenciaVencida;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CheckLicenciasVencimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bethel:check-licencias {--force : Forzar ejecución sin deduplicación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar licencias próximas a vencer y generar alertas automáticas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando licencias de estaciones...');
        $this->newLine();

        $force = $this->option('force');
        $diasAlerta = config('alerts.licencias.dias_alerta', [15, 30, 90, 180]);
        $ventanaDeduplicacion = config('alerts.licencias.ventana_deduplicacion', 24);

        $alertasGeneradas = 0;
        $alertasDuplicadas = 0;
        $licenciasVencidas = 0;

        // 1. Verificar licencias VENCIDAS
        $this->info('📋 Verificando licencias vencidas...');

        $estacionesVencidas = Estacion::whereNotNull('licencia_vencimiento')
            ->whereDate('licencia_vencimiento', '<', now())
            ->get();

        foreach ($estacionesVencidas as $estacion) {
            $diasVencida = abs(now()->diffInDays($estacion->licencia_vencimiento, false));

            // Verificar deduplicación
            if (!$force && !$this->debeNotificar('licencia_vencida', $estacion->id, $diasVencida, $ventanaDeduplicacion)) {
                $alertasDuplicadas++;
                continue;
            }

            // Obtener usuarios a notificar
            $usuarios = $this->getUsuariosNotificar($estacion);

            foreach ($usuarios as $usuario) {
                $usuario->notify(new LicenciaVencida($estacion, $diasVencida));
            }

            $this->line("  ⚠️  {$estacion->codigo} - VENCIDA hace {$diasVencida} días");
            $alertasGeneradas++;
            $licenciasVencidas++;
        }

        $this->newLine();

        // 2. Verificar licencias próximas a vencer
        $this->info('📋 Verificando licencias próximas a vencer...');

        foreach ($diasAlerta as $dias) {
            $fechaObjetivo = now()->addDays($dias)->startOfDay();

            $estaciones = Estacion::whereNotNull('licencia_vencimiento')
                ->whereDate('licencia_vencimiento', '=', $fechaObjetivo)
                ->get();

            foreach ($estaciones as $estacion) {
                // Verificar deduplicación
                if (!$force && !$this->debeNotificar('licencia_vencimiento', $estacion->id, $dias, $ventanaDeduplicacion)) {
                    $alertasDuplicadas++;
                    continue;
                }

                // Determinar severidad
                $severity = $this->getSeveridad($dias);

                // Obtener usuarios a notificar
                $usuarios = $this->getUsuariosNotificar($estacion);

                foreach ($usuarios as $usuario) {
                    $usuario->notify(new LicenciaProximaVencer($estacion, $dias, $severity));
                }

                $this->line("  ⏰ {$estacion->codigo} - Vence en {$dias} días ({$severity})");
                $alertasGeneradas++;
            }
        }

        $this->newLine();

        // Resumen
        $this->info('✅ Verificación completada');
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Licencias vencidas', $licenciasVencidas],
                ['Alertas generadas', $alertasGeneradas],
                ['Alertas duplicadas (omitidas)', $alertasDuplicadas],
                ['Total estaciones procesadas', $estacionesVencidas->count() + Estacion::whereNotNull('licencia_vencimiento')->count()],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Verificar si debe notificar (deduplicación)
     */
    protected function debeNotificar(string $tipo, int $estacionId, int $dias, int $ventanaHoras): bool
    {
        if (!config('alerts.general.habilitar_deduplicacion', true)) {
            return true;
        }

        $fechaLimite = now()->subHours($ventanaHoras);

        // Buscar notificación similar reciente
        $existeReciente = \DB::table('notifications')
            ->where('data->type', $tipo)
            ->where('data->estacion_id', $estacionId)
            ->where('data->dias_restantes', $dias)
            ->where('created_at', '>=', $fechaLimite)
            ->exists();

        return !$existeReciente;
    }

    /**
     * Obtener severidad según días restantes
     */
    protected function getSeveridad(int $dias): string
    {
        $severidades = config('alerts.licencias.severidad', [
            'critica' => 15,
            'alta' => 30,
            'media' => 90,
            'baja' => 180,
        ]);

        if ($dias <= $severidades['critica']) return 'critica';
        if ($dias <= $severidades['alta']) return 'alta';
        if ($dias <= $severidades['media']) return 'media';
        return 'baja';
    }

    /**
     * Obtener usuarios a notificar según estación
     */
    protected function getUsuariosNotificar(Estacion $estacion): \Illuminate\Database\Eloquent\Collection
    {
        $rolesNotificados = config('alerts.licencias.roles_notificados', [
            'administrador',
            'gerente',
            'gestor_radiodifusion',
            'coordinador_operaciones',
        ]);

        // Obtener usuarios por roles
        $usuarios = User::whereIn('rol', $rolesNotificados)
            ->where('activo', true)
            ->get();

        // Agregar jefe de estación si existe
        if ($estacion->jefeEstacion) {
            $usuarios->push($estacion->jefeEstacion);
        }

        // Filtrar por sector (sectoristas solo de su sector)
        $usuarios = $usuarios->filter(function ($user) use ($estacion) {
            if ($user->rol->value === 'sectorista') {
                return $user->sector_asignado === $estacion->sector->value;
            }
            return true;
        });

        return $usuarios->unique('id');
    }
}

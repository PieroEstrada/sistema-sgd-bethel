<?php

namespace App\Console\Commands;

use App\Enums\EstadoEstacion;
use App\Models\Estacion;
use App\Models\User;
use App\Notifications\EstacionFueraDelAire;
use Illuminate\Console\Command;

class CheckEstacionesFueraAire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bethel:check-estaciones-fa {--force : Forzar ejecución sin deduplicación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar estaciones fuera del aire que exceden el tiempo permitido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando estaciones fuera del aire...');
        $this->newLine();

        $force = $this->option('force');
        $maxDiasFueraAire = config('alerts.estaciones.max_dias_fuera_aire', 7);
        $notificarCada = config('alerts.estaciones.notificar_cada', 7);
        $ventanaDeduplicacion = config('alerts.general.habilitar_deduplicacion') ? 24 : 0;

        $alertasGeneradas = 0;
        $alertasDuplicadas = 0;

        // Obtener estaciones fuera del aire
        $estaciones = Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
            ->whereNotNull('fecha_salida_aire')
            ->get();

        $this->info("📊 Total estaciones fuera del aire: {$estaciones->count()}");
        $this->newLine();

        foreach ($estaciones as $estacion) {
            $diasFA = now()->diffInDays($estacion->fecha_salida_aire);

            // Solo alertar si excede el límite
            if ($diasFA < $maxDiasFueraAire) {
                continue;
            }

            // Notificar solo cada N días después del límite
            // Por ejemplo: 7, 14, 21, 28 días
            if ($diasFA % $notificarCada !== 0 && !$force) {
                continue;
            }

            // Verificar deduplicación
            if (!$force && !$this->debeNotificar($estacion->id, $diasFA, $ventanaDeduplicacion)) {
                $alertasDuplicadas++;
                continue;
            }

            // Determinar severidad
            $severity = $this->getSeveridad($diasFA);

            // Obtener usuarios a notificar
            $usuarios = $this->getUsuariosNotificar($estacion);

            foreach ($usuarios as $usuario) {
                $usuario->notify(new EstacionFueraDelAire($estacion, $diasFA, $severity));
            }

            $this->line("  ⚠️  {$estacion->codigo} - {$diasFA} días F.A. ({$severity})");
            $alertasGeneradas++;
        }

        $this->newLine();

        // Resumen
        $this->info('✅ Verificación completada');
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Estaciones fuera del aire', $estaciones->count()],
                ['Estaciones críticas (>{maxDiasFueraAire} días)', $estaciones->filter(fn($e) => now()->diffInDays($e->fecha_salida_aire) > $maxDiasFueraAire)->count()],
                ['Alertas generadas', $alertasGeneradas],
                ['Alertas duplicadas (omitidas)', $alertasDuplicadas],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Verificar si debe notificar (deduplicación)
     */
    protected function debeNotificar(int $estacionId, int $dias, int $ventanaHoras): bool
    {
        if ($ventanaHoras === 0) {
            return true;
        }

        $fechaLimite = now()->subHours($ventanaHoras);

        $existeReciente = \DB::table('notifications')
            ->where('data->type', 'estacion_fuera_aire')
            ->where('data->estacion_id', $estacionId)
            ->where('data->dias_fuera_aire', $dias)
            ->where('created_at', '>=', $fechaLimite)
            ->exists();

        return !$existeReciente;
    }

    /**
     * Obtener severidad según días fuera del aire
     */
    protected function getSeveridad(int $dias): string
    {
        $severidades = config('alerts.estaciones.severidad', [
            'critica' => 30,
            'alta' => 14,
            'media' => 7,
        ]);

        if ($dias > $severidades['critica']) return 'critica';
        if ($dias > $severidades['alta']) return 'alta';
        return 'media';
    }

    /**
     * Obtener usuarios a notificar según estación
     */
    protected function getUsuariosNotificar(Estacion $estacion): \Illuminate\Database\Eloquent\Collection
    {
        $rolesNotificados = config('alerts.estaciones.roles_notificados', [
            'administrador',
            'gerente',
            'coordinador_operaciones',
            'sectorista',
            'jefe_estacion',
        ]);

        // Obtener usuarios por roles
        $usuarios = User::whereIn('rol', $rolesNotificados)
            ->where('activo', true)
            ->get();

        // Agregar jefe de estación
        if ($estacion->jefeEstacion) {
            $usuarios->push($estacion->jefeEstacion);
        }

        // Filtrar por sector
        $usuarios = $usuarios->filter(function ($user) use ($estacion) {
            if ($user->rol->value === 'sectorista' || $user->rol->value === 'jefe_estacion') {
                return $user->sector_asignado === $estacion->sector->value;
            }
            return true;
        });

        return $usuarios->unique('id');
    }
}

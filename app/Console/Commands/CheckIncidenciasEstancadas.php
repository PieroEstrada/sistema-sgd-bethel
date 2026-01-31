<?php

namespace App\Console\Commands;

use App\Models\Incidencia;
use App\Models\User;
use App\Notifications\IncidenciaEstancada;
use Illuminate\Console\Command;

class CheckIncidenciasEstancadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bethel:check-incidencias-estancadas {--force : Forzar ejecución sin deduplicación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar incidencias sin cambios en el historial por tiempo prolongado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando incidencias estancadas...');
        $this->newLine();

        $force = $this->option('force');
        $diasSinCambio = config('alerts.incidencias.dias_sin_cambio', [
            'critica' => 1,
            'alta' => 3,
            'media' => 7,
            'baja' => 14,
        ]);
        $ventanaDeduplicacion = config('alerts.incidencias.ventana_deduplicacion', 24);

        $alertasGeneradas = 0;
        $alertasDuplicadas = 0;

        // Obtener incidencias abiertas o en proceso
        $incidencias = Incidencia::whereIn('estado', ['abierta', 'en_proceso'])
            ->with(['historial', 'estacion', 'responsableActual', 'asignadoAUsuario'])
            ->get();

        $this->info("📊 Total incidencias activas: {$incidencias->count()}");
        $this->newLine();

        foreach ($incidencias as $incidencia) {
            // Obtener último cambio en historial
            $ultimoCambio = $incidencia->historial()->latest()->first();

            if (!$ultimoCambio) {
                // Si no tiene historial, usar fecha de creación
                $ultimoCambio = (object) ['created_at' => $incidencia->created_at];
            }

            $diasSinCambioActual = now()->diffInDays($ultimoCambio->created_at);

            // Obtener límite según prioridad
            $prioridadValue = $incidencia->prioridad->value ?? 'media';
            $limiteSegunPrioridad = $diasSinCambio[$prioridadValue] ?? $diasSinCambio['media'];

            // Solo alertar si excede el límite
            if ($diasSinCambioActual < $limiteSegunPrioridad) {
                continue;
            }

            // Verificar deduplicación
            if (!$force && !$this->debeNotificar($incidencia->id, $diasSinCambioActual, $ventanaDeduplicacion)) {
                $alertasDuplicadas++;
                continue;
            }

            // Determinar severidad
            $severity = $this->getSeveridad($diasSinCambioActual, $prioridadValue);

            // Obtener usuarios a notificar
            $usuarios = $this->getUsuariosNotificar($incidencia);

            foreach ($usuarios as $usuario) {
                $usuario->notify(new IncidenciaEstancada($incidencia, $diasSinCambioActual, $severity));
            }

            $this->line("  ⏱️  {$incidencia->codigo_incidencia} - {$diasSinCambioActual} días sin cambios (Prioridad: {$prioridadValue})");
            $alertasGeneradas++;
        }

        $this->newLine();

        // Resumen
        $this->info('✅ Verificación completada');
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Incidencias activas', $incidencias->count()],
                ['Incidencias estancadas', $alertasGeneradas + $alertasDuplicadas],
                ['Alertas generadas', $alertasGeneradas],
                ['Alertas duplicadas (omitidas)', $alertasDuplicadas],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Verificar si debe notificar (deduplicación)
     */
    protected function debeNotificar(int $incidenciaId, int $dias, int $ventanaHoras): bool
    {
        if (!config('alerts.general.habilitar_deduplicacion', true)) {
            return true;
        }

        $fechaLimite = now()->subHours($ventanaHoras);

        $existeReciente = \DB::table('notifications')
            ->where('data->type', 'incidencia_estancada')
            ->where('data->incidencia_id', $incidenciaId)
            ->where('data->dias_sin_cambio', $dias)
            ->where('created_at', '>=', $fechaLimite)
            ->exists();

        return !$existeReciente;
    }

    /**
     * Obtener severidad según días sin cambio y prioridad
     */
    protected function getSeveridad(int $dias, string $prioridad): string
    {
        // Incidencias críticas siempre son de severidad alta si están estancadas
        if ($prioridad === 'critica' && $dias > 1) return 'critica';
        if ($prioridad === 'alta' && $dias > 3) return 'alta';
        if ($dias > 14) return 'alta';
        if ($dias > 7) return 'media';
        return 'baja';
    }

    /**
     * Obtener usuarios a notificar según incidencia
     */
    protected function getUsuariosNotificar(Incidencia $incidencia): \Illuminate\Database\Eloquent\Collection
    {
        $rolesNotificados = config('alerts.incidencias.roles_notificados', [
            'administrador',
            'coordinador_operaciones',
            'supervisor_tecnico',
            'jefe_estacion',
        ]);

        // Obtener usuarios por roles
        $usuarios = User::whereIn('rol', $rolesNotificados)
            ->where('activo', true)
            ->get();

        // Agregar responsable actual
        if ($incidencia->responsableActual) {
            $usuarios->push($incidencia->responsableActual);
        }

        // Agregar asignado
        if ($incidencia->asignadoAUsuario) {
            $usuarios->push($incidencia->asignadoAUsuario);
        }

        // Agregar jefe de estación si existe
        if ($incidencia->estacion && $incidencia->estacion->jefeEstacion) {
            $usuarios->push($incidencia->estacion->jefeEstacion);
        }

        return $usuarios->unique('id');
    }
}

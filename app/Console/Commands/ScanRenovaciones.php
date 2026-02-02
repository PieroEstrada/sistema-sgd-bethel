<?php

namespace App\Console\Commands;

use App\Enums\RiesgoLicencia;
use App\Enums\TipoTicket;
use App\Models\Estacion;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RenovacionAlertNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ScanRenovaciones extends Command
{
    protected $signature = 'renovaciones:scan
                            {--dry-run : Simular sin crear tickets ni notificaciones}
                            {--force : Forzar actualización aunque ya existan tickets}';

    protected $description = 'Escanea licencias próximas a vencer, actualiza riesgos y crea tickets automáticos';

    private array $stats = [
        'estaciones_evaluadas' => 0,
        'riesgo_actualizado' => 0,
        'tickets_tramites_creados' => 0,
        'tickets_operaciones_creados' => 0,
        'tickets_escalados' => 0,
        'notificaciones_enviadas' => 0,
        'ya_existentes' => 0,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('=== ESCANEO DE RENOVACIONES DE LICENCIAS ===');
        $this->info('Fecha actual: ' . now()->format('d/m/Y'));

        if ($dryRun) {
            $this->warn('Modo simulación activado');
        }

        try {
            DB::beginTransaction();

            // 1. Actualizar riesgo de todas las estaciones con fecha de vencimiento
            $this->actualizarRiesgos($dryRun);

            // 2. Crear tickets para estaciones en riesgo alto (<12 meses)
            $this->crearTicketsRiesgoAlto($dryRun, $force);

            // 3. Escalar tickets existentes si ya están en <=6 meses
            $this->escalarTicketsCriticos($dryRun);

            // 4. Enviar notificaciones
            $this->enviarNotificaciones($dryRun);

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            $this->displaySummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            Log::error("ScanRenovaciones error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        }
    }

    private function actualizarRiesgos(bool $dryRun): void
    {
        $this->info("\n1. Actualizando niveles de riesgo...");

        $estaciones = Estacion::whereNotNull('licencia_vence')->get();

        $progressBar = $this->output->createProgressBar($estaciones->count());
        $progressBar->start();

        foreach ($estaciones as $estacion) {
            $this->stats['estaciones_evaluadas']++;

            $fechaVencimiento = Carbon::parse($estacion->licencia_vence);
            $hoy = now();

            // Calcular meses restantes (negativo si ya venció)
            $mesesRestantes = $hoy->diffInMonths($fechaVencimiento, false);
            if ($fechaVencimiento < $hoy) {
                $mesesRestantes = -abs($mesesRestantes);
            }

            // Calcular nuevo riesgo
            $nuevoRiesgo = RiesgoLicencia::calcularDesdesMeses((int) $mesesRestantes);

            // Solo actualizar si cambió
            if ($estacion->riesgo_licencia !== $nuevoRiesgo?->value) {

                if (!$dryRun) {
                    $estacion->update([
                        'riesgo_licencia' => $nuevoRiesgo?->value,
                    ]);
                }

                $this->stats['riesgo_actualizado']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function crearTicketsRiesgoAlto(bool $dryRun, bool $force): void
    {
        $this->info("\n2. Creando tickets para estaciones en riesgo alto (<12 meses)...");

        $estacionesRiesgoAlto = Estacion::where('riesgo_licencia', 'ALTO')
            ->whereNotNull('licencia_vence')
            ->get();

        $this->info("   Estaciones en riesgo alto: {$estacionesRiesgoAlto->count()}");

        foreach ($estacionesRiesgoAlto as $estacion) {
            $añoVencimiento = Carbon::parse($estacion->licencia_vence)->year;
            $mesesRestantes = $estacion->licencia_meses_restantes ?? 0;

            // Determinar fase
            $fase = $mesesRestantes <= 6 ? '6_meses' : '12_meses';
            $prioridad = $mesesRestantes <= 6 ? 'alta' : 'media';

            // Crear ticket de TRÁMITES si no existe
            $this->crearTicketSiNoExiste(
                $estacion,
                TipoTicket::TRAMITES,
                $fase,
                $añoVencimiento,
                $prioridad,
                'gestor_radiodifusion',
                $dryRun,
                $force
            );

            // Crear ticket de OPERACIONES si no existe
            $this->crearTicketSiNoExiste(
                $estacion,
                TipoTicket::OPERACIONES,
                $fase,
                $añoVencimiento,
                $prioridad,
                'coordinador_operaciones',
                $dryRun,
                $force
            );
        }
    }

    private function crearTicketSiNoExiste(
        Estacion $estacion,
        TipoTicket $tipoTicket,
        string $fase,
        int $añoVencimiento,
        string $prioridad,
        string $rolAsignado,
        bool $dryRun,
        bool $force
    ): void {
        // Verificar si ya existe
        $existente = Ticket::where('estacion_id', $estacion->id)
            ->where('tipo_ticket', $tipoTicket->value)
            ->where('renovacion_fase', $fase)
            ->where('licencia_año', $añoVencimiento)
            ->first();

        if ($existente && !$force) {
            $this->stats['ya_existentes']++;
            return;
        }

        $mesesRestantes = $estacion->licencia_meses_restantes ?? 0;
        $fechaVencimiento = $estacion->licencia_vence;

        $titulo = $this->generarTituloTicket($estacion, $tipoTicket, $mesesRestantes);
        $descripcion = $this->generarDescripcionTicket($estacion, $tipoTicket, $mesesRestantes, $fechaVencimiento);

        if (!$dryRun) {
            Ticket::create([
                'titulo' => $titulo,
                'estacion_id' => $estacion->id,
                'tipo_ticket' => $tipoTicket->value,
                'estado' => 'solicitud_nueva',
                'prioridad' => $prioridad,
                'assigned_role' => $rolAsignado,
                'renovacion_fase' => $fase,
                'licencia_año' => $añoVencimiento,
                'fecha_objetivo' => $fechaVencimiento,
                'descripcion' => $descripcion,
                'equipo' => $tipoTicket->label(),
                'servicio' => 'Renovación de Licencia',
                'fecha_ingreso' => now(),
                'creado_por_user_id' => 1, // Sistema
            ]);
        }

        if ($tipoTicket === TipoTicket::TRAMITES) {
            $this->stats['tickets_tramites_creados']++;
        } else {
            $this->stats['tickets_operaciones_creados']++;
        }

        $this->line("   + Ticket {$tipoTicket->label()} creado para: {$estacion->localidad} ({$fase})");
    }

    private function generarTituloTicket(Estacion $estacion, TipoTicket $tipo, int $meses): string
    {
        $urgencia = $meses <= 0 ? 'VENCIDA' : ($meses <= 6 ? 'URGENTE' : 'Próxima');

        return match($tipo) {
            TipoTicket::TRAMITES => "[{$urgencia}] Renovación licencia - {$estacion->localidad} ({$estacion->banda->value})",
            TipoTicket::OPERACIONES => "[{$urgencia}] Preparar documentación técnica - {$estacion->localidad}",
            default => "Renovación - {$estacion->localidad}",
        };
    }

    private function generarDescripcionTicket(Estacion $estacion, TipoTicket $tipo, int $meses, $fechaVencimiento): string
    {
        $fechaFormateada = $fechaVencimiento ? Carbon::parse($fechaVencimiento)->format('d/m/Y') : 'N/A';
        $rvm = $estacion->licencia_rvm ?? 'Sin RVM';

        $base = "ESTACIÓN: {$estacion->localidad}, {$estacion->departamento}\n";
        $base .= "BANDA: {$estacion->banda->value} - ";
        $base .= $estacion->banda->esTv() ? "Canal {$estacion->canal_tv}" : "{$estacion->frecuencia} MHz";
        $base .= "\nSECTOR: {$estacion->sector->value}\n";
        $base .= "RVM ACTUAL: {$rvm}\n";
        $base .= "FECHA VENCIMIENTO: {$fechaFormateada}\n";
        $base .= "MESES RESTANTES: {$meses}\n\n";

        if ($meses <= 0) {
            $base .= "⚠️ LICENCIA VENCIDA - ACCIÓN INMEDIATA REQUERIDA\n\n";
        } elseif ($meses <= 6) {
            $base .= "🔴 URGENTE: Menos de 6 meses para vencimiento\n\n";
        } else {
            $base .= "🟡 ATENCIÓN: Menos de 12 meses para vencimiento\n\n";
        }

        return match($tipo) {
            TipoTicket::TRAMITES => $base . "ACCIONES REQUERIDAS (Trámites):\n" .
                "1. Verificar documentación vigente\n" .
                "2. Preparar expediente de renovación\n" .
                "3. Coordinar con MTC para presentación\n" .
                "4. Dar seguimiento al trámite",

            TipoTicket::OPERACIONES => $base . "ACCIONES REQUERIDAS (Operaciones):\n" .
                "1. Verificar estado técnico de la estación\n" .
                "2. Actualizar documentación técnica\n" .
                "3. Coordinar inspección si es necesario\n" .
                "4. Preparar informes técnicos requeridos",

            default => $base,
        };
    }

    private function escalarTicketsCriticos(bool $dryRun): void
    {
        $this->info("\n3. Escalando tickets críticos (<=6 meses)...");

        // Buscar tickets de 12_meses que ahora deberían ser 6_meses
        $ticketsParaEscalar = Ticket::where('renovacion_fase', '12_meses')
            ->where('prioridad', '!=', 'alta')
            ->where('prioridad', '!=', 'critica')
            ->whereHas('estacion', function ($q) {
                $q->whereNotNull('licencia_vence')
                  ->where('licencia_vence', '<=', DB::raw("DATE_ADD(CURDATE(), INTERVAL 6 MONTH)"));
            })
            ->get();

        foreach ($ticketsParaEscalar as $ticket) {
            $meses = $ticket->estacion->licencia_meses_restantes ?? 0;

            if (!$dryRun) {
                $ticket->update([
                    'prioridad' => $meses <= 0 ? 'critica' : 'alta',
                    'titulo' => str_replace(['[Próxima]', '[URGENTE]'], '[URGENTE]', $ticket->titulo),
                ]);
            }

            $this->stats['tickets_escalados']++;
            $this->line("   ↑ Escalado: {$ticket->estacion->localidad} -> prioridad alta");
        }
    }

    private function enviarNotificaciones(bool $dryRun): void
    {
        $this->info("\n4. Enviando notificaciones...");

        if ($dryRun) {
            $this->warn("   Notificaciones omitidas en modo simulación");
            return;
        }

        // Obtener estaciones críticas (<=6 meses)
        $estacionesCriticas = Estacion::where('riesgo_licencia', 'ALTO')
            ->whereNotNull('licencia_vence')
            ->where('licencia_vence', '<=', DB::raw("DATE_ADD(CURDATE(), INTERVAL 6 MONTH)"))
            ->get();

        if ($estacionesCriticas->isEmpty()) {
            $this->info("   No hay estaciones críticas que notificar");
            return;
        }

        // Notificar a gestores de radiodifusión
        $gestores = User::where('rol', 'gestor_radiodifusion')
            ->where('activo', true)
            ->get();

        // Notificar a coordinadores de operaciones
        $coordinadores = User::where('rol', 'coordinador_operaciones')
            ->where('activo', true)
            ->get();

        // Notificar a administradores
        $admins = User::where('rol', 'administrador')
            ->where('activo', true)
            ->get();

        $usuariosNotificar = $gestores->merge($coordinadores)->merge($admins)->unique('id');

        foreach ($usuariosNotificar as $usuario) {
            try {
                $usuario->notify(new RenovacionAlertNotification($estacionesCriticas));
                $this->stats['notificaciones_enviadas']++;
            } catch (\Exception $e) {
                Log::warning("No se pudo notificar a {$usuario->email}: {$e->getMessage()}");
            }
        }

        $this->info("   Notificaciones enviadas: {$this->stats['notificaciones_enviadas']}");
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== RESUMEN DEL ESCANEO ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Estaciones evaluadas', $this->stats['estaciones_evaluadas']],
                ['Riesgo actualizado', $this->stats['riesgo_actualizado']],
                ['Tickets Trámites creados', $this->stats['tickets_tramites_creados']],
                ['Tickets Operaciones creados', $this->stats['tickets_operaciones_creados']],
                ['Tickets escalados', $this->stats['tickets_escalados']],
                ['Ya existentes (omitidos)', $this->stats['ya_existentes']],
                ['Notificaciones enviadas', $this->stats['notificaciones_enviadas']],
            ]
        );

        // Mostrar distribución actual de riesgo
        $this->newLine();
        $this->info('=== DISTRIBUCIÓN DE RIESGO ACTUAL ===');
        $this->table(
            ['Nivel', 'Cantidad', 'Porcentaje'],
            collect([
                ['Alto (<12 meses)', Estacion::where('riesgo_licencia', 'ALTO')->count()],
                ['Medio (12-24 meses)', Estacion::where('riesgo_licencia', 'MEDIO')->count()],
                ['Seguro (>24 meses)', Estacion::where('riesgo_licencia', 'SEGURO')->count()],
                ['Sin evaluar', Estacion::whereNull('riesgo_licencia')->count()],
            ])->map(function ($row) {
                $total = Estacion::count();
                $porcentaje = $total > 0 ? round(($row[1] / $total) * 100, 1) : 0;
                return [$row[0], $row[1], "{$porcentaje}%"];
            })->toArray()
        );
    }
}

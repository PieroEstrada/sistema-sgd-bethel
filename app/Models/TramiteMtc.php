<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\TipoTramiteMtc;
use App\Enums\EstadoTramiteMtc;

class TramiteMtc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tramites_mtc';

    protected $fillable = [
        'numero_expediente',
        'tipo_tramite',
        'estacion_id',
        'estado',
        'fecha_presentacion',
        'fecha_respuesta',
        'fecha_vencimiento',
        'observaciones',
        'resolucion',
        'direccion_completa',
        'coordenadas_utm',
        'responsable_id',
        'costo_tramite',
        'documentos_requeridos',
        'documentos_presentados',
        'observaciones_mtc'
    ];

    protected $casts = [
        'tipo_tramite' => TipoTramiteMtc::class,
        'estado' => EstadoTramiteMtc::class,
        'fecha_presentacion' => 'date',
        'fecha_respuesta' => 'date',
        'fecha_vencimiento' => 'date',
        'costo_tramite' => 'decimal:2',
        'documentos_requeridos' => 'array',
        'documentos_presentados' => 'array'
    ];

    // Relaciones
    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // public function seguimientos()
    // {
    //     return $this->hasMany(SeguimientoTramite::class);
    // }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'tramite_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', [
            EstadoTramiteMtc::PRESENTADO,
            EstadoTramiteMtc::EN_PROCESO
        ]);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', EstadoTramiteMtc::APROBADO);
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', EstadoTramiteMtc::RECHAZADO);
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
                    ->whereNotIn('estado', [
                        EstadoTramiteMtc::APROBADO,
                        EstadoTramiteMtc::RECHAZADO
                    ]);
    }

    public function scopePorTipo($query, TipoTramiteMtc $tipo)
    {
        return $query->where('tipo_tramite', $tipo);
    }

    public function scopeDelAño($query, int $año)
    {
        return $query->whereYear('fecha_presentacion', $año);
    }

    // Métodos de negocio
    public function getTipoTramiteTextoAttribute(): string
    {
        return $this->tipo_tramite->getLabel();
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado->getLabel();
    }

    public function getColorEstadoAttribute(): string
    {
        return $this->estado->getColor();
    }

    public function getIconoEstadoAttribute(): string
    {
        return $this->estado->getIcon();
    }

    public function getDiasTranscurridosAttribute(): int
    {
        return $this->fecha_presentacion->diffInDays(
            $this->fecha_respuesta ?? now()
        );
    }

    public function getDiasParaVencimientoAttribute(): ?int
    {
        return $this->fecha_vencimiento 
            ? now()->diffInDays($this->fecha_vencimiento, false)
            : null;
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && 
               $this->fecha_vencimiento < now() &&
               !$this->estaFinalizado();
    }

    public function estaPendiente(): bool
    {
        return in_array($this->estado, [
            EstadoTramiteMtc::PRESENTADO,
            EstadoTramiteMtc::EN_PROCESO
        ]);
    }

    public function estaAprobado(): bool
    {
        return $this->estado === EstadoTramiteMtc::APROBADO;
    }

    public function estaRechazado(): bool
    {
        return $this->estado === EstadoTramiteMtc::RECHAZADO;
    }

    public function estaFinalizado(): bool
    {
        return in_array($this->estado, [
            EstadoTramiteMtc::APROBADO,
            EstadoTramiteMtc::RECHAZADO
        ]);
    }

    public function puedeSerEditado(): bool
    {
        return in_array($this->estado, [
            EstadoTramiteMtc::PRESENTADO,
            EstadoTramiteMtc::EN_PROCESO
        ]);
    }

    public function requiereDocumentosAdicionales(): bool
    {
        if (!$this->documentos_requeridos || !$this->documentos_presentados) {
            return false;
        }

        return count(array_diff(
            $this->documentos_requeridos,
            $this->documentos_presentados
        )) > 0;
    }

    public function getDocumentosFaltantesAttribute(): array
    {
        if (!$this->documentos_requeridos || !$this->documentos_presentados) {
            return [];
        }

        return array_diff(
            $this->documentos_requeridos,
            $this->documentos_presentados
        );
    }

    public function getPorcentajeCompletudAttribute(): int
    {
        if (!$this->documentos_requeridos) {
            return 100;
        }

        $requeridos = count($this->documentos_requeridos);
        $presentados = count($this->documentos_presentados ?? []);

        return $requeridos > 0 ? round(($presentados / $requeridos) * 100) : 0;
    }

    // Métodos de acción
    public function aprobar(string $resolucion = null): void
    {
        $this->update([
            'estado' => EstadoTramiteMtc::APROBADO,
            'fecha_respuesta' => now(),
            'resolucion' => $resolucion
        ]);

        $this->seguimientos()->create([
            'comentario' => "Trámite aprobado" . ($resolucion ? ": {$resolucion}" : ""),
            'usuario_id' => Auth::id(),
            'tipo' => 'aprobacion'
        ]);
    }

    public function rechazar(string $motivo): void
    {
        $this->update([
            'estado' => EstadoTramiteMtc::RECHAZADO,
            'fecha_respuesta' => now(),
            'observaciones_mtc' => $motivo
        ]);

        $this->seguimientos()->create([
            'comentario' => "Trámite rechazado: {$motivo}",
            'usuario_id' => Auth::id(),
            'tipo' => 'rechazo'
        ]);
    }

    public function actualizarEstado(EstadoTramiteMtc $nuevoEstado, string $comentario = null): void
    {
        $estadoAnterior = $this->estado;
        
        $this->update(['estado' => $nuevoEstado]);

        if ($comentario) {
            $this->seguimientos()->create([
                'comentario' => $comentario,
                'usuario_id' => Auth::id(),
                'tipo' => 'actualizacion_estado'
            ]);
        }
    }

    public function agregarSeguimiento(string $comentario, string $tipo = 'seguimiento'): void
    {
        $this->seguimientos()->create([
            'comentario' => $comentario,
            'usuario_id' => Auth::id(),
            'tipo' => $tipo
        ]);
    }

    // Método estático para estadísticas
    public static function getEstadisticas(): array
    {
        return [
            'total' => self::count(),
            'presentados' => self::where('estado', EstadoTramiteMtc::PRESENTADO)->count(),
            'en_proceso' => self::where('estado', EstadoTramiteMtc::EN_PROCESO)->count(),
            'aprobados' => self::where('estado', EstadoTramiteMtc::APROBADO)->count(),
            'rechazados' => self::where('estado', EstadoTramiteMtc::RECHAZADO)->count(),
            'vencidos' => self::vencidos()->count(),
            'este_año' => self::delAño(date('Y'))->count()
        ];
    }

    // Método para obtener trámites por tipo
    public static function porTipoTramite()
    {
        return self::select('tipo_tramite', DB::raw('count(*) as total'))
                   ->groupBy('tipo_tramite')
                   ->get()
                   ->mapWithKeys(function($item) {
                       return [$item->tipo_tramite->getLabel() => $item->total];
                   });
    }
}
<?php

namespace App\Exports;

use App\Models\Incidencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidenciasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filtros;
    protected $columnas;

    public function __construct($filtros = [], $columnas = [])
    {
        $this->filtros = $filtros;
        $this->columnas = $columnas ?: $this->columnasDefecto();
    }

    private function columnasDefecto()
    {
        return ['codigo', 'estacion', 'titulo', 'prioridad', 'estado', 'tipo', 'area_responsable', 'reportado_por', 'asignado_a', 'fecha_reporte', 'dias_transcurridos'];
    }

    public function collection()
    {
        $query = Incidencia::with([
            'estacion',
            'reportadoPor',
            'asignadoA',
            'responsableActual'
        ]);

        // Aplicar filtros
        if (isset($this->filtros['search']) && $this->filtros['search']) {
            $search = $this->filtros['search'];
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_incidencia', 'LIKE', "%{$search}%");
            });
        }

        if (isset($this->filtros['estacion']) && $this->filtros['estacion']) {
            $query->where('estacion_id', $this->filtros['estacion']);
        }

        if (isset($this->filtros['prioridad']) && $this->filtros['prioridad']) {
            $query->where('prioridad', $this->filtros['prioridad']);
        }

        if (isset($this->filtros['estado']) && $this->filtros['estado']) {
            $query->where('estado', $this->filtros['estado']);
        }

        if (isset($this->filtros['tipo']) && $this->filtros['tipo']) {
            $query->where('tipo', $this->filtros['tipo']);
        }

        if (isset($this->filtros['area']) && $this->filtros['area']) {
            $query->where('area_responsable_actual', $this->filtros['area']);
        }

        if (isset($this->filtros['reportado_por_usuario']) && $this->filtros['reportado_por_usuario']) {
            $query->where('reportado_por_usuario_id', $this->filtros['reportado_por_usuario']);
        }

        if (isset($this->filtros['asignado_a_usuario']) && $this->filtros['asignado_a_usuario']) {
            $query->where('asignado_a_usuario_id', $this->filtros['asignado_a_usuario']);
        }

        return $query->orderBy('fecha_reporte', 'desc')->get();
    }

    public function headings(): array
    {
        $todosHeaders = [
            'codigo' => 'Código',
            'estacion' => 'Estación',
            'localidad' => 'Localidad',
            'titulo' => 'Título',
            'descripcion' => 'Descripción',
            'prioridad' => 'Prioridad',
            'estado' => 'Estado',
            'tipo' => 'Tipo',
            'area_responsable' => 'Área Responsable',
            'reportado_por' => 'Reportado Por',
            'asignado_a' => 'Asignado A',
            'responsable_actual' => 'Responsable Actual',
            'fecha_reporte' => 'Fecha Reporte',
            'fecha_resolucion' => 'Fecha Resolución',
            'dias_transcurridos' => 'Días Transcurridos',
            'costo_soles' => 'Costo (S/.)',
            'costo_dolares' => 'Costo (USD)',
            'transferencias' => 'N° Transferencias',
        ];

        $headers = [];
        foreach ($this->columnas as $col) {
            if (isset($todosHeaders[$col])) {
                $headers[] = $todosHeaders[$col];
            }
        }

        return $headers;
    }

    public function map($incidencia): array
    {
        $todosValores = [
            'codigo' => $incidencia->codigo_incidencia ?: 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT),
            'estacion' => $incidencia->estacion->codigo ?? 'N/A',
            'localidad' => $incidencia->estacion->localidad ?? 'N/A',
            'titulo' => $incidencia->titulo,
            'descripcion' => $incidencia->descripcion,
            'prioridad' => $this->getPrioridadLabel($incidencia->prioridad_value),
            'estado' => $this->getEstadoLabel($incidencia->estado_value),
            'tipo' => $incidencia->tipo ? $this->getTipoLabel($incidencia->tipo_value) : 'No especificado',
            'area_responsable' => $incidencia->area_responsable_actual ?: 'No asignada',
            'reportado_por' => $incidencia->reportadoPor->name ?? 'N/A',
            'asignado_a' => $incidencia->asignadoA->name ?? 'Sin asignar',
            'responsable_actual' => $incidencia->responsableActual->name ?? 'Sin asignar',
            'fecha_reporte' => $incidencia->fecha_reporte->format('d/m/Y H:i'),
            'fecha_resolucion' => $incidencia->fecha_resolucion ? $incidencia->fecha_resolucion->format('d/m/Y H:i') : 'Pendiente',
            'dias_transcurridos' => $this->calcularDiasTranscurridos($incidencia),
            'costo_soles' => $incidencia->costo_soles ? 'S/. ' . number_format($incidencia->costo_soles, 2) : '-',
            'costo_dolares' => $incidencia->costo_dolares ? '$ ' . number_format($incidencia->costo_dolares, 2) : '-',
            'transferencias' => $incidencia->contador_transferencias ?? 0,
        ];

        $valores = [];
        foreach ($this->columnas as $col) {
            if (isset($todosValores[$col])) {
                $valores[] = $todosValores[$col];
            }
        }

        return $valores;
    }

    private function getPrioridadLabel($prioridad): string
    {
        return match($prioridad) {
            'critica' => 'CRÍTICA',
            'alta' => 'ALTA',
            'media' => 'MEDIA',
            'baja' => 'BAJA',
            default => strtoupper($prioridad)
        };
    }

    private function getEstadoLabel($estado): string
    {
        return match($estado) {
            'abierta' => 'ABIERTA',
            'en_proceso' => 'EN PROCESO',
            'resuelta' => 'RESUELTA',
            'cerrada' => 'CERRADA',
            'cancelada' => 'CANCELADA',
            default => strtoupper($estado)
        };
    }

    private function getTipoLabel($tipo): string
    {
        return match($tipo) {
            'tecnica' => 'TÉCNICA',
            'administrativa' => 'ADMINISTRATIVA',
            'operativa' => 'OPERATIVA',
            'infraestructura' => 'INFRAESTRUCTURA',
            'legal' => 'LEGAL',
            'otra' => 'OTRA',
            default => strtoupper($tipo)
        };
    }

    private function calcularDiasTranscurridos($incidencia): string
    {
        if ($incidencia->fecha_resolucion) {
            $dias = $incidencia->fecha_reporte->diffInDays($incidencia->fecha_resolucion);
            return $dias . ' días';
        }

        $diasActuales = $incidencia->fecha_reporte->diffInDays(now());
        return $diasActuales . ' días (en curso)';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

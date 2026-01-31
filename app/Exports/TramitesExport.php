<?php

namespace App\Exports;

use App\Models\TramiteMtc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TramitesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = TramiteMtc::with(['estacion', 'responsable']);

        if (isset($this->filtros['buscar']) && $this->filtros['buscar']) {
            $buscar = $this->filtros['buscar'];
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('estacion', function($subQ) use ($buscar) {
                      $subQ->where('razon_social', 'LIKE', "%{$buscar}%")
                           ->orWhere('localidad', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if (isset($this->filtros['tipo_tramite']) && $this->filtros['tipo_tramite']) {
            $query->where('tipo_tramite', $this->filtros['tipo_tramite']);
        }

        if (isset($this->filtros['estado']) && $this->filtros['estado']) {
            $query->where('estado', $this->filtros['estado']);
        }

        if (isset($this->filtros['estacion_id']) && $this->filtros['estacion_id']) {
            $query->where('estacion_id', $this->filtros['estacion_id']);
        }

        return $query->orderBy('fecha_presentacion', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Nº Expediente',
            'Tipo de Trámite',
            'Estación',
            'Localidad',
            'Estado',
            'Fecha Presentación',
            'Fecha Respuesta',
            'Días Transcurridos',
            'Documentos %',
            'Responsable',
            'Costo (S/.)',
        ];
    }

    public function map($tramite): array
    {
        return [
            $tramite->numero_expediente ?? '-',
            $tramite->tipo_tramite ? (method_exists($tramite->tipo_tramite, 'getLabel') ? $tramite->tipo_tramite->getLabel() : $tramite->tipo_tramite) : '-',
            $tramite->estacion ? $tramite->estacion->razon_social : '-',
            $tramite->estacion ? $tramite->estacion->localidad : '-',
            $tramite->estado ? (method_exists($tramite->estado, 'getLabel') ? $tramite->estado->getLabel() : $tramite->estado) : '-',
            $tramite->fecha_presentacion ? $tramite->fecha_presentacion->format('d/m/Y') : '-',
            $tramite->fecha_respuesta ? $tramite->fecha_respuesta->format('d/m/Y') : 'Pendiente',
            $tramite->dias_transcurridos ?? '0',
            ($tramite->porcentaje_completud ?? 0) . '%',
            $tramite->responsable ? $tramite->responsable->name : 'Sin asignar',
            $tramite->costo_tramite ? 'S/. ' . number_format($tramite->costo_tramite, 2) : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
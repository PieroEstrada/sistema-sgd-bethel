<?php

namespace App\Exports;

use App\Models\Estacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstacionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        return ['codigo', 'razon_social', 'localidad', 'provincia', 'departamento', 'sector', 'banda', 'frecuencia', 'potencia_watts', 'estado', 'licencia_vencimiento'];
    }

    public function collection()
    {
        $query = Estacion::query();

        // Aplicar filtros
        if (isset($this->filtros['buscar']) && $this->filtros['buscar']) {
            $buscar = $this->filtros['buscar'];
            $query->where(function($q) use ($buscar) {
                $q->where('razon_social', 'LIKE', "%{$buscar}%")
                  ->orWhere('localidad', 'LIKE', "%{$buscar}%")
                  ->orWhere('provincia', 'LIKE', "%{$buscar}%")
                  ->orWhere('codigo', 'LIKE', "%{$buscar}%");
            });
        }

        if (isset($this->filtros['sector']) && $this->filtros['sector']) {
            $query->where('sector', $this->filtros['sector']);
        }

        if (isset($this->filtros['estado']) && $this->filtros['estado']) {
            $query->where('estado', $this->filtros['estado']);
        }

        if (isset($this->filtros['banda']) && $this->filtros['banda']) {
            $query->where('banda', $this->filtros['banda']);
        }

        if (isset($this->filtros['departamento']) && $this->filtros['departamento']) {
            $query->where('departamento', $this->filtros['departamento']);
        }

        if (isset($this->filtros['renovacion']) && $this->filtros['renovacion']) {
            $query->where('en_renovacion', $this->filtros['renovacion'] == '1');
        }

        if (isset($this->filtros['riesgo']) && $this->filtros['riesgo']) {
            $riesgo = strtoupper($this->filtros['riesgo']);
            if (in_array($riesgo, ['ALTO', 'MEDIO', 'SEGURO'])) {
                $query->where('licencia_riesgo', $riesgo);
            } elseif ($riesgo === 'SIN_EVALUAR' || $this->filtros['riesgo'] === 'sin_evaluar') {
                $query->whereNull('licencia_riesgo');
            }
        }

        if (isset($this->filtros['vencidas']) && $this->filtros['vencidas'] == '1') {
            $query->where('licencia_meses_restantes', '<', 0);
        }

        return $query->orderBy('localidad')->get();
    }

    public function headings(): array
    {
        $todosHeaders = [
            'codigo' => 'Código',
            'razon_social' => 'Razón Social',
            'localidad' => 'Localidad',
            'provincia' => 'Provincia',
            'departamento' => 'Departamento',
            'sector' => 'Sector',
            'banda' => 'Banda',
            'frecuencia' => 'Frecuencia (MHz)',
            'canal_tv' => 'Canal TV',
            'potencia_watts' => 'Potencia (W)',
            'estado' => 'Estado',
            'licencia_vencimiento' => 'Vence Licencia',
            'licencia_meses_restantes' => 'Meses Restantes',
            'licencia_riesgo' => 'Riesgo Licencia',
            'celular_encargado' => 'Celular Encargado',
            'latitud' => 'Latitud',
            'longitud' => 'Longitud',
            'en_renovacion' => 'En Renovación',
        ];

        $headers = [];
        foreach ($this->columnas as $col) {
            if (isset($todosHeaders[$col])) {
                $headers[] = $todosHeaders[$col];
            }
        }

        return $headers;
    }

    public function map($estacion): array
    {
        $todosValores = [
            'codigo' => $estacion->codigo,
            'razon_social' => $estacion->razon_social,
            'localidad' => $estacion->localidad,
            'provincia' => $estacion->provincia,
            'departamento' => $estacion->departamento,
            'sector' => is_object($estacion->sector) ? $estacion->sector->value : $estacion->sector,
            'banda' => is_object($estacion->banda) ? $estacion->banda->value : $estacion->banda,
            'frecuencia' => $this->getFrecuencia($estacion),
            'canal_tv' => $estacion->canal_tv ?: '-',
            'potencia_watts' => number_format($estacion->potencia_watts) . ' W',
            'estado' => $this->getEstadoLabel($estacion),
            'licencia_vencimiento' => $estacion->licencia_vencimiento ? $estacion->licencia_vencimiento->format('d/m/Y') : 'Sin fecha',
            'licencia_meses_restantes' => $estacion->licencia_meses_restantes !== null ? $estacion->licencia_meses_restantes . ' meses' : 'N/A',
            'licencia_riesgo' => $estacion->licencia_riesgo ?: 'Sin evaluar',
            'celular_encargado' => $estacion->celular_encargado ?: '-',
            'latitud' => $estacion->latitud ?: '-',
            'longitud' => $estacion->longitud ?: '-',
            'en_renovacion' => $estacion->en_renovacion ? 'Sí' : 'No',
        ];

        $valores = [];
        foreach ($this->columnas as $col) {
            if (isset($todosValores[$col])) {
                $valores[] = $todosValores[$col];
            }
        }

        return $valores;
    }

    private function getFrecuencia($estacion): string
    {
        $bandaValue = is_object($estacion->banda) ? $estacion->banda->value : $estacion->banda;

        if (in_array($bandaValue, ['VHF', 'UHF'])) {
            return $estacion->canal_tv ? 'Canal ' . $estacion->canal_tv : '-';
        }

        return $estacion->frecuencia ? $estacion->frecuencia . ' MHz' : '-';
    }

    private function getEstadoLabel($estacion): string
    {
        $estadoValue = is_object($estacion->estado) ? $estacion->estado->value : $estacion->estado;

        return match($estadoValue) {
            'A.A' => 'Al Aire',
            'F.A' => 'Fuera del Aire',
            'N.I' => 'No Instalada',
            default => $estadoValue
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

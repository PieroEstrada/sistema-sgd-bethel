<?php

namespace App\Console\Commands;

use App\Enums\EstadoEstacion;
use App\Models\Estacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportRenovacionesACB extends Command
{
    protected $signature = 'import:renovaciones-acb {--path= : Ruta del Excel. Si no se indica, usa storage/app/imports/Renovaciones ACB.xlsx}';
    protected $description = 'Importa Renovaciones ACB desde Excel y actualiza estaciones (licencia, estado, potencia, etc.)';

    public function handle(): int
    {
        $defaultPath = storage_path('app/imports/Renovaciones ACB.xlsx');
        $path = $this->option('path') ?: $defaultPath;

        if (!file_exists($path)) {
            $this->error("No se encontró el archivo: {$path}");
            $this->line("Sugerencia: colócalo en storage/app/imports/ con el nombre 'Renovaciones ACB.xlsx' o usa --path=");
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $this->error("El Excel no tiene filas suficientes.");
            return self::FAILURE;
        }

        // 1) Encabezados
        $headerRow = $rows[1]; // primera fila
        $originalHeaders = array_values($headerRow);

        // Mapa: header normalizado -> letra de columna (A, B, C...)
        $headerMap = [];
        foreach ($headerRow as $colLetter => $headerText) {
            $norm = $this->normHeader($headerText);
            if ($norm === '') continue;
            // si hay duplicados (ej: Nº repetido), conservamos el primero,
            // y el segundo lo guardamos si quieres, pero no lo exigimos.
            if (!isset($headerMap[$norm])) {
                $headerMap[$norm] = $colLetter;
            }
        }

        // 2) Columnas requeridas (NORMALIZADAS)
        $required = [
            'LOCALIDAD',
            'DEPARTAMENTO',
            'BANDA',
            'FREC',
            'POTENCIA',
            'ESTADO',
            'SITUACION DE LA LICENCIA',
            'RVM',
            'FECHA DE VENCIMIENTO',
        ];

        $missing = [];
        foreach ($required as $req) {
            if (!isset($headerMap[$this->normHeader($req)])) {
                $missing[] = $req;
            }
        }

        if (!empty($missing)) {
            $this->error("Faltan columnas requeridas: " . implode(', ', $missing));
            $this->line("Encabezados detectados: " . implode(' | ', $originalHeaders));
            return self::FAILURE;
        }

        // 3) Import
        $updated = 0;
        $notFound = 0;
        $skipped = 0;

        // Columna opcional: Nº (ID externo). Como está duplicado, tomamos la primera que aparezca.
        $colExternal = $headerMap[$this->normHeader('N')] ?? $headerMap[$this->normHeader('NO')] ?? $headerMap[$this->normHeader('Nº')] ?? null;

        $colLocalidad  = $headerMap[$this->normHeader('LOCALIDAD')];
        $colDepto      = $headerMap[$this->normHeader('DEPARTAMENTO')];
        $colBanda      = $headerMap[$this->normHeader('BANDA')];
        $colFrec       = $headerMap[$this->normHeader('FREC')];
        $colPotencia   = $headerMap[$this->normHeader('POTENCIA')];
        $colEstado     = $headerMap[$this->normHeader('ESTADO')];
        $colSituacion  = $headerMap[$this->normHeader('SITUACION DE LA LICENCIA')];
        $colRvm        = $headerMap[$this->normHeader('RVM')];
        $colVence      = $headerMap[$this->normHeader('FECHA DE VENCIMIENTO')];

        for ($i = 2; $i <= count($rows); $i++) {
            $r = $rows[$i];

            $localidad = trim((string)($r[$colLocalidad] ?? ''));
            $depto     = trim((string)($r[$colDepto] ?? ''));
            $banda     = strtoupper(trim((string)($r[$colBanda] ?? '')));
            $frecRaw   = $r[$colFrec] ?? null;

            if ($localidad === '' || $depto === '' || $banda === '' || $frecRaw === null || $frecRaw === '') {
                $skipped++;
                continue;
            }

            $frecuencia = is_numeric($frecRaw) ? (float)$frecRaw : (float)str_replace(',', '.', (string)$frecRaw);

            // Lookup por índice compuesto (localidad, departamento, banda, frecuencia)
            $estacion = Estacion::query()
                ->whereRaw('LOWER(localidad) = ?', [mb_strtolower($localidad)])
                ->whereRaw('LOWER(departamento) = ?', [mb_strtolower($depto)])
                ->where('banda', $banda)
                ->whereBetween('frecuencia', [$frecuencia - 0.01, $frecuencia + 0.01])
                ->first();

            if (!$estacion) {
                $notFound++;
                continue;
            }

            // Actualizaciones
            if ($colExternal && isset($r[$colExternal]) && $r[$colExternal] !== null && $r[$colExternal] !== '') {
                $estacion->station_external_id = trim((string)$r[$colExternal]);
            }

            // Potencia
            $potRaw = $r[$colPotencia] ?? null;
            if ($potRaw !== null && $potRaw !== '') {
                $estacion->potencia_watts = is_numeric($potRaw) ? (int)$potRaw : (int)preg_replace('/\D+/', '', (string)$potRaw);
            }

            // Estado (solo 3 permitidos)
            $estadoExcel = trim((string)($r[$colEstado] ?? ''));
            $estacion->estado = $this->mapEstadoToEnumValue($estadoExcel);

            // Licencia
            $estacion->licencia_situacion = trim((string)($r[$colSituacion] ?? ''));
            $estacion->licencia_rvm = trim((string)($r[$colRvm] ?? ''));

            // Fecha vencimiento
            $venceRaw = $r[$colVence] ?? null;
            $vence = $this->parseExcelDate($venceRaw);
            $estacion->licencia_vence = $vence;

            // Riesgo (si hay fecha). licencia_meses_restantes se calcula dinámicamente via accessor.
            if ($vence) {
                $months = Carbon::today()->diffInMonths($vence, false); // negativo si ya venció

                if ($months <= 0) {
                    $estacion->riesgo_licencia = 'ALTO';
                } elseif ($months <= 6) {
                    $estacion->riesgo_licencia = 'MEDIO';
                } else {
                    $estacion->riesgo_licencia = 'SEGURO';
                }
            } else {
                $estacion->riesgo_licencia = null;
            }

            $estacion->save();
            $updated++;
        }

        $this->info("Import terminado.");
        $this->line("Actualizadas: {$updated}");
        $this->line("No encontradas (por lookup): {$notFound}");
        $this->line("Saltadas (filas incompletas): {$skipped}");

        return self::SUCCESS;
    }

    private function normHeader($value): string
    {
        $s = trim((string)$value);
        if ($s === '') return '';

        // quitar tildes
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        $s = strtoupper($s);

        // normalizar símbolos comunes
        $s = str_replace(['º', '°'], '', $s);

        // quitar puntos, dobles espacios, etc.
        $s = preg_replace('/[\.]+/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        // casos típicos del excel: "FREC." -> "FREC"
        $s = trim($s);

        return $s;
    }

    private function mapEstadoToEnumValue(string $estadoExcel): string
    {
        $e = $this->normHeader($estadoExcel);

        // variantes
        if ($e === 'AL AIRE' || $e === 'ALAIRE') return EstadoEstacion::AL_AIRE->value;

        // tu excel tiene "FUERA AL AIRE"
        if ($e === 'FUERA AL AIRE' || $e === 'FUERA DEL AIRE' || $e === 'FUERADELAIRE') {
            return EstadoEstacion::FUERA_DEL_AIRE->value;
        }

        if ($e === 'NO INSTALADA' || $e === 'NOINSTALADA') return EstadoEstacion::NO_INSTALADA->value;

        // fallback seguro: si viene vacío o raro, no instalamos
        return EstadoEstacion::NO_INSTALADA->value;
    }

    private function parseExcelDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Si PhpSpreadsheet lo da como DateTime
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        // Si viene como serial de Excel
        if (is_numeric($value)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                return Carbon::instance($dt)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Si viene como string
        $s = trim((string)$value);
        if ($s === '') return null;

        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

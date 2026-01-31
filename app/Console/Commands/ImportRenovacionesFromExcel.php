<?php

namespace App\Console\Commands;

use App\Enums\Banda;
use App\Enums\RiesgoLicencia;
use App\Models\Estacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportRenovacionesFromExcel extends Command
{
    protected $signature = 'estaciones:import-renovaciones
                            {--path= : Ruta personalizada al archivo Excel}
                            {--sheet= : Nombre de la hoja a usar}
                            {--dry-run : Simular importación sin guardar datos}';

    protected $description = 'Importa fechas de vencimiento de licencias desde Renovaciones ACB.xlsx';

    private int $matched = 0;
    private int $notMatched = 0;
    private int $updated = 0;
    private array $noMatchLog = [];

    private const DEFAULT_PATH = 'C:\\xampp\\htdocs\\bethel-sgd\\data\\Renovaciones ACB.xlsx';
    private const DEFAULT_SHEET = 'informe de Gestión 2021-2025';

    public function handle(): int
    {
        $path = $this->option('path') ?: self::DEFAULT_PATH;
        $sheetName = $this->option('sheet') ?: self::DEFAULT_SHEET;
        $dryRun = $this->option('dry-run');

        if (!file_exists($path)) {
            $this->error("El archivo no existe: {$path}");
            return Command::FAILURE;
        }

        $this->info("Importando renovaciones desde: {$path}");
        $this->info("Hoja: {$sheetName}");
        if ($dryRun) {
            $this->warn('Modo simulación activado - no se guardarán datos');
        }

        try {
            $data = $this->loadExcelData($path, $sheetName);

            if (empty($data)) {
                $this->error('No se encontraron datos en la hoja especificada');
                return Command::FAILURE;
            }

            $this->info("Filas encontradas: " . count($data));

            $progressBar = $this->output->createProgressBar(count($data));
            $progressBar->start();

            DB::beginTransaction();

            foreach ($data as $row) {
                $this->processRow($row, $dryRun);
                $progressBar->advance();
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            $progressBar->finish();
            $this->newLine();

            $this->displaySummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error durante la importación: {$e->getMessage()}");
            Log::error("ImportRenovaciones error: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        }
    }

    private function loadExcelData(string $path, string $sheetName): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            throw new \Exception("No se encontró la hoja: {$sheetName}");
        }

        $data = $sheet->toArray(null, true, true, true);

        return $this->parseSheetData($data);
    }

    private function parseSheetData(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        // Encontrar fila de encabezados
        $headerRow = null;
        $headerIndex = 0;

        foreach ($data as $index => $row) {
            $rowText = mb_strtoupper(implode('', array_filter($row)));
            if (str_contains($rowText, 'LOCALIDAD') && str_contains($rowText, 'VENCIMIENTO')) {
                $headerRow = $row;
                $headerIndex = $index;
                break;
            }
        }

        if (!$headerRow) {
            // Asumir primera fila como encabezado
            $headerRow = reset($data);
            $headerIndex = key($data);
        }

        // Normalizar encabezados
        $headers = [];
        foreach ($headerRow as $key => $value) {
            $headers[$key] = $this->normalizeHeaderName($value);
        }

        // Convertir datos
        $result = [];
        foreach ($data as $index => $row) {
            if ($index <= $headerIndex) {
                continue;
            }

            // Saltar filas vacías
            $rowText = trim(implode('', array_filter($row)));
            if (empty($rowText)) {
                continue;
            }

            $item = [];
            foreach ($headers as $colKey => $headerName) {
                if (!empty($headerName)) {
                    $item[$headerName] = $row[$colKey] ?? null;
                }
            }

            // Solo agregar si tiene localidad y fecha de vencimiento
            if (!empty($item['localidad'])) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function normalizeHeaderName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        $name = mb_strtoupper(trim($name));

        $mappings = [
            'LOCALIDAD' => 'localidad',
            'DEPARTAMENTO' => 'departamento',
            'DPTO' => 'departamento',
            'BANDA' => 'banda',
            'FREC' => 'frecuencia',
            'FREC.' => 'frecuencia',
            'FRECUENCIA' => 'frecuencia',
            'POTENCIA' => 'potencia',
            'ESTADO' => 'estado',
            'SITUACIÓN DE LA LICENCIA' => 'situacion',
            'SITUACION DE LA LICENCIA' => 'situacion',
            'RVM' => 'rvm',
            'FECHA DE VENCIMIENTO' => 'fecha_vencimiento',
            'VENCIMIENTO' => 'fecha_vencimiento',
        ];

        foreach ($mappings as $pattern => $normalized) {
            if ($name === $pattern || str_starts_with($name, $pattern)) {
                return $normalized;
            }
        }

        return mb_strtolower($name);
    }

    private function processRow(array $row, bool $dryRun): void
    {
        $localidad = $this->cleanString($row['localidad'] ?? null);
        $departamento = $this->cleanString($row['departamento'] ?? null);
        $bandaRaw = $this->cleanString($row['banda'] ?? null);
        $frecuenciaRaw = $row['frecuencia'] ?? null;
        $fechaVencimientoRaw = $row['fecha_vencimiento'] ?? null;
        $rvm = $this->cleanString($row['rvm'] ?? null);
        $situacion = $this->cleanString($row['situacion'] ?? null);

        if (empty($localidad)) {
            return;
        }

        // Parsear fecha de vencimiento
        $fechaVencimiento = $this->parseFecha($fechaVencimientoRaw);

        if (!$fechaVencimiento) {
            $this->noMatchLog[] = "Sin fecha válida: {$localidad} ({$departamento})";
            $this->notMatched++;
            return;
        }

        // Parsear banda y frecuencia
        $banda = $this->parseBanda($bandaRaw);
        $frecuencia = $this->parseFrecuencia($frecuenciaRaw);

        // Buscar estación
        $estacion = $this->findEstacion($localidad, $departamento, $banda, $frecuencia);

        if (!$estacion) {
            $this->noMatchLog[] = "No match: {$localidad} | {$departamento} | {$bandaRaw} | {$frecuenciaRaw}";
            $this->notMatched++;
            return;
        }

        $this->matched++;

        // Calcular riesgo
        $mesesRestantes = now()->diffInMonths($fechaVencimiento, false);
        if ($fechaVencimiento < now()) {
            $mesesRestantes = -abs($mesesRestantes);
        }
        $riesgo = RiesgoLicencia::calcularDesdesMeses((int) $mesesRestantes);

        // Preparar datos
        $updateData = [
            'licencia_vencimiento' => $fechaVencimiento,
            'licencia_rvm' => $rvm,
            'licencia_situacion' => $situacion,
            'licencia_meses_restantes' => (int) $mesesRestantes,
            'licencia_riesgo' => $riesgo?->value,
        ];

        if (!$dryRun) {
            $estacion->update($updateData);
            $this->updated++;
        } else {
            $this->updated++;
        }
    }

    private function findEstacion(string $localidad, ?string $departamento, ?Banda $banda, ?float $frecuencia): ?Estacion
    {
        // Búsqueda exacta primero
        $query = Estacion::query();

        // Normalizar localidad para búsqueda
        $localidadNorm = $this->normalizeLocalidad($localidad);

        // Intentar match exacto por localidad + departamento + banda + frecuencia
        if ($departamento && $banda && $frecuencia) {
            $estacion = Estacion::where(function ($q) use ($localidad, $localidadNorm) {
                    $q->where('localidad', $localidad)
                      ->orWhere('localidad', 'LIKE', "%{$localidadNorm}%");
                })
                ->where('departamento', 'LIKE', "%{$departamento}%")
                ->where('banda', $banda)
                ->where(function ($q) use ($frecuencia, $banda) {
                    if ($banda->esTv()) {
                        $q->where('canal_tv', (int) $frecuencia);
                    } else {
                        // Tolerancia de ±0.2 MHz para FM/AM
                        $q->whereBetween('frecuencia', [$frecuencia - 0.2, $frecuencia + 0.2]);
                    }
                })
                ->first();

            if ($estacion) {
                return $estacion;
            }
        }

        // Match por localidad + departamento + banda (sin frecuencia exacta)
        if ($departamento && $banda) {
            $estacion = Estacion::where(function ($q) use ($localidad, $localidadNorm) {
                    $q->where('localidad', $localidad)
                      ->orWhere('localidad', 'LIKE', "%{$localidadNorm}%");
                })
                ->where('departamento', 'LIKE', "%{$departamento}%")
                ->where('banda', $banda)
                ->first();

            if ($estacion) {
                return $estacion;
            }
        }

        // Match por localidad + departamento solamente
        if ($departamento) {
            $estacion = Estacion::where(function ($q) use ($localidad, $localidadNorm) {
                    $q->where('localidad', $localidad)
                      ->orWhere('localidad', 'LIKE', "%{$localidadNorm}%");
                })
                ->where('departamento', 'LIKE', "%{$departamento}%")
                ->first();

            if ($estacion) {
                return $estacion;
            }
        }

        // Match por localidad solamente (último recurso)
        return Estacion::where(function ($q) use ($localidad, $localidadNorm) {
                $q->where('localidad', $localidad)
                  ->orWhere('localidad', 'LIKE', "%{$localidadNorm}%");
            })
            ->first();
    }

    private function normalizeLocalidad(string $localidad): string
    {
        // Remover tildes y caracteres especiales
        $localidad = mb_strtolower(trim($localidad));
        $localidad = strtr($localidad, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u'
        ]);
        return $localidad;
    }

    private function parseBanda(?string $value): ?Banda
    {
        if (empty($value)) {
            return null;
        }

        $value = mb_strtoupper(trim($value));

        return match ($value) {
            'FM' => Banda::FM,
            'AM', 'OM' => Banda::AM,
            'VHF', 'TV-VHF' => Banda::VHF,
            'UHF', 'TV-UHF' => Banda::UHF,
            default => null,
        };
    }

    private function parseFrecuencia($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace(',', '.', trim($value));
        $value = preg_replace('/[^0-9.]/', '', $value);

        return $value !== '' ? (float) $value : null;
    }

    private function parseFecha($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        // Si es un número (Excel serial date)
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            } catch (\Exception $e) {
                return null;
            }
        }

        // Intentar varios formatos de fecha
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim($value));
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Intentar parse natural
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cleanString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== RESUMEN DE IMPORTACIÓN DE RENOVACIONES ===');
        $this->info("Estaciones encontradas (match): {$this->matched}");
        $this->info("Estaciones actualizadas: {$this->updated}");
        $this->warn("Sin match: {$this->notMatched}");

        if (!empty($this->noMatchLog)) {
            $this->newLine();
            $this->warn('Registros sin match (primeros 20):');
            foreach (array_slice($this->noMatchLog, 0, 20) as $log) {
                $this->line("  - {$log}");
            }
            if (count($this->noMatchLog) > 20) {
                $this->line("  ... y " . (count($this->noMatchLog) - 20) . " más");
            }

            // Guardar log completo
            $logFile = storage_path('logs/renovaciones_no_match_' . date('Y-m-d_His') . '.txt');
            file_put_contents($logFile, implode("\n", $this->noMatchLog));
            $this->info("Log completo guardado en: {$logFile}");
        }

        // Mostrar estadísticas de riesgo
        $this->newLine();
        $this->info('=== ESTADÍSTICAS DE RIESGO ===');
        $this->table(
            ['Riesgo', 'Cantidad'],
            [
                ['Alto (<12 meses)', Estacion::where('licencia_riesgo', 'ALTO')->count()],
                ['Medio (12-24 meses)', Estacion::where('licencia_riesgo', 'MEDIO')->count()],
                ['Seguro (>24 meses)', Estacion::where('licencia_riesgo', 'SEGURO')->count()],
                ['Sin fecha', Estacion::whereNull('licencia_vencimiento')->count()],
            ]
        );
    }
}

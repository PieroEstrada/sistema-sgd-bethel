<?php

namespace App\Console\Commands;

use App\Enums\Banda;
use App\Enums\EstadoEstacion;
use App\Enums\NivelFA;
use App\Enums\Sector;
use App\Models\Estacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEstacionesFromExcel extends Command
{
    protected $signature = 'estaciones:import-excel
                            {--path= : Ruta personalizada al archivo Excel}
                            {--dry-run : Simular importación sin guardar datos}';

    protected $description = 'Importa estaciones desde el archivo Excel SECTORIZACION - 2025.xlsx';

    private int $created = 0;
    private int $updated = 0;
    private array $errors = [];
    private array $sectorStations = [];

    private const DEFAULT_PATH = 'C:\\xampp\\htdocs\\bethel-sgd\\data\\SECTORIZACION - 2025.xlsx';

    public function handle(): int
    {
        $path = $this->option('path') ?: self::DEFAULT_PATH;
        $dryRun = $this->option('dry-run');

        if (!file_exists($path)) {
            $this->error("El archivo no existe: {$path}");
            return Command::FAILURE;
        }

        $this->info("Importando estaciones desde: {$path}");
        if ($dryRun) {
            $this->warn('Modo simulación activado - no se guardarán datos');
        }

        try {
            // Cargar hojas de sectores primero para validación
            $this->loadSectorSheets($path);

            // Cargar y procesar hoja principal ESTACIONES
            $this->processMainSheet($path, $dryRun);

            $this->displaySummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error durante la importación: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function loadSectorSheets(string $path): void
    {
        $this->info('Cargando hojas de sectores para validación...');

        foreach (['NORTE', 'CENTRO', 'SUR'] as $sector) {
            try {
                $sheets = $this->getSheetByName($path, $sector);

                if ($sheets) {
                    foreach ($sheets as $row) {
                        $key = $this->buildStationKey($row);
                        if ($key) {
                            $this->sectorStations[$key] = $sector;
                        }
                    }
                    $this->info("  - Hoja {$sector}: " . count($sheets) . " filas cargadas");
                }
            } catch (\Exception $e) {
                $this->warn("  - No se pudo cargar hoja {$sector}: {$e->getMessage()}");
            }
        }
    }

    private function getSheetByName(string $path, string $sheetName): ?array
    {
        // Usar PhpSpreadsheet (versión moderna)
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            return null;
        }

        $data = $sheet->toArray(null, true, true, true);

        // Detectar fila de encabezados y convertir a array asociativo
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
            $rowText = strtoupper(implode('', array_filter($row)));
            if (str_contains($rowText, 'LOCALIDAD') || str_contains($rowText, 'DPTO') || str_contains($rowText, 'FREC')) {
                $headerRow = $row;
                $headerIndex = $index;
                break;
            }
        }

        if (!$headerRow) {
            return [];
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

            // Solo agregar si tiene localidad
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

        // Mapeo de nombres de columnas
        $mappings = [
            'N°' => 'numero',
            'NO.' => 'numero',
            'NUM' => 'numero',
            'Nº' => 'numero',
            'LOCALIDAD' => 'localidad',
            'DPTO' => 'departamento',
            'DPTO.' => 'departamento',
            'DEPARTAMENTO' => 'departamento',
            'BANDA' => 'banda',
            'FREC' => 'frecuencia',
            'FREC.' => 'frecuencia',
            'FRECUENCIA' => 'frecuencia',
            'FREC/CANAL' => 'frecuencia',
            'CANAL' => 'frecuencia',
            'POT' => 'potencia',
            'POTENCIA' => 'potencia',
            'POT.' => 'potencia',
            'ESTADO' => 'estado',
            'F.A' => 'estado',
            'FA' => 'estado',
            'PRESB' => 'presbitero',
            'PRESB.' => 'presbitero',
            'PRESBÍTERO' => 'presbitero',
            'PRESBITERO' => 'presbitero',
            'INCIDENCIAS' => 'incidencias',
            'INCIDENCIA' => 'incidencias',
            'RESPONSABLE' => 'responsable',
            'NIVEL' => 'nivel',
            'PRESUP S/.' => 'presupuesto_soles',
            'PRESUP S/' => 'presupuesto_soles',
            'PRESUP. S/.' => 'presupuesto_soles',
            'PRESUPUESTO S/.' => 'presupuesto_soles',
            'S/.' => 'presupuesto_soles',
            'PRESUP $' => 'presupuesto_dolares',
            'PRESUP. $' => 'presupuesto_dolares',
            'PRESUPUESTO $' => 'presupuesto_dolares',
            '$' => 'presupuesto_dolares',
            'MOTIVO' => 'motivo',
            'OBSERVACION' => 'observacion',
            'OBSERVACIONES' => 'observacion',
            'OBS' => 'observacion',
            'LAT' => 'latitud',
            'LATITUD' => 'latitud',
            'LNG' => 'longitud',
            'LONG' => 'longitud',
            'LONGITUD' => 'longitud',
            'GMS' => 'gms',
            'COORD GMS' => 'gms',
            'SECTOR' => 'sector',
            'ID' => 'external_id',
            'CODIGO' => 'external_id',
        ];

        foreach ($mappings as $pattern => $normalized) {
            if ($name === $pattern || str_starts_with($name, $pattern)) {
                return $normalized;
            }
        }

        return mb_strtolower($name);
    }

    private function processMainSheet(string $path, bool $dryRun): void
    {
        $this->info('Procesando hoja principal ESTACIONES...');

        $mainData = $this->getSheetByName($path, 'ESTACIONES');

        if (!$mainData) {
            $this->error('No se encontró la hoja ESTACIONES');
            return;
        }

        $this->info("Filas encontradas: " . count($mainData));

        $progressBar = $this->output->createProgressBar(count($mainData));
        $progressBar->start();

        DB::beginTransaction();
        try {
            foreach ($mainData as $index => $row) {
                $this->processRow($row, $index + 1, $dryRun);
                $progressBar->advance();
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $progressBar->finish();
        $this->newLine();
    }

    private function processRow(array $row, int $rowNumber, bool $dryRun): void
    {
        try {
            // Validar campos requeridos
            $localidad = $this->cleanString($row['localidad'] ?? null);
            $departamento = $this->cleanString($row['departamento'] ?? null);
            $bandaRaw = $this->cleanString($row['banda'] ?? null);
            $frecuenciaRaw = $row['frecuencia'] ?? null;

            if (empty($localidad)) {
                return; // Fila vacía
            }

            if (empty($departamento) || empty($bandaRaw)) {
                $this->addError($rowNumber, "Faltan datos obligatorios: DPTO o BANDA");
                return;
            }

            // Parsear banda
            $banda = $this->parseBanda($bandaRaw);
            if (!$banda) {
                $this->addError($rowNumber, "Banda no reconocida: {$bandaRaw}");
                return;
            }

            // Parsear frecuencia/canal
            $frecuencia = $this->parseFrecuencia($frecuenciaRaw);
            $canalTv = $banda->esTv() ? (int) $frecuencia : null;
            $frecuenciaNum = $banda->esRadio() ? $frecuencia : null;

            // Determinar sector
            $sector = $this->determineSector($row, $localidad, $departamento, $banda, $frecuencia);
            if (!$sector) {
                $this->addError($rowNumber, "No se pudo determinar el sector para {$localidad}");
                return;
            }

            // Parsear estado
            $estado = $this->parseEstado($row['estado'] ?? null);

            // Buscar estación existente por clave compuesta
            $estacion = Estacion::where('localidad', $localidad)
                ->where('departamento', $departamento)
                ->where('banda', $banda)
                ->where(function ($query) use ($frecuenciaNum, $canalTv, $banda) {
                    if ($banda->esTv()) {
                        $query->where('canal_tv', $canalTv);
                    } else {
                        $query->where('frecuencia', $frecuenciaNum);
                    }
                })
                ->first();

            // Preparar datos
            $incidencias = $this->cleanString($row['incidencias'] ?? null);
            $observacion = $this->cleanString($row['observacion'] ?? null);
            // Combinar incidencias y observación si ambas existen
            $observacionesFinal = $observacion;
            if ($incidencias && $observacion) {
                $observacionesFinal = $incidencias . "\n\n" . $observacion;
            } elseif ($incidencias) {
                $observacionesFinal = $incidencias;
            }

            $data = [
                'localidad' => $localidad,
                'departamento' => $departamento,
                'provincia' => $this->cleanString($row['provincia'] ?? $departamento),
                'banda' => $banda,
                'frecuencia' => $frecuenciaNum,
                'canal_tv' => $canalTv,
                'sector' => $sector,
                'estado' => $estado,
                'potencia_watts' => $this->parsePotencia($row['potencia'] ?? null),
                'presbitero_id' => $this->parsePresbiteroId($row['presbitero'] ?? null),
                'responsable_fa' => $this->cleanString($row['responsable'] ?? null),
                'nivel_fa' => $this->parseNivel($row['nivel'] ?? null),
                'presupuesto_fa' => $this->parseDecimal($row['presupuesto_soles'] ?? null),
                'presupuesto_dolares' => $this->parseDecimal($row['presupuesto_dolares'] ?? null),
                'diagnostico_fa' => $this->cleanString($row['motivo'] ?? null),
                'observaciones' => $observacionesFinal,
                'latitud' => $this->parseCoordinate($row['latitud'] ?? null),
                'longitud' => $this->parseCoordinate($row['longitud'] ?? null),
                'coordenadas_gms' => $this->cleanString($row['gms'] ?? null),
                'station_external_id' => $this->parseExternalId($row['external_id'] ?? $row['numero'] ?? null),
            ];

            // Generar código si es nueva
            if (!$estacion) {
                $data['codigo'] = $this->generateCodigo($banda, $sector, $localidad);
                $data['razon_social'] = "Radio/TV Bethel - {$localidad}";
                $data['activa'] = true;
            }

            if ($dryRun) {
                $estacion ? $this->updated++ : $this->created++;
                return;
            }

            if ($estacion) {
                $estacion->update($data);
                $this->updated++;
            } else {
                Estacion::create($data);
                $this->created++;
            }
        } catch (\Exception $e) {
            $this->addError($rowNumber, $e->getMessage());
        }
    }

    private function buildStationKey(array $row): ?string
    {
        $localidad = $this->cleanString($row['localidad'] ?? null);
        $departamento = $this->cleanString($row['departamento'] ?? null);
        $banda = $this->cleanString($row['banda'] ?? null);
        $frecuencia = $this->parseFrecuencia($row['frecuencia'] ?? null);

        if (empty($localidad) || empty($banda)) {
            return null;
        }

        return mb_strtoupper("{$localidad}|{$departamento}|{$banda}|{$frecuencia}");
    }

    private function determineSector(array $row, string $localidad, string $departamento, Banda $banda, ?float $frecuencia): ?Sector
    {
        // 1. Primero verificar si viene en los datos
        $sectorRaw = $this->cleanString($row['sector'] ?? null);
        if ($sectorRaw) {
            $sector = $this->parseSector($sectorRaw);
            if ($sector) {
                return $sector;
            }
        }

        // 2. Buscar en las hojas de sectores
        $key = mb_strtoupper("{$localidad}|{$departamento}|{$banda->value}|{$frecuencia}");
        if (isset($this->sectorStations[$key])) {
            return $this->parseSector($this->sectorStations[$key]);
        }

        // 3. Intentar determinar por departamento
        $sector = Sector::getSectorPorDepartamento($departamento);
        if ($sector) {
            return $sector;
        }

        // 4. Intentar con departamento normalizado
        $deptoNormalizado = $this->normalizeDepartamento($departamento);
        return Sector::getSectorPorDepartamento($deptoNormalizado);
    }

    private function parseSector(?string $value): ?Sector
    {
        if (empty($value)) {
            return null;
        }

        $value = mb_strtoupper(trim($value));

        return match ($value) {
            'NORTE', 'N' => Sector::NORTE,
            'CENTRO', 'C' => Sector::CENTRO,
            'SUR', 'S' => Sector::SUR,
            default => null,
        };
    }

    private function parseBanda(?string $value): ?Banda
    {
        if (empty($value)) {
            return null;
        }

        $value = mb_strtoupper(trim($value));

        return match ($value) {
            'FM' => Banda::FM,
            'AM', 'OM' => Banda::AM, // OM (Onda Media) = AM
            'VHF', 'TV-VHF' => Banda::VHF,
            'UHF', 'TV-UHF' => Banda::UHF,
            default => null,
        };
    }

    private function parseEstado(?string $value): EstadoEstacion
    {
        if (empty($value)) {
            return EstadoEstacion::NO_INSTALADA;
        }

        $value = mb_strtoupper(trim($value));

        return match (true) {
            str_contains($value, 'A.A'), $value === 'AA', $value === 'AL AIRE' => EstadoEstacion::AL_AIRE,
            str_contains($value, 'F.A'), $value === 'FA', $value === 'FUERA' => EstadoEstacion::FUERA_DEL_AIRE,
            str_contains($value, 'N.I'), $value === 'NI', $value === 'NO INSTALADA' => EstadoEstacion::NO_INSTALADA,
            // MANT/MTTO ya no existe, tratar como FUERA_DEL_AIRE
            str_contains($value, 'MTTO'), str_contains($value, 'MANT'), $value === 'MANTENIMIENTO' => EstadoEstacion::FUERA_DEL_AIRE,
            default => EstadoEstacion::NO_INSTALADA,
        };
    }

    private function parseNivel(?string $value): ?NivelFA
    {
        if (empty($value)) {
            return null;
        }

        $value = mb_strtoupper(trim($value));

        return match (true) {
            str_contains($value, 'CRIT'), str_contains($value, 'NIVEL 1'), $value === '1', $value === 'ALTO' => NivelFA::CRITICO,
            str_contains($value, 'MED'), str_contains($value, 'NIVEL 2'), $value === '2' => NivelFA::MEDIO,
            str_contains($value, 'BAJ'), str_contains($value, 'NIVEL 3'), $value === '3' => NivelFA::BAJO,
            default => null,
        };
    }

    private function parsePresbiteroId($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $id = null;
        if (is_numeric($value)) {
            $id = (int) $value;
        } else {
            // Extraer número si está en texto
            $value = preg_replace('/[^0-9]/', '', trim($value));
            $id = $value !== '' ? (int) $value : null;
        }

        // Validar que el presbitero existe en la base de datos
        if ($id !== null) {
            $exists = \App\Models\Presbitero::find($id);
            if (!$exists) {
                return null; // No asignar si el presbitero no existe
            }
        }

        return $id;
    }

    private function parseFrecuencia($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Convertir coma a punto (106,7 -> 106.7)
        $value = str_replace(',', '.', trim($value));

        // Remover caracteres no numéricos excepto punto
        $value = preg_replace('/[^0-9.]/', '', $value);

        return $value !== '' ? (float) $value : null;
    }

    private function parsePotencia($value): int
    {
        if (empty($value)) {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        // Extraer número
        $value = preg_replace('/[^0-9]/', '', trim($value));

        return $value !== '' ? (int) $value : 0;
    }

    private function parseDecimal($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Convertir coma a punto y limpiar
        $value = str_replace(',', '.', trim($value));
        $value = preg_replace('/[^0-9.]/', '', $value);

        return $value !== '' ? (float) $value : null;
    }

    private function parseCoordinate($value): ?float
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Intentar parsear GMS a decimal
        $value = trim($value);

        // Si ya es decimal
        if (preg_match('/^-?\d+\.?\d*$/', $value)) {
            return (float) $value;
        }

        // Formato GMS: -12° 2' 53.4" o similar
        if (preg_match('/(-?\d+)[°\s]+(\d+)[\'′\s]+(\d+\.?\d*)[\"″]?/', $value, $matches)) {
            $degrees = (float) $matches[1];
            $minutes = (float) $matches[2];
            $seconds = (float) $matches[3];

            $sign = $degrees < 0 ? -1 : 1;
            return $sign * (abs($degrees) + $minutes / 60 + $seconds / 3600);
        }

        return null;
    }

    private function parseExternalId($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Si ya tiene formato R-X, C-X, E-X, etc.
        if (preg_match('/^[RCE]\s*-?\s*\d+/i', $value)) {
            // Normalizar: "R - 1" -> "R-1"
            return preg_replace('/\s+/', '', $value);
        }

        // Si es solo un número, devolverlo como string
        if (is_numeric($value)) {
            return (string) $value;
        }

        return $value;
    }

    private function cleanString($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function normalizeDepartamento(string $departamento): string
    {
        // Normalizar tildes y mayúsculas
        $departamento = mb_strtoupper(trim($departamento));

        // Mapeo de departamentos
        $mappings = [
            'LIMA' => 'Lima',
            'AREQUIPA' => 'Arequipa',
            'CUSCO' => 'Cusco',
            'CUZCO' => 'Cusco',
            'PIURA' => 'Piura',
            'LA LIBERTAD' => 'La Libertad',
            'LAMBAYEQUE' => 'Lambayeque',
            'CAJAMARCA' => 'Cajamarca',
            'JUNIN' => 'Junín',
            'JUNÍN' => 'Junín',
            'ANCASH' => 'Ancash',
            'ÁNCASH' => 'Ancash',
            'ICA' => 'Ica',
            'PUNO' => 'Puno',
            'TACNA' => 'Tacna',
            'MOQUEGUA' => 'Moquegua',
            'AYACUCHO' => 'Ayacucho',
            'HUANCAVELICA' => 'Huancavelica',
            'APURIMAC' => 'Apurímac',
            'APURÍMAC' => 'Apurímac',
            'LORETO' => 'Loreto',
            'UCAYALI' => 'Ucayali',
            'SAN MARTIN' => 'San Martín',
            'SAN MARTÍN' => 'San Martín',
            'AMAZONAS' => 'Amazonas',
            'MADRE DE DIOS' => 'Madre de Dios',
            'HUANUCO' => 'Huánuco',
            'HUÁNUCO' => 'Huánuco',
            'PASCO' => 'Pasco',
            'TUMBES' => 'Tumbes',
            'CALLAO' => 'Callao',
        ];

        if (isset($mappings[$departamento])) {
            return $mappings[$departamento];
        }

        // Mapeo de provincias a departamentos (para casos donde Excel tiene provincia en lugar de departamento)
        $provinciasToDpto = [
            'CAJABAMBA' => 'Cajamarca',
            'JAEN' => 'Cajamarca',
            'JAÉN' => 'Cajamarca',
            'CHOTA' => 'Cajamarca',
            'CUTERVO' => 'Cajamarca',
            'CHICLAYO' => 'Lambayeque',
            'FERREÑAFE' => 'Lambayeque',
            'TRUJILLO' => 'La Libertad',
            'PACASMAYO' => 'La Libertad',
            'ASCOPE' => 'La Libertad',
            'HUARAZ' => 'Ancash',
            'HUARI' => 'Ancash',
            'SULLANA' => 'Piura',
            'TALARA' => 'Piura',
            'PAITA' => 'Piura',
            'CHINCHA' => 'Ica',
            'PISCO' => 'Ica',
            'NAZCA' => 'Ica',
            'HUANCAYO' => 'Junín',
            'TARMA' => 'Junín',
            'SATIPO' => 'Junín',
            'AREQUIPA' => 'Arequipa',
            'CAMANA' => 'Arequipa',
            'ISLAY' => 'Arequipa',
            'TACNA' => 'Tacna',
            'PUNO' => 'Puno',
            'JULIACA' => 'Puno',
            'AYAVIRI' => 'Puno',
            'CUSCO' => 'Cusco',
            'QUILLABAMBA' => 'Cusco',
            'ABANCAY' => 'Apurímac',
            'ANDAHUAYLAS' => 'Apurímac',
            'AYACUCHO' => 'Ayacucho',
            'HUANTA' => 'Ayacucho',
            'HUANCAVELICA' => 'Huancavelica',
            'TUMBES' => 'Tumbes',
            'MOYOBAMBA' => 'San Martín',
            'TARAPOTO' => 'San Martín',
            'CHACHAPOYAS' => 'Amazonas',
            'BAGUA' => 'Amazonas',
            'IQUITOS' => 'Loreto',
            'YURIMAGUAS' => 'Loreto',
            'PUCALLPA' => 'Ucayali',
            'PUERTO MALDONADO' => 'Madre de Dios',
            'HUANUCO' => 'Huánuco',
            'TINGO MARIA' => 'Huánuco',
            'CERRO DE PASCO' => 'Pasco',
        ];

        if (isset($provinciasToDpto[$departamento])) {
            return $provinciasToDpto[$departamento];
        }

        return ucfirst(strtolower($departamento));
    }

    private function generateCodigo(Banda $banda, Sector $sector, string $localidad): string
    {
        $prefix = $banda->esTv() ? 'TV' : 'R';
        $sectorPrefix = substr($sector->value, 0, 1);
        $localidadCode = mb_strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $localidad), 0, 3));

        // Buscar el siguiente número disponible
        $lastEstacion = Estacion::where('codigo', 'like', "{$prefix}{$sectorPrefix}{$localidadCode}%")
            ->orderByRaw("CAST(SUBSTRING(codigo, " . (strlen("{$prefix}{$sectorPrefix}{$localidadCode}") + 1) . ") AS UNSIGNED) DESC")
            ->first();

        $nextNum = 1;
        if ($lastEstacion) {
            $lastNum = (int) substr($lastEstacion->codigo, strlen("{$prefix}{$sectorPrefix}{$localidadCode}"));
            $nextNum = $lastNum + 1;
        }

        return "{$prefix}{$sectorPrefix}{$localidadCode}" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    private function addError(int $rowNumber, string $message): void
    {
        $this->errors[] = "Fila {$rowNumber}: {$message}";
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== RESUMEN DE IMPORTACIÓN ===');
        $this->info("Estaciones creadas: {$this->created}");
        $this->info("Estaciones actualizadas: {$this->updated}");
        $this->info("Errores: " . count($this->errors));

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn('Errores encontrados:');
            foreach (array_slice($this->errors, 0, 20) as $error) {
                $this->line("  - {$error}");
            }
            if (count($this->errors) > 20) {
                $this->line("  ... y " . (count($this->errors) - 20) . " errores más");
            }
        }
    }
}

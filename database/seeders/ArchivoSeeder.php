<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Archivo;
use App\Models\Estacion;
use App\Models\Carpeta;
use App\Models\User;
use App\Models\TramiteMtc;
use App\Enums\RolUsuario;

class ArchivoSeeder extends Seeder
{
    public function run()
    {
        $estaciones = Estacion::with('carpetas')->limit(5)->get();
        $usuarios = User::whereIn('rol', [RolUsuario::ADMINISTRADOR, RolUsuario::GERENTE, RolUsuario::JEFE_ESTACION])->get();
        $tramites = TramiteMtc::all();

        $archivosEjemplo = [
            [
                'nombre_original' => 'RVM_N088-2002_AUTORIZACION.pdf',
                'tipo_documento' => 'autorizacion',
                'extension' => 'pdf',
                'tamano' => 2450000,
                'descripcion' => 'Resolución Viceministerial de Autorización de Funcionamiento'
            ],
            [
                'nombre_original' => 'LICENCIA_OPERACION_2020.pdf',
                'tipo_documento' => 'renovacion',
                'extension' => 'pdf',
                'tamano' => 1850000,
                'descripcion' => 'Licencia de Operación renovada para el periodo 2020-2030'
            ],
            [
                'nombre_original' => 'ACTA_INSPECCION_TECNICA.pdf',
                'tipo_documento' => 'tecnico',
                'extension' => 'pdf',
                'tamano' => 950000,
                'descripcion' => 'Acta de inspección técnica realizada por MTC'
            ],
            [
                'nombre_original' => 'ANTECEDENTES_ESTACION.pdf',
                'tipo_documento' => 'administrativo',
                'extension' => 'pdf',
                'tamano' => 3200000,
                'descripcion' => 'Expediente completo con antecedentes de la estación'
            ],
            [
                'nombre_original' => 'MANUAL_TECNICO_EQUIPOS.docx',
                'tipo_documento' => 'tecnico',
                'extension' => 'docx',
                'tamano' => 5600000,
                'descripcion' => 'Manual técnico de equipos de transmisión'
            ],
            [
                'nombre_original' => 'ESTUDIO_COBERTURA.pdf',
                'tipo_documento' => 'tecnico',
                'extension' => 'pdf',
                'tamano' => 8900000,
                'descripcion' => 'Estudio de cobertura y compatibilidad electromagnética'
            ],
            [
                'nombre_original' => 'PRESUPUESTO_MANTENIMIENTO.xlsx',
                'tipo_documento' => 'financiero',
                'extension' => 'xlsx',
                'tamano' => 450000,
                'descripcion' => 'Presupuesto para mantenimiento preventivo 2024'
            ],
            [
                'nombre_original' => 'INVENTARIO_EQUIPOS.xlsx',
                'tipo_documento' => 'administrativo',
                'extension' => 'xlsx',
                'tamano' => 280000,
                'descripcion' => 'Inventario actualizado de equipos de la estación'
            ],
            [
                'nombre_original' => 'INFORME_TECNICO_MENSUAL.docx',
                'tipo_documento' => 'tecnico',
                'extension' => 'docx',
                'tamano' => 1200000,
                'descripcion' => 'Informe técnico de operaciones del mes'
            ],
            [
                'nombre_original' => 'PLANO_UBICACION.dwg',
                'tipo_documento' => 'tecnico',
                'extension' => 'dwg',
                'tamano' => 3400000,
                'descripcion' => 'Plano técnico de ubicación y distribución'
            ]
        ];

        $archivosCreados = 0;

        foreach ($estaciones as $estacion) {
            $carpetasEstacion = $estacion->carpetas;
            $usuario = $usuarios->random();

            // Crear 5-8 archivos por estación
            $numArchivos = rand(5, 8);
            
            for ($i = 0; $i < $numArchivos; $i++) {
                $archivoEjemplo = $archivosEjemplo[array_rand($archivosEjemplo)];
                $carpeta = $carpetasEstacion->where('tipo', $this->determinarTipoCarpeta($archivoEjemplo['tipo_documento']))->first();
                
                if (!$carpeta) {
                    $carpeta = $carpetasEstacion->first();
                }

                $nombreArchivo = uniqid() . '.' . $archivoEjemplo['extension'];
                $ruta = "archivos/estaciones/{$estacion->id}/{$nombreArchivo}";

                $archivo = Archivo::create([
                    'nombre_original' => $archivoEjemplo['nombre_original'],
                    'nombre_archivo' => $nombreArchivo,
                    'ruta' => $ruta,
                    'tipo_documento' => $archivoEjemplo['tipo_documento'],
                    'tamano' => $archivoEjemplo['tamano'],
                    'extension' => $archivoEjemplo['extension'],
                    'mime_type' => $this->getMimeType($archivoEjemplo['extension']),
                    'estacion_id' => $estacion->id,
                    'carpeta_id' => $carpeta?->id,
                    'subido_por' => $usuario->id,
                    'descripcion' => $archivoEjemplo['descripcion'] . " - {$estacion->localidad}",
                    'es_publico' => rand(1, 100) <= 30, // 30% públicos
                    'hash_archivo' => md5(uniqid() . $archivoEjemplo['nombre_original']),
                    'version' => 1
                ]);

                $archivosCreados++;
            }

            // Crear algunos archivos asociados a trámites
            $tramitesEstacion = $tramites->where('estacion_id', $estacion->id);
            foreach ($tramitesEstacion->take(2) as $tramite) {
                $archivoTramite = [
                    'nombre_original' => "EXPEDIENTE_{$tramite->numero_expediente}.pdf",
                    'tipo_documento' => 'legal',
                    'extension' => 'pdf',
                    'tamano' => rand(1000000, 5000000),
                    'descripcion' => "Documentación del trámite {$tramite->tipo_tramite->getLabel()}"
                ];

                $nombreArchivo = uniqid() . '.pdf';
                $ruta = "archivos/tramites/{$tramite->id}/{$nombreArchivo}";

                Archivo::create([
                    'nombre_original' => $archivoTramite['nombre_original'],
                    'nombre_archivo' => $nombreArchivo,
                    'ruta' => $ruta,
                    'tipo_documento' => $archivoTramite['tipo_documento'],
                    'tamano' => $archivoTramite['tamano'],
                    'extension' => $archivoTramite['extension'],
                    'mime_type' => 'application/pdf',
                    'estacion_id' => $estacion->id,
                    'tramite_id' => $tramite->id,
                    'subido_por' => $usuario->id,
                    'descripcion' => $archivoTramite['descripcion'],
                    'es_publico' => false,
                    'hash_archivo' => md5(uniqid() . $archivoTramite['nombre_original']),
                    'version' => 1
                ]);

                $archivosCreados++;
            }
        }

        $this->command->info('✅ Archivos creados: ' . $archivosCreados);
        $this->command->info('   - Técnicos: ' . Archivo::where('tipo_documento', 'tecnico')->count());
        $this->command->info('   - Legales: ' . Archivo::where('tipo_documento', 'legal')->count());
        $this->command->info('   - Públicos: ' . Archivo::where('es_publico', true)->count());
    }

    private function determinarTipoCarpeta(string $tipoDocumento): string
    {
        return match($tipoDocumento) {
            'autorizacion', 'renovacion', 'transferencia' => 'legal',
            'tecnico' => 'tecnica',
            'financiero' => 'financiera',
            'administrativo' => 'documentacion',
            default => 'otros'
        };
    }

    private function getMimeType(string $extension): string
    {
        return match(strtolower($extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'dwg' => 'application/acad',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'txt' => 'text/plain',
            default => 'application/octet-stream'
        };
    }
}
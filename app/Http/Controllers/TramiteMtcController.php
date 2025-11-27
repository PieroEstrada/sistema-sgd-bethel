<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TramiteMtc;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\TipoTramiteMtc;
use App\Enums\EstadoTramiteMtc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\TramitesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf; // Si decides usar DomPDF

class TramiteMtcController extends Controller
{
    public function index(Request $request)
    {
        $query = TramiteMtc::with(['estacion', 'responsable']);

        // Filtros de búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('estacion', function($subQ) use ($buscar) {
                      $subQ->where('razon_social', 'LIKE', "%{$buscar}%")
                           ->orWhere('localidad', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('tipo_tramite')) {
            $query->where('tipo_tramite', $request->tipo_tramite);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('estacion_id')) {
            $query->where('estacion_id', $request->estacion_id);
        }

        if ($request->filled('responsable_id')) {
            $query->where('responsable_id', $request->responsable_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_presentacion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_presentacion', '<=', $request->fecha_hasta);
        }

        // Filtro especial: Vencidos
        if ($request->filled('mostrar_vencidos') && $request->mostrar_vencidos == '1') {
            $query->vencidos();
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'fecha_presentacion');
        $direccion = $request->get('direccion', 'desc');
        $query->orderBy($ordenar, $direccion);

        $tramites = $query->paginate(15)->appends($request->query());

        // Datos para filtros
        $tipos_tramite = TipoTramiteMtc::getOptions();
        $estados = EstadoTramiteMtc::getOptions();
        
        $estaciones = Estacion::select('id', 'razon_social', 'localidad')
                              ->orderBy('razon_social')
                              ->get();
        
        $responsables = User::whereIn('rol', ['administrador', 'gerente', 'jefe_estacion', 'operador'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        // Estadísticas
        $estadisticas = TramiteMtc::getEstadisticas();

        return view('tramites.index', compact(
            'tramites', 'tipos_tramite', 'estados', 
            'estaciones', 'responsables', 'estadisticas'
        ));
    }

    public function show(TramiteMtc $tramite)
    {
        $tramite->load([
            'estacion',
            'responsable',
            'archivos' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);

        // Calcular progreso de documentos
        $docsRequeridos = $tramite->documentos_requeridos ?? [];
        $docsPresentados = $tramite->documentos_presentados ?? [];
        $docsFaltantes = array_diff($docsRequeridos, $docsPresentados);

        // Información adicional del tipo de trámite
        $tipoInfo = [
            'label' => $tramite->tipo_tramite->getLabel(),
            'description' => $tramite->tipo_tramite->getDescription(),
            'documentos_requeridos' => $tramite->tipo_tramite->getDocumentosRequeridos(),
            'tiempo_promedio' => $tramite->tipo_tramite->getTiempoPromedioDias(),
            'costo' => $tramite->tipo_tramite->getCostoTramite(),
            'color' => $tramite->tipo_tramite->getColor(),
            'icon' => $tramite->tipo_tramite->getIcon()
        ];

        return view('tramites.show', compact(
            'tramite', 'docsRequeridos', 'docsPresentados', 
            'docsFaltantes', 'tipoInfo'
        ));
    }

    public function create()
    {
        $estaciones = Estacion::select('id', 'razon_social', 'localidad', 'departamento')
                              ->orderBy('razon_social')
                              ->get();
        
        $responsables = User::whereIn('rol', ['administrador', 'gerente', 'jefe_estacion', 'operador'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        $tipos_tramite = TipoTramiteMtc::getOptions();

        return view('tramites.create', compact(
            'estaciones', 'responsables', 'tipos_tramite'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_expediente' => 'required|string|max:255|unique:tramites_mtc',
            'tipo_tramite' => 'required|in:' . implode(',', array_column(TipoTramiteMtc::cases(), 'value')),
            'estacion_id' => 'required|exists:estaciones,id',
            'fecha_presentacion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_presentacion',
            'responsable_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:1000',
            'direccion_completa' => 'nullable|string|max:500',
            'coordenadas_utm' => 'nullable|string|max:255',
            'costo_tramite' => 'nullable|numeric|min:0',
            'documentos_presentados' => 'nullable|array'
        ]);

        // Obtener documentos requeridos según tipo de trámite
        $tipoTramite = TipoTramiteMtc::from($validated['tipo_tramite']);
        $validated['documentos_requeridos'] = $tipoTramite->getDocumentosRequeridos();
        
        // Si no se especificó costo, usar el costo por defecto
        if (!isset($validated['costo_tramite'])) {
            $validated['costo_tramite'] = $tipoTramite->getCostoTramite();
        }

        // Estado inicial
        $validated['estado'] = EstadoTramiteMtc::PRESENTADO;

        $tramite = TramiteMtc::create($validated);

        return redirect()->route('tramites.show', $tramite)
                        ->with('success', 'Trámite creado exitosamente.');
    }

    public function edit(TramiteMtc $tramite)
    {
        // Solo se pueden editar trámites en ciertos estados
        if (!$tramite->puedeSerEditado()) {
            return back()->withErrors(['error' => 'Este trámite no puede ser editado en su estado actual.']);
        }

        $estaciones = Estacion::select('id', 'razon_social', 'localidad', 'departamento')
                              ->orderBy('razon_social')
                              ->get();
        
        $responsables = User::whereIn('rol', ['administrador', 'gerente', 'jefe_estacion', 'operador'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        $tipos_tramite = TipoTramiteMtc::getOptions();

        return view('tramites.edit', compact(
            'tramite', 'estaciones', 'responsables', 'tipos_tramite'
        ));
    }

    public function update(Request $request, TramiteMtc $tramite)
    {
        if (!$tramite->puedeSerEditado()) {
            return back()->withErrors(['error' => 'Este trámite no puede ser editado en su estado actual.']);
        }

        $validated = $request->validate([
            'numero_expediente' => 'required|string|max:255|unique:tramites_mtc,numero_expediente,' . $tramite->id,
            'tipo_tramite' => 'required|in:' . implode(',', array_column(TipoTramiteMtc::cases(), 'value')),
            'estacion_id' => 'required|exists:estaciones,id',
            'fecha_presentacion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_presentacion',
            'responsable_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:1000',
            'direccion_completa' => 'nullable|string|max:500',
            'coordenadas_utm' => 'nullable|string|max:255',
            'costo_tramite' => 'nullable|numeric|min:0',
            'documentos_presentados' => 'nullable|array'
        ]);

        // Actualizar documentos requeridos si cambió el tipo de trámite
        if ($tramite->tipo_tramite->value !== $validated['tipo_tramite']) {
            $tipoTramite = TipoTramiteMtc::from($validated['tipo_tramite']);
            $validated['documentos_requeridos'] = $tipoTramite->getDocumentosRequeridos();
        }

        $tramite->update($validated);

        return redirect()->route('tramites.show', $tramite)
                        ->with('success', 'Trámite actualizado exitosamente.');
    }

    public function destroy(TramiteMtc $tramite)
    {
        // Solo se pueden eliminar trámites en estado presentado o rechazado
        if (!in_array($tramite->estado, [EstadoTramiteMtc::PRESENTADO, EstadoTramiteMtc::RECHAZADO])) {
            return back()->withErrors(['error' => 'No se puede eliminar un trámite en proceso o aprobado.']);
        }

        $tramite->delete();

        return redirect()->route('tramites.index')
                        ->with('success', 'Trámite eliminado exitosamente.');
    }

    // AJAX: Cambiar estado del trámite
    public function cambiarEstado(Request $request, TramiteMtc $tramite)
    {
        $validated = $request->validate([
            'nuevo_estado' => 'required|in:' . implode(',', array_column(EstadoTramiteMtc::cases(), 'value')),
            'comentario' => 'nullable|string|max:500',
            'resolucion' => 'nullable|string|max:500'
        ]);

        $nuevoEstado = EstadoTramiteMtc::from($validated['nuevo_estado']);
        $estadoAnterior = $tramite->estado;

        // Actualizar estado
        $tramite->estado = $nuevoEstado;

        // Si se aprueba o rechaza, guardar fecha de respuesta
        if (in_array($nuevoEstado, [EstadoTramiteMtc::APROBADO, EstadoTramiteMtc::RECHAZADO])) {
            $tramite->fecha_respuesta = now();
            
            if (isset($validated['resolucion'])) {
                $tramite->resolucion = $validated['resolucion'];
            }
        }

        // Si se observa, guardar observaciones del MTC
        if ($nuevoEstado === EstadoTramiteMtc::OBSERVADO && isset($validated['comentario'])) {
            $tramite->observaciones_mtc = $validated['comentario'];
        }

        $tramite->save();

        return response()->json([
            'success' => true,
            'mensaje' => "Estado actualizado de {$estadoAnterior->getLabel()} a {$nuevoEstado->getLabel()}",
            'nuevo_estado' => [
                'value' => $nuevoEstado->value,
                'label' => $nuevoEstado->getLabel(),
                'color' => $nuevoEstado->getColor(),
                'icon' => $nuevoEstado->getIcon()
            ],
            'dias_transcurridos' => $tramite->dias_transcurridos,
            'fecha_respuesta' => $tramite->fecha_respuesta ? $tramite->fecha_respuesta->format('d/m/Y') : null
        ]);
    }

    // AJAX: Obtener información del tipo de trámite
    public function getTipoInfo(Request $request)
    {
        $tipo = $request->get('tipo');
        
        try {
            $tipoTramite = TipoTramiteMtc::from($tipo);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'label' => $tipoTramite->getLabel(),
                    'description' => $tipoTramite->getDescription(),
                    'documentos_requeridos' => $tipoTramite->getDocumentosRequeridos(),
                    'tiempo_promedio_dias' => $tipoTramite->getTiempoPromedioDias(),
                    'costo_tramite' => $tipoTramite->getCostoTramite(),
                    'color' => $tipoTramite->getColor(),
                    'icon' => $tipoTramite->getIcon()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de trámite no válido'
            ], 400);
        }
    }

    // AJAX: Marcar documento como presentado
    public function toggleDocumento(Request $request, TramiteMtc $tramite)
    {
        $validated = $request->validate([
            'documento' => 'required|string'
        ]);

        $documentosPresentados = $tramite->documentos_presentados ?? [];
        $documento = $validated['documento'];

        // Toggle: si ya existe, lo quitamos; si no existe, lo agregamos
        if (in_array($documento, $documentosPresentados)) {
            $documentosPresentados = array_values(array_diff($documentosPresentados, [$documento]));
            $accion = 'removido';
        } else {
            $documentosPresentados[] = $documento;
            $accion = 'agregado';
        }

        $tramite->documentos_presentados = $documentosPresentados;
        $tramite->save();

        return response()->json([
            'success' => true,
            'mensaje' => "Documento {$accion} exitosamente",
            'documentos_presentados' => $documentosPresentados,
            'porcentaje_completud' => $tramite->porcentaje_completud,
            'requiere_documentos' => $tramite->requiereDocumentosAdicionales()
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $filtros = $request->only([
            'buscar', 'tipo_tramite', 'estado', 'estacion_id', 
            'responsable_id', 'fecha_desde', 'fecha_hasta', 'mostrar_vencidos'
        ]);

        return Excel::download(
            new TramitesExport($filtros), 
            'tramites_mtc_' . date('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Exportar trámites a PDF
     */
    public function exportarPdf(Request $request)
    {
        $query = TramiteMtc::with(['estacion', 'responsable']);

        // Aplicar filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'LIKE', "%{$buscar}%")
                ->orWhereHas('estacion', function($subQ) use ($buscar) {
                    $subQ->where('razon_social', 'LIKE', "%{$buscar}%")
                        ->orWhere('localidad', 'LIKE', "%{$buscar}%");
                });
            });
        }

        if ($request->filled('tipo_tramite')) {
            $query->where('tipo_tramite', $request->tipo_tramite);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('estacion_id')) {
            $query->where('estacion_id', $request->estacion_id);
        }

        if ($request->filled('responsable_id')) {
            $query->where('responsable_id', $request->responsable_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_presentacion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_presentacion', '<=', $request->fecha_hasta);
        }

        if ($request->filled('mostrar_vencidos') && $request->mostrar_vencidos == '1') {
            $query->vencidos();
        }

        $tramites = $query->orderBy('fecha_presentacion', 'desc')->get();
        $estadisticas = TramiteMtc::getEstadisticas();

        // Generar HTML para el PDF
        $html = view('tramites.pdf', compact('tramites', 'estadisticas'))->render();

        // Usar DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('tramites_mtc_' . date('Y-m-d') . '.pdf');
    }
}
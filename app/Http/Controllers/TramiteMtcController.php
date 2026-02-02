<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TramiteMtc;
use App\Models\TipoTramiteMtc;
use App\Models\EstadoTramiteMtc;
use App\Models\ClasificacionTramite;
use App\Models\TramiteHistorial;
use App\Models\Estacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\TramitesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Archivo;

class TramiteMtcController extends Controller
{
    public function index(Request $request)
    {
        $query = TramiteMtc::with(['estacion', 'responsable', 'tipoTramite', 'estadoActual', 'tramitePadre']);

        // Filtro por busqueda general
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('numero_expediente', 'LIKE', "%{$buscar}%")
                  ->orWhere('numero_oficio_mtc', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('estacion', function($subQ) use ($buscar) {
                      $subQ->where('razon_social', 'LIKE', "%{$buscar}%")
                           ->orWhere('localidad', 'LIKE', "%{$buscar}%");
                  })
                  ->orWhereHas('tipoTramite', function($subQ) use ($buscar) {
                      $subQ->where('nombre', 'LIKE', "%{$buscar}%")
                           ->orWhere('codigo', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        // Filtro por origen (TUPA Digital / Mesa Partes)
        if ($request->filled('origen')) {
            $query->porOrigen($request->origen);
        }

        // Filtro por clasificacion
        if ($request->filled('clasificacion_id')) {
            $query->porClasificacion($request->clasificacion_id);
        }

        // Filtro por tipo de tramite (nuevo sistema)
        if ($request->filled('tipo_tramite_id')) {
            $query->where('tipo_tramite_id', $request->tipo_tramite_id);
        }

        // Filtro por estado (nuevo sistema)
        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }

        // Filtros legacy (mantener compatibilidad)
        if ($request->filled('tipo_tramite') && !$request->filled('tipo_tramite_id')) {
            $query->where('tipo_tramite', $request->tipo_tramite);
        }

        if ($request->filled('estado') && !$request->filled('estado_id')) {
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

        // Filtro: Solo con tramite padre
        if ($request->filled('solo_vinculados') && $request->solo_vinculados == '1') {
            $query->conTramitePadre();
        }

        // Filtro: Vencidos
        if ($request->filled('mostrar_vencidos') && $request->mostrar_vencidos == '1') {
            $query->vencidos();
        }

        // Filtro: Silencio positivo aplicable
        if ($request->filled('silencio_positivo') && $request->silencio_positivo == '1') {
            $query->conEvaluacionPositiva()->vencidosEvaluacion();
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'fecha_presentacion');
        $direccion = $request->get('direccion', 'desc');
        $query->orderBy($ordenar, $direccion);

        $tramites = $query->paginate(15)->appends($request->query());

        // Datos para filtros - nuevo sistema
        $tiposTramite = TipoTramiteMtc::getOptionsAgrupadas();
        $estados = EstadoTramiteMtc::getOptions();
        $clasificaciones = ClasificacionTramite::getOptions();

        $estaciones = Estacion::select('id', 'razon_social', 'localidad')
                              ->orderBy('razon_social')
                              ->get();

        $responsables = User::whereIn('rol', ['administrador', 'gestor_radiodifusion'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        // Estadisticas
        $estadisticas = TramiteMtc::getEstadisticas();

        return view('tramites.index', compact(
            'tramites', 'tiposTramite', 'estados', 'clasificaciones',
            'estaciones', 'responsables', 'estadisticas'
        ));
    }

    public function show(TramiteMtc $tramite)
    {
        $tramite->load([
            'estacion',
            'responsable',
            'tipoTramite.clasificacion',
            'tipoTramite.requisitos' => fn($q) => $q->activos()->ordenados(),
            'estadoActual',
            'tramitePadre',
            'tramitesHijos.estadoActual',
            'archivos' => fn($q) => $q->orderBy('created_at', 'desc'),
            'historial' => fn($q) => $q->with(['usuarioAccion:id,name', 'estadoAnterior', 'estadoNuevo'])
                                       ->orderBy('created_at', 'desc')
                                       ->limit(50),
            'eventos' => fn($q) => $q->with('usuario:id,name')->orderBy('created_at', 'desc'),
        ]);

        // Estados posibles para transicion
        $estadosPosibles = $tramite->getEstadosPosibles();

        // Informacion del tipo de tramite
        $tipoInfo = null;
        if ($tramite->tipoTramite) {
            $tipoInfo = [
                'id' => $tramite->tipoTramite->id,
                'codigo' => $tramite->tipoTramite->codigo,
                'nombre' => $tramite->tipoTramite->nombre,
                'descripcion' => $tramite->tipoTramite->descripcion,
                'origen' => $tramite->tipoTramite->origen,
                'origen_label' => $tramite->tipoTramite->origen_label,
                'plazo_dias' => $tramite->tipoTramite->plazo_dias,
                'tipo_evaluacion' => $tramite->tipoTramite->tipo_evaluacion,
                'tipo_evaluacion_label' => $tramite->tipoTramite->tipo_evaluacion_label,
                'tipo_evaluacion_color' => $tramite->tipoTramite->tipo_evaluacion_color,
                'costo' => $tramite->tipoTramite->getCostoSoles(),
                'costo_formateado' => $tramite->tipoTramite->costo_formateado,
                'color' => $tramite->tipoTramite->color,
                'icono' => $tramite->tipoTramite->icono,
                'clasificacion' => $tramite->tipoTramite->clasificacion?->nombre,
            ];
        }

        // Requisitos del tipo de tramite con estado de cumplimiento
        $requisitos = [];
        if ($tramite->tipoTramite) {
            $cumplidos = $tramite->requisitos_cumplidos ?? [];
            foreach ($tramite->tipoTramite->getRequisitosActivos() as $req) {
                $requisitos[] = [
                    'id' => $req->id,
                    'nombre' => $req->nombre,
                    'descripcion' => $req->descripcion,
                    'es_obligatorio' => $req->es_obligatorio,
                    'cumplido' => in_array($req->id, $cumplidos),
                ];
            }
        }

        // Progreso de documentos (legacy)
        $docsRequeridos = $tramite->documentos_requeridos ?? [];
        $docsPresentados = $tramite->documentos_presentados ?? [];
        $docsFaltantes = array_diff($docsRequeridos, $docsPresentados);

        // Alerta de silencio positivo
        $alertaSilencioPositivo = $tramite->aplicaSilencioPositivo();

        return view('tramites.show', compact(
            'tramite', 'estadosPosibles', 'tipoInfo', 'requisitos',
            'docsRequeridos', 'docsPresentados', 'docsFaltantes',
            'alertaSilencioPositivo'
        ));
    }

    public function create(Request $request)
    {
        $estaciones = Estacion::select('id', 'razon_social', 'localidad', 'departamento')
                              ->orderBy('razon_social')
                              ->get();

        $responsables = User::whereIn('rol', ['administrador', 'gestor_radiodifusion'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        // Tipos de tramite agrupados por origen
        $tiposTramite = TipoTramiteMtc::getOptionsAgrupadas();
        $clasificaciones = ClasificacionTramite::getOptions();

        // Estado inicial
        $estadoInicial = EstadoTramiteMtc::getEstadoInicial();

        // Tramites disponibles para vincular (como padre)
        $tramitesParaVincular = TramiteMtc::with('tipoTramite:id,nombre,codigo')
            ->whereNotNull('numero_expediente')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get(['id', 'numero_expediente', 'tipo_tramite_id', 'estacion_id']);

        // Origen preseleccionado (si viene de un link)
        $origenPreseleccionado = $request->get('origen', 'tupa_digital');

        return view('tramites.create', compact(
            'estaciones', 'responsables', 'tiposTramite', 'clasificaciones',
            'estadoInicial', 'tramitesParaVincular', 'origenPreseleccionado'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_expediente' => 'required|string|max:255|unique:tramites_mtc',
            'numero_oficio_mtc' => 'nullable|string|max:100',
            'tipo_tramite_id' => 'required|exists:tipos_tramite_mtc,id',
            'estacion_id' => 'nullable|exists:estaciones,id',
            'tramite_padre_id' => 'nullable|exists:tramites_mtc,id',
            'fecha_presentacion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_presentacion',
            'fecha_limite_respuesta' => 'nullable|date',
            'responsable_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:1000',
            'direccion_completa' => 'nullable|string|max:500',
            'coordenadas_utm' => 'nullable|string|max:255',
            'costo_tramite' => 'nullable|numeric|min:0',
        ]);

        // Obtener tipo de tramite
        $tipoTramite = TipoTramiteMtc::find($validated['tipo_tramite_id']);

        // Validar que si requiere estacion, se proporcione
        if ($tipoTramite->requiere_estacion && empty($validated['estacion_id'])) {
            return back()->withErrors(['estacion_id' => 'Este tipo de tramite requiere seleccionar una estacion.'])
                        ->withInput();
        }

        // Estado inicial
        $estadoInicial = EstadoTramiteMtc::getEstadoInicial();
        $validated['estado_id'] = $estadoInicial?->id;

        // Si no se especifico costo, usar el costo del tipo de tramite
        if (!isset($validated['costo_tramite']) || $validated['costo_tramite'] === null) {
            $validated['costo_tramite'] = $tipoTramite->getCostoSoles();
        }

        // Calcular fecha de vencimiento si hay fecha de presentacion y plazo
        if (!empty($validated['fecha_presentacion']) && $tipoTramite->plazo_dias && empty($validated['fecha_vencimiento'])) {
            $fechaBase = \Carbon\Carbon::parse($validated['fecha_presentacion']);
            $validated['fecha_vencimiento'] = $fechaBase->addWeekdays($tipoTramite->plazo_dias);
        }

        // Guardar documentos requeridos del tipo (legacy - compatibilidad)
        $validated['documentos_requeridos'] = $tipoTramite->documentos_requeridos;

        $tramite = TramiteMtc::create($validated);

        // Registrar en historial
        TramiteHistorial::registrarCreacion($tramite, Auth::id(), 'Tramite creado desde el formulario');

        // Si se vinculo a un tramite padre, registrar
        if ($tramite->tramite_padre_id) {
            $tramitePadre = TramiteMtc::find($tramite->tramite_padre_id);
            if ($tramitePadre) {
                TramiteHistorial::registrarVinculacionTramite($tramite, $tramitePadre, Auth::id());
            }
        }

        return redirect()->route('tramites.show', $tramite)
                        ->with('success', 'Tramite creado exitosamente.');
    }

    public function edit(TramiteMtc $tramite)
    {
        if (!$tramite->puedeSerEditado()) {
            return back()->withErrors(['error' => 'Este tramite no puede ser editado en su estado actual.']);
        }

        $tramite->load(['tipoTramite', 'estadoActual', 'tramitePadre']);

        $estaciones = Estacion::select('id', 'razon_social', 'localidad', 'departamento')
                              ->orderBy('razon_social')
                              ->get();

        $responsables = User::whereIn('rol', ['administrador', 'gestor_radiodifusion'])
                            ->where('activo', true)
                            ->orderBy('name')
                            ->get();

        $tiposTramite = TipoTramiteMtc::getOptionsAgrupadas();
        $clasificaciones = ClasificacionTramite::getOptions();

        $tramitesParaVincular = TramiteMtc::with('tipoTramite:id,nombre,codigo')
            ->where('id', '!=', $tramite->id)
            ->whereNotNull('numero_expediente')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get(['id', 'numero_expediente', 'tipo_tramite_id', 'estacion_id']);

        return view('tramites.edit', compact(
            'tramite', 'estaciones', 'responsables', 'tiposTramite',
            'clasificaciones', 'tramitesParaVincular'
        ));
    }

    public function update(Request $request, TramiteMtc $tramite)
    {
        if (!$tramite->puedeSerEditado()) {
            return back()->withErrors(['error' => 'Este tramite no puede ser editado en su estado actual.']);
        }

        $validated = $request->validate([
            'numero_expediente' => 'required|string|max:255|unique:tramites_mtc,numero_expediente,' . $tramite->id,
            'numero_oficio_mtc' => 'nullable|string|max:100',
            'tipo_tramite_id' => 'required|exists:tipos_tramite_mtc,id',
            'estacion_id' => 'nullable|exists:estaciones,id',
            'tramite_padre_id' => 'nullable|exists:tramites_mtc,id',
            'fecha_presentacion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_presentacion',
            'fecha_limite_respuesta' => 'nullable|date',
            'responsable_id' => 'required|exists:users,id',
            'observaciones' => 'nullable|string|max:1000',
            'direccion_completa' => 'nullable|string|max:500',
            'coordenadas_utm' => 'nullable|string|max:255',
            'costo_tramite' => 'nullable|numeric|min:0',
        ]);

        // Obtener tipo de tramite
        $tipoTramite = TipoTramiteMtc::find($validated['tipo_tramite_id']);

        // Validar que si requiere estacion, se proporcione
        if ($tipoTramite->requiere_estacion && empty($validated['estacion_id'])) {
            return back()->withErrors(['estacion_id' => 'Este tipo de tramite requiere seleccionar una estacion.'])
                        ->withInput();
        }

        // Si cambio el tipo de tramite, actualizar documentos requeridos
        if ($tramite->tipo_tramite_id !== $validated['tipo_tramite_id']) {
            $validated['documentos_requeridos'] = $tipoTramite->documentos_requeridos;
            // Resetear requisitos cumplidos si cambio el tipo
            $validated['requisitos_cumplidos'] = [];
        }

        // Verificar si cambio el responsable para registrar en historial
        $responsableCambio = $tramite->responsable_id !== $validated['responsable_id'];
        $responsableAnteriorId = $tramite->responsable_id;

        $tramite->update($validated);

        // Registrar cambio de responsable si aplica
        if ($responsableCambio) {
            TramiteHistorial::registrarAsignacionResponsable(
                $tramite,
                $responsableAnteriorId,
                $validated['responsable_id'],
                Auth::id()
            );
        }

        // Registrar actualizacion general
        TramiteHistorial::registrarActualizacion($tramite, Auth::id(), 'Tramite actualizado');

        return redirect()->route('tramites.show', $tramite)
                        ->with('success', 'Tramite actualizado exitosamente.');
    }

    public function destroy(TramiteMtc $tramite)
    {
        // Solo se pueden eliminar tramites en ciertos estados
        $estadosPermitidos = ['recopilacion', 'denegado'];
        if ($tramite->estadoActual && !in_array($tramite->estadoActual->codigo, $estadosPermitidos)) {
            return back()->withErrors(['error' => 'No se puede eliminar un tramite en proceso o finalizado.']);
        }

        $tramite->delete();

        return redirect()->route('tramites.index')
                        ->with('success', 'Tramite eliminado exitosamente.');
    }

    // =====================================================
    // AJAX: Cambiar estado del tramite
    // =====================================================
    public function cambiarEstado(Request $request, TramiteMtc $tramite)
    {
        $validated = $request->validate([
            'nuevo_estado_id' => 'required|exists:estados_tramite_mtc,id',
            'comentario' => 'nullable|string|max:500',
            'resolucion' => 'nullable|string|max:500'
        ]);

        $resultado = $tramite->cambiarEstado(
            $validated['nuevo_estado_id'],
            Auth::id(),
            $validated['comentario'] ?? null,
            $validated['resolucion'] ?? null
        );

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'mensaje' => $resultado['mensaje']
            ], 422);
        }

        $nuevoEstado = $resultado['estado'];

        return response()->json([
            'success' => true,
            'mensaje' => $resultado['mensaje'],
            'nuevo_estado' => [
                'id' => $nuevoEstado->id,
                'codigo' => $nuevoEstado->codigo,
                'nombre' => $nuevoEstado->nombre,
                'color' => $nuevoEstado->color,
                'icono' => $nuevoEstado->icono,
                'es_final' => $nuevoEstado->es_final,
            ],
            'dias_transcurridos' => $tramite->dias_transcurridos,
            'fecha_respuesta' => $tramite->fecha_respuesta ? $tramite->fecha_respuesta->format('d/m/Y') : null
        ]);
    }

    // =====================================================
    // AJAX: Obtener estados posibles
    // =====================================================
    public function getEstadosPosibles(TramiteMtc $tramite)
    {
        $estados = $tramite->getEstadosPosibles();

        // Obtener informacion de transicion para cada estado
        $estadosConInfo = $estados->map(function($estado) use ($tramite) {
            $transicion = $tramite->estadoActual?->getTransicionHacia($estado->id);

            return [
                'id' => $estado->id,
                'codigo' => $estado->codigo,
                'nombre' => $estado->nombre,
                'color' => $estado->color,
                'icono' => $estado->icono,
                'es_final' => $estado->es_final,
                'requiere_comentario' => $transicion?->requiere_comentario ?? false,
                'requiere_resolucion' => $transicion?->requiere_resolucion ?? false,
                'requiere_documentos_completos' => $transicion?->requiere_documentos_completos ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'estados' => $estadosConInfo,
            'estado_actual' => $tramite->estadoActual ? [
                'id' => $tramite->estadoActual->id,
                'nombre' => $tramite->estadoActual->nombre,
            ] : null
        ]);
    }

    // =====================================================
    // AJAX: Toggle requisito cumplido
    // =====================================================
    public function toggleRequisito(Request $request, TramiteMtc $tramite)
    {
        $validated = $request->validate([
            'requisito_id' => 'required|exists:requisitos_tipo_tramite,id'
        ]);

        $resultado = $tramite->toggleRequisito($validated['requisito_id'], Auth::id());

        return response()->json($resultado);
    }

    // =====================================================
    // API: Obtener tipos de tramite por origen
    // =====================================================
    public function getTiposPorOrigen(Request $request)
    {
        $origen = $request->get('origen', 'tupa_digital');

        $tipos = TipoTramiteMtc::getOptions($origen);

        return response()->json([
            'success' => true,
            'tipos' => $tipos
        ]);
    }

    // =====================================================
    // AJAX: Obtener informacion del tipo de tramite (por query param)
    // =====================================================
    public function getTipoInfo(Request $request)
    {
        $tipoId = $request->get('tipo_id');

        $tipo = TipoTramiteMtc::with('clasificacion', 'requisitos')->find($tipoId);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de tramite no encontrado'
            ], 404);
        }

        return $this->formatTipoInfoResponse($tipo);
    }

    // =====================================================
    // AJAX: Obtener informacion del tipo de tramite (por URL param)
    // =====================================================
    public function getTipoInfoById($tipoId)
    {
        $tipo = TipoTramiteMtc::with('clasificacion', 'requisitos')->find($tipoId);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de tramite no encontrado'
            ], 404);
        }

        return $this->formatTipoInfoResponse($tipo);
    }

    // =====================================================
    // Helper: Formatear respuesta de info del tipo
    // =====================================================
    private function formatTipoInfoResponse(TipoTramiteMtc $tipo)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tipo->id,
                'codigo' => $tipo->codigo,
                'nombre' => $tipo->nombre,
                'nombre_completo' => $tipo->nombre_completo,
                'descripcion' => $tipo->descripcion,
                'origen' => $tipo->origen,
                'origen_label' => $tipo->origen_label,
                'clasificacion' => $tipo->clasificacion?->nombre,
                'plazo_dias' => $tipo->plazo_dias,
                'plazo_dias_texto' => $tipo->plazo_dias_texto,
                'tipo_evaluacion' => $tipo->tipo_evaluacion,
                'tipo_evaluacion_label' => $tipo->tipo_evaluacion_label,
                'tipo_evaluacion_color' => $tipo->tipo_evaluacion_color,
                'costo' => $tipo->getCostoSoles(),
                'costo_formateado' => $tipo->costo_formateado,
                'requiere_estacion' => $tipo->requiere_estacion,
                'permite_tramite_padre' => $tipo->permite_tramite_padre,
                'color' => $tipo->color,
                'icono' => $tipo->icono,
                'requisitos' => $tipo->requisitos->where('activo', true)->values()->map(fn($r) => [
                    'id' => $r->id,
                    'nombre' => $r->nombre,
                    'descripcion' => $r->descripcion,
                    'es_obligatorio' => $r->es_obligatorio,
                ]),
            ]
        ]);
    }

    // =====================================================
    // AJAX: Marcar documento como presentado (legacy)
    // =====================================================
    public function toggleDocumento(Request $request, TramiteMtc $tramite)
    {
        $validated = $request->validate([
            'documento' => 'required|string'
        ]);

        $documentosPresentados = $tramite->documentos_presentados ?? [];
        $documento = $validated['documento'];

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

    // =====================================================
    // Exportaciones
    // =====================================================
    public function exportarExcel(Request $request)
    {
        $filtros = $request->only([
            'buscar', 'origen', 'clasificacion_id', 'tipo_tramite_id', 'estado_id',
            'tipo_tramite', 'estado', 'estacion_id',
            'responsable_id', 'fecha_desde', 'fecha_hasta', 'mostrar_vencidos'
        ]);

        return Excel::download(
            new TramitesExport($filtros),
            'tramites_mtc_' . date('Y-m-d_His') . '.xlsx'
        );
    }

    public function exportarPdf(Request $request)
    {
        $query = TramiteMtc::with(['estacion', 'responsable', 'tipoTramite', 'estadoActual']);

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

        if ($request->filled('origen')) {
            $query->porOrigen($request->origen);
        }

        if ($request->filled('tipo_tramite_id')) {
            $query->where('tipo_tramite_id', $request->tipo_tramite_id);
        }

        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
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

        $html = view('tramites.pdf', compact('tramites', 'estadisticas'))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('tramites_mtc_' . date('Y-m-d') . '.pdf');
    }

    // =====================================================
    // Archivos
    // =====================================================
    public function subirArchivo(Request $request, TramiteMtc $tramite)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.mimes' => 'Solo se permiten PDF, JPG o PNG.',
            'archivo.max' => 'El archivo no debe exceder 10MB.',
        ]);

        $file = $request->file('archivo');

        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $safeBase = Str::slug(pathinfo($original, PATHINFO_FILENAME));
        $nombreArchivo = now()->format('YmdHis') . '_' . $safeBase . '.' . $ext;

        $file->storeAs("archivos/tramites/{$tramite->id}", $nombreArchivo, 'public');

        $ruta = "archivos/tramites/{$tramite->id}/{$nombreArchivo}";

        Archivo::create([
            'nombre_original' => $original,
            'nombre_archivo' => $nombreArchivo,
            'ruta' => $ruta,
            'tipo_documento' => 'legal',
            'tamano' => $file->getSize(),
            'extension' => $ext,
            'mime_type' => $file->getClientMimeType(),
            'estacion_id' => $tramite->estacion_id,
            'tramite_id' => $tramite->id,
            'subido_por' => Auth::id(),
            'descripcion' => 'Adjunto de tramite MTC',
            'es_publico' => false,
            'hash_archivo' => md5(uniqid() . $original),
            'version' => 1,
        ]);

        // Registrar en historial
        TramiteHistorial::registrarDocumentoSubido($tramite, Auth::id(), $original);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    public function descargarArchivo(Archivo $archivo)
    {
        if (!$archivo->ruta || !Storage::disk('public')->exists($archivo->ruta)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($archivo->ruta, $archivo->nombre_original);
    }

    public function eliminarArchivo(Archivo $archivo)
    {
        if ($archivo->ruta && Storage::disk('public')->exists($archivo->ruta)) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        $nombreArchivo = $archivo->nombre_original;
        $tramiteId = $archivo->tramite_id;

        $archivo->delete();

        // Registrar en historial si tiene tramite asociado
        if ($tramiteId) {
            $tramite = TramiteMtc::find($tramiteId);
            if ($tramite) {
                TramiteHistorial::registrarActualizacion(
                    $tramite,
                    Auth::id(),
                    "Archivo eliminado: {$nombreArchivo}"
                );
            }
        }

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    public function subirDocumentoPrincipal(Request $request, TramiteMtc $tramite)
    {
        $request->validate([
            'documento_principal' => 'required|file|mimes:pdf,doc,docx|max:20480',
        ], [
            'documento_principal.required' => 'Debe seleccionar un documento.',
            'documento_principal.mimes' => 'Solo se permiten PDF, DOC o DOCX.',
            'documento_principal.max' => 'El archivo no debe exceder 20MB.',
        ]);

        $file = $request->file('documento_principal');

        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $safeBase = Str::slug(pathinfo($original, PATHINFO_FILENAME));
        $nombreArchivo = 'doc_principal_' . now()->format('YmdHis') . '_' . $safeBase . '.' . $ext;

        $file->storeAs("archivos/tramites/{$tramite->id}", $nombreArchivo, 'public');

        $ruta = "archivos/tramites/{$tramite->id}/{$nombreArchivo}";

        $tramite->update([
            'documento_principal_nombre' => $original,
            'documento_principal_ruta' => $ruta,
            'documento_principal_size' => $file->getSize(),
        ]);

        Archivo::create([
            'nombre_original' => $original,
            'nombre_archivo' => $nombreArchivo,
            'ruta' => $ruta,
            'tipo_documento' => 'legal',
            'tamano' => $file->getSize(),
            'extension' => $ext,
            'mime_type' => $file->getClientMimeType(),
            'estacion_id' => $tramite->estacion_id,
            'tramite_id' => $tramite->id,
            'subido_por' => Auth::id(),
            'descripcion' => 'Documento principal del tramite MTC',
            'es_publico' => false,
            'hash_archivo' => md5(uniqid() . $original),
            'version' => 1,
        ]);

        TramiteHistorial::registrarDocumentoSubido($tramite, Auth::id(), "Documento principal: {$original}");

        return back()->with('success', 'Documento principal subido correctamente.');
    }

    public function eliminarDocumentoPrincipal(TramiteMtc $tramite)
    {
        if (!$tramite->documento_principal_ruta) {
            return back()->withErrors(['error' => 'Este tramite no tiene documento principal.']);
        }

        // Eliminar archivo fisico
        if (Storage::disk('public')->exists($tramite->documento_principal_ruta)) {
            Storage::disk('public')->delete($tramite->documento_principal_ruta);
        }

        $nombreDocumento = $tramite->documento_principal_nombre;

        // Eliminar registro Archivo asociado (si existe)
        Archivo::where('tramite_id', $tramite->id)
            ->where('ruta', $tramite->documento_principal_ruta)
            ->delete();

        // Limpiar campos del tramite
        $tramite->update([
            'documento_principal_ruta' => null,
            'documento_principal_nombre' => null,
            'documento_principal_size' => null,
        ]);

        // Registrar en historial
        TramiteHistorial::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'documento_eliminado',
            'descripcion_cambio' => "Documento principal eliminado: {$nombreDocumento}",
            'usuario_accion_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Documento principal eliminado correctamente.');
    }
}

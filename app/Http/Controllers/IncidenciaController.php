<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\RolUsuario;
use App\Enums\TipoIncidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IncidenciaController extends Controller
{
    /**
     * Mostrar lista de incidencias
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        $query = Incidencia::with([
            'estacion:id,codigo,localidad,razon_social,sector',  
            'reportadoPorUsuario:id,name,rol,email,telefono',  
            'asignadoAUsuario:id,name,rol,email,telefono'     
        ]);

        $this->aplicarFiltrosPorRol($query, $userRole, $user);

        // Filtros de búsqueda
        if ($request->filled('estacion_id')) {
            $query->where('estacion_id', $request->estacion_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('asignado_a')) {
            $query->where(function($q) use ($request) {
                $q->where('asignado_a_user_id', $request->asignado_a)
                  ->orWhere('asignado_a', $request->asignado_a);
            });
        }

        if ($request->filled('area_responsable')) {
            if ($request->area_responsable === 'sin_asignar') {
                $query->whereNull('area_responsable_actual');
            } else {
                $query->where('area_responsable_actual', $request->area_responsable);
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_reporte', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_reporte', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $search = $request->buscar;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%")
                  ->orWhereHas('estacion', function($subQ) use ($search) {
                      $subQ->where('codigo', 'LIKE', "%{$search}%")
                           ->orWhere('razon_social', 'LIKE', "%{$search}%");
                  });
            });
        }

        $perPage = in_array($request->input('per_page'), [15, 25, 50, 100])
            ? (int) $request->input('per_page')
            : 15;

        $incidencias = $query->orderBy('fecha_reporte', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage)
                            ->appends($request->query());

        $incidencias->getCollection()->transform(function($incidencia) use ($user, $userRole) {
            $incidencia->puede_editar = $this->puedeEditarIncidencia($incidencia, $user, $userRole);
            $incidencia->puede_eliminar = $this->puedeEliminarIncidencia($incidencia);
            $incidencia->puede_cambiar_estado = $this->puedeCambiarEstado($incidencia, $user, $userRole);
            $incidencia->puede_asignar = $this->puedeAsignarIncidencia($incidencia, $user, $userRole);
            $incidencia->puede_transferir = $this->puedeTransferirIncidencia($incidencia, $user, $userRole);
            return $incidencia;
        });

        $estaciones = $this->getEstacionesParaFiltro($userRole, $user);
        $usuarios = $this->getUsuariosParaAsignacion($userRole, $user);
        $estadisticas = $this->calcularEstadisticas($userRole, $user);

        return view('incidencias.index', compact(
            'incidencias', 
            'estaciones', 
            'usuarios',
            'estadisticas'
        ));
    }

    /**
     * Mostrar formulario para crear incidencia
     */
    public function create()
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if ($userRole === RolUsuario::VISOR) {
            abort(403, 'No tienes permisos para crear incidencias.');
        }

        $estaciones = $this->getEstacionesParaFormulario($userRole, $user);

        $prioridades = [
            'critica' => 'Crítica',
            'alta' => 'Alta', 
            'media' => 'Media',
            'baja' => 'Baja'
        ];

        $usuariosTecnicos = $this->getUsuariosParaAsignacion($userRole, $user);

        $categorias = [
            'tecnica' => 'Técnica',
            'operativa' => 'Operativa',
            'administrativa' => 'Administrativa',
            'infraestructura' => 'Infraestructura',
            'equipamiento' => 'Equipamiento',
            'informativa' => 'Informativa (Solo registro)'
        ];

        return view('incidencias.create', compact('estaciones', 'prioridades', 'usuariosTecnicos', 'categorias'));
    }

    /**
     * Almacenar nueva incidencia
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if ($userRole === RolUsuario::VISOR) {
            abort(403, 'No tienes permisos para crear incidencias.');
        }

        $categoria = $request->input('categoria');
        $esInformativa = $categoria === 'informativa';

        $rules = [
            'estacion_id' => 'required|exists:estaciones,id',
            'descripcion_corta' => 'required|string|max:255',
            'descripcion_detallada' => 'required|string|min:20|max:2000',
            'categoria' => 'required|in:tecnica,operativa,administrativa,infraestructura,equipamiento,informativa',
            'impacto_servicio' => 'required|in:bajo,medio,alto',
            'area_responsable' => 'nullable|in:ingenieria,laboratorio,logistica,operaciones,administracion,contabilidad,iglesia_local',
            'observaciones' => 'nullable|string|max:1000',
            'reportado_por_user_id' => 'nullable|exists:users,id',
        ];

        if (!$esInformativa) {
            $rules['prioridad'] = 'required|in:critica,alta,media,baja';
        } else {
            $rules['prioridad'] = 'nullable|in:critica,alta,media,baja';
        }

        $messages = [
            'estacion_id.required' => 'Debe seleccionar una estación',
            'estacion_id.exists' => 'La estación seleccionada no existe',
            'descripcion_corta.required' => 'La descripción corta es obligatoria',
            'descripcion_corta.max' => 'La descripción corta no puede exceder 255 caracteres',
            'descripcion_detallada.required' => 'La descripción detallada es obligatoria',
            'descripcion_detallada.min' => 'La descripción detallada debe tener al menos 20 caracteres',
            'categoria.required' => 'Debe seleccionar una categoría',
            'impacto_servicio.required' => 'Debe seleccionar el impacto en el servicio',
            'prioridad.required' => 'Debe seleccionar una prioridad',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes crear incidencias en estaciones de tu sector asignado.'
                ])->withInput();
            }
        }

        try {
            $incidencia = new Incidencia();
            $incidencia->estacion_id = $validated['estacion_id'];
            // Mapeo de campos del formulario → campos de la BD
            $incidencia->titulo = $validated['descripcion_corta'];
            $incidencia->descripcion = $validated['descripcion_detallada'];
            $incidencia->prioridad = $validated['prioridad'] ?? 'baja';
            $incidencia->categoria = $validated['categoria'] ?? null;
            $incidencia->impacto_servicio = $validated['impacto_servicio'] ?? null;
            $incidencia->observaciones_tecnicas = $validated['observaciones'] ?? null;

            // Reportado por
            $incidencia->reportado_por_user_id = $validated['reportado_por_user_id'] ?? $user->id;
            $incidencia->reportado_por = $validated['reportado_por_user_id'] ?? $user->id;
            $incidencia->fecha_reporte = now();

            // Área responsable
            $incidencia->area_responsable_actual = $validated['area_responsable'] ?? null;

            if ($esInformativa) {
                $incidencia->estado = 'cerrada';
                $incidencia->fecha_resolucion = now();
            } else {
                if ($incidencia->area_responsable_actual) {
                    $incidencia->estado = 'en_proceso';
                } else {
                    $incidencia->estado = 'abierta';
                }
            }

            $incidencia->save();

            \App\Models\IncidenciaHistorial::registrarCreacion(
                $incidencia,
                $user->id,
                'Incidencia creada por ' . $user->name
            );

            return redirect()->route('incidencias.show', $incidencia)
                           ->with('success', 'Incidencia creada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Error al crear la incidencia: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Mostrar incidencia específica
     */
    public function show(Incidencia $incidencia)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if (!$this->puedeVerIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para ver esta incidencia.');
        }

        // Cargar solo relaciones que efectivamente existen
        $incidencia->load([
            'estacion:id,codigo,razon_social,localidad,provincia,departamento,sector',
            'reportadoPorUsuario:id,name,email,telefono,rol',
            'asignadoAUsuario:id,name,email,telefono,rol',
            'historial.usuarioAccion:id,name'
        ]);

        $permisos = [
            'puede_editar' => $this->puedeEditarIncidencia($incidencia, $user, $userRole),
            'puede_eliminar' => $this->puedeEliminarIncidencia($incidencia),
            'puede_cambiar_estado' => $this->puedeCambiarEstado($incidencia, $user, $userRole),
            'puede_asignar' => $this->puedeAsignarIncidencia($incidencia, $user, $userRole),
            'puede_transferir' => $this->puedeTransferirIncidencia($incidencia, $user, $userRole)
        ];

        $usuariosAsignacion = [];
        if ($permisos['puede_asignar']) {
            $usuariosAsignacion = $this->getUsuariosParaAsignacion($userRole, $user);
        }

        $usuariosTransferencia = [];
        if ($permisos['puede_transferir']) {
            $usuariosTransferencia = User::where('activo', true)
                ->whereIn('rol', [
                    'administrador',
                    'coordinador_operaciones',
                    'encargado_ingenieria',
                    'encargado_laboratorio',
                    'supervisor_tecnico',
                    'sectorista',
                    'jefe_estacion'
                ])
                ->orderBy('name')
                ->get();
        }

        // Estadísticas de tiempo
        $fechaReporte = $incidencia->fecha_reporte ?? $incidencia->created_at;
        $ahora = now();
        $tiempoTranscurrido = $fechaReporte->diff($ahora);
        
        $estadisticas = [
            'tiempo_transcurrido_dias' => $tiempoTranscurrido->days,
            'tiempo_transcurrido_horas' => $tiempoTranscurrido->h + ($tiempoTranscurrido->days * 24),
            'tiempo_transcurrido_minutos' => $tiempoTranscurrido->i + ($tiempoTranscurrido->h * 60) + ($tiempoTranscurrido->days * 24 * 60),
            'tiempo_transcurrido_formato' => $fechaReporte->diffForHumans(),
            'fecha_reporte' => $fechaReporte->format('d/m/Y H:i'),
            'dias_abierta' => $tiempoTranscurrido->days,
            'esta_vencida' => $incidencia->tiempo_respuesta_estimado && 
                              ($tiempoTranscurrido->h + ($tiempoTranscurrido->days * 24)) > $incidencia->tiempo_respuesta_estimado
        ];

        // Si no hay registros en historial, crear el registro inicial
        if ($incidencia->historial->isEmpty()) {
            \App\Models\IncidenciaHistorial::registrarCreacion(
                $incidencia,
                $incidencia->reportado_por_user_id ?? auth()->id(),
                'Registro inicial generado automáticamente'
            );
            $incidencia->load('historial.usuarioAccion');
        }

        return view('incidencias.show', compact('incidencia', 'permisos', 'usuariosAsignacion', 'usuariosTransferencia', 'estadisticas'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Incidencia $incidencia)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        $estaciones = $this->getEstacionesParaFormulario($userRole, $user);

        $prioridades = [
            'critica' => 'Crítica',
            'alta' => 'Alta',
            'media' => 'Media', 
            'baja' => 'Baja'
        ];

        $estados = [
            'abierta' => 'Abierta',
            'en_proceso' => 'En Proceso',
            'resuelta' => 'Resuelta',
            'cerrada' => 'Cerrada',
            'cancelada' => 'Cancelada'
        ];

        $usuariosTecnicos = $this->getUsuariosParaAsignacion($userRole, $user);
        $camposEditables = $this->getCamposEditables($userRole, $incidencia);

        $categorias = [
            'tecnica' => 'Técnica',
            'operativa' => 'Operativa',
            'administrativa' => 'Administrativa',
            'infraestructura' => 'Infraestructura',
            'equipamiento' => 'Equipamiento',
            'informativa' => 'Informativa (Solo registro)'
        ];

        return view('incidencias.edit', compact(
            'incidencia', 
            'estaciones', 
            'prioridades', 
            'estados',
            'usuariosTecnicos',
            'camposEditables',
            'categorias'
        ));
    }

    /**
     * Actualizar incidencia
     */
    public function update(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        // Validación: el formulario envía descripcion_corta y descripcion_detallada
        $rules = [
            'descripcion_corta' => 'required|string|max:255',
            'descripcion_detallada' => 'required|string|min:10|max:2000',
            'estacion_id' => 'required|exists:estaciones,id',
            'prioridad' => 'required|in:critica,alta,media,baja',
            'categoria' => 'nullable|in:tecnica,operativa,administrativa,infraestructura,equipamiento,informativa',
            'impacto_servicio' => 'nullable|in:bajo,medio,alto',
        ];

        $camposEditables = $this->getCamposEditables($userRole, $incidencia);
        
        if ($camposEditables['puede_cambiar_estado']) {
            $rules['estado'] = 'required|in:abierta,en_proceso,resuelta,cerrada,cancelada';
        }
        
        if ($camposEditables['puede_asignar']) {
            $rules['asignado_a_user_id'] = 'nullable|exists:users,id';
        }
        
        if ($camposEditables['puede_gestionar_tiempo']) {
            $rules['tiempo_respuesta_estimado'] = 'nullable|integer|min:1|max:720';
            $rules['fecha_visita_programada'] = 'nullable|date';
            $rules['requiere_visita_tecnica'] = 'boolean';
        }
        
        if ($camposEditables['puede_documentar_solucion']) {
            $rules['solucion'] = 'nullable|string|max:2000';
            $rules['fecha_resolucion'] = 'nullable|date';
        }
        
        if ($camposEditables['puede_gestionar_costos']) {
            $rules['costo_soles'] = 'nullable|numeric|min:0';
            $rules['costo_dolares'] = 'nullable|numeric|min:0';
        }
        
        $rules['observaciones'] = 'nullable|string|max:1000';
        $rules['acciones_tomadas'] = 'nullable|string|max:2000';

        $validator = Validator::make($request->all(), $rules, [
            'descripcion_corta.required' => 'La descripción corta es obligatoria',
            'descripcion_detallada.required' => 'La descripción detallada es obligatoria',
            'descripcion_detallada.min' => 'La descripción detallada debe tener al menos 10 caracteres',
            'estacion_id.required' => 'Debe seleccionar una estación',
            'estacion_id.exists' => 'La estación seleccionada no existe',
            'prioridad.required' => 'Debe seleccionar una prioridad',
            'asignado_a_user_id.exists' => 'El usuario asignado no existe'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes asignar estaciones de tu sector.'
                ])->withInput();
            }
        }

        try {
            // Mapeo de campos del formulario → campos de la BD
            $incidencia->titulo = $validated['descripcion_corta'];
            $incidencia->descripcion = $validated['descripcion_detallada'];
            $incidencia->estacion_id = $validated['estacion_id'];
            $incidencia->prioridad = $validated['prioridad'];
            $incidencia->categoria = $validated['categoria'] ?? $incidencia->categoria;
            $incidencia->impacto_servicio = $validated['impacto_servicio'] ?? $incidencia->impacto_servicio;
            $incidencia->observaciones_tecnicas = $validated['observaciones'] ?? $incidencia->observaciones_tecnicas;

            // Estado
            if (isset($validated['estado']) && $camposEditables['puede_cambiar_estado']) {
                $incidencia->estado = $validated['estado'];
            }

            // Asignación
            if (isset($validated['asignado_a_user_id']) && $camposEditables['puede_asignar']) {
                $incidencia->asignado_a_user_id = $validated['asignado_a_user_id'];
                $incidencia->asignado_a = $validated['asignado_a_user_id']; // Sincronizar legacy
                
                if ($validated['asignado_a_user_id'] && $incidencia->estado === 'abierta') {
                    $incidencia->estado = 'en_proceso';
                }
            }

            // Campos opcionales según permisos
            if ($camposEditables['puede_gestionar_tiempo']) {
                if (isset($validated['tiempo_respuesta_estimado'])) {
                    $incidencia->tiempo_respuesta_estimado = $validated['tiempo_respuesta_estimado'];
                }
                if (isset($validated['fecha_visita_programada'])) {
                    $incidencia->fecha_visita_programada = $validated['fecha_visita_programada'];
                }
                $incidencia->requiere_visita_tecnica = $validated['requiere_visita_tecnica'] ?? false;
            }

            if ($camposEditables['puede_documentar_solucion']) {
                if (isset($validated['solucion'])) {
                    $incidencia->solucion = $validated['solucion'];
                }
            }

            if ($camposEditables['puede_gestionar_costos']) {
                if (isset($validated['costo_soles'])) {
                    $incidencia->costo_soles = $validated['costo_soles'];
                }
                if (isset($validated['costo_dolares'])) {
                    $incidencia->costo_dolares = $validated['costo_dolares'];
                }
            }

            // Lógica de resolución
            if ($incidencia->estado === 'resuelta' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }

            // Lógica de cierre
            if ($incidencia->estado === 'cerrada' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }

            $incidencia->save();

            $mensaje = $this->getMensajeActualizacion($userRole);

            return redirect()->route('incidencias.show', $incidencia)
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Error al actualizar la incidencia: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Eliminar incidencia (soft delete)
     */
    public function destroy(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if ($userRole !== RolUsuario::ADMINISTRADOR) {
            return back()->with('error', 'No tienes permisos para eliminar incidencias.');
        }

        if ($incidencia->estado_value === 'en_proceso' && $incidencia->prioridad_value === 'critica') {
            return back()->with('error', 'No se puede eliminar una incidencia crítica en proceso.');
        }

        try {
            $this->registrarEliminacionIncidencia($incidencia, $user, $request);

            $codigoEstacion = $incidencia->estacion->codigo;
            $tituloIncidencia = $incidencia->titulo;
            
            $incidencia->delete();
            
            return redirect()->route('incidencias.index')
                           ->with('success', "Incidencia '{$tituloIncidencia}' de la estación {$codigoEstacion} eliminada exitosamente.");
                           
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la incidencia: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de incidencia (AJAX desde index)
     */
    public function cambiarEstado(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();

        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        if (!$this->puedeCambiarEstado($incidencia, $user, $userRole)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos para cambiar el estado.'], 403);
        }

        $validated = $request->validate([
            'nuevo_estado' => 'required|in:abierta,en_proceso,resuelta,cerrada,cancelada',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $estadoAnterior = $incidencia->estado_value;
            $incidencia->estado = $validated['nuevo_estado'];

            if ($validated['nuevo_estado'] === 'resuelta' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }
            if ($validated['nuevo_estado'] === 'cerrada' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }

            $incidencia->save();

            \App\Models\IncidenciaHistorial::registrarCambioEstado(
                $incidencia,
                $estadoAnterior,
                $validated['nuevo_estado'],
                $user->id,
                $validated['observaciones'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'nuevo_estado' => $validated['nuevo_estado'],
            ]);
        } catch (\Exception $e) {
            Log::error("Error al cambiar estado de incidencia {$incidencia->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado.'], 500);
        }
    }

    /**
     * Transferir responsabilidad de incidencia a otra área
     */
    public function transferir(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();

        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        // Verificar permisos
        if (!$this->puedeTransferirIncidencia($incidencia, $user, $userRole)) {
            return back()->with('error', 'No tienes permisos para transferir esta incidencia.');
        }

        // Verificar que la incidencia sea transferible (estado abierta o en_proceso)
        if (!$incidencia->esTransferible()) {
            return back()->with('error', 'Esta incidencia no puede ser transferida en su estado actual. Solo se pueden transferir incidencias abiertas o en proceso.');
        }

        // Validar datos del formulario
        $validated = $request->validate([
            'area_nueva' => 'required|string|in:ingenieria,laboratorio,logistica,operaciones,administracion,contabilidad,iglesia_local',
            'observaciones' => 'required|string|min:10|max:500',
            'responsable_nuevo_id' => 'nullable|exists:users,id',
        ], [
            'area_nueva.required' => 'Debe especificar el área destino',
            'area_nueva.in' => 'El área seleccionada no es válida',
            'observaciones.required' => 'Las observaciones son obligatorias',
            'observaciones.min' => 'Las observaciones deben tener al menos 10 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
        ]);

        try {
            // Ejecutar transferencia
            $resultado = $incidencia->transferirResponsabilidad(
                $validated['area_nueva'],
                $validated['observaciones'],
                $user->id
            );

            if ($resultado) {
                // Si se envió un responsable_nuevo_id, actualizar la asignación
                if (!empty($validated['responsable_nuevo_id'])) {
                    $incidencia->asignado_a_user_id = $validated['responsable_nuevo_id'];
                    $incidencia->asignado_a = $validated['responsable_nuevo_id'];
                    $incidencia->save();
                }

                $areaLabels = [
                    'ingenieria' => 'Ingeniería',
                    'laboratorio' => 'Laboratorio',
                    'logistica' => 'Logística',
                    'operaciones' => 'Operaciones',
                    'administracion' => 'Administración',
                    'contabilidad' => 'Contabilidad',
                    'iglesia_local' => 'Iglesia Local',
                ];
                $areaLabel = $areaLabels[$validated['area_nueva']] ?? ucfirst($validated['area_nueva']);
                $mensaje = "Incidencia transferida exitosamente al área: " . $areaLabel;

                return redirect()
                    ->route('incidencias.show', $incidencia)
                    ->with('success', $mensaje);
            } else {
                return back()
                    ->with('error', 'Error al transferir la incidencia. Revise los datos e intente nuevamente.')
                    ->withInput();
            }

        } catch (\Exception $e) {
            Log::error("Error al transferir incidencia {$incidencia->id}: " . $e->getMessage());
            return back()
                ->with('error', 'Error al transferir la incidencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    // =====================================================
    // MÉTODOS PRIVADOS DE PERMISOS
    // =====================================================

    /**
     * Aplicar filtros según roles
     */
    private function aplicarFiltrosPorRol($query, $userRole, $user)
    {
        switch ($userRole) {
            case RolUsuario::ADMINISTRADOR:
                break;

            case RolUsuario::SECTORISTA:
                if ($user->sector_asignado) {
                    $query->whereHas('estacion', function($q) use ($user) {
                        $q->where('sector', $user->sector_asignado);
                    });
                }
                break;

            case RolUsuario::ENCARGADO_INGENIERIA:
            case RolUsuario::ENCARGADO_LABORATORIO:
            case RolUsuario::COORDINADOR_OPERACIONES:
            case RolUsuario::ENCARGADO_LOGISTICO:
            case RolUsuario::ASISTENTE_CONTABLE:
            case RolUsuario::GESTOR_RADIODIFUSION:
            case RolUsuario::VISOR:
            default:
                break;
        }
    }

    /**
     * Determinar campos editables por rol
     */
    private function getCamposEditables($userRole, $incidencia): array
    {
        $base = [
            'puede_cambiar_basicos' => true,
            'puede_cambiar_prioridad' => false,
            'puede_cambiar_estado' => false,
            'puede_asignar' => false,
            'puede_gestionar_tiempo' => false,
            'puede_documentar_solucion' => false,
            'puede_gestionar_costos' => false,
        ];

        switch ($userRole) {
            case RolUsuario::ADMINISTRADOR:
                return array_map(fn() => true, $base);

            case RolUsuario::SECTORISTA:
                return array_merge($base, [
                    'puede_cambiar_prioridad' => true,
                    'puede_cambiar_estado' => true,
                    'puede_asignar' => true,
                    'puede_gestionar_tiempo' => true,
                ]);

            case RolUsuario::ENCARGADO_INGENIERIA:
            case RolUsuario::ENCARGADO_LABORATORIO:
                return array_merge($base, [
                    'puede_cambiar_estado' => true,
                    'puede_documentar_solucion' => true,
                    'puede_gestionar_tiempo' => true,
                ]);

            case RolUsuario::COORDINADOR_OPERACIONES:
                return array_merge($base, [
                    'puede_cambiar_prioridad' => true,
                    'puede_cambiar_estado' => true,
                    'puede_asignar' => true,
                    'puede_gestionar_tiempo' => true,
                    'puede_documentar_solucion' => true,
                ]);

            case RolUsuario::ENCARGADO_LOGISTICO:
                return array_merge($base, [
                    'puede_gestionar_costos' => true,
                ]);

            default:
                return $base;
        }
    }

    /**
     * Verificar si el usuario puede ver la incidencia
     */
    private function puedeVerIncidencia($incidencia, $user, $userRole): bool
    {
        if ($userRole === RolUsuario::ADMINISTRADOR) return true;
        
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        // Todos los demás roles ven todas (con restricciones de edición)
        return true;
    }

    /**
     * Verificar si el usuario puede editar la incidencia
     */
    private function puedeEditarIncidencia($incidencia, $user, $userRole): bool
    {
        if ($userRole === RolUsuario::VISOR) return false;
        if (in_array($incidencia->estado_value, ['cerrada', 'cancelada'])) return false;
        if ($userRole === RolUsuario::ADMINISTRADOR) return true;
        
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        if (in_array($userRole, [
            RolUsuario::ENCARGADO_INGENIERIA,
            RolUsuario::ENCARGADO_LABORATORIO,
            RolUsuario::COORDINADOR_OPERACIONES
        ])) {
            return true;
        }

        if ($userRole === RolUsuario::ENCARGADO_LOGISTICO) {
            return str_contains(strtolower($incidencia->titulo), 'equipo') || 
                   str_contains(strtolower($incidencia->titulo), 'logistica');
        }
        
        if (in_array($userRole, [
            RolUsuario::ASISTENTE_CONTABLE,
            RolUsuario::GESTOR_RADIODIFUSION
        ])) {
            return false;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede eliminar la incidencia
     */
    private function puedeEliminarIncidencia($incidencia): bool
    {
        $user = Auth::user();
        return $user->rol === 'administrador';
    }

    /**
     * Verificar si el usuario puede asignar la incidencia
     */
    private function puedeAsignarIncidencia($incidencia, $user, $userRole): bool
    {
        if (in_array($incidencia->estado_value, ['cerrada', 'cancelada'])) {
            return false;
        }

        return in_array($userRole, [
            RolUsuario::ADMINISTRADOR,
            RolUsuario::SECTORISTA,
            RolUsuario::COORDINADOR_OPERACIONES
        ]);
    }

    /**
     * Verificar si el usuario puede cambiar el estado
     */
    private function puedeCambiarEstado($incidencia, $user, $userRole): bool
    {
        return $this->puedeEditarIncidencia($incidencia, $user, $userRole) &&
               !in_array($incidencia->estado_value, ['cerrada']);
    }

    /**
     * ✅ MÉTODO QUE FALTABA: Verificar si el usuario puede transferir la incidencia
     * 
     * Condiciones para poder transferir:
     *  - La incidencia debe estar en estado abierta o en_proceso
     *  - El usuario debe tener un rol con permisos de transferencia
     */
    private function puedeTransferirIncidencia($incidencia, $user, $userRole): bool
    {
        // Solo se puede transferir si la incidencia está activa
        if (!$incidencia->esTransferible()) {
            return false;
        }

        // Roles que pueden transferir
        return in_array($userRole, [
            RolUsuario::ADMINISTRADOR,
            RolUsuario::SECTORISTA,
            RolUsuario::COORDINADOR_OPERACIONES,
            RolUsuario::ENCARGADO_INGENIERIA,
            RolUsuario::ENCARGADO_LABORATORIO
        ]);
    }

    // =====================================================
    // MÉTODOS AUXILIARES
    // =====================================================

    private function getMensajeActualizacion($userRole): string
    {
        return match($userRole) {
            RolUsuario::ADMINISTRADOR => 'Incidencia actualizada exitosamente.',
            RolUsuario::SECTORISTA => 'Incidencia actualizada. Los cambios han sido registrados para tu sector.',
            RolUsuario::ENCARGADO_INGENIERIA => 'Información técnica actualizada exitosamente.',
            RolUsuario::ENCARGADO_LABORATORIO => 'Diagnóstico y solución técnica actualizados.',
            RolUsuario::COORDINADOR_OPERACIONES => 'Incidencia gestionada exitosamente.',
            RolUsuario::ENCARGADO_LOGISTICO => 'Información de costos y logística actualizada.',
            default => 'Incidencia actualizada exitosamente.'
        };
    }

    private function registrarEliminacionIncidencia($incidencia, $user, $request)
    {
        try {
            DB::statement("CREATE TABLE IF NOT EXISTS auditoria_incidencias (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                incidencia_id BIGINT NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                estacion_codigo VARCHAR(50) NOT NULL,
                estado VARCHAR(50) NOT NULL,
                prioridad VARCHAR(50) NOT NULL,
                eliminado_por BIGINT NOT NULL,
                eliminado_por_nombre VARCHAR(255) NOT NULL,
                razon_eliminacion TEXT,
                fecha_eliminacion TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            DB::table('auditoria_incidencias')->insert([
                'incidencia_id' => $incidencia->id,
                'titulo' => $incidencia->titulo,
                'estacion_codigo' => $incidencia->estacion->codigo,
                'estado' => $incidencia->estado_value,
                'prioridad' => $incidencia->prioridad_value,
                'eliminado_por' => $user->id,
                'eliminado_por_nombre' => $user->name,
                'razon_eliminacion' => $request->input('razon', 'Sin razón especificada'),
                'fecha_eliminacion' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error("Error en auditoría de eliminación: " . $e->getMessage());
        }
    }

    private function getEstacionesParaFiltro($userRole, $user)
    {
        $query = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                         ->orderBy('codigo');

        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $query->where('sector', $user->sector_asignado);
        }

        return $query->get();
    }

    private function getEstacionesParaFormulario($userRole, $user)
    {
        $query = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                         ->where('activa', 1)
                         ->orderBy('codigo');

        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $query->where('sector', $user->sector_asignado);
        }

        return $query->get();
    }

    private function getUsuariosParaAsignacion($userRole, $user)
    {
        if (!in_array($userRole, [
            RolUsuario::ADMINISTRADOR,
            RolUsuario::SECTORISTA,
            RolUsuario::COORDINADOR_OPERACIONES
        ])) {
            return collect();
        }

        return User::where('activo', 1)
                  ->whereIn('rol', [
                      'administrador',
                      'sectorista',
                      'encargado_ingenieria',
                      'encargado_laboratorio',
                      'coordinador_operaciones',
                      'encargado_logistico'
                  ])
                  ->select('id', 'name', 'rol', 'sector_asignado', 'area_especialidad')
                  ->orderBy('name')
                  ->get();
    }

    private function calcularEstadisticas($userRole = null, $user = null): array
    {
        $query = Incidencia::query();

        if ($userRole) {
            $this->aplicarFiltrosPorRol($query, $userRole, $user);
        }

        return [
            'total' => $query->count(),
            'abiertas' => (clone $query)->where('estado', 'abierta')->count(),
            'en_proceso' => (clone $query)->where('estado', 'en_proceso')->count(),
            'resuelta' => (clone $query)->where('estado', 'resuelta')->count(),
            'cerrada' => (clone $query)->where('estado', 'cerrada')->count(),
            'criticas' => (clone $query)->where('prioridad', 'critica')->count(),
            'altas' => (clone $query)->where('prioridad', 'alta')->count(),
            'vencidas' => (clone $query)->where('fecha_reporte', '<', now()->subHours(24))
                                       ->whereIn('estado', ['abierta', 'en_proceso'])
                                       ->count(),
            'resueltasHoy' => (clone $query)->where('estado', 'resuelta')
                                            ->whereDate('fecha_resolucion', now()->toDateString())
                                            ->count(),
            'resueltas_mes' => (clone $query)->where('estado', 'resuelta')
                                             ->whereMonth('fecha_resolucion', now()->month)
                                             ->count(),
            'nuevas_hoy' => (clone $query)->whereDate('fecha_reporte', now()->toDateString())->count(),
            'pendientes' => (clone $query)->whereIn('estado', ['abierta', 'en_proceso'])->count()
        ];
    }

    // =====================================================
    // EXPORTACIONES
    // =====================================================

    public function exportarPdf(Request $request)
    {
        $user = Auth::user();

        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        $columnasDisponibles = [
            'codigo' => 'Código',
            'estacion' => 'Estación',
            'localidad' => 'Localidad',
            'titulo' => 'Título',
            'prioridad' => 'Prioridad',
            'estado' => 'Estado',
            'tipo' => 'Tipo',
            'area_responsable' => 'Área Resp.',
            'reportado_por' => 'Reportado Por',
            'asignado_a' => 'Asignado A',
            'fecha_reporte' => 'Fecha',
            'dias_transcurridos' => 'Días',
        ];

        $columnasDefecto = ['codigo', 'estacion', 'titulo', 'prioridad', 'estado', 'area_responsable', 'fecha_reporte', 'dias_transcurridos'];
        $columnasSeleccionadas = $request->input('columnas', $columnasDefecto);

        if (is_string($columnasSeleccionadas)) {
            $columnasSeleccionadas = explode(',', $columnasSeleccionadas);
        }

        $columnasSeleccionadas = array_intersect($columnasSeleccionadas, array_keys($columnasDisponibles));

        $query = Incidencia::with([
            'estacion:id,codigo,localidad,razon_social,sector',
            'reportadoPorUsuario:id,name',
            'asignadoAUsuario:id,name'
        ]);

        $this->aplicarFiltrosPorRol($query, $userRole, $user);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('estacion')) {
            $query->where('estacion_id', $request->estacion);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('area')) {
            $query->where('area_responsable_actual', $request->area);
        }

        $incidencias = $query->orderBy('fecha_reporte', 'desc')->get();

        $columnas = [];
        foreach ($columnasSeleccionadas as $key) {
            $columnas[$key] = $columnasDisponibles[$key];
        }

        $estadisticas = [
            'total' => $incidencias->count(),
            'abiertas' => $incidencias->where('estado_value', 'abierta')->count(),
            'en_proceso' => $incidencias->where('estado_value', 'en_proceso')->count(),
            'cerradas' => $incidencias->where('estado_value', 'cerrada')->count(),
            'criticas' => $incidencias->where('prioridad_value', 'critica')->count(),
        ];

        $titulo = 'Listado de Incidencias';
        $filtrosAplicados = [];

        if ($request->filled('prioridad')) {
            $filtrosAplicados[] = 'Prioridad: ' . ucfirst($request->prioridad);
        }
        if ($request->filled('estado')) {
            $filtrosAplicados[] = 'Estado: ' . ucfirst(str_replace('_', ' ', $request->estado));
        }
        if ($request->filled('tipo')) {
            $filtrosAplicados[] = 'Tipo: ' . ucfirst($request->tipo);
        }
        if ($request->filled('area')) {
            $filtrosAplicados[] = 'Área: ' . $request->area;
        }

        $html = view('incidencias.pdf', compact(
            'incidencias',
            'columnas',
            'estadisticas',
            'titulo',
            'filtrosAplicados'
        ))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('incidencias_' . date('Y-m-d') . '.pdf');
    }

    public function exportarExcel(Request $request)
    {
        $user = Auth::user();

        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            $userRole = RolUsuario::VISOR;
        }

        $filtros = [
            'search' => $request->input('search'),
            'estacion' => $request->input('estacion'),
            'prioridad' => $request->input('prioridad'),
            'estado' => $request->input('estado'),
            'tipo' => $request->input('tipo'),
            'area' => $request->input('area'),
            'reportado_por_usuario' => $request->input('reportado_por_usuario'),
            'asignado_a_usuario' => $request->input('asignado_a_usuario'),
        ];

        $columnasDefecto = ['codigo', 'estacion', 'localidad', 'titulo', 'prioridad', 'estado', 'tipo', 'area_responsable', 'reportado_por', 'asignado_a', 'fecha_reporte', 'dias_transcurridos'];
        $columnas = $request->input('columnas', $columnasDefecto);

        if (is_string($columnas)) {
            $columnas = explode(',', $columnas);
        }

        $export = new \App\Exports\IncidenciasExport($filtros, $columnas);

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'incidencias_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function columnasExportacion()
    {
        return response()->json([
            'columnas' => [
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
                'fecha_reporte' => 'Fecha Reporte',
                'fecha_resolucion' => 'Fecha Resolución',
                'dias_transcurridos' => 'Días Transcurridos',
                'costo_soles' => 'Costo (S/.)',
                'costo_dolares' => 'Costo (USD)',
                'transferencias' => 'N° Transferencias',
            ],
            'defecto' => ['codigo', 'estacion', 'titulo', 'prioridad', 'estado', 'area_responsable', 'reportado_por', 'asignado_a', 'fecha_reporte', 'dias_transcurridos']
        ]);
    }
}
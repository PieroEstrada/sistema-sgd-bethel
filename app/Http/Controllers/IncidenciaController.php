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
     * Mostrar lista de incidencias - ✅ ACTUALIZADO PARA NUEVOS ROLES
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ✅ MANEJAR TRANSICIÓN DE ROLES
        try {
            $userRole = $user->rol instanceof RolUsuario
                ? $user->rol
                : RolUsuario::from((string) $user->rol);
        } catch (\ValueError $e) {
            // Si rol antiguo no existe en enum, usar visor por defecto
            $userRole = RolUsuario::VISOR;
        }

        // 🎯 BASE QUERY CON RELACIONES (MANTENER TU IMPLEMENTACIÓN)
        $query = Incidencia::with([
            'estacion:id,codigo,localidad,razon_social,sector',  
            'reportadoPorUsuario:id,name,rol,email,telefono',  
            'asignadoAUsuario:id,name,rol,email,telefono'     
        ]);

        // 🔐 APLICAR FILTROS POR ROL - ✅ ACTUALIZADO
        $this->aplicarFiltrosPorRol($query, $userRole, $user);

        // 📊 APLICAR FILTROS DE BÚSQUEDA (MANTENER TU IMPLEMENTACIÓN)
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

        // Filtro por área responsable
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

        // 📄 PAGINACIÓN CON OPCIONES (15, 25, 50, 100)
        $perPage = in_array($request->input('per_page'), [15, 25, 50, 100])
            ? (int) $request->input('per_page')
            : 15;

        $incidencias = $query->orderBy('fecha_reporte', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage)
                            ->appends($request->query());

        // 🎯 AGREGAR PERMISOS A CADA INCIDENCIA (ACTUALIZADO)
        $incidencias->getCollection()->transform(function($incidencia) use ($user, $userRole) {
            $incidencia->puede_editar = $this->puedeEditarIncidencia($incidencia, $user, $userRole);
            $incidencia->puede_eliminar = $this->puedeEliminarIncidencia($incidencia);
            $incidencia->puede_cambiar_estado = $this->puedeCambiarEstado($incidencia, $user, $userRole);
            $incidencia->puede_asignar = $this->puedeAsignarIncidencia($incidencia, $user, $userRole);
            return $incidencia;
        });

        // 📊 OBTENER DATOS PARA FILTROS (ACTUALIZADO)
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
     * Mostrar formulario para crear incidencia - ✅ ACTUALIZADO
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

        // Verificar permisos para crear
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

        // Categorías de incidencia
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
     * Almacenar nueva incidencia - ✅ MANTENER TU VALIDACIÓN, ACTUALIZAR ROLES
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

        // Verificar permisos
        if ($userRole === RolUsuario::VISOR) {
            abort(403, 'No tienes permisos para crear incidencias.');
        }

        // Obtener categoría para validación condicional
        $categoria = $request->input('categoria');
        $esInformativa = $categoria === 'informativa';

        // ✅ VALIDACIÓN ACTUALIZADA - campos del formulario real
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

        // La prioridad solo es obligatoria si NO es informativa
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

        // Mapear campos del formulario a campos del modelo
        $validated['titulo'] = $validated['descripcion_corta'];
        $validated['descripcion'] = $validated['descripcion_detallada'];

        // ✅ VERIFICACIÓN DE SECTOR ACTUALIZADA
        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes crear incidencias en estaciones de tu sector asignado.'
                ])->withInput();
            }
        }

        try {
            // ✅ CREAR INCIDENCIA CON CAMPOS CORRECTOS
            $incidencia = new Incidencia();
            $incidencia->estacion_id = $validated['estacion_id'];
            $incidencia->titulo = $validated['titulo'];
            $incidencia->descripcion = $validated['descripcion'];
            $incidencia->prioridad = $validated['prioridad'] ?? 'baja';
            $incidencia->categoria = $validated['categoria'] ?? null;
            $incidencia->impacto_servicio = $validated['impacto_servicio'] ?? null;
            $incidencia->observaciones = $validated['observaciones'] ?? null;

            // Reportado por
            $incidencia->reportado_por_user_id = $validated['reportado_por_user_id'] ?? $user->id;
            $incidencia->reportado_por = $validated['reportado_por_user_id'] ?? $user->id;
            $incidencia->fecha_reporte = now();

            // Asignar área responsable (ya no usuario)
            $incidencia->area_responsable_actual = $validated['area_responsable'] ?? null;

            // Si es categoría informativa, se crea con estado cerrada (finalizado)
            if ($esInformativa) {
                $incidencia->estado = 'cerrada'; // Se muestra como "Finalizado"
                $incidencia->fecha_resolucion = now();
            } else {
                // Si hay área asignada, poner en proceso
                if ($incidencia->area_responsable_actual) {
                    $incidencia->estado = 'en_proceso';
                } else {
                    $incidencia->estado = 'abierta';
                }
            }

            $incidencia->save();

            // ✅ REGISTRAR CREACIÓN EN HISTORIAL
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
     * Mostrar incidencia específica - ✅ MANTENER TU IMPLEMENTACIÓN
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

        // ✅ CARGAR RELACIONES INCLUYENDO HISTORIAL
        $incidencia->load([
            'estacion:id,codigo,razon_social,localidad,provincia,departamento,sector',
            'reportadoPorUsuario:id,name,email,telefono,rol',
            'asignadoAUsuario:id,name,email,telefono,rol',
            'responsableActual:id,name,email,telefono,rol',
            'historial.usuarioAccion:id,name',
            'historial.responsableAnterior:id,name',
            'historial.responsableNuevo:id,name'
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

        // Usuarios disponibles para transferencia
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

        // Calcular estadísticas de tiempo
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

        // ✅ OBTENER HISTORIAL REAL DESDE LA BASE DE DATOS
        // Si no hay registros en historial, crear el registro inicial
        if ($incidencia->historial->isEmpty()) {
            \App\Models\IncidenciaHistorial::registrarCreacion(
                $incidencia,
                $incidencia->reportado_por_user_id ?? auth()->id(),
                'Registro inicial generado automáticamente'
            );
            // Recargar historial
            $incidencia->load('historial.usuarioAccion');
        }

        return view('incidencias.show', compact('incidencia', 'permisos', 'usuariosAsignacion', 'usuariosTransferencia', 'estadisticas'));
    }

    /**
     * ✅ MÉTODO EDIT COMPLETO CON NUEVOS ROLES
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

        // Obtener estaciones según el rol
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

        // ✅ USUARIOS TÉCNICOS ACTUALIZADOS
        $usuariosTecnicos = $this->getUsuariosParaAsignacion($userRole, $user);

        // ✅ VERIFICAR QUÉ CAMPOS PUEDE EDITAR SEGÚN EL ROL
        $camposEditables = $this->getCamposEditables($userRole, $incidencia);

        // Categorías de incidencia
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
     * ✅ MÉTODO UPDATE COMPLETO CON NUEVOS ROLES Y VALIDACIONES
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

        // ✅ VALIDACIÓN DIFERENCIADA POR ROL
        $rules = [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|min:10',
            'estacion_id' => 'required|exists:estaciones,id',
            'prioridad' => 'required|in:critica,alta,media,baja',
        ];

        // Campos adicionales según el rol
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
        
        $rules['observaciones_tecnicas'] = 'nullable|string|max:1000';

        $validator = Validator::make($request->all(), $rules, [
            'titulo.required' => 'El título es obligatorio',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres',
            'estacion_id.required' => 'Debe seleccionar una estación',
            'estacion_id.exists' => 'La estación seleccionada no existe',
            'prioridad.required' => 'Debe seleccionar una prioridad',
            'asignado_a_user_id.exists' => 'El usuario asignado no existe'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // ✅ VERIFICACIONES ESPECÍFICAS POR ROL
        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes asignar estaciones de tu sector.'
                ])->withInput();
            }
        }

        // ✅ LÓGICA ESPECÍFICA POR TIPO DE USUARIO
        if ($userRole === RolUsuario::ENCARGADO_LOGISTICO) {
            // Solo puede editar aspectos de costos y equipos
            $validated = array_intersect_key($validated, array_flip([
                'observaciones_tecnicas', 'costo_soles', 'costo_dolares', 'descripcion'
            ]));
        }

        try {
            // Actualizar solo los campos permitidos según el rol
            $incidencia->fill($validated);

            // ✅ LÓGICA DE ASIGNACIÓN
            if (isset($validated['asignado_a_user_id']) && $camposEditables['puede_asignar']) {
                $incidencia->asignado_a = $validated['asignado_a_user_id'];
                
                // Auto-cambiar estado si se asigna
                if ($validated['asignado_a_user_id'] && $incidencia->estado === 'abierta') {
                    $incidencia->estado = 'en_proceso';
                }
            }

            // ✅ LÓGICA DE RESOLUCIÓN
            if (isset($validated['estado']) && $validated['estado'] === 'resuelta' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }

            // ✅ LÓGICA DE CIERRE
            if (isset($validated['estado']) && $validated['estado'] === 'cerrada') {
                if (!$incidencia->fecha_resolucion) {
                    $incidencia->fecha_resolucion = now();
                }
                $incidencia->fecha_cierre = now();
                $incidencia->cerrado_por = $user->id;
            }

            $incidencia->save();

            // ✅ MENSAJE PERSONALIZADO POR ROL
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
     * ✅ MÉTODO DESTROY COMPLETO CON AUDITORÍA
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


        // Solo administrador puede eliminar
        if ($userRole !== RolUsuario::ADMINISTRADOR) {
            return back()->with('error', 'No tienes permisos para eliminar incidencias.');
        }

        // Verificar que no esté en proceso crítico
        if ($incidencia->estado_value === 'en_proceso' && $incidencia->prioridad_value === 'critica') {
            return back()->with('error', 'No se puede eliminar una incidencia crítica en proceso.');
        }

        try {
            // ✅ AUDITORÍA ANTES DE ELIMINAR (crear tabla si no existe)
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
     * Transferir responsabilidad de incidencia a otra área/usuario
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

        // Verificar que la incidencia sea transferible
        if (!$incidencia->esTransferible()) {
            return back()->with('error', 'Esta incidencia no puede ser transferida en su estado actual. Solo se pueden transferir incidencias abiertas o en proceso.');
        }

        // Validar request (solo área requerida, observaciones opcionales)
        $validated = $request->validate([
            'area_nueva' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'area_nueva.required' => 'Debe especificar el área destino',
            'area_nueva.max' => 'El área no puede exceder 100 caracteres',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
        ]);

        try {
            // Ejecutar transferencia (sin responsable específico, solo área)
            $resultado = $incidencia->transferirResponsabilidad(
                $validated['area_nueva'],
                (string) ($validated['observaciones'] ?? ''),
                $user->id
            );

            if ($resultado) {
                $mensaje = "Incidencia transferida exitosamente al área: {$validated['area_nueva']}";

                return redirect()
                    ->route('incidencias.show', $incidencia)
                    ->with('success', $mensaje);
            } else {
                return back()
                    ->withErrors(['error' => 'Error al transferir la incidencia'])
                    ->withInput();
            }

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al transferir la incidencia: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // =====================================================
    // MÉTODOS PRIVADOS NUEVOS Y ACTUALIZADOS
    // =====================================================

    /**
     * ✅ NUEVO: Aplicar filtros según nuevos roles
     */
    private function aplicarFiltrosPorRol($query, $userRole, $user)
    {
        switch ($userRole) {
            case RolUsuario::ADMINISTRADOR:
                // Ve todas las incidencias
                break;

            case RolUsuario::SECTORISTA:
                // Solo incidencias de su sector
                if ($user->sector_asignado) {
                    $query->whereHas('estacion', function($q) use ($user) {
                        $q->where('sector', $user->sector_asignado);
                    });
                }
                break;

            case RolUsuario::ENCARGADO_INGENIERIA:
            case RolUsuario::ENCARGADO_LABORATORIO:
            case RolUsuario::COORDINADOR_OPERACIONES:
                // Ve todas, puede gestionar según especialidad
                break;

            case RolUsuario::ENCARGADO_LOGISTICO:
            case RolUsuario::ASISTENTE_CONTABLE:
            case RolUsuario::GESTOR_RADIODIFUSION:
            case RolUsuario::VISOR:
                // Ve todas las incidencias (solo lectura)
                break;

            default:
                // Fallback: acceso completo si rol no reconocido
                break;
        }
    }

    /**
     * ✅ NUEVO: Determinar campos editables por rol
     */
    private function getCamposEditables($userRole, $incidencia): array
    {
        $base = [
            'puede_cambiar_basicos' => true, // título, descripción
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
                return $base; // Solo cambios básicos
        }
    }

    /**
     * ✅ NUEVO: Mensaje de actualización personalizado
     */
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

    /**
     * ✅ NUEVO: Registrar eliminación para auditoría
     */
    private function registrarEliminacionIncidencia($incidencia, $user, $request)
    {
        try {
            // Crear tabla de auditoría si no existe
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

    /**
     * ✅ PERMISOS DE VISUALIZACIÓN ACTUALIZADOS
     */
    private function puedeVerIncidencia($incidencia, $user, $userRole): bool
    {
        // Administrador ve todas
        if ($userRole === RolUsuario::ADMINISTRADOR) return true;
        
        // Sectorista: solo las de su sector
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        // Roles técnicos ven todas
        if (in_array($userRole, [
            RolUsuario::ENCARGADO_INGENIERIA,
            RolUsuario::ENCARGADO_LABORATORIO,
            RolUsuario::COORDINADOR_OPERACIONES,
            RolUsuario::ENCARGADO_LOGISTICO
        ])) {
            return true;
        }
        
        // Roles administrativos/financieros ven todas (solo lectura)
        if (in_array($userRole, [
            RolUsuario::ASISTENTE_CONTABLE,
            RolUsuario::GESTOR_RADIODIFUSION,
            RolUsuario::VISOR
        ])) {
            return true;
        }

        // ⚠️ LEGACY: mantener lógica antigua para roles antiguos
        if (isset($user->estaciones_asignadas) && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            return in_array($incidencia->estacion_id, $estacionesAsignadas);
        }
        
        return true;
    }

    /**
     * ✅ PERMISOS DE EDICIÓN ACTUALIZADOS
     */
    private function puedeEditarIncidencia($incidencia, $user, $userRole): bool
    {
        // Visor no puede editar
        if ($userRole === RolUsuario::VISOR) return false;
        
        // No editar si está cerrada
        if (in_array($incidencia->estado_value, ['cerrada', 'cancelada'])) return false;
        
        // Administrador puede editar todas
        if ($userRole === RolUsuario::ADMINISTRADOR) return true;
        
        // Sectorista: solo su sector
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        // Roles técnicos pueden editar
        if (in_array($userRole, [
            RolUsuario::ENCARGADO_INGENIERIA,
            RolUsuario::ENCARGADO_LABORATORIO,
            RolUsuario::COORDINADOR_OPERACIONES
        ])) {
            return true;
        }

        // Encargado logístico: solo incidencias relacionadas
        if ($userRole === RolUsuario::ENCARGADO_LOGISTICO) {
            return str_contains(strtolower($incidencia->titulo), 'equipo') || 
                   str_contains(strtolower($incidencia->titulo), 'logistica');
        }
        
        // Roles de solo lectura: no pueden editar
        if (in_array($userRole, [
            RolUsuario::ASISTENTE_CONTABLE,
            RolUsuario::GESTOR_RADIODIFUSION
        ])) {
            return false;
        }

        return false;
    }

    /**
     * ✅ PERMISOS DE ELIMINACIÓN ACTUALIZADOS
     */
    private function puedeEliminarIncidencia($incidencia): bool
    {
        $user = Auth::user();
        return $user->rol === 'administrador'; // Solo administrador
    }

    /**
     * ✅ PERMISOS DE ASIGNACIÓN ACTUALIZADOS
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
     * ✅ OBTENER ESTACIONES SEGÚN ROL
     */
    private function getEstacionesParaFiltro($userRole, $user)
    {
        $query = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                         ->orderBy('codigo');

        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $query->where('sector', $user->sector_asignado);
        }

        return $query->get();
    }

    /**
     * ✅ OBTENER ESTACIONES PARA FORMULARIO
     */
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

    /**
     * ✅ OBTENER USUARIOS PARA ASIGNACIÓN SEGÚN NUEVOS ROLES
     */
    private function getUsuariosParaAsignacion($userRole, $user)
    {
        // Solo ciertos roles pueden asignar
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

    /**
     * ✅ CALCULAR ESTADÍSTICAS ACTUALIZADO
     */
    private function calcularEstadisticas($userRole = null, $user = null): array
    {
        $query = Incidencia::query();

        // Aplicar mismo filtro que en index
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

    /**
     * ✅ MÉTODO AUXILIAR PARA CAMBIO DE ESTADO
     */
    private function puedeCambiarEstado($incidencia, $user, $userRole): bool
    {
        return $this->puedeEditarIncidencia($incidencia, $user, $userRole) &&
               !in_array($incidencia->estado_value, ['cerrada']);
    }

    // =====================================================
    // MÉTODOS DE EXPORTACIÓN
    // =====================================================

    /**
     * Exportar incidencias a PDF con filtros y selección de columnas
     */
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

        // Columnas disponibles
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

        // Columnas seleccionadas (por defecto, las principales)
        $columnasDefecto = ['codigo', 'estacion', 'titulo', 'prioridad', 'estado', 'area_responsable', 'fecha_reporte', 'dias_transcurridos'];
        $columnasSeleccionadas = $request->input('columnas', $columnasDefecto);

        // Si es una cadena separada por comas, convertir a array
        if (is_string($columnasSeleccionadas)) {
            $columnasSeleccionadas = explode(',', $columnasSeleccionadas);
        }

        // Filtrar solo columnas válidas
        $columnasSeleccionadas = array_intersect($columnasSeleccionadas, array_keys($columnasDisponibles));

        // Query base con relaciones
        $query = Incidencia::with([
            'estacion:id,codigo,localidad,razon_social,sector',
            'reportadoPorUsuario:id,name',
            'asignadoAUsuario:id,name',
            'responsableActual:id,name'
        ]);

        // Aplicar filtros por rol
        $this->aplicarFiltrosPorRol($query, $userRole, $user);

        // Aplicar los mismos filtros que en index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_incidencia', 'LIKE', "%{$search}%");
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

        if ($request->filled('reportado_por_usuario')) {
            $query->where('reportado_por_user_id', $request->reportado_por_usuario);
        }

        if ($request->filled('asignado_a_usuario')) {
            $query->where('asignado_a_user_id', $request->asignado_a_usuario);
        }

        $incidencias = $query->orderBy('fecha_reporte', 'desc')->get();

        // Preparar columnas para la vista
        $columnas = [];
        foreach ($columnasSeleccionadas as $key) {
            $columnas[$key] = $columnasDisponibles[$key];
        }

        // Estadísticas
        $estadisticas = [
            'total' => $incidencias->count(),
            'abiertas' => $incidencias->where('estado_value', 'abierta')->count(),
            'en_proceso' => $incidencias->where('estado_value', 'en_proceso')->count(),
            'cerradas' => $incidencias->where('estado_value', 'cerrada')->count(),
            'criticas' => $incidencias->where('prioridad_value', 'critica')->count(),
        ];

        // Título del reporte basado en filtros
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

    /**
     * Exportar incidencias a Excel con filtros y selección de columnas
     */
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

        // Obtener filtros desde request
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

        // Columnas seleccionadas
        $columnasDefecto = ['codigo', 'estacion', 'localidad', 'titulo', 'prioridad', 'estado', 'tipo', 'area_responsable', 'reportado_por', 'asignado_a', 'fecha_reporte', 'dias_transcurridos'];
        $columnas = $request->input('columnas', $columnasDefecto);

        // Si es una cadena separada por comas, convertir a array
        if (is_string($columnas)) {
            $columnas = explode(',', $columnas);
        }

        $export = new \App\Exports\IncidenciasExport($filtros, $columnas);

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'incidencias_' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Retornar columnas disponibles para el modal de exportación
     */
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
                'responsable_actual' => 'Responsable Actual',
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
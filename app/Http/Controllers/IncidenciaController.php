<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\Estacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\PrioridadIncidencia;
use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;

class IncidenciaController extends Controller
{
    /**
     * Mostrar lista de incidencias con filtros y control de permisos por roles
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🎯 BASE QUERY CON RELACIONES CORRECTAS
        $query = Incidencia::with([
            'estacion:id,codigo,razon_social',  
            'reportadoPorUsuario:id,name,rol,email',   // ✅ Relación correcta
            'asignadoAUsuario:id,name,rol,email'       // ✅ Relación correcta
        ]);

        // 🔐 FILTROS POR ROL
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            // Sectorista: solo incidencias de su sector
            $query->whereHas('estacion', function($q) use ($user) {
                $q->where('sector', $user->sector_asignado);
            });
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            // Jefe de estación: solo sus estaciones asignadas
            $query->whereIn('estacion_id', $user->estaciones_asignadas);
        } elseif ($userRole === RolUsuario::OPERADOR) {
            // Operador: solo las que reportó (si se desea restricción)
            // Comentar la siguiente línea si quieres que vea todas
            // $query->where('reportado_por_user_id', $user->id);
        }

        // Filtro de búsqueda por código o descripción
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")      // ✅ Campo correcto
                ->orWhere('descripcion', 'LIKE', "%{$search}%"); // ✅ Campo correcto
            });
        }

        // Filtro por estación
        if ($request->filled('estacion')) {
            $query->where('estacion_id', $request->estacion);
        }

        // Filtro por prioridad
        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por usuario reportante (nuevo)
        if ($request->filled('reportado_por_usuario')) {
            $query->where('reportado_por_user_id', $request->reportado_por_usuario);
        }

        // Filtro por usuario asignado (nuevo)
        if ($request->filled('asignado_a_usuario')) {
            $query->where('asignado_a_user_id', $request->asignado_a_usuario);
        }

        // Ordenar por más recientes primero
        $incidencias = $query->orderBy('created_at', 'desc')->paginate(15);

        // 🎯 AGREGAR PERMISOS A CADA INCIDENCIA
        $incidencias->getCollection()->transform(function($incidencia) use ($user, $userRole) {
            $incidencia->puede_editar = $this->puedeEditarIncidencia($incidencia, $user, $userRole);
            $incidencia->puede_eliminar = $this->puedeEliminarIncidencia($incidencia);
            $incidencia->puede_cambiar_estado = $this->puedeCambiarEstado($incidencia, $user, $userRole);
            return $incidencia;
        });

        // Cargar datos para filtros (filtrados por permisos)
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estaciones = Estacion::where('sector', $user->sector_asignado)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estaciones = Estacion::whereIn('id', $user->estaciones_asignadas)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } else {
            $estaciones = Estacion::select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        }
        
        // Usuarios para filtros
        $usuarios = User::where('activo', true)
                       ->select('id', 'name', 'rol')
                       ->orderBy('name')
                       ->get();

        // Calcular estadísticas
        $estadisticas = $this->calcularEstadisticas();

        // 🔐 PERMISOS PARA LA VISTA
        $permisos = [
            'puede_crear' => $userRole->puedeGestionarIncidencias() || $userRole === RolUsuario::OPERADOR,
            'puede_exportar' => $userRole->puedeAcceder('ver_informes_todos') || 
                              $userRole->puedeAcceder('ver_informes_sector'),
            'puede_eliminar_global' => $userRole->esAdministrativo(),
            'es_solo_lectura' => $userRole === RolUsuario::CONSULTA,
        ];

        return view('incidencias.index', compact(
            'incidencias', 
            'estaciones', 
            'usuarios',
            'estadisticas',
            'permisos'
        ));
    }

    /**
     * Mostrar formulario para crear incidencia
     */
    public function create()
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS
        if (!$userRole->puedeGestionarIncidencias() && $userRole !== RolUsuario::OPERADOR) {
            abort(403, 'No tienes permisos para crear incidencias.');
        }

        // Estaciones según permisos
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estaciones = Estacion::where('sector', $user->sector_asignado)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estaciones = Estacion::whereIn('id', $user->estaciones_asignadas)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } else {
            $estaciones = Estacion::select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        }

        $prioridades = [
            'baja' => 'Baja',
            'media' => 'Media', 
            'alta' => 'Alta',
            'critica' => 'Crítica'  // ✅ Agregada crítica
        ];

        $categorias = [
            'tecnica' => 'Técnica',
            'infraestructura' => 'Infraestructura',
            'conectividad' => 'Conectividad',
            'energia' => 'Energía',
            'software' => 'Software',
            'otros' => 'Otros'
        ];

        return view('incidencias.create', compact('estaciones', 'prioridades', 'categorias'));
    }

    /**
     * Guardar nueva incidencia
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS
        if (!$userRole->puedeGestionarIncidencias() && $userRole !== RolUsuario::OPERADOR) {
            abort(403, 'No tienes permisos para crear incidencias.');
        }

        $validated = $request->validate([
            'estacion_id' => 'required|exists:estaciones,id',
            'reportado_por_user_id' => 'required|exists:users,id',  // ✅ Campo correcto del original
            'titulo' => 'required|string|max:255',          // ✅ Campo correcto del original
            'descripcion' => 'required|string|max:2000|min:20', // ✅ Campo correcto del original
            'prioridad' => 'required|in:critica,alta,media,baja', // ✅ Con crítica
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // 🎯 VERIFICAR RESTRICCIONES POR ROL
        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector->value !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes crear incidencias en estaciones de tu sector asignado.'
                ])->withInput();
            }
        } elseif ($userRole === RolUsuario::JEFE_ESTACION) {
            if (!in_array($validated['estacion_id'], $user->estaciones_asignadas ?? [])) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes crear incidencias en tus estaciones asignadas.'
                ])->withInput();
            }
        }

        // Estado inicial
        $validated['estado'] = EstadoIncidencia::ABIERTA;
        
        // Fecha de reporte
        $validated['fecha_reporte'] = now();

        // Crear la incidencia
        $incidencia = Incidencia::create($validated);

        return redirect()
            ->route('incidencias.index')
            ->with('success', "Incidencia INC-" . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT) . " creada exitosamente.");
    }

    /**
     * Mostrar detalles de incidencia
     */
    public function show(Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS DE LECTURA
        if (!$this->puedeVerIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para ver esta incidencia.');
        }

        $incidencia->load([
            'estacion:id,codigo,razon_social,direccion',
            'reportadoPorUsuario:id,name,rol,email,telefono',
            'asignadoAUsuario:id,name,rol,email,telefono'
        ]);

        // Calcular estadísticas de tiempo
        $tiempoTranscurrido = $incidencia->fecha_reporte->diffInHours(now());
        $diasTranscurridos = $incidencia->fecha_reporte->diffInDays(now());

        // Generar historial simulado
        $historial = $this->generarHistorialSimulado($incidencia);

        // 🔐 PERMISOS PARA LA VISTA
        $permisos = [
            'puede_editar' => $this->puedeEditarIncidencia($incidencia, $user, $userRole),
            'puede_eliminar' => $this->puedeEliminarIncidencia($incidencia),
            'puede_cambiar_estado' => $this->puedeCambiarEstado($incidencia, $user, $userRole),
            'puede_asignar' => $userRole->esAdministrativo() || $userRole === RolUsuario::SECTORISTA,
        ];

        return view('incidencias.show', compact('incidencia', 'tiempoTranscurrido', 'diasTranscurridos', 'historial', 'permisos'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS
        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        // Verificar si se puede editar según estado
        if ($incidencia->estado === EstadoIncidencia::CERRADA && !$this->puedeReabrirIncidencia()) {
            return redirect()
                ->route('incidencias.show', $incidencia)
                ->with('error', 'No se puede editar una incidencia cerrada.');
        }

        $incidencia->load([
            'estacion:id,codigo,razon_social',
            'reportadoPorUsuario:id,name,rol',
            'asignadoAUsuario:id,name,rol'
        ]);

        // Estaciones según permisos
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estaciones = Estacion::where('sector', $user->sector_asignado)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estaciones = Estacion::whereIn('id', $user->estaciones_asignadas)
                                ->select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        } else {
            $estaciones = Estacion::select('id', 'codigo', 'razon_social')
                                ->orderBy('codigo')
                                ->get();
        }

        $prioridades = [
            'baja' => 'Baja',
            'media' => 'Media', 
            'alta' => 'Alta',
            'critica' => 'Crítica'  // ✅ Agregada crítica
        ];

        $estados = [
            'abierta' => 'Abierta',
            'en_proceso' => 'En Proceso',
            'resuelta' => 'Resuelta',
            'cerrada' => 'Cerrada',
            'cancelada' => 'Cancelada'
        ];

        $categorias = [
            'tecnica' => 'Técnica',
            'infraestructura' => 'Infraestructura',
            'conectividad' => 'Conectividad',
            'energia' => 'Energía',
            'software' => 'Software',
            'otros' => 'Otros'
        ];

        return view('incidencias.edit', compact('incidencia', 'estaciones', 'prioridades', 'estados', 'categorias'));
    }

    /**
     * Actualizar incidencia
     */
    public function update(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS
        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        $validated = $request->validate([
            'estacion_id' => 'required|exists:estaciones,id',
            'titulo' => 'required|string|max:255',           // ✅ Campo correcto
            'descripcion' => 'required|string|max:2000|min:20', // ✅ Campo correcto
            'prioridad' => 'required|in:critica,alta,media,baja', // ✅ Con crítica
            'estado' => 'required|in:abierta,en_proceso,resuelta,cerrada,cancelada',
            'categoria' => 'required|in:tecnica,infraestructura,conectividad,energia,software,otros',
            'impacto_servicio' => 'required|in:bajo,medio,alto',
            'asignado_a' => 'nullable|string|max:255',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'fecha_resolucion_estimada' => 'nullable|date',
            'acciones_tomadas' => 'nullable|string|max:2000',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // 🎯 VERIFICAR RESTRICCIONES POR ROL EN ESTACIÓN
        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector->value !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes modificar incidencias de tu sector asignado.'
                ])->withInput();
            }
        }

        // Validar transición de estado
        $estadoAnterior = $incidencia->estado;
        $estadoNuevo = $validated['estado'];

        if (!$this->validarTransicionEstado($estadoAnterior, $estadoNuevo)) {
            return back()
                ->withInput()
                ->with('error', "No se puede cambiar el estado de {$estadoAnterior->value} a {$estadoNuevo}.");
        }

        // Auto-asignar fechas según el estado
        if ($estadoNuevo === 'en_proceso' && $estadoAnterior !== EstadoIncidencia::EN_PROCESO) {
            $validated['fecha_inicio_atencion'] = now();
        }

        if (in_array($estadoNuevo, ['resuelta', 'cerrada']) && !in_array($estadoAnterior->value, ['resuelta', 'cerrada'])) {
            $validated['fecha_resolucion'] = now();
        }

        // Actualizar incidencia
        $incidencia->update($validated);

        return redirect()
            ->route('incidencias.show', $incidencia)
            ->with('success', 'Incidencia actualizada exitosamente.');
    }

    /**
     * 🗑️ ELIMINAR INCIDENCIA CON 6 NIVELES DE SEGURIDAD
     */
    public function destroy(Request $request, Incidencia $incidencia)
    {
        // 🔐 NIVEL 1: Verificar permisos del usuario
        if (!$this->puedeEliminarIncidencia($incidencia)) {
            return back()->with('error', 'No tienes permisos para eliminar esta incidencia.');
        }

        // 🔐 NIVEL 2: Verificar estado de la incidencia
        if (!$this->incidenciaPuedeSerEliminada($incidencia)) {
            return back()->with('error', 'Esta incidencia no puede ser eliminada en su estado actual.');
        }

        // 🔐 NIVEL 3: Verificar actividad crítica
        if ($this->incidenciaTieneActividadCritica($incidencia)) {
            return back()->with('error', 'No se puede eliminar una incidencia con actividad crítica registrada.');
        }

        // 🔐 NIVEL 4: Validar razón obligatoria
        $request->validate([
            'razon_eliminacion' => 'required|string|min:10|max:500',
            'confirmar_eliminacion' => 'required|accepted'
        ], [
            'razon_eliminacion.required' => 'Debe proporcionar una razón para la eliminación.',
            'razon_eliminacion.min' => 'La razón debe tener al menos 10 caracteres.',
            'confirmar_eliminacion.accepted' => 'Debe confirmar la eliminación.'
        ]);

        // 🔐 NIVEL 5: Registrar en auditoría
        $this->registrarEliminacionEnAuditoria($incidencia, $request->razon_eliminacion);

        // 🔐 NIVEL 6: Eliminar (soft delete por defecto)
        $codigoIncidencia = $incidencia->codigo_incidencia ?? 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT);
        
        if (config('app.use_soft_deletes', true)) {
            $incidencia->delete(); // Soft delete
        } else {
            $incidencia->forceDelete(); // Hard delete
        }

        return redirect()
            ->route('incidencias.index')
            ->with('success', "Incidencia {$codigoIncidencia} eliminada exitosamente y registrada en auditoría.");
    }

    /**
     * Cambiar estado de incidencia vía AJAX
     */
    public function cambiarEstado(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🔐 VERIFICAR PERMISOS
        if (!$this->puedeCambiarEstado($incidencia, $user, $userRole)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cambiar el estado de esta incidencia.'
            ], 403);
        }

        $validated = $request->validate([
            'nuevo_estado' => 'required|in:abierta,en_proceso,resuelta,cerrada,cancelada',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $estadoAnterior = $incidencia->estado;
        $estadoNuevo = $validated['nuevo_estado'];

        // Validar transición
        if (!$this->validarTransicionEstado($estadoAnterior, $estadoNuevo)) {
            return response()->json([
                'success' => false,
                'message' => "No se puede cambiar de {$estadoAnterior->value} a {$estadoNuevo}"
            ]);
        }

        // Actualizar estado
        $incidencia->estado = $estadoNuevo;
        
        if ($validated['observaciones']) {
            $incidencia->observaciones = $incidencia->observaciones . "\n\n" . now()->format('d/m/Y H:i') . " - Cambio de estado: " . $validated['observaciones'];
        }

        $incidencia->save();

        return response()->json([
            'success' => true,
            'message' => "Estado cambiado exitosamente a {$estadoNuevo}",
            'nuevo_estado' => $estadoNuevo
        ]);
    }

    // ==========================================
    // 🔐 MÉTODOS PRIVADOS DE SEGURIDAD
    // ==========================================

    /**
     * 🔐 Verificar si el usuario puede eliminar la incidencia
     */
    private function puedeEliminarIncidencia($incidencia)
    {
        $user = Auth::user();
        
        // Solo administradores y gerentes pueden eliminar
        if (!in_array($user->rol, ['administrador', 'gerente'])) {
            return false;
        }

        // El reportante puede eliminar solo si es reciente (< 2 horas)
        if ($incidencia->reportado_por_user_id === $user->id) {
            return $incidencia->created_at->diffInHours(now()) < 2;
        }

        return true;
    }

    /**
     * 🔐 Verificar si la incidencia puede ser eliminada según su estado
     */
    private function incidenciaPuedeSerEliminada($incidencia)
    {
        $estadosPermitidos = ['cancelada', 'abierta'];
        
        // Solo estados específicos pueden ser eliminados
        if (!in_array($incidencia->estado->value, $estadosPermitidos)) {
            return false;
        }

        // Si está abierta, verificar que sea reciente (< 24 horas)
        if ($incidencia->estado->value === 'abierta') {
            return $incidencia->created_at->diffInHours(now()) < 24;
        }

        return true;
    }

    /**
     * 🔐 Verificar si tiene actividad crítica que impida eliminación
     */
    private function incidenciaTieneActividadCritica($incidencia)
    {
        // Verificar si tiene acciones tomadas importantes
        if (!empty($incidencia->acciones_tomadas) && strlen($incidencia->acciones_tomadas) > 50) {
            return true;
        }

        // Verificar si está asignada a alguien
        if ($incidencia->asignado_a_user_id) {
            return true;
        }

        // Verificar si tiene fecha de inicio de atención
        if ($incidencia->fecha_inicio_atencion) {
            return true;
        }

        return false;
    }

    /**
     * 🔐 Registrar eliminación en auditoría
     */
    private function registrarEliminacionEnAuditoria($incidencia, $razon)
    {
        try {
            DB::table('auditoria_incidencias')->insert([
                'incidencia_id' => $incidencia->id,
                'codigo_incidencia' => $incidencia->codigo_incidencia ?? 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT),
                'accion' => 'ELIMINACION',
                'usuario_id' => Auth::id(),
                'usuario_nombre' => Auth::user()->name,
                'razon' => $razon,
                'datos_incidencia' => json_encode([
                    'estacion' => $incidencia->estacion->codigo ?? 'N/A',
                    'titulo' => $incidencia->titulo,           // ✅ Campo correcto
                    'descripcion' => $incidencia->descripcion, // ✅ Campo correcto
                    'prioridad' => $incidencia->prioridad->value,
                    'estado' => $incidencia->estado->value,
                    'fecha_reporte' => $incidencia->fecha_reporte,
                    'reportado_por' => $incidencia->reportadoPorUsuario->name ?? $incidencia->reportado_por,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 🔐 MÉTODOS PRIVADOS DE PERMISOS POR ROL
    // ==========================================

    /**
     * Verificar si puede ver incidencia
     */
    private function puedeVerIncidencia($incidencia, $user, $userRole)
    {
        // Admin y gerente pueden ver todas
        if ($userRole->esAdministrativo()) {
            return true;
        }

        // Sectorista solo de su sector
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector->value === $user->sector_asignado;
        }

        // Jefe de estación solo de sus estaciones
        if ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            return in_array($incidencia->estacion_id, $user->estaciones_asignadas);
        }

        // Operador y consulta pueden ver todas (según requerimientos)
        return true;
    }

    /**
     * Verificar si puede editar incidencia
     */
    private function puedeEditarIncidencia($incidencia, $user, $userRole)
    {
        // Consulta nunca puede editar
        if ($userRole === RolUsuario::CONSULTA) {
            return false;
        }

        // Operador solo lectura
        if ($userRole === RolUsuario::OPERADOR) {
            return false;
        }

        // Admin y gerente pueden editar todas
        if ($userRole->esAdministrativo()) {
            return true;
        }

        // Sectorista solo de su sector
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector->value === $user->sector_asignado;
        }

        // Jefe de estación solo de sus estaciones
        if ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            return in_array($incidencia->estacion_id, $user->estaciones_asignadas);
        }

        return false;
    }

    /**
     * Verificar si puede cambiar estado
     */
    private function puedeCambiarEstado($incidencia, $user, $userRole)
    {
        // Mismo que editar por ahora
        return $this->puedeEditarIncidencia($incidencia, $user, $userRole);
    }

    // ==========================================
    // MÉTODOS PRIVADOS ORIGINALES
    // ==========================================

    /**
     * Verificar si puede reabrir incidencia
     */
    private function puedeReabrirIncidencia()
    {
        return in_array(Auth::user()->rol, ['administrador', 'gerente']);
    }

    /**
     * Calcular estadísticas para dashboard
     */
    private function calcularEstadisticas()
    {
        $total = Incidencia::count();
        $abiertas = Incidencia::whereIn('estado', ['abierta', 'en_proceso'])->count();
        $criticas = Incidencia::where('prioridad', 'critica')  // ✅ Cambiado de 'alta' a 'critica'
                             ->whereIn('estado', ['abierta', 'en_proceso'])
                             ->count();
        $resueltasHoy = Incidencia::whereIn('estado', ['resuelta', 'cerrada'])  // ✅ Agregado 'resuelta'
                                 ->whereDate('fecha_resolucion', today())
                                 ->count();

        return compact('total', 'abiertas', 'criticas', 'resueltasHoy');
    }

    /**
     * Generar código único para incidencia
     */
    private function generarCodigoIncidencia()
    {
        $año = date('Y');
        $ultimo = Incidencia::where('codigo_incidencia', 'LIKE', "INC-{$año}-%")
                           ->orderBy('codigo_incidencia', 'desc')
                           ->first();

        if ($ultimo) {
            $ultimoNumero = intval(substr($ultimo->codigo_incidencia, -4));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return sprintf("INC-%s-%04d", $año, $nuevoNumero);
    }

    /**
     * Validar transición de estado
     */
    private function validarTransicionEstado($estadoActual, $estadoNuevo)
    {
        $transicionesValidas = [
            'abierta' => ['en_proceso', 'cancelada'],
            'en_proceso' => ['resuelta', 'abierta', 'cancelada'],  // ✅ Agregado 'resuelta'
            'resuelta' => ['cerrada', 'en_proceso'],               // ✅ Agregado estado 'resuelta'
            'cerrada' => [], // No se puede cambiar desde cerrada
            'cancelada' => ['abierta'] // Solo se puede reabrir
        ];

        if ($estadoActual instanceof \BackedEnum) {
            $estadoActual = $estadoActual->value;
        }

        return in_array($estadoNuevo, $transicionesValidas[$estadoActual] ?? []);
    }

    /**
     * Generar historial simulado de la incidencia
     */
    private function generarHistorialSimulado($incidencia)
    {
        $historial = [];
        
        // Evento de creación
        $historial[] = [
            'accion' => 'Incidencia creada',
            'descripcion' => 'Incidencia reportada en el sistema',
            'usuario' => $incidencia->reportadoPorUsuario->name ?? $incidencia->reportado_por ?? 'Sistema',
            'fecha' => $incidencia->created_at,
            'tipo' => 'creacion'
        ];

        // Si tiene fecha de inicio de atención
        if ($incidencia->fecha_inicio_atencion) {
            $historial[] = [
                'accion' => 'Atención iniciada',
                'descripcion' => 'Se comenzó a trabajar en la resolución',
                'usuario' => $incidencia->asignadoAUsuario->name ?? 'Sistema',
                'fecha' => $incidencia->fecha_inicio_atencion,
                'tipo' => 'proceso'
            ];
        }

        // Si está resuelta o cerrada
        if ($incidencia->fecha_resolucion) {
            $historial[] = [
                'accion' => $incidencia->estado->value === 'cerrada' ? 'Incidencia cerrada' : 'Incidencia resuelta',
                'descripcion' => $incidencia->estado->value === 'cerrada' ? 'La incidencia ha sido cerrada' : 'La incidencia ha sido resuelta',
                'usuario' => $incidencia->asignadoAUsuario->name ?? 'Sistema',
                'fecha' => $incidencia->fecha_resolucion,
                'tipo' => 'resolucion'
            ];
        }

        return $historial;
    }
}
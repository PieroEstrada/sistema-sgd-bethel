<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Incidencia;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\RolUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IncidenciaController extends Controller
{
    /**
     * Mostrar lista de incidencias
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        // 🎯 BASE QUERY CON RELACIONES CORREGIDAS
        $query = Incidencia::with([
            'estacion:id,codigo,razon_social,sector',  
            'reportadoPorUsuario:id,name,rol,email,telefono',  
            'asignadoAUsuario:id,name,rol,email,telefono'     
        ]);

        // 🔐 APLICAR FILTROS POR ROL
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            // Sectorista: solo incidencias de su sector
            $query->whereHas('estacion', function($q) use ($user) {
                $q->where('sector', $user->sector_asignado);
            });
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            // Jefe de estación: solo sus estaciones asignadas
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            if (!empty($estacionesAsignadas)) {
                $query->whereIn('estacion_id', $estacionesAsignadas);
            }
        }

        // 📊 APLICAR FILTROS DE BÚSQUEDA
        if ($request->filled('estacion_id')) {
            $query->where('estacion_id', $request->estacion_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('asignado_a')) {
            $query->where(function($q) use ($request) {
                $q->where('asignado_a_user_id', $request->asignado_a)
                  ->orWhere('asignado_a', $request->asignado_a);
            });
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

        // 📄 PAGINACIÓN
        $incidencias = $query->orderBy('fecha_reporte', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15)
                            ->appends($request->query());

        // 🎯 AGREGAR PERMISOS A CADA INCIDENCIA
        $incidencias->getCollection()->transform(function($incidencia) use ($user, $userRole) {
            $incidencia->puede_editar = $this->puedeEditarIncidencia($incidencia, $user, $userRole);
            $incidencia->puede_eliminar = $this->puedeEliminarIncidencia($incidencia);
            $incidencia->puede_cambiar_estado = $this->puedeCambiarEstado($incidencia, $user, $userRole);
            $incidencia->puede_asignar = $this->puedeAsignarIncidencia($incidencia, $user, $userRole);
            return $incidencia;
        });

        // 📊 OBTENER DATOS PARA FILTROS
        $estacionesQuery = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                                  ->orderBy('codigo');

        // Filtrar estaciones según el rol
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estacionesQuery->where('sector', $user->sector_asignado);
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            if (!empty($estacionesAsignadas)) {
                $estacionesQuery->whereIn('id', $estacionesAsignadas);
            }
        }

        $estaciones = $estacionesQuery->get();

        // Usuarios para asignación (solo roles técnicos)
        $usuarios = User::whereIn('rol', ['administrador', 'gerente', 'sectorista', 'jefe_estacion'])
                       ->where('activo', 1)
                       ->select('id', 'name', 'rol', 'sector_asignado')
                       ->orderBy('name')
                       ->get();

        // Calcular estadísticas
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
        $userRole = RolUsuario::from($user->rol);

        // 📊 OBTENER ESTACIONES SEGÚN ROL
        $estacionesQuery = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                                  ->where('activa', 1)
                                  ->orderBy('codigo');

        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estacionesQuery->where('sector', $user->sector_asignado);
        } elseif ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            if (!empty($estacionesAsignadas)) {
                $estacionesQuery->whereIn('id', $estacionesAsignadas);
            }
        }

        $estaciones = $estacionesQuery->get();

        $prioridades = [
            'critica' => 'Crítica',
            'alta' => 'Alta', 
            'media' => 'Media',
            'baja' => 'Baja'
        ];

        $usuariosTecnicos = User::whereIn('rol', ['administrador', 'gerente', 'sectorista', 'jefe_estacion'])
                               ->where('activo', 1)
                               ->select('id', 'name', 'rol')
                               ->orderBy('name')
                               ->get();

        return view('incidencias.create', compact('estaciones', 'prioridades', 'usuariosTecnicos'));
    }

    /**
     * Almacenar nueva incidencia
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|min:10',
            'estacion_id' => 'required|exists:estaciones,id',
            'prioridad' => 'required|in:critica,alta,media,baja',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'tiempo_respuesta_estimado' => 'nullable|integer|min:1|max:720',
            'requiere_visita_tecnica' => 'boolean',
            'fecha_visita_programada' => 'nullable|date|after:now',
            'observaciones_tecnicas' => 'nullable|string|max:1000'
        ], [
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

        // Verificar restricciones por rol
        if ($userRole === RolUsuario::SECTORISTA) {
            $estacion = Estacion::findOrFail($validated['estacion_id']);
            if ($estacion->sector !== $user->sector_asignado) {
                return back()->withErrors([
                    'estacion_id' => 'Solo puedes crear incidencias en estaciones de tu sector asignado.'
                ])->withInput();
            }
        }

        try {
            $incidencia = new Incidencia($validated);
            $incidencia->reportado_por_user_id = $user->id;
            $incidencia->reportado_por = $user->id;
            $incidencia->fecha_reporte = now();
            $incidencia->estado = 'abierta';

            if ($incidencia->asignado_a_user_id) {
                $incidencia->estado = 'en_proceso';
                $incidencia->asignado_a = $incidencia->asignado_a_user_id;
            }

            $incidencia->save();

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
        $userRole = RolUsuario::from($user->rol);

        if (!$this->puedeVerIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para ver esta incidencia.');
        }

        $incidencia->load([
            'estacion:id,codigo,razon_social,localidad,provincia,departamento,sector',
            'reportadoPorUsuario:id,name,email,telefono,rol',
            'asignadoAUsuario:id,name,email,telefono,rol'
        ]);

        $permisos = [
            'puede_editar' => $this->puedeEditarIncidencia($incidencia, $user, $userRole),
            'puede_eliminar' => $this->puedeEliminarIncidencia($incidencia),
            'puede_cambiar_estado' => $this->puedeCambiarEstado($incidencia, $user, $userRole),
            'puede_asignar' => $this->puedeAsignarIncidencia($incidencia, $user, $userRole)
        ];

        $usuariosAsignacion = [];
        if ($permisos['puede_asignar']) {
            $usuariosAsignacion = User::whereIn('rol', ['administrador', 'gerente', 'sectorista', 'jefe_estacion'])
                                     ->where('activo', 1)
                                     ->select('id', 'name', 'rol', 'sector_asignado')
                                     ->orderBy('name')
                                     ->get();
        }

        return view('incidencias.show', compact('incidencia', 'permisos', 'usuariosAsignacion'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        $estacionesQuery = Estacion::select('id', 'codigo', 'razon_social', 'sector')
                                  ->where('activa', 1)
                                  ->orderBy('codigo');

        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            $estacionesQuery->where('sector', $user->sector_asignado);
        }

        $estaciones = $estacionesQuery->get();

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

        $usuariosTecnicos = User::whereIn('rol', ['administrador', 'gerente', 'sectorista', 'jefe_estacion'])
                               ->where('activo', 1)
                               ->select('id', 'name', 'rol')
                               ->orderBy('name')
                               ->get();

        return view('incidencias.edit', compact(
            'incidencia', 
            'estaciones', 
            'prioridades', 
            'estados',
            'usuariosTecnicos'
        ));
    }

    /**
     * Actualizar incidencia
     */
    public function update(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        $userRole = RolUsuario::from($user->rol);

        if (!$this->puedeEditarIncidencia($incidencia, $user, $userRole)) {
            abort(403, 'No tienes permisos para editar esta incidencia.');
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|min:10',
            'estacion_id' => 'required|exists:estaciones,id',
            'prioridad' => 'required|in:critica,alta,media,baja',
            'estado' => 'required|in:abierta,en_proceso,resuelta,cerrada,cancelada',
            'asignado_a_user_id' => 'nullable|exists:users,id',
            'tiempo_respuesta_estimado' => 'nullable|integer|min:1|max:720',
            'solucion' => 'nullable|string|max:2000',
            'costo_soles' => 'nullable|numeric|min:0',
            'costo_dolares' => 'nullable|numeric|min:0',
            'observaciones_tecnicas' => 'nullable|string|max:1000',
            'requiere_visita_tecnica' => 'boolean',
            'fecha_visita_programada' => 'nullable|date',
            'fecha_resolucion' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $incidencia->fill($validated);

            if ($incidencia->asignado_a_user_id) {
                $incidencia->asignado_a = $incidencia->asignado_a_user_id;
            }

            if ($validated['estado'] === 'resuelta' && !$incidencia->fecha_resolucion) {
                $incidencia->fecha_resolucion = now();
            }

            $incidencia->save();

            return redirect()->route('incidencias.show', $incidencia)
                           ->with('success', 'Incidencia actualizada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Error al actualizar la incidencia: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Eliminar incidencia
     */
    public function destroy(Request $request, Incidencia $incidencia)
    {
        if (!in_array(Auth::user()->rol, ['administrador', 'gerente'])) {
            return back()->with('error', 'No tienes permisos para eliminar incidencias.');
        }

        try {
            $incidencia->delete();
            return redirect()->route('incidencias.index')
                           ->with('success', 'Incidencia eliminada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la incidencia: ' . $e->getMessage());
        }
    }

    // =====================================================
    // MÉTODOS PRIVADOS DE PERMISOS
    // =====================================================

    private function puedeVerIncidencia($incidencia, $user, $userRole): bool
    {
        if (in_array($userRole->value, ['administrador', 'gerente'])) return true;
        
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        if ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            return in_array($incidencia->estacion_id, $estacionesAsignadas);
        }
        
        return true;
    }

    private function puedeEditarIncidencia($incidencia, $user, $userRole): bool
    {
        if (in_array($userRole->value, ['consulta', 'operador'])) return false;
        
        if (in_array($incidencia->estado->value, ['cerrada', 'cancelada'])) return false;
        
        if (in_array($userRole->value, ['administrador', 'gerente'])) return true;
        
        if ($userRole === RolUsuario::SECTORISTA && $user->sector_asignado) {
            return $incidencia->estacion->sector === $user->sector_asignado;
        }
        
        if ($userRole === RolUsuario::JEFE_ESTACION && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            return in_array($incidencia->estacion_id, $estacionesAsignadas);
        }
        
        return false;
    }

    private function puedeEliminarIncidencia($incidencia): bool
    {
        return in_array(Auth::user()->rol, ['administrador', 'gerente']);
    }

    private function puedeCambiarEstado($incidencia, $user, $userRole): bool
    {
        return $this->puedeEditarIncidencia($incidencia, $user, $userRole) && 
               !in_array($incidencia->estado->value, ['cerrada']);
    }

    private function puedeAsignarIncidencia($incidencia, $user, $userRole): bool
    {
        return in_array($userRole->value, ['administrador', 'gerente', 'sectorista']) &&
               !in_array($incidencia->estado->value, ['cerrada', 'cancelada']);
    }

    /**
     * Calcular estadísticas para el dashboard
     */
    private function calcularEstadisticas($userRole = null, $user = null): array
    {
        $query = Incidencia::query();

        // Aplicar filtros por rol si se especifica
        if ($userRole && $userRole === RolUsuario::SECTORISTA && $user && $user->sector_asignado) {
            $query->whereHas('estacion', function($q) use ($user) {
                $q->where('sector', $user->sector_asignado);
            });
        } elseif ($userRole && $userRole === RolUsuario::JEFE_ESTACION && $user && $user->estaciones_asignadas) {
            $estacionesAsignadas = json_decode($user->estaciones_asignadas, true) ?? [];
            if (!empty($estacionesAsignadas)) {
                $query->whereIn('estacion_id', $estacionesAsignadas);
            }
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
}
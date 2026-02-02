<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estacion;
use App\Models\EstacionEquipamiento;
use App\Models\User;
use App\Models\Carpeta;
use App\Enums\RolUsuario;
use App\Enums\Banda;
use App\Enums\EstadoEstacion;
use App\Enums\Sector;
use App\Enums\TipoEquipamiento;
use App\Enums\EstadoEquipamiento;
use App\Enums\RiesgoLicencia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EstacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Estacion::with(['incidencias' => function($q) {
            $q->whereNotIn('estado', ['cerrada', 'cancelada']);
        }]);

        // Filtros de búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('razon_social', 'LIKE', "%{$buscar}%")
                  ->orWhere('localidad', 'LIKE', "%{$buscar}%")
                  ->orWhere('provincia', 'LIKE', "%{$buscar}%")
                  ->orWhere('codigo', 'LIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('banda')) {
            $query->where('banda', $request->banda);
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        // 2.1) Filtros por Presbítero (número y nombre)
        if ($request->filled('presbitero_id')) {
            $query->where('presbitero_id', $request->presbitero_id);
        }

        if ($request->filled('presbitero_nombre')) {
            $query->whereHas('presbitero', function($q) use ($request) {
                $q->where('nombre_completo', 'LIKE', "%{$request->presbitero_nombre}%");
            });
        }

        // Filtro de renovación
        if ($request->filled('renovacion')) {
            $query->where('en_renovacion', $request->renovacion == '1');
        }

        // Filtro de riesgo de licencia
        if ($request->filled('riesgo')) {
            $riesgo = strtoupper($request->riesgo);
            if (in_array($riesgo, ['ALTO', 'MEDIO', 'SEGURO'])) {
                $query->where('riesgo_licencia', $riesgo);
            } elseif ($riesgo === 'SIN_EVALUAR' || $request->riesgo === 'sin_evaluar') {
                $query->whereNull('riesgo_licencia');
            }
        }

        // Filtro de licencias vencidas (cálculo dinámico desde licencia_vence)
        if ($request->filled('vencidas') && $request->vencidas == '1') {
            $query->whereNotNull('licencia_vence')
                  ->where('licencia_vence', '<', DB::raw('CURDATE()'));
        }

        // Filtro por sector del usuario (para sectoristas)
        $usuario = Auth::user();
        if ($usuario && $usuario->esSectorista() && $usuario->sector_asignado) {
            $query->where('sector', $usuario->sector_asignado);
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'localidad');
        $direccion = $request->get('direccion', 'asc');
        $query->orderBy($ordenar, $direccion);

        // Paginación configurable
        $perPage = $request->get('per_page', 15);
        // Validar que sea un valor permitido
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $estaciones = $query->paginate($perPage)->appends($request->query());

        // Datos para filtros
        $sectores = [
            'NORTE' => 'Norte',
            'CENTRO' => 'Centro', 
            'SUR' => 'Sur'
            // 'ORIENTE' => 'Oriente'
        ];
        
        $estados = [
            'AL_AIRE' => 'Al Aire',
            'FUERA_DEL_AIRE' => 'Fuera del Aire',
            'NO_INSTALADA' => 'No Instalada'
        ];

        $bandas = [
            'FM' => 'FM',
            'AM' => 'AM',
            'VHF' => 'VHF',
            'UHF' => 'UHF'
        ];

        $departamentos = Estacion::select('departamento')
                                ->distinct()
                                ->orderBy('departamento')
                                ->pluck('departamento');

        // Estadísticas para el dashboard de estaciones (solo 3 estados válidos)
        $estadisticas = [
            'total' => Estacion::count(),
            'al_aire' => Estacion::where('estado', EstadoEstacion::AL_AIRE)->count(),
            'fuera_aire' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
            'no_instalada' => Estacion::where('estado', EstadoEstacion::NO_INSTALADA)->count(),
            'en_renovacion' => Estacion::where('en_renovacion', true)->count(),
            // Estadísticas de riesgo de licencia
            'riesgo_alto' => Estacion::where('riesgo_licencia', RiesgoLicencia::ALTO)->count(),
            'riesgo_medio' => Estacion::where('riesgo_licencia', RiesgoLicencia::MEDIO)->count(),
            'riesgo_seguro' => Estacion::where('riesgo_licencia', RiesgoLicencia::SEGURO)->count(),
            'licencias_vencidas' => Estacion::whereNotNull('licencia_vence')
                ->where('licencia_vence', '<', DB::raw('CURDATE()'))->count(),
        ];

        // Opciones de riesgo para filtros
        $riesgos = [
            'alto' => 'Riesgo Alto (<12 meses)',
            'medio' => 'Riesgo Medio (12-24 meses)',
            'seguro' => 'Seguro (>24 meses)',
            'sin_evaluar' => 'Sin evaluar',
        ];

        return view('estaciones.index', compact(
            'estaciones', 'sectores', 'estados', 'bandas',
            'departamentos', 'estadisticas', 'riesgos'
        ));
    }

    public function show(Estacion $estacion)
    {
        $estacion->load([
            'incidencias' => function($q) {
                $q->with(['reportadoPor', 'asignadoA'])
                  ->orderBy('created_at', 'desc')
                  ->limit(10);
            },
            'tramitesMtc' => function($q) {
                $q->with('responsable')
                  ->orderBy('created_at', 'desc')
                  ->limit(10);
            },
            'historialEstados' => function($q) {
                $q->with('responsableCambio')
                  ->orderBy('fecha_cambio', 'desc')
                  ->limit(10);
            },
            'equipamientos' => function($q) {
                $q->orderBy('tipo');
            }
        ]);

        // Estadísticas de la estación
        $estadisticas = [
            'incidencias_abiertas' => $estacion->incidencias()
                                               ->whereIn('estado', ['abierta', 'en_proceso'])
                                               ->count(),
            'tramites_pendientes' => $estacion->tramitesMtc()
                                              ->whereIn('estado', ['presentado', 'en_proceso'])
                                              ->count(),
            'archivos_total' => $estacion->archivos()->count(),
            'dias_estado_actual' => $estacion->ultima_actualizacion_estado
                                              ? $estacion->ultima_actualizacion_estado->diffInDays(now())
                                              : $estacion->created_at->diffInDays(now()),
            'equipamiento_operativo' => $estacion->equipamientos()->where('estado', 'OPERATIVO')->count(),
            'equipamiento_averiado' => $estacion->equipamientos()->where('estado', 'AVERIADO')->count(),
        ];

        // Últimas actualizaciones
        $ultimasActualizaciones = collect();
        
        if ($estacion->incidencias->count() > 0) {
            $ultimasActualizaciones = $ultimasActualizaciones->merge(
                $estacion->incidencias->take(3)->map(function($inc) {
                    return [
                        'tipo' => 'incidencia',
                        'titulo' => $inc->titulo,
                        'fecha' => $inc->created_at,
                        'url' => '#',
                        'icono' => 'fas fa-exclamation-triangle',
                        'color' => $inc->prioridad->value == 'critica' ? 'danger' : 'warning'
                    ];
                })
            );
        }
        
        if ($estacion->tramitesMtc->count() > 0) {
            $ultimasActualizaciones = $ultimasActualizaciones->merge(
                $estacion->tramitesMtc->take(3)->map(function($tramite) {
                    return [
                        'tipo' => 'tramite',
                        'titulo' => $tramite->numero_expediente,
                        'fecha' => $tramite->created_at,
                        'url' => '#',
                        'icono' => 'fas fa-file-alt',
                        'color' => $tramite->estado->value == 'aprobado' ? 'success' : 'primary'
                    ];
                })
            );
        }
        
        $ultimasActualizaciones = $ultimasActualizaciones->sortByDesc('fecha')->take(6);

        return view('estaciones.show', compact(
            'estacion', 'estadisticas', 'ultimasActualizaciones'
        ));
    }

    public function create()
    {
        $sectores = [
            'NORTE' => 'Norte',
            'CENTRO' => 'Centro', 
            'SUR' => 'Sur'
            // 'ORIENTE' => 'Oriente'
        ];
        
        $estados = [
            'AL_AIRE' => 'Al Aire',
            'FUERA_DEL_AIRE' => 'Fuera del Aire',
            'NO_INSTALADA' => 'No Instalada'
        ];

        $bandas = [
            'FM' => 'FM',
            'AM' => 'AM',
            'VHF' => 'VHF',
            'UHF' => 'UHF'
        ];

        return view('estaciones.create', compact('sectores', 'estados', 'bandas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:estaciones',
            'razon_social' => 'required|string|max:255',
            'localidad' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'banda' => 'required|in:FM,AM,VHF,UHF',
            'frecuencia' => 'nullable|numeric|min:0.1',
            'canal_tv' => 'nullable|integer|min:2|max:69',
            'presbitero_id' => 'nullable|exists:presbiteros,id',
            'estado' => 'required|in:AL_AIRE,FUERA_DEL_AIRE,NO_INSTALADA',
            'potencia_watts' => 'required|integer|min:1',
            'sector' => 'required|in:NORTE,CENTRO,SUR',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'celular_encargado' => 'nullable|string|max:20',
            'fecha_autorizacion' => 'nullable|date',
            'fecha_vencimiento_autorizacion' => 'nullable|date|after:fecha_autorizacion',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        // Validaciones específicas por banda
        if (in_array($validated['banda'], ['FM', 'AM']) && empty($validated['frecuencia'])) {
            return back()->withErrors(['frecuencia' => 'La frecuencia es requerida para radio.']);
        }

        if (in_array($validated['banda'], ['VHF', 'UHF']) && empty($validated['canal_tv'])) {
            return back()->withErrors(['canal_tv' => 'El canal de TV es requerido para televisión.']);
        }

        $validated['ultima_actualizacion_estado'] = now();

        $estacion = Estacion::create($validated);

        return redirect()->route('estaciones.index')
                        ->with('success', 'Estación creada exitosamente.');
    }

    public function edit(Estacion $estacion)
    {
        // Verificar permisos: sectorista solo puede editar estaciones de su sector
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'No tiene permisos para editar esta estación.');
        }

        $sectores = [
            'NORTE' => 'Norte',
            'CENTRO' => 'Centro',
            'SUR' => 'Sur'
        ];

        $estados = [
            'AL_AIRE' => 'Al Aire',
            'FUERA_DEL_AIRE' => 'Fuera del Aire',
            'NO_INSTALADA' => 'No Instalada'
        ];

        $bandas = [
            'FM' => 'FM',
            'AM' => 'AM',
            'VHF' => 'VHF',
            'UHF' => 'UHF'
        ];

        // Cargar presbiteros activos
        $presbiteros = \App\Models\Presbitero::where('estado', 'activo')
            ->orderBy('sector')
            ->orderBy('nombre_completo')
            ->get();

        return view('estaciones.edit', compact('estacion', 'sectores', 'estados', 'bandas', 'presbiteros'));
    }

    public function update(Request $request, Estacion $estacion)
    {
        // Verificar permisos
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'No tiene permisos para editar esta estación.');
        }

        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:estaciones,codigo,' . $estacion->id,
            'razon_social' => 'required|string|max:255',
            'localidad' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'banda' => 'required|in:FM,AM,VHF,UHF',
            'frecuencia' => 'nullable|numeric|min:0.1',
            'canal_tv' => 'nullable|integer|min:2|max:69',
            'presbitero_id' => 'nullable|exists:presbiteros,id',
            'estado' => 'required|in:AL_AIRE,FUERA_DEL_AIRE,NO_INSTALADA',
            'potencia_watts' => 'required|integer|min:1',
            'sector' => 'required|in:NORTE,CENTRO,SUR',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'celular_encargado' => 'nullable|string|max:20',
            'fecha_autorizacion' => 'nullable|date',
            'fecha_vencimiento_autorizacion' => 'nullable|date|after:fecha_autorizacion',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        // Si cambió el estado, actualizar fecha
        if ($estacion->estado->value !== $validated['estado']) {
            $validated['ultima_actualizacion_estado'] = now();
        }

        $estacion->update($validated);

        return redirect()->route('estaciones.show', $estacion)
                        ->with('success', 'Estación actualizada exitosamente.');
    }

    public function destroy(Estacion $estacion)
    {
        // Verificar que no tenga incidencias o trámites activos
        if ($estacion->incidencias()->whereIn('estado', ['abierta', 'en_proceso'])->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar una estación con incidencias activas.']);
        }

        if ($estacion->tramitesMtc()->whereIn('estado', ['presentado', 'en_proceso'])->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar una estación con trámites pendientes.']);
        }

        $estacion->delete();

        return redirect()->route('estaciones.index')
                        ->with('success', 'Estación eliminada exitosamente.');
    }

    public function mapa()
    {
        $estaciones = Estacion::whereNotNull('latitud')
                             ->whereNotNull('longitud')
                             ->get();

        return view('estaciones.mapa', compact('estaciones'));
    }

    public function sectorizacion()
    {
        $estacionesPorSector = [];
        
        $sectores = ['NORTE', 'CENTRO', 'SUR'];
        
        foreach ($sectores as $sector) {
            $estaciones = Estacion::where('sector', $sector)
                                 ->orderBy('localidad')
                                 ->get();
            
            if ($estaciones->isNotEmpty()) {
                $estacionesPorSector[$sector] = [
                    'sector' => $sector,
                    'nombre' => ucfirst(strtolower($sector)),
                    'estaciones' => $estaciones,
                    'estadisticas' => [
                        'total' => $estaciones->count(),
                        'al_aire' => $estaciones->where('estado', EstadoEstacion::AL_AIRE)->count(),
                        'fuera_aire' => $estaciones->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count()
                    ]
                ];
            }
        }

        return view('estaciones.sectorizacion', compact('estacionesPorSector'));
    }

    public function fichaTecnica(Estacion $estacion)
    {
        return view('estaciones.ficha-tecnica', compact('estacion'));
    }

    public function actualizarEstado(Request $request, Estacion $estacion)
    {
        // Verificar permisos
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No tiene permisos para modificar esta estación.'
            ], 403);
        }

        $validated = $request->validate([
            'estado' => 'required|in:AL_AIRE,FUERA_DEL_AIRE,NO_INSTALADA',
            'motivo' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $estadoAnterior = $estacion->estado;
        $nuevoEstado = EstadoEstacion::from($validated['estado']);

        // Usar el método del modelo que registra en historial
        $estacion->actualizarEstado(
            $nuevoEstado,
            $validated['motivo'] ?? null,
            $validated['observaciones'] ?? null
        );

        $mensaje = "Estado actualizado de {$estadoAnterior->label()} a {$nuevoEstado->label()}";

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje,
            'nuevo_estado' => $nuevoEstado->label(),
            'color_estado' => $nuevoEstado->color()
        ]);
    }

    // ==========================================
    // MÉTODOS DE EQUIPAMIENTO
    // ==========================================

    public function storeEquipamiento(Request $request, Estacion $estacion)
    {
        // Verificar permisos
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'No tiene permisos para modificar esta estación.');
        }

        $validated = $request->validate([
            'tipo' => 'required|string|in:TRANSMISOR,ANTENA,CONSOLA,EXCITADOR,UPS,GENERADOR,OTRO',
            'estado' => 'required|string|in:OPERATIVO,AVERIADO,EN_REPARACION,BAJA',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serie' => 'nullable|string|max:100',
            'fecha_instalacion' => 'nullable|date',
            'fecha_ultimo_mantenimiento' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $estacion->equipamientos()->create($validated);

        return redirect()->route('estaciones.show', $estacion)
            ->with('success', 'Equipo agregado exitosamente.');
    }

    public function updateEquipamiento(Request $request, Estacion $estacion, EstacionEquipamiento $equipamiento)
    {
        // Verificar permisos
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'No tiene permisos para modificar esta estación.');
        }

        // Verificar que el equipamiento pertenece a la estación
        if ($equipamiento->estacion_id !== $estacion->id) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'El equipo no pertenece a esta estación.');
        }

        $validated = $request->validate([
            'tipo' => 'required|string|in:TRANSMISOR,ANTENA,CONSOLA,EXCITADOR,UPS,GENERADOR,OTRO',
            'estado' => 'required|string|in:OPERATIVO,AVERIADO,EN_REPARACION,BAJA',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serie' => 'nullable|string|max:100',
            'fecha_instalacion' => 'nullable|date',
            'fecha_ultimo_mantenimiento' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $equipamiento->update($validated);

        return redirect()->route('estaciones.show', $estacion)
            ->with('success', 'Equipo actualizado exitosamente.');
    }

    public function destroyEquipamiento(Estacion $estacion, EstacionEquipamiento $equipamiento)
    {
        // Verificar permisos
        if (!Auth::user()->puedeModificarEstacion($estacion)) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'No tiene permisos para modificar esta estación.');
        }

        // Verificar que el equipamiento pertenece a la estación
        if ($equipamiento->estacion_id !== $estacion->id) {
            return redirect()->route('estaciones.show', $estacion)
                ->with('error', 'El equipo no pertenece a esta estación.');
        }

        $equipamiento->delete();

        return redirect()->route('estaciones.show', $estacion)
            ->with('success', 'Equipo eliminado exitosamente.');
    }

    /**
     * Exportar estaciones a PDF con selección de columnas
     */
    public function exportarPdf(Request $request)
    {
        // Columnas disponibles con sus labels
        $columnasDisponibles = [
            'codigo' => 'Código',
            'razon_social' => 'Razón Social',
            'localidad' => 'Localidad',
            'provincia' => 'Provincia',
            'departamento' => 'Departamento',
            'sector' => 'Sector',
            'banda' => 'Banda',
            'frecuencia' => 'Frecuencia',
            'canal_tv' => 'Canal TV',
            'potencia_watts' => 'Potencia (W)',
            'estado' => 'Estado',
            'licencia_vence' => 'Vence Licencia',
            'riesgo_licencia' => 'Riesgo',
            'celular_encargado' => 'Celular',
        ];

        // Columnas seleccionadas (por defecto, las principales)
        $columnasDefecto = ['codigo', 'localidad', 'departamento', 'sector', 'banda', 'frecuencia', 'estado'];
        $columnasSeleccionadas = $request->input('columnas', $columnasDefecto);

        // Si es una cadena separada por comas, convertir a array
        if (is_string($columnasSeleccionadas)) {
            $columnasSeleccionadas = explode(',', $columnasSeleccionadas);
        }

        // Filtrar solo columnas válidas
        $columnasSeleccionadas = array_intersect($columnasSeleccionadas, array_keys($columnasDisponibles));

        // Query base
        $query = Estacion::query();

        // Aplicar los mismos filtros que en index
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('razon_social', 'LIKE', "%{$buscar}%")
                  ->orWhere('localidad', 'LIKE', "%{$buscar}%")
                  ->orWhere('provincia', 'LIKE', "%{$buscar}%")
                  ->orWhere('codigo', 'LIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('banda')) {
            $query->where('banda', $request->banda);
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        if ($request->filled('riesgo')) {
            $riesgo = strtoupper($request->riesgo);
            if (in_array($riesgo, ['ALTO', 'MEDIO', 'SEGURO'])) {
                $query->where('riesgo_licencia', $riesgo);
            } elseif ($riesgo === 'SIN_EVALUAR' || $request->riesgo === 'sin_evaluar') {
                $query->whereNull('riesgo_licencia');
            }
        }

        // Filtro por sector del usuario (para sectoristas)
        $usuario = Auth::user();
        if ($usuario && $usuario->esSectorista() && $usuario->sector_asignado) {
            $query->where('sector', $usuario->sector_asignado);
        }

        $estaciones = $query->orderBy('localidad')->get();

        // Preparar columnas para la vista
        $columnas = [];
        foreach ($columnasSeleccionadas as $key) {
            $columnas[$key] = $columnasDisponibles[$key];
        }

        // Estadísticas
        $estadisticas = [
            'total' => $estaciones->count(),
            'al_aire' => $estaciones->where('estado', EstadoEstacion::AL_AIRE)->count(),
            'fuera_aire' => $estaciones->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
            'no_instalada' => $estaciones->where('estado', EstadoEstacion::NO_INSTALADA)->count(),
        ];

        // Título del reporte basado en filtros
        $titulo = 'Listado de Estaciones';
        $filtrosAplicados = [];
        if ($request->filled('sector')) {
            $filtrosAplicados[] = 'Sector: ' . $request->sector;
        }
        if ($request->filled('estado')) {
            $filtrosAplicados[] = 'Estado: ' . $request->estado;
        }
        if ($request->filled('banda')) {
            $filtrosAplicados[] = 'Banda: ' . $request->banda;
        }
        if ($request->filled('riesgo')) {
            $filtrosAplicados[] = 'Riesgo: ' . ucfirst($request->riesgo);
        }

        $html = view('estaciones.pdf', compact(
            'estaciones',
            'columnas',
            'estadisticas',
            'titulo',
            'filtrosAplicados'
        ))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('estaciones_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exportar estaciones a Excel con filtros y selección de columnas
     */
    public function exportarExcel(Request $request)
    {
        // Obtener filtros desde request
        $filtros = [
            'buscar' => $request->input('buscar'),
            'sector' => $request->input('sector'),
            'estado' => $request->input('estado'),
            'banda' => $request->input('banda'),
            'departamento' => $request->input('departamento'),
            'renovacion' => $request->input('renovacion'),
            'riesgo' => $request->input('riesgo'),
            'vencidas' => $request->input('vencidas'),
        ];

        // Columnas seleccionadas
        $columnasDefecto = ['codigo', 'razon_social', 'localidad', 'provincia', 'departamento', 'sector', 'banda', 'frecuencia', 'potencia_watts', 'estado', 'licencia_vence'];
        $columnas = $request->input('columnas', $columnasDefecto);

        // Si es una cadena separada por comas, convertir a array
        if (is_string($columnas)) {
            $columnas = explode(',', $columnas);
        }

        $export = new \App\Exports\EstacionesExport($filtros, $columnas);

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'estaciones_' . date('Y-m-d') . '.xlsx'
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
                'razon_social' => 'Razón Social',
                'localidad' => 'Localidad',
                'provincia' => 'Provincia',
                'departamento' => 'Departamento',
                'sector' => 'Sector',
                'banda' => 'Banda',
                'frecuencia' => 'Frecuencia',
                'canal_tv' => 'Canal TV',
                'potencia_watts' => 'Potencia (W)',
                'estado' => 'Estado',
                'licencia_vence' => 'Vence Licencia',
                'riesgo_licencia' => 'Riesgo',
                'celular_encargado' => 'Celular',
                'latitud' => 'Latitud',
                'longitud' => 'Longitud',
                'en_renovacion' => 'En Renovación',
            ],
            'defecto' => ['codigo', 'razon_social', 'localidad', 'departamento', 'sector', 'banda', 'frecuencia', 'estado']
        ]);
    }
}
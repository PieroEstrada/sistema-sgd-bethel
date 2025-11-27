<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estacion;
use App\Models\User;
use App\Models\Carpeta;
use App\Enums\RolUsuario;
use App\Enums\Banda;
use App\Enums\EstadoEstacion;
use App\Enums\Sector;
use Illuminate\Support\Facades\DB;

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

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'localidad');
        $direccion = $request->get('direccion', 'asc');
        $query->orderBy($ordenar, $direccion);

        $estaciones = $query->paginate(15)->appends($request->query());

        // Datos para filtros
        $sectores = [
            'NORTE' => 'Norte',
            'CENTRO' => 'Centro', 
            'SUR' => 'Sur',
            'ORIENTE' => 'Oriente'
        ];
        
        $estados = [
            'A.A' => 'Al Aire',
            'F.A' => 'Fuera del Aire',
            'MANT' => 'Mantenimiento',
            'N.I' => 'No Instalada'
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

        // Estadísticas para el dashboard de estaciones
        $estadisticas = [
            'total' => Estacion::count(),
            'al_aire' => Estacion::where('estado', 'A.A')->count(),
            'fuera_aire' => Estacion::where('estado', 'F.A')->count(),
            'mantenimiento' => Estacion::where('estado', 'MANT')->count(),
            'no_instalada' => Estacion::where('estado', 'N.I')->count()
        ];

        return view('estaciones.index', compact(
            'estaciones', 'sectores', 'estados', 'bandas', 
            'departamentos', 'estadisticas'
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
            'dias_estado_actual' => $estacion->created_at->diffInDays(now())
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
            'SUR' => 'Sur',
            'ORIENTE' => 'Oriente'
        ];
        
        $estados = [
            'A.A' => 'Al Aire',
            'F.A' => 'Fuera del Aire',
            'MANT' => 'Mantenimiento',
            'N.I' => 'No Instalada'
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
            'presbyter_id' => 'nullable|integer|min:1',
            'estado' => 'required|in:A.A,F.A,N.I,MANT',
            'potencia_watts' => 'required|integer|min:1',
            'sector' => 'required|in:NORTE,CENTRO,SUR,ORIENTE',
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
        $sectores = [
            'NORTE' => 'Norte',
            'CENTRO' => 'Centro', 
            'SUR' => 'Sur',
            'ORIENTE' => 'Oriente'
        ];
        
        $estados = [
            'A.A' => 'Al Aire',
            'F.A' => 'Fuera del Aire',
            'MANT' => 'Mantenimiento',
            'N.I' => 'No Instalada'
        ];
        
        $bandas = [
            'FM' => 'FM',
            'AM' => 'AM',
            'VHF' => 'VHF',
            'UHF' => 'UHF'
        ];

        return view('estaciones.edit', compact('estacion', 'sectores', 'estados', 'bandas'));
    }

    public function update(Request $request, Estacion $estacion)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:estaciones,codigo,' . $estacion->id,
            'razon_social' => 'required|string|max:255',
            'localidad' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'banda' => 'required|in:FM,AM,VHF,UHF',
            'frecuencia' => 'nullable|numeric|min:0.1',
            'canal_tv' => 'nullable|integer|min:2|max:69',
            'presbyter_id' => 'nullable|integer|min:1',
            'estado' => 'required|in:A.A,F.A,N.I,MANT',
            'potencia_watts' => 'required|integer|min:1',
            'sector' => 'required|in:NORTE,CENTRO,SUR,ORIENTE',
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
        
        $sectores = ['NORTE', 'CENTRO', 'SUR', 'ORIENTE'];
        
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
                        'al_aire' => $estaciones->where('estado', 'A.A')->count(),
                        'fuera_aire' => $estaciones->where('estado', 'F.A')->count()
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
        $validated = $request->validate([
            'estado' => 'required|in:A.A,F.A,N.I,MANT',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $estadoAnterior = $estacion->estado;
        
        $estacion->update([
            'estado' => $validated['estado'],
            'ultima_actualizacion_estado' => now(),
            'observaciones' => $validated['observaciones'] ?? $estacion->observaciones
        ]);

        $mensaje = "Estado actualizado de {$estadoAnterior->name} a {$estacion->estado->name}";

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje,
            'nuevo_estado' => $estacion->estado->name,
            'color_estado' => $estacion->estado->value == 'A.A' ? 'success' : 'danger'
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Presbitero;
use App\Models\Estacion;
use Illuminate\Http\Request;

class PresbiterioController extends Controller
{
    /**
     * Mostrar lista de presbiterios (zonas)
     */
    public function index(Request $request)
    {
        $query = Presbitero::query()
            ->withCount('estaciones');

        // Filtro por sector
        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre_completo', 'like', "%{$search}%")
                  ->orWhere('celular', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('iglesias_asignadas', 'like', "%{$search}%");
            });
        }

        $presbiterios = $query->orderBy('sector')
            ->orderBy('codigo')
            ->get();

        $sectores = ['NORTE' => 'Norte', 'CENTRO' => 'Centro', 'SUR' => 'Sur'];

        return view('presbiterios.index', compact('presbiterios', 'sectores'));
    }

    /**
     * Ver detalle de un presbiterio con sus estaciones asignadas
     */
    public function show(Presbitero $presbiterio)
    {
        $presbiterio->load('estaciones');

        return view('presbiterios.show', compact('presbiterio'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Presbitero $presbiterio)
    {
        $sectores = ['NORTE' => 'Norte', 'CENTRO' => 'Centro', 'SUR' => 'Sur'];

        return view('presbiterios.edit', compact('presbiterio', 'sectores'));
    }

    /**
     * Actualizar presbiterio
     */
    public function update(Request $request, Presbitero $presbiterio)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:presbiteros,codigo,' . $presbiterio->id,
            'nombre_completo' => 'required|string|max:255',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'sector' => 'required|in:NORTE,CENTRO,SUR',
            'iglesias_asignadas' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,inactivo,licencia',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $presbiterio->update($validated);

        return redirect()->route('presbiterios.show', $presbiterio)
            ->with('success', 'Presbiterio actualizado correctamente.');
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $sectores = ['NORTE' => 'Norte', 'CENTRO' => 'Centro', 'SUR' => 'Sur'];

        return view('presbiterios.create', compact('sectores'));
    }

    /**
     * Guardar nuevo presbiterio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:20|unique:presbiteros,codigo',
            'nombre_completo' => 'required|string|max:255',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'sector' => 'required|in:NORTE,CENTRO,SUR',
            'fecha_ordenacion' => 'nullable|date',
            'iglesias_asignadas' => 'nullable|string|max:500',
            'estado' => 'required|in:activo,inactivo,licencia',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $presbiterio = Presbitero::create($validated);

        return redirect()->route('presbiterios.show', $presbiterio)
            ->with('success', 'Presbiterio creado correctamente.');
    }
}

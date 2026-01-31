<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Estacion;
use App\Enums\RolUsuario;
use App\Notifications\TicketStatusUpdated;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        return view('tickets.index', [
            'estados' => Ticket::estados(),
        ]);
    }

    /**
     * Endpoint JSON para búsqueda AJAX de tickets
     */
    public function data(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $query = Ticket::query()->with(['estacion']);

        // Búsqueda en cualquier campo
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('equipo', 'like', "%{$q}%")
                    ->orWhere('servicio', 'like', "%{$q}%")
                    ->orWhere('estado', 'like', "%{$q}%")
                    ->orWhere('fecha_ingreso', 'like', "%{$q}%")
                    ->orWhereHas('estacion', fn($s) => $s->where('localidad', 'like', "%{$q}%"));
            });
        }

        // Si sectorista, filtrar por sector
        $user = auth()->user();
        $rol = $user->rol instanceof RolUsuario ? $user->rol->value : $user->rol;

        if ($rol === 'sectorista' && $user->sector_asignado) {
            $query->whereHas('estacion', function ($s) use ($user) {
                $s->where('sector', $user->sector_asignado);
            });
        }

        // Ordenar por fecha_ingreso DESC
        $tickets = $query->orderByDesc('fecha_ingreso')->get();

        $estados = Ticket::estados();

        $data = $tickets->map(function ($t) use ($estados) {
            return [
                'id' => $t->id,
                'fecha_ingreso' => $t->fecha_ingreso?->format('Y-m-d'),
                'equipo' => $t->equipo,
                'servicio' => $t->servicio,
                'estacion' => $t->estacion?->localidad ?? '-',
                'estado' => $t->estado,
                'estado_label' => $estados[$t->estado] ?? $t->estado,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    public function create()
    {
        $estaciones = Estacion::orderBy('localidad')->get();
        return view('tickets.create', [
            'estaciones' => $estaciones,
            'estados' => Ticket::estados(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_ingreso' => 'nullable|date',
            'equipo' => 'required|string|max:150',
            'servicio' => 'nullable|string|max:150',
            'estacion_id' => 'nullable|exists:estaciones,id',
            'descripcion' => 'nullable|string|max:5000',
        ]);

        $ticket = Ticket::create([
            ...$validated,
            'estado' => 'solicitud_nueva',
            'creado_por_user_id' => auth()->id(),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['estacion', 'creadoPor', 'actualizadoPor']);
        return view('tickets.show', [
            'ticket' => $ticket,
            'estados' => Ticket::estados(),
        ]);
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeEdit($ticket);

        $estaciones = Estacion::orderBy('localidad')->get();
        return view('tickets.edit', [
            'ticket' => $ticket,
            'estaciones' => $estaciones,
            'estados' => Ticket::estados(),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeEdit($ticket);

        $validated = $request->validate([
            'fecha_ingreso' => 'nullable|date',
            'equipo' => 'required|string|max:150',
            'servicio' => 'nullable|string|max:150',
            'estacion_id' => 'nullable|exists:estaciones,id',
            'descripcion' => 'nullable|string|max:5000',
        ]);

        $ticket->update([
            ...$validated,
            'actualizado_por_user_id' => auth()->id(),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeDelete($ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        // Solo admin, coordinador_operaciones o encargado_logistico deben poder cambiar estado:
        $user = auth()->user();
        $rol = $user->rol instanceof RolUsuario ? $user->rol->value : $user->rol;

        if (!in_array($rol, ['administrador', 'coordinador_operaciones', 'encargado_logistico'])) {
            abort(403);
        }

        $validated = $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Ticket::estados())),
            'observacion_logistica' => 'nullable|string|max:5000',
        ]);

        $estadoAnterior = $ticket->estado;
        $estadoNuevo = $validated['estado'];

        $ticket->estado = $estadoNuevo;
        $ticket->observacion_logistica = $validated['observacion_logistica'] ?? $ticket->observacion_logistica;
        $ticket->actualizado_por_user_id = auth()->id();
        $ticket->fecha_cambio_estado = now();
        $ticket->save();

        // Notificar a todos los encargados_logistico cuando cambie estado
        if ($estadoAnterior !== $estadoNuevo) {
            $logisticos = User::where('activo', 1)
                ->where('rol', 'encargado_logistico')
                ->get();

            foreach ($logisticos as $logistico) {
                $logistico->notify(new TicketStatusUpdated($ticket, Ticket::estados()[$estadoAnterior] ?? $estadoAnterior, Ticket::estados()[$estadoNuevo] ?? $estadoNuevo));
            }
        }

        return back()->with('success', 'Estado actualizado.');
    }

    private function authorizeEdit(Ticket $ticket): void
    {
        $user = auth()->user();
        $rol = $user->rol instanceof RolUsuario ? $user->rol->value : $user->rol;

        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) return;

        // Creador puede editar mientras no esté cerrado
        if ($ticket->creado_por_user_id === $user->id && $ticket->estado !== 'cerrado') return;

        abort(403);
    }

    private function authorizeDelete(Ticket $ticket): void
    {
        $user = auth()->user();
        $rol = $user->rol instanceof RolUsuario ? $user->rol->value : $user->rol;

        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) return;

        // Creador puede eliminar si aún es solicitud_nueva
        if ($ticket->creado_por_user_id === $user->id && $ticket->estado === 'solicitud_nueva') return;

        abort(403);
    }
}

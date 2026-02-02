<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mostrar centro de notificaciones
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Construir query de notificaciones
        $query = $user->notifications();

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('data->type', $request->type);
        }

        // Filtro por severidad
        if ($request->filled('severity')) {
            $query->where('data->severity', $request->severity);
        }

        // Filtro por sector
        if ($request->filled('sector')) {
            $query->where('data->sector', $request->sector);
        }

        // Filtro por estado (leídas/no leídas)
        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc');

        // Paginación
        $notifications = $query->paginate(20);

        // Estadísticas
        $estadisticas = [
            'total' => $user->notifications()->count(),
            'no_leidas' => $user->unreadNotifications()->count(),
            'leidas' => $user->notifications()->whereNotNull('read_at')->count(),
            'criticas' => $user->unreadNotifications()->where('data->severity', 'critica')->count(),
            'hoy' => $user->notifications()->whereDate('created_at', today())->count(),
        ];

        // Tipos de notificación para filtros
        $tipos = [
            'licencia_vence' => 'Licencias por vencer',
            'licencia_vencida' => 'Licencias vencidas',
            'estacion_fuera_aire' => 'Estaciones fuera del aire',
            'incidencia_estancada' => 'Incidencias estancadas',
            'incidencia_transferida' => 'Transferencias',
            'ticket' => 'Tickets',
            'renovacion' => 'Renovaciones',
        ];

        return view('notifications.index', compact('notifications', 'estadisticas', 'tipos'));
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas las notificaciones han sido marcadas como leídas');
    }

    /**
     * Eliminar una notificación
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada'
        ]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function deleteRead()
    {
        $count = Auth::user()->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', "{$count} notificaciones leídas han sido eliminadas");
    }

    /**
     * Obtener notificaciones sin leer (AJAX)
     */
    public function getUnread()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->take(10)->get();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'general',
                    'severity' => $notification->data['severity'] ?? 'info',
                    'titulo' => $notification->data['titulo'] ?? 'Notificación',
                    'mensaje' => $notification->data['mensaje'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'icono' => $notification->data['icono'] ?? 'bell',
                    'color' => $notification->data['color'] ?? 'info',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })
        ]);
    }
}

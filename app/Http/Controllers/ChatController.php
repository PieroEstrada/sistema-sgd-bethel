<?php

namespace App\Http\Controllers;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Mostrar la interfaz principal del chat
     */
    public function index()
    {
        $currentUserId = auth()->id();

        // Obtener usuarios con los que el usuario actual ha tenido conversaciones
        $conversations = $this->getConversationsList($currentUserId);

        return view('chat.index', [
            'conversations' => $conversations,
            'allUsers' => User::where('id', '!=', $currentUserId)
                             ->orderBy('name')
                             ->get(['id', 'name', 'email', 'rol'])
        ]);
    }

    /**
     * Obtener lista de conversaciones del usuario actual
     */
    public function getConversations()
    {
        $currentUserId = auth()->id();
        $conversations = $this->getConversationsList($currentUserId);

        return response()->json($conversations);
    }

    /**
     * Obtener mensajes de una conversación específica
     */
    public function getMessages(Request $request, $otherUserId)
    {
        $currentUserId = auth()->id();

        $messages = Message::betweenUsers($currentUserId, $otherUserId)
            ->with(['fromUser:id,name', 'toUser:id,name'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcar como leídos los mensajes recibidos
        Message::where('from_user_id', $otherUserId)
            ->where('to_user_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages,
            'otherUser' => User::find($otherUserId, ['id', 'name', 'email', 'rol'])
        ]);
    }

    /**
     * Enviar un nuevo mensaje
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        // Validar que el sender_id sea el usuario autenticado
        $message = Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id' => $request->to_user_id,
            'message' => $request->message,
        ]);

        $message->load(['fromUser:id,name', 'toUser:id,name']);

        // Broadcast del evento MessageSent en tiempo real
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Marcar mensajes como leídos
     */
    public function markAsRead(Request $request, $otherUserId)
    {
        $currentUserId = auth()->id();
        $readAt = now();

        // Obtener IDs de mensajes que se van a marcar como leídos
        $messageIds = Message::where('from_user_id', $otherUserId)
            ->where('to_user_id', $currentUserId)
            ->whereNull('read_at')
            ->pluck('id')
            ->toArray();

        // Marcar como leídos
        $updated = Message::whereIn('id', $messageIds)
            ->update(['read_at' => $readAt]);

        // Si se marcaron mensajes, emitir evento
        if (count($messageIds) > 0) {
            broadcast(new MessageRead(
                $messageIds,
                $currentUserId,
                (int) $otherUserId,
                $readAt->toISOString()
            ))->toOthers();
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message_ids' => $messageIds
        ]);
    }

    /**
     * Obtener contador de mensajes no leídos
     */
    public function getUnreadCount()
    {
        $count = Message::where('to_user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Obtener lista de usuarios para nueva conversación (paginado)
     */
    public function getUsers(Request $request)
    {
        $currentUserId = auth()->id();
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        $query = User::where('id', '!=', $currentUserId)
                     ->where('activo', true)
                     ->select('id', 'name', 'email', 'rol');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
                       ->paginate($perPage, ['*'], 'page', $page);

        // Formatear rol para mostrar
        $users->getCollection()->transform(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => is_object($user->rol) ? $user->rol->getDisplayName() : $user->rol
            ];
        });

        return response()->json([
            'users' => $users->items(),
            'has_more' => $users->hasMorePages(),
            'current_page' => $users->currentPage(),
            'total' => $users->total()
        ]);
    }

    /**
     * Helper: Obtener lista de conversaciones con último mensaje y contador
     */
    private function getConversationsList($userId)
    {
        // Obtener últimos mensajes de cada conversación
        $lastMessages = DB::table('messages as m1')
            ->select('m1.*')
            ->whereIn('m1.id', function ($query) use ($userId) {
                $query->select(DB::raw('MAX(m2.id)'))
                    ->from('messages as m2')
                    ->where(function ($q) use ($userId) {
                        $q->where('m2.from_user_id', $userId)
                          ->orWhere('m2.to_user_id', $userId);
                    })
                    ->groupBy(DB::raw('LEAST(m2.from_user_id, m2.to_user_id), GREATEST(m2.from_user_id, m2.to_user_id)'));
            })
            ->orderBy('m1.created_at', 'desc')
            ->get();

        $conversations = [];
        foreach ($lastMessages as $msg) {
            $otherUserId = $msg->from_user_id == $userId ? $msg->to_user_id : $msg->from_user_id;
            $otherUser = User::find($otherUserId, ['id', 'name', 'email', 'rol']);

            if (!$otherUser) continue;

            // Contar mensajes no leídos de este usuario
            $unreadCount = Message::where('from_user_id', $otherUserId)
                ->where('to_user_id', $userId)
                ->unread()
                ->count();

            $conversations[] = [
                'user' => $otherUser,
                'last_message' => $msg->message,
                'last_message_time' => $msg->created_at,
                'unread_count' => $unreadCount,
                'is_from_me' => $msg->from_user_id == $userId,
            ];
        }

        return $conversations;
    }
}

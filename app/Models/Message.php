<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relación con el usuario remitente
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Relación con el usuario destinatario
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Marcar mensaje como leído
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Verificar si el mensaje ha sido leído
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Scope para mensajes entre dos usuarios específicos
     */
    public function scopeBetweenUsers($query, $user1Id, $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where('from_user_id', $user1Id)->where('to_user_id', $user2Id);
        })->orWhere(function ($q) use ($user1Id, $user2Id) {
            $q->where('from_user_id', $user2Id)->where('to_user_id', $user1Id);
        });
    }

    /**
     * Scope para mensajes no leídos
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para mensajes recibidos por un usuario
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    /**
     * Scope para mensajes enviados por un usuario
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('from_user_id', $userId);
    }

    /**
     * Obtener el ID de conversación determinístico para dos usuarios
     * Formato: chat.{minId}.{maxId}
     */
    public static function getConversationId(int $userId1, int $userId2): string
    {
        $ids = [min($userId1, $userId2), max($userId1, $userId2)];
        return "chat.{$ids[0]}.{$ids[1]}";
    }

    /**
     * Obtener el ID de conversación para este mensaje
     */
    public function getConversationIdAttribute(): string
    {
        return self::getConversationId($this->from_user_id, $this->to_user_id);
    }
}

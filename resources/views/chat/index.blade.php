@extends('layouts.app')

@section('title', 'Mensajes')

@section('content')
<div class="container-fluid px-4 py-3" x-data="chatApp()" x-init="init()">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Mensajes</li>
        </ol>
    </nav>

    <div class="row">
        <!-- LISTA DE CONVERSACIONES -->
        <div class="col-lg-4 col-md-5 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>Conversaciones
                        <span x-show="totalUnread > 0" class="badge bg-danger ms-2" x-text="totalUnread"></span>
                    </h5>
                    <!-- BOTÓN + CON MÉTODO DUAL -->
                    <button type="button"
                            id="btnNuevaConversacion"
                            class="btn btn-sm btn-light"
                            data-bs-toggle="modal"
                            data-bs-target="#newChatModal"
                            @click="onOpenModal()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <!-- Loading -->
                    <template x-if="loadingConversations">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2 small">Cargando conversaciones...</p>
                        </div>
                    </template>

                    <!-- Sin conversaciones -->
                    <template x-if="!loadingConversations && conversations.length === 0">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No hay conversaciones aún</p>
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#newChatModal">
                                <i class="fas fa-plus me-1"></i>Iniciar Chat
                            </button>
                        </div>
                    </template>

                    <!-- Lista de conversaciones -->
                    <template x-if="!loadingConversations && conversations.length > 0">
                        <div>
                            <template x-for="conv in conversations" :key="'conv-' + conv.user.id">
                                <div class="conversation-item p-3 border-bottom position-relative"
                                     :class="{'bg-light': selectedUserId == conv.user.id, 'conv-unread': conv.unread_count > 0}"
                                     @click="selectUser(conv.user.id)"
                                     style="cursor: pointer; transition: background 0.2s;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <strong x-text="conv.user.name"></strong>
                                                <span x-show="conv.unread_count > 0"
                                                      class="badge bg-danger rounded-pill ms-2 px-2"
                                                      x-text="conv.unread_count"
                                                      style="font-size: 0.7rem;"></span>
                                            </div>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                <span x-show="conv.is_from_me" class="fw-bold text-primary">Tú: </span>
                                                <span x-text="conv.last_message"></span>
                                            </div>
                                        </div>
                                        <div class="text-muted small text-end ms-2" style="min-width: 60px; font-size: 0.75rem;">
                                            <span x-text="formatTime(conv.last_message_time)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- VENTANA DE MENSAJES -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm" style="height: calc(100vh - 200px);">
                <!-- Header del chat -->
                <div class="card-header bg-white border-bottom">
                    <template x-if="!selectedUserId">
                        <div class="text-muted text-center py-2">
                            <i class="fas fa-arrow-left me-2"></i>
                            Selecciona una conversación para comenzar
                        </div>
                    </template>
                    <template x-if="selectedUserId && selectedUser">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px; font-weight: bold;">
                                <span x-text="selectedUser.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <h6 class="mb-0" x-text="selectedUser.name"></h6>
                                <small class="text-muted" x-text="selectedUser.email"></small>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Área de mensajes -->
                <div class="card-body p-3" id="messagesContainer"
                     style="overflow-y: auto; height: calc(100% - 140px); background: #f8f9fa;">

                    <!-- Sin selección -->
                    <template x-if="!selectedUserId">
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                            <div class="text-center">
                                <i class="fas fa-comments fa-4x mb-3 opacity-50"></i>
                                <h5>Bienvenido a Mensajes</h5>
                                <p>Selecciona una conversación o inicia una nueva</p>
                            </div>
                        </div>
                    </template>

                    <!-- Loading mensajes -->
                    <template x-if="selectedUserId && loadingMessages">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2 small">Cargando mensajes...</p>
                        </div>
                    </template>

                    <!-- Mensajes -->
                    <template x-if="selectedUserId && !loadingMessages">
                        <div>
                            <template x-for="msg in messages" :key="'msg-' + msg.id">
                                <div class="mb-3"
                                     :class="msg.from_user_id == currentUserId ? 'text-end' : 'text-start'">
                                    <div class="d-inline-block message-bubble px-3 py-2 rounded-3 shadow-sm"
                                         :class="msg.from_user_id == currentUserId ? 'bg-primary text-white' : 'bg-white border'"
                                         style="max-width: 70%;">
                                        <div x-html="escapeAndFormat(msg.message)"
                                             style="white-space: pre-wrap; word-break: break-word;"></div>
                                        <div class="small mt-1"
                                             :class="msg.from_user_id == currentUserId ? 'text-white-50' : 'text-muted'"
                                             style="font-size: 0.7rem;">
                                            <span x-text="formatDateTime(msg.created_at)"></span>
                                            <span x-show="msg.from_user_id == currentUserId && msg.read_at">
                                                <i class="fas fa-check-double ms-1"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Input de mensaje -->
                <div class="card-footer bg-white border-top p-3">
                    <template x-if="selectedUserId">
                        <form @submit.prevent="sendMessage()" class="d-flex gap-2">
                            <textarea
                                x-model="newMessage"
                                class="form-control"
                                rows="2"
                                placeholder="Escribe tu mensaje..."
                                @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                                :disabled="sending"
                                style="resize: none;"></textarea>
                            <button type="submit"
                                    class="btn btn-primary"
                                    :disabled="!newMessage.trim() || sending"
                                    style="min-width: 80px;">
                                <span x-show="!sending"><i class="fas fa-paper-plane"></i></span>
                                <span x-show="sending" class="spinner-border spinner-border-sm"></span>
                            </button>
                        </form>
                    </template>
                    <template x-if="!selectedUserId">
                        <div class="text-center text-muted py-2">
                            <small>Selecciona una conversación para enviar mensajes</small>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nueva Conversación -->
    <div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newChatModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Nueva Conversación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Búsqueda -->
                    <div class="mb-3">
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar usuario..."
                               x-model="searchQuery"
                               @input="searchUsers()">
                    </div>

                    <!-- Lista de usuarios -->
                    <div style="max-height: 400px; overflow-y: auto;">
                        <template x-if="filteredUsers.length === 0">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                <p class="mb-0">No se encontraron usuarios</p>
                            </div>
                        </template>

                        <template x-for="user in filteredUsers" :key="'user-' + user.id">
                            <div class="user-item p-2 border-bottom"
                                 @click="startChatWithUser(user.id)"
                                 style="cursor: pointer;"
                                 :class="{'bg-light': hoveredUserId == user.id}">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3"
                                         style="width: 40px; height: 40px; font-weight: bold;">
                                        <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0" x-text="user.name"></h6>
                                        <small class="text-muted" x-text="user.email"></small>
                                    </div>
                                    <button type="button"
                                            class="btn btn-sm btn-primary"
                                            @click.stop="startChatWithUser(user.id)">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.conversation-item:hover {
    background-color: #e9ecef !important;
}

.conv-unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196F3;
}

.user-item:hover {
    background-color: #f8f9fa !important;
}

.message-bubble {
    display: inline-block;
    text-align: left;
}

#messagesContainer {
    scroll-behavior: smooth;
}

#messagesContainer::-webkit-scrollbar {
    width: 6px;
}

#messagesContainer::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 3px;
}

#messagesContainer::-webkit-scrollbar-track {
    background-color: rgba(0,0,0,0.05);
}
</style>
@endsection

@push('scripts')
<script>
// ⚡ CHAT CON WEBSOCKETS (Laravel Reverb + Echo)
function chatApp() {
    return {
        // Estado
        currentUserId: {{ auth()->id() }},
        conversations: [],
        allUsers: @json($allUsers ?? []),
        selectedUserId: null,
        selectedUser: null,
        messages: [],
        newMessage: '',
        searchQuery: '',
        hoveredUserId: null,

        // Flags
        loadingConversations: true,
        loadingMessages: false,
        sending: false,

        // WebSocket
        currentChannel: null,
        conversationId: null,

        // Computed
        get filteredUsers() {
            if (!this.searchQuery) return this.allUsers;
            const query = this.searchQuery.toLowerCase();
            return this.allUsers.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query)
            );
        },

        get totalUnread() {
            return this.conversations.reduce((sum, conv) => sum + (conv.unread_count || 0), 0);
        },

        // Inicialización
        async init() {
            console.log('💬 Inicializando chat con WebSockets...');

            // Verificar que Echo esté disponible
            if (typeof window.Echo === 'undefined') {
                console.error('❌ Laravel Echo no está disponible. Asegúrate de ejecutar "npm run build"');
                alert('Error: WebSocket no disponible. Contacta al administrador.');
                return;
            }

            console.log('✅ Echo disponible, iniciando...');
            await this.loadConversations();
        },

        // Callback cuando se abre el modal
        onOpenModal() {
            console.log('📂 Abriendo modal de nueva conversación...');
            this.searchQuery = '';
        },

        // Cargar lista de conversaciones
        async loadConversations() {
            try {
                const response = await fetch('/chat/conversations');
                if (!response.ok) throw new Error('Error al cargar conversaciones');

                const data = await response.json();
                this.conversations = Array.isArray(data) ? data : [];
                console.log(`✓ Cargadas ${this.conversations.length} conversaciones`);
            } catch (error) {
                console.error('Error al cargar conversaciones:', error);
                this.conversations = [];
            } finally {
                this.loadingConversations = false;
            }
        },

        // Seleccionar usuario y cargar mensajes
        async selectUser(userId) {
            // Desconectar canal anterior si existe
            if (this.currentChannel) {
                console.log(`🔌 Desconectando del canal: ${this.conversationId}`);
                window.Echo.leave(this.conversationId);
                this.currentChannel = null;
            }

            this.selectedUserId = userId;
            this.loadingMessages = true;
            this.messages = [];

            try {
                const response = await fetch(`/chat/messages/${userId}`);
                if (!response.ok) throw new Error('Error al cargar mensajes');

                const data = await response.json();
                this.messages = data.messages || [];
                this.selectedUser = data.otherUser;

                console.log(`✓ Cargados ${this.messages.length} mensajes con ${this.selectedUser?.name}`);

                // Scroll to bottom
                this.$nextTick(() => {
                    this.scrollToBottom();
                });

                // Marcar como leído
                const conv = this.conversations.find(c => c.user.id == userId);
                if (conv && conv.unread_count > 0) {
                    conv.unread_count = 0;
                    await this.markAsRead(userId);
                }

                // Conectar a canal WebSocket
                this.connectToChannel(userId);

            } catch (error) {
                console.error('Error al cargar mensajes:', error);
                alert('Error al cargar mensajes');
            } finally {
                this.loadingMessages = false;
            }
        },

        // Conectar a canal WebSocket privado
        connectToChannel(otherUserId) {
            const ids = [Math.min(this.currentUserId, otherUserId), Math.max(this.currentUserId, otherUserId)];
            this.conversationId = `chat.${ids[0]}.${ids[1]}`;

            console.log(`📡 Conectando a canal privado: ${this.conversationId}`);

            this.currentChannel = window.Echo.private(this.conversationId);

            // Listener: Mensaje enviado
            this.currentChannel.listen('.message.sent', (event) => {
                console.log('📨 Mensaje recibido por WebSocket:', event);

                // Solo agregar si el mensaje no está ya en el array
                if (!this.messages.find(m => m.id === event.id)) {
                    this.messages.push(event);

                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });

                    // Marcar como leído automáticamente si estoy en esta conversación
                    this.markAsRead(otherUserId);
                }

                // Actualizar lista de conversaciones
                this.loadConversations();
            });

            // Listener: Mensaje leído
            this.currentChannel.listen('.message.read', (event) => {
                console.log('✅ Mensaje(s) marcado(s) como leído(s):', event);

                // Actualizar read_at en los mensajes
                event.message_ids.forEach(msgId => {
                    const msg = this.messages.find(m => m.id === msgId);
                    if (msg) {
                        msg.read_at = event.read_at;
                    }
                });
            });

            console.log('✅ Conectado al canal WebSocket');
        },

        // Enviar mensaje
        async sendMessage() {
            if (!this.newMessage.trim() || this.sending || !this.selectedUserId) {
                return;
            }

            this.sending = true;
            const messageText = this.newMessage.trim();
            this.newMessage = ''; // Limpiar input inmediatamente

            try {
                console.log('📤 Enviando mensaje...');

                const response = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        to_user_id: this.selectedUserId,
                        message: messageText
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Error ${response.status}: ${errorText}`);
                }

                const data = await response.json();

                if (data.success && data.message) {
                    console.log('✓ Mensaje enviado:', data.message.id);

                    // Agregar mensaje al array (el WebSocket también lo enviará, pero lo filtramos por ID)
                    if (!this.messages.find(m => m.id === data.message.id)) {
                        this.messages.push(data.message);
                    }

                    // Scroll to bottom
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });

                    // Actualizar conversaciones
                    await this.loadConversations();
                } else {
                    throw new Error('Respuesta inválida del servidor');
                }

            } catch (error) {
                console.error('❌ Error enviando mensaje:', error);
                alert('Error al enviar mensaje: ' + error.message);
                this.newMessage = messageText; // Restaurar mensaje
            } finally {
                this.sending = false;
            }
        },

        // Marcar mensajes como leídos
        async markAsRead(userId) {
            try {
                await fetch(`/chat/mark-read/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Error al marcar como leído:', error);
            }
        },

        // Iniciar chat con usuario desde modal
        async startChatWithUser(userId) {
            console.log('💬 Iniciando chat con usuario:', userId);

            // Cerrar modal usando Bootstrap API
            const modalEl = document.getElementById('newChatModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Seleccionar usuario
            await this.selectUser(userId);
        },

        searchUsers() {
            // La búsqueda se hace reactivamente con filteredUsers
        },

        // Utilidades
        scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(hours / 24);

            if (days === 0) {
                if (hours === 0) {
                    const minutes = Math.floor(diff / 60000);
                    return minutes === 0 ? 'Ahora' : `${minutes}m`;
                }
                return `${hours}h`;
            } else if (days === 1) {
                return 'Ayer';
            } else if (days < 7) {
                return `${days}d`;
            } else {
                return date.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
            }
        },

        formatDateTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        escapeAndFormat(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
}
</script>
@endpush

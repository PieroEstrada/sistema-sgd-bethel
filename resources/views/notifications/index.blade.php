@extends('layouts.app')

@section('title', 'Centro de Notificaciones - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Notificaciones</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-bell text-primary me-2"></i>
                    Centro de Notificaciones
                </h1>
                <div>
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
                        </button>
                    </form>
                    <form action="{{ route('notifications.delete-read') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Eliminar todas las notificaciones leídas?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> Eliminar leídas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $estadisticas['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">No Leídas</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $estadisticas['no_leidas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Leídas</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $estadisticas['leidas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Críticas</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $estadisticas['criticas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hoy</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $estadisticas['hoy'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="filter" class="form-label">Estado</label>
                        <select name="filter" id="filter" class="form-select">
                            <option value="">Todas</option>
                            <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>No leídas</option>
                            <option value="read" {{ request('filter') === 'read' ? 'selected' : '' }}>Leídas</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="severity" class="form-label">Severidad</label>
                        <select name="severity" id="severity" class="form-select">
                            <option value="">Todas</option>
                            <option value="critica" {{ request('severity') === 'critica' ? 'selected' : '' }}>Crítica</option>
                            <option value="alta" {{ request('severity') === 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="media" {{ request('severity') === 'media' ? 'selected' : '' }}>Media</option>
                            <option value="baja" {{ request('severity') === 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sector" class="form-label">Sector</label>
                        <select name="sector" id="sector" class="form-select">
                            <option value="">Todos los sectores</option>
                            <option value="NORTE" {{ request('sector') === 'NORTE' ? 'selected' : '' }}>Norte</option>
                            <option value="CENTRO" {{ request('sector') === 'CENTRO' ? 'selected' : '' }}>Centro</option>
                            <option value="SUR" {{ request('sector') === 'SUR' ? 'selected' : '' }}>Sur</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Notificaciones -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Notificaciones ({{ $notifications->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="notification-item border-bottom p-3 {{ $isUnread ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-start">
                        <!-- Icono -->
                        <div class="notification-icon me-3">
                            <i class="fas fa-{{ $data['icono'] ?? 'bell' }} fa-2x text-{{ $data['color'] ?? 'info' }}"></i>
                        </div>

                        <!-- Contenido -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $data['titulo'] ?? 'Notificación' }}
                                        @if($isUnread)
                                            <span class="badge bg-primary ms-2">Nuevo</span>
                                        @endif
                                        @if(isset($data['severity']))
                                            <span class="badge bg-{{ $data['color'] ?? 'secondary' }} ms-1">
                                                {{ ucfirst($data['severity']) }}
                                            </span>
                                        @endif
                                    </h6>
                                    <p class="mb-1 text-muted">{{ $data['mensaje'] ?? '' }}</p>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Metadata adicional -->
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if(isset($data['sector']))
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $data['sector'] }}
                                    </span>
                                @endif
                                @if(isset($data['estacion_codigo']))
                                    <span class="badge bg-info">
                                        <i class="fas fa-broadcast-tower me-1"></i>{{ $data['estacion_codigo'] }}
                                    </span>
                                @endif
                                @if(isset($data['incidencia_codigo']))
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clipboard-list me-1"></i>{{ $data['incidencia_codigo'] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Acciones -->
                            <div class="d-flex gap-2">
                                @if(isset($data['url']))
                                    <a href="{{ $data['url'] }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Ver detalle
                                    </a>
                                @endif
                                @if($isUnread)
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="markAsRead('{{ $notification->id }}')">
                                        <i class="fas fa-check me-1"></i> Marcar como leída
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteNotification('{{ $notification->id }}')">
                                    <i class="fas fa-trash me-1"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No hay notificaciones para mostrar</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Paginación -->
    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (!confirm('¿Eliminar esta notificación?')) return;

    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush

@push('styles')
<style>
.notification-item {
    transition: background-color 0.2s;
}
.notification-item:hover {
    background-color: #f8f9fc !important;
}
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.gap-2 { gap: 0.5rem; }
</style>
@endpush

@extends('layouts.app')

@section('title', 'Editar Incidencia - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
            <li class="breadcrumb-item"><a href="{{ route('incidencias.show', $incidencia) }}">{{ $incidencia->codigo_incidencia }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit text-warning me-2"></i>
                        Editar Incidencia
                    </h1>
                    <p class="text-muted">{{ $incidencia->codigo_incidencia }} - {{ $incidencia->titulo }}</p>
                </div>
                <div>
                    <a href="{{ route('incidencias.show', $incidencia) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-2"></i>Ver Detalles
                    </a>
                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">Modificar Datos de la Incidencia</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('incidencias.update', $incidencia) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información Básica -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-warning border-bottom pb-2">
                                    <i class="fas fa-info-circle me-2"></i>Información Básica
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="codigo_incidencia" class="form-label">Código de Incidencia</label>
                                <input type="text" class="form-control" id="codigo_incidencia" 
                                       value="{{ $incidencia->codigo_incidencia }}" readonly>
                                <small class="form-text text-muted">El código no se puede modificar</small>
                            </div>
                            <div class="col-md-4">
                                <label for="estacion_id" class="form-label">Estación Afectada <span class="text-danger">*</span></label>
                                <select class="form-control @error('estacion_id') is-invalid @enderror" 
                                        id="estacion_id" name="estacion_id" required>
                                    <option value="">Seleccionar estación</option>
                                    @foreach($estaciones as $estacion)
                                        <option value="{{ $estacion->id }}" {{ old('estacion_id', $incidencia->estacion_id) == $estacion->id ? 'selected' : '' }}>
                                            {{ $estacion->codigo }} - {{ $estacion->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estacion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="reportado_por_display" class="form-label">Reportado Por</label>
                                <input type="text" class="form-control" id="reportado_por_display" 
                                       value="{{ $incidencia->nombre_reportante }}" readonly>
                                <small class="form-text text-muted">No se puede modificar el reportante</small>
                            </div>
                        </div>

                        <!-- 
                            El formulario envía 'descripcion_corta' y 'descripcion_detallada'.
                            El controller update() los mapea a titulo y descripcion respectivamente.
                        -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="descripcion_corta" class="form-label">Descripción Corta (Título) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('descripcion_corta') is-invalid @enderror" 
                                       id="descripcion_corta" name="descripcion_corta" 
                                       value="{{ old('descripcion_corta', $incidencia->titulo) }}" 
                                       placeholder="Resumen breve del problema" maxlength="255" required>
                                @error('descripcion_corta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="descripcion_detallada" class="form-label">Descripción Detallada <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('descripcion_detallada') is-invalid @enderror" 
                                          id="descripcion_detallada" name="descripcion_detallada" rows="4" 
                                          placeholder="Describe detalladamente el problema..." 
                                          maxlength="2000" required>{{ old('descripcion_detallada', $incidencia->descripcion) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_descripcion">0</span>/2000 caracteres
                                </small>
                                @error('descripcion_detallada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Clasificación y Estado -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-warning border-bottom pb-2">
                                    <i class="fas fa-tags me-2"></i>Clasificación y Estado
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                                <select class="form-control @error('prioridad') is-invalid @enderror" 
                                        id="prioridad" name="prioridad" required>
                                    <option value="">Seleccionar prioridad</option>
                                    @foreach($prioridades as $key => $value)
                                        <option value="{{ $key }}" {{ old('prioridad', $incidencia->prioridad->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-control @error('estado') is-invalid @enderror" 
                                        id="estado" name="estado" required
                                        @if(!($camposEditables['puede_cambiar_estado'] ?? false)) disabled @endif>
                                    <option value="">Seleccionar estado</option>
                                    @foreach($estados as $key => $value)
                                        <option value="{{ $key }}" {{ old('estado', $incidencia->estado->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Si está disabled, enviar el valor actual oculto -->
                                @if(!($camposEditables['puede_cambiar_estado'] ?? false))
                                <input type="hidden" name="estado" value="{{ $incidencia->estado->value }}">
                                @endif
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-control @error('categoria') is-invalid @enderror" 
                                        id="categoria" name="categoria">
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categorias as $key => $value)
                                        <option value="{{ $key }}" {{ old('categoria', $incidencia->categoria) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="impacto_servicio" class="form-label">Impacto en el Servicio</label>
                                <select class="form-control @error('impacto_servicio') is-invalid @enderror" 
                                        id="impacto_servicio" name="impacto_servicio">
                                    <option value="">Seleccionar impacto</option>
                                    <option value="bajo" {{ old('impacto_servicio', $incidencia->impacto_servicio) == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                    <option value="medio" {{ old('impacto_servicio', $incidencia->impacto_servicio) == 'medio' ? 'selected' : '' }}>Medio</option>
                                    <option value="alto" {{ old('impacto_servicio', $incidencia->impacto_servicio) == 'alto' ? 'selected' : '' }}>Alto</option>
                                </select>
                                @error('impacto_servicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Gestión y Seguimiento -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-warning border-bottom pb-2">
                                    <i class="fas fa-tasks me-2"></i>Gestión y Seguimiento
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="asignado_a_user_id" class="form-label">Asignado A</label>
                                <select class="form-control @error('asignado_a_user_id') is-invalid @enderror" 
                                        id="asignado_a_user_id" name="asignado_a_user_id"
                                        @if(!($camposEditables['puede_asignar'] ?? false)) disabled @endif>
                                    <option value="">Sin asignar</option>
                                    @foreach($usuariosTecnicos as $usuario)
                                        <option value="{{ $usuario->id }}" {{ old('asignado_a_user_id', $incidencia->asignado_a_user_id) == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->name }} ({{ $usuario->rol }})
                                        </option>
                                    @endforeach
                                </select>
                                <!-- Si está disabled, enviar el valor actual oculto -->
                                @if(!($camposEditables['puede_asignar'] ?? false))
                                <input type="hidden" name="asignado_a_user_id" value="{{ $incidencia->asignado_a_user_id }}">
                                @endif
                                @error('asignado_a_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    @if($camposEditables['puede_asignar'] ?? false)
                                        Seleccione el usuario que será responsable de resolver la incidencia.
                                        Para transferir a otra <strong>área</strong>, use el botón "Transferir" en la vista de detalle.
                                    @else
                                        No tienes permisos para cambiar la asignación.
                                    @endif
                                </small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Área Responsable</label>
                                <input type="text" class="form-control" 
                                       value="{{ $incidencia->area_responsable_actual ? ucfirst($incidencia->area_responsable_actual) : 'No asignada' }}" 
                                       readonly>
                                <small class="form-text text-muted">
                                    Para cambiar el área, use el botón <strong>"Transferir"</strong> en la vista de detalle.
                                </small>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones Técnicas</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales, notas técnicas, etc..." 
                                          maxlength="1000">{{ old('observaciones', $incidencia->observaciones_tecnicas) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_observaciones">0</span>/1000 caracteres
                                </small>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Fechas (solo lectura) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Información de fechas:</strong><br>
                                    <small>
                                        <strong>Fecha de reporte:</strong> {{ $incidencia->fecha_reporte->format('d/m/Y H:i:s') }}<br>
                                        @if($incidencia->fecha_resolucion)
                                            <strong>Fecha de resolución:</strong> {{ $incidencia->fecha_resolucion->format('d/m/Y H:i:s') }}<br>
                                        @endif
                                        @if($incidencia->fecha_ultima_transferencia)
                                            <strong>Última transferencia:</strong> {{ $incidencia->fecha_ultima_transferencia->format('d/m/Y H:i:s') }}<br>
                                        @endif
                                        <strong>Última actualización:</strong> {{ $incidencia->updated_at->format('d/m/Y H:i:s') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Actualizar Incidencia
                                    </button>
                                    <a href="{{ route('incidencias.show', $incidencia) }}" class="btn btn-info">
                                        <i class="fas fa-eye me-2"></i>Ver Detalles
                                    </a>
                                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función genérica para contadores de caracteres
    function setupCharacterCounter(textareaId, counterId, maxLength) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);
        
        if (textarea && counter) {
            textarea.addEventListener('input', function() {
                const longitud = this.value.length;
                counter.textContent = longitud;
                
                if (longitud > maxLength * 0.95) {
                    counter.className = 'text-danger';
                } else if (longitud > maxLength * 0.8) {
                    counter.className = 'text-warning';
                } else {
                    counter.className = 'text-muted';
                }
            });
            
            // Trigger inicial
            textarea.dispatchEvent(new Event('input'));
        }
    }
    
    // Configurar contadores
    setupCharacterCounter('descripcion_detallada', 'contador_descripcion', 2000);
    setupCharacterCounter('observaciones', 'contador_observaciones', 1000);
    
    // Validación del estado
    const estadoSelect = document.getElementById('estado');
    const estadoActual = '{{ $incidencia->estado->value }}';
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            const nuevoEstado = this.value;
            
            // Si se cambia a cerrada, preguntar por confirmación
            if (nuevoEstado === 'cerrada' && estadoActual !== 'cerrada') {
                if (!confirm('¿Estás seguro de que quieres cerrar esta incidencia? Esta acción marcará la fecha de resolución.')) {
                    this.value = estadoActual;
                }
            }

            // Si se cambia a resuelta
            if (nuevoEstado === 'resuelta' && estadoActual !== 'resuelta') {
                if (!confirm('¿Estás seguro de que quieres marcar esta incidencia como resuelta?')) {
                    this.value = estadoActual;
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.border-bottom {
    border-bottom: 2px solid #dee2e6 !important;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background: linear-gradient(90deg, #f8f9fc 0%, #e9ecef 100%);
    border-bottom: 2px solid #e3e6f0;
}

.btn {
    font-weight: 600;
    padding: 0.5rem 1rem;
}

.text-warning {
    color: #f39c12 !important;
}

.text-danger {
    color: #e74a3b !important;
}

/* Estilos para campos readonly */
input[readonly] {
    background-color: #f8f9fc;
    border-color: #e3e6f0;
}

/* Highlight para campos importantes */
#estado:focus, #prioridad:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}
</style>
@endpush
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
                    <p class="text-muted">{{ $incidencia->codigo_incidencia }} - {{ $incidencia->descripcion_corta }}</p>
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
                                <label for="reportado_por" class="form-label">Reportado Por</label>
                                <input type="text" class="form-control" id="reportado_por" 
                                       value="{{ $incidencia->reportado_por }}" readonly>
                                <small class="form-text text-muted">No se puede modificar el reportante</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="descripcion_corta" class="form-label">Descripción Corta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('descripcion_corta') is-invalid @enderror" 
                                       id="descripcion_corta" name="descripcion_corta" 
                                       value="{{ old('descripcion_corta', $incidencia->descripcion_corta) }}" 
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
                                          maxlength="2000" required>{{ old('descripcion_detallada', $incidencia->descripcion_detallada) }}</textarea>
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
                                        id="estado" name="estado" required>
                                    <option value="">Seleccionar estado</option>
                                    @foreach($estados as $key => $value)
                                        <option value="{{ $key }}" {{ old('estado', $incidencia->estado->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control @error('categoria') is-invalid @enderror" 
                                        id="categoria" name="categoria" required>
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
                                <label for="impacto_servicio" class="form-label">Impacto en el Servicio <span class="text-danger">*</span></label>
                                <select class="form-control @error('impacto_servicio') is-invalid @enderror" 
                                        id="impacto_servicio" name="impacto_servicio" required>
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
                                <label for="asignado_a" class="form-label">Asignado A</label>
                                <input type="text" class="form-control @error('asignado_a') is-invalid @enderror" 
                                       id="asignado_a" name="asignado_a" 
                                       value="{{ old('asignado_a', $incidencia->asignado_a) }}" 
                                       placeholder="Nombre del responsable de la resolución" maxlength="255">
                                @error('asignado_a')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="fecha_resolucion_estimada" class="form-label">Fecha de Resolución Estimada</label>
                                <input type="datetime-local" class="form-control @error('fecha_resolucion_estimada') is-invalid @enderror" 
                                       id="fecha_resolucion_estimada" name="fecha_resolucion_estimada" 
                                       value="{{ old('fecha_resolucion_estimada', $incidencia->fecha_resolucion_estimada?->format('Y-m-d\TH:i')) }}">
                                @error('fecha_resolucion_estimada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="acciones_tomadas" class="form-label">Acciones Tomadas</label>
                                <textarea class="form-control @error('acciones_tomadas') is-invalid @enderror" 
                                          id="acciones_tomadas" name="acciones_tomadas" rows="4" 
                                          placeholder="Describe las acciones realizadas para resolver la incidencia..." 
                                          maxlength="2000">{{ old('acciones_tomadas', $incidencia->acciones_tomadas) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_acciones">0</span>/2000 caracteres
                                </small>
                                @error('acciones_tomadas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales, notas, etc..." 
                                          maxlength="1000">{{ old('observaciones', $incidencia->observaciones) }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_observaciones">0</span>/1000 caracteres
                                </small>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Fechas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Información de fechas:</strong><br>
                                    <small>
                                        <strong>Fecha de reporte:</strong> {{ $incidencia->fecha_reporte->format('d/m/Y H:i:s') }}<br>
                                        @if($incidencia->fecha_inicio_atencion)
                                            <strong>Inicio de atención:</strong> {{ $incidencia->fecha_inicio_atencion->format('d/m/Y H:i:s') }}<br>
                                        @endif
                                        @if($incidencia->fecha_resolucion)
                                            <strong>Fecha de resolución:</strong> {{ $incidencia->fecha_resolucion->format('d/m/Y H:i:s') }}<br>
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
                
                if (longitud > maxLength * 0.9) {
                    counter.className = 'text-warning';
                } else if (longitud > maxLength * 0.95) {
                    counter.className = 'text-danger';
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
    setupCharacterCounter('acciones_tomadas', 'contador_acciones', 2000);
    setupCharacterCounter('observaciones', 'contador_observaciones', 1000);
    
    // Validación del estado
    const estadoSelect = document.getElementById('estado');
    const estadoActual = '{{ $incidencia->estado->value }}';
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            const nuevoEstado = this.value;
            
            // Validar transiciones de estado
            if (estadoActual === 'cerrada' && nuevoEstado !== 'cerrada') {
                if (confirm('¿Estás seguro de que quieres reabrir esta incidencia cerrada?')) {
                    // Continuar
                } else {
                    this.value = 'cerrada';
                }
            }
            
            // Si se cambia a cerrada, preguntar por acciones
            if (nuevoEstado === 'cerrada' && estadoActual !== 'cerrada') {
                const accionesTomadas = document.getElementById('acciones_tomadas');
                if (!accionesTomadas.value.trim()) {
                    alert('Se recomienda agregar las acciones tomadas antes de cerrar la incidencia.');
                    accionesTomadas.focus();
                }
            }
        });
    }
    
    // Validación del formulario
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const estado = document.getElementById('estado').value;
            const accionesTomadas = document.getElementById('acciones_tomadas').value;
            
            if (estado === 'cerrada' && !accionesTomadas.trim()) {
                if (!confirm('Estás cerrando la incidencia sin describir las acciones tomadas. ¿Deseas continuar?')) {
                    e.preventDefault();
                    document.getElementById('acciones_tomadas').focus();
                    return;
                }
            }
            
            if (estado === 'en_proceso') {
                const asignadoA = document.getElementById('asignado_a').value;
                if (!asignadoA.trim()) {
                    if (confirm('Se recomienda asignar la incidencia a alguien cuando está en proceso. ¿Deseas continuar sin asignar?')) {
                        // Continuar
                    } else {
                        e.preventDefault();
                        document.getElementById('asignado_a').focus();
                        return;
                    }
                }
            }
        });
    }
    
    // Auto-sugerir fecha de resolución
    const estadoSelectForDate = document.getElementById('estado');
    const fechaResolucionInput = document.getElementById('fecha_resolucion_estimada');
    
    if (estadoSelectForDate && fechaResolucionInput) {
        estadoSelectForDate.addEventListener('change', function() {
            if (this.value === 'en_proceso' && !fechaResolucionInput.value) {
                // Sugerir fecha 48 horas después
                const fechaSugerida = new Date();
                fechaSugerida.setHours(fechaSugerida.getHours() + 48);
                const fechaFormateada = fechaSugerida.toISOString().slice(0, 16);
                
                if (confirm('¿Quieres establecer una fecha de resolución estimada para dentro de 48 horas?')) {
                    fechaResolucionInput.value = fechaFormateada;
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
@extends('layouts.app')

@section('title', 'Editar Trámite - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.index') }}">Trámites MTC</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.show', $tramite) }}">{{ $tramite->numero_expediente }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-warning me-2"></i>
                Editar Trámite: {{ $tramite->numero_expediente }}
            </h1>
            <p class="text-muted">Actualice la información del trámite</p>
        </div>
    </div>

    <!-- Alerta de Estado -->
    @if(!$tramite->puedeSerEditado())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Advertencia:</strong> Este trámite está en estado "{{ $tramite->estado->getLabel() }}" y normalmente no debería editarse.
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('tramites.update', $tramite) }}" method="POST" id="formEditarTramite">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">
                <!-- Información Básica -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Información Básica del Trámite
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="numero_expediente" class="form-label">
                                    Número de Expediente <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('numero_expediente') is-invalid @enderror" 
                                       id="numero_expediente" 
                                       name="numero_expediente" 
                                       value="{{ old('numero_expediente', $tramite->numero_expediente) }}"
                                       placeholder="Ej: T-123456-2025"
                                       required>
                                @error('numero_expediente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tipo_tramite" class="form-label">
                                    Tipo de Trámite <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('tipo_tramite') is-invalid @enderror" 
                                        id="tipo_tramite" 
                                        name="tipo_tramite" 
                                        required>
                                    <option value="">Seleccione un tipo de trámite</option>
                                    @foreach($tipos_tramite as $tipo)
                                        <option value="{{ $tipo['value'] }}" 
                                                data-descripcion="{{ $tipo['description'] }}"
                                                data-costo="{{ $tipo['costo'] }}"
                                                data-tiempo="{{ $tipo['tiempo_promedio'] }}"
                                                {{ old('tipo_tramite', $tramite->tipo_tramite->value) == $tipo['value'] ? 'selected' : '' }}>
                                            {{ $tipo['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo_tramite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="estacion_id" class="form-label">
                                    Estación <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('estacion_id') is-invalid @enderror" 
                                        id="estacion_id" 
                                        name="estacion_id" 
                                        required>
                                    <option value="">Seleccione una estación</option>
                                    @foreach($estaciones as $estacion)
                                        <option value="{{ $estacion->id }}" 
                                                {{ old('estacion_id', $tramite->estacion_id) == $estacion->id ? 'selected' : '' }}>
                                            {{ $estacion->localidad }} - {{ $estacion->razon_social }} ({{ $estacion->departamento }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('estacion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información del Tipo de Trámite -->
                        <div id="infoTipoTramite" style="{{ $tramite->tipo_tramite ? '' : 'display: none;' }}">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Información del Trámite</h6>
                                <p id="descripcionTramite" class="mb-2">{{ $tramite->tipo_tramite->getDescription() }}</p>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Tiempo Promedio:</strong> <span id="tiempoPromedio">{{ $tramite->tipo_tramite->getTiempoPromedioDias() }}</span> días
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Costo de Trámite:</strong> S/. <span id="costoTramite">{{ number_format($tramite->tipo_tramite->getCostoTramite(), 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_presentacion" class="form-label">
                                    Fecha de Presentación <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_presentacion') is-invalid @enderror" 
                                       id="fecha_presentacion" 
                                       name="fecha_presentacion" 
                                       value="{{ old('fecha_presentacion', $tramite->fecha_presentacion->format('Y-m-d')) }}"
                                       required>
                                @error('fecha_presentacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="fecha_vencimiento" class="form-label">
                                    Fecha de Vencimiento
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_vencimiento') is-invalid @enderror" 
                                       id="fecha_vencimiento" 
                                       name="fecha_vencimiento" 
                                       value="{{ old('fecha_vencimiento', $tramite->fecha_vencimiento ? $tramite->fecha_vencimiento->format('Y-m-d') : '') }}">
                                @error('fecha_vencimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="responsable_id" class="form-label">
                                    Responsable <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('responsable_id') is-invalid @enderror" 
                                        id="responsable_id" 
                                        name="responsable_id" 
                                        required>
                                    <option value="">Seleccione responsable</option>
                                    @foreach($responsables as $responsable)
                                        <option value="{{ $responsable->id }}" 
                                                {{ old('responsable_id', $tramite->responsable_id) == $responsable->id ? 'selected' : '' }}>
                                            {{ $responsable->name }} ({{ ucfirst($responsable->rol) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsable_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="costo_tramite" class="form-label">
                                    Costo del Trámite (S/.)
                                </label>
                                <input type="number" 
                                       class="form-control @error('costo_tramite') is-invalid @enderror" 
                                       id="costo_tramite" 
                                       name="costo_tramite" 
                                       value="{{ old('costo_tramite', $tramite->costo_tramite) }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00">
                                @error('costo_tramite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="coordenadas_utm" class="form-label">
                                    Coordenadas UTM
                                </label>
                                <input type="text" 
                                       class="form-control @error('coordenadas_utm') is-invalid @enderror" 
                                       id="coordenadas_utm" 
                                       name="coordenadas_utm" 
                                       value="{{ old('coordenadas_utm', $tramite->coordenadas_utm) }}"
                                       placeholder='Ej: L.O. 79° 47\' 33.29" L.S. 05° 02\' 41.45"'>
                                @error('coordenadas_utm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="direccion_completa" class="form-label">
                                    Dirección Completa
                                </label>
                                <textarea class="form-control @error('direccion_completa') is-invalid @enderror" 
                                          id="direccion_completa" 
                                          name="direccion_completa" 
                                          rows="2"
                                          placeholder="Ingrese la dirección completa de la estación">{{ old('direccion_completa', $tramite->direccion_completa) }}</textarea>
                                @error('direccion_completa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="observaciones" class="form-label">
                                    Observaciones
                                </label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" 
                                          name="observaciones" 
                                          rows="3"
                                          placeholder="Ingrese observaciones adicionales sobre el trámite">{{ old('observaciones', $tramite->observaciones) }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral -->
            <div class="col-lg-4">
                <!-- Documentos Presentados -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-alt me-2"></i>Documentos Presentados
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Marque los documentos que ya ha presentado:</p>
                        <div id="checklistDocumentos">
                            @php
                                $docsRequeridos = $tramite->tipo_tramite->getDocumentosRequeridos();
                                $docsPresentados = $tramite->documentos_presentados ?? [];
                            @endphp
                            @foreach($docsRequeridos as $index => $documento)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="documentos_presentados[]" 
                                           value="{{ $documento }}" 
                                           id="doc{{ $index }}"
                                           {{ in_array($documento, $docsPresentados) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="doc{{ $index }}">
                                        {{ $documento }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <div class="progress mb-2" style="height: 20px;">
                            @php
                                $porcentaje = $tramite->porcentaje_completud;
                                $colorBarra = $porcentaje >= 75 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress-bar bg-{{ $colorBarra }}" 
                                 id="progressBar"
                                 role="progressbar" 
                                 style="width: {{ $porcentaje }}%;">
                                <span id="progressText">{{ $porcentaje }}%</span>
                            </div>
                        </div>
                        <p class="small text-muted text-center mb-0">
                            <span id="docsCount">{{ count($docsPresentados) }}</span> de {{ count($docsRequeridos) }} documentos
                        </p>
                    </div>
                </div>

                <!-- Estado Actual -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Estado Actual
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge bg-{{ $tramite->estado->getColor() }}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                <i class="{{ $tramite->estado->getIcon() }} me-2"></i>
                                {{ $tramite->estado->getLabel() }}
                            </span>
                        </div>

                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <strong>Presentado:</strong> {{ $tramite->fecha_presentacion->format('d/m/Y') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <strong>Días transcurridos:</strong> {{ $tramite->dias_transcurridos }}
                            </li>
                            @if($tramite->fecha_vencimiento)
                            <li class="mb-2">
                                <i class="fas fa-exclamation-triangle {{ $tramite->estaVencido() ? 'text-danger' : 'text-success' }} me-2"></i>
                                <strong>Vencimiento:</strong> {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Resumen de Cambios -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-exclamation-circle me-2"></i>Importante
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Los cambios en el tipo de trámite actualizarán automáticamente la lista de documentos requeridos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body text-end">
                        <a href="{{ route('tramites.show', $tramite) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Actualizar Trámite
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoTramiteSelect = document.getElementById('tipo_tramite');
    const costoInput = document.getElementById('costo_tramite');
    const checkboxes = document.querySelectorAll('input[name="documentos_presentados[]"]');

    // Actualizar información cuando cambia el tipo de trámite
    tipoTramiteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const descripcion = selectedOption.dataset.descripcion;
            const costo = selectedOption.dataset.costo;
            const tiempo = selectedOption.dataset.tiempo;

            document.getElementById('descripcionTramite').textContent = descripcion;
            document.getElementById('tiempoPromedio').textContent = tiempo;
            document.getElementById('costoTramite').textContent = parseFloat(costo).toFixed(2);
            document.getElementById('infoTipoTramite').style.display = 'block';

            // Solo actualizar costo si el usuario no lo ha modificado manualmente
            if (!costoInput.dataset.userModified) {
                costoInput.value = parseFloat(costo).toFixed(2);
            }

            // Cargar nuevos documentos requeridos
            cargarDocumentosRequeridos(this.value);
        }
    });

    // Marcar que el usuario modificó el costo manualmente
    costoInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });

    // Actualizar barra de progreso al cambiar checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', actualizarProgreso);
    });

    function actualizarProgreso() {
        const total = checkboxes.length;
        const checked = document.querySelectorAll('input[name="documentos_presentados[]"]:checked').length;
        const porcentaje = total > 0 ? Math.round((checked / total) * 100) : 0;

        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const docsCount = document.getElementById('docsCount');

        progressBar.style.width = porcentaje + '%';
        progressText.textContent = porcentaje + '%';
        docsCount.textContent = checked;

        // Cambiar color según porcentaje
        progressBar.className = 'progress-bar';
        if (porcentaje >= 75) {
            progressBar.classList.add('bg-success');
        } else if (porcentaje >= 50) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }
    }

    function cargarDocumentosRequeridos(tipoTramite) {
        fetch(`/tramites-mtc/tipo-info/get?tipo=${tipoTramite}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const documentos = data.data.documentos_requeridos;
                    let html = '';
                    
                    documentos.forEach((doc, index) => {
                        html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" 
                                       name="documentos_presentados[]" 
                                       value="${doc}" 
                                       id="doc${index}">
                                <label class="form-check-label small" for="doc${index}">
                                    ${doc}
                                </label>
                            </div>
                        `;
                    });
                    
                    document.getElementById('checklistDocumentos').innerHTML = html;

                    // Reactivar listeners
                    const newCheckboxes = document.querySelectorAll('input[name="documentos_presentados[]"]');
                    newCheckboxes.forEach(cb => cb.addEventListener('change', actualizarProgreso));
                    actualizarProgreso();
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Validación antes de enviar
    document.getElementById('formEditarTramite').addEventListener('submit', function(e) {
        const numeroExpediente = document.getElementById('numero_expediente').value;
        const tipoTramite = document.getElementById('tipo_tramite').value;
        const estacion = document.getElementById('estacion_id').value;

        if (!numeroExpediente || !tipoTramite || !estacion) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios');
            return false;
        }
    });
});
</script>
@endpush
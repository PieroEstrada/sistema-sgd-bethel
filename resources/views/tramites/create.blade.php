@extends('layouts.app')

@section('title', 'Nuevo Trámite MTC - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.index') }}">Trámites MTC</a></li>
            <li class="breadcrumb-item active">Nuevo Trámite</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                Registrar Nuevo Trámite MTC
            </h1>
            <p class="text-muted">Complete el formulario para registrar un nuevo trámite ante el MTC</p>
        </div>
    </div>

    <!-- Formulario -->
    <form action="{{ route('tramites.store') }}" method="POST" id="formCrearTramite">
        @csrf
        
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
                                       value="{{ old('numero_expediente') }}"
                                       placeholder="Ej: T-123456-2025"
                                       required>
                                @error('numero_expediente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Formato: T-XXXXXX-YYYY</small>
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
                                                {{ old('tipo_tramite') == $tipo['value'] ? 'selected' : '' }}>
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
                                        <option value="{{ $estacion->id }}" {{ old('estacion_id') == $estacion->id ? 'selected' : '' }}>
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
                        <div id="infoTipoTramite" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Información del Trámite</h6>
                                <p id="descripcionTramite" class="mb-2"></p>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Tiempo Promedio:</strong> <span id="tiempoPromedio"></span> días
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Costo de Trámite:</strong> S/. <span id="costoTramite"></span>
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
                                       value="{{ old('fecha_presentacion', date('Y-m-d')) }}"
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
                                       value="{{ old('fecha_vencimiento') }}">
                                @error('fecha_vencimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Opcional - Se calculará automáticamente</small>
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
                                                {{ old('responsable_id', Auth::id()) == $responsable->id ? 'selected' : '' }}>
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
                                       value="{{ old('costo_tramite') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00">
                                @error('costo_tramite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Se asignará automáticamente según el tipo</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="coordenadas_utm" class="form-label">
                                    Coordenadas UTM
                                </label>
                                <input type="text" 
                                       class="form-control @error('coordenadas_utm') is-invalid @enderror" 
                                       id="coordenadas_utm" 
                                       name="coordenadas_utm" 
                                       value="{{ old('coordenadas_utm') }}"
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
                                          placeholder="Ingrese la dirección completa de la estación">{{ old('direccion_completa') }}</textarea>
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
                                          placeholder="Ingrese observaciones adicionales sobre el trámite">{{ old('observaciones') }}</textarea>
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
                <!-- Documentos Requeridos -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-alt me-2"></i>Documentos Requeridos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="listaDocumentosRequeridos">
                            <p class="text-muted text-center">
                                <i class="fas fa-arrow-up fa-2x mb-2"></i><br>
                                Seleccione un tipo de trámite para ver los documentos requeridos
                            </p>
                        </div>

                        <div id="checklistDocumentos" style="display: none;">
                            <p class="small text-muted mb-2">Marque los documentos que ya tiene preparados:</p>
                            <div id="documentosCheckboxes"></div>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-clipboard-check me-2"></i>Resumen
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                <strong>Estado Inicial:</strong> Presentado
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                <strong>Documentos:</strong> <span id="resumenDocumentos">Por definir</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-primary" style="font-size: 0.5rem;"></i>
                                <strong>Tiempo Estimado:</strong> <span id="resumenTiempo">Por definir</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body text-end">
                        <a href="{{ route('tramites.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar Trámite
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
    const fechaPresentacion = document.getElementById('fecha_presentacion');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const costoInput = document.getElementById('costo_tramite');

    // Cuando cambia el tipo de trámite
    tipoTramiteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            // Mostrar información del trámite
            const descripcion = selectedOption.dataset.descripcion;
            const costo = selectedOption.dataset.costo;
            const tiempo = selectedOption.dataset.tiempo;

            document.getElementById('descripcionTramite').textContent = descripcion;
            document.getElementById('tiempoPromedio').textContent = tiempo;
            document.getElementById('costoTramite').textContent = parseFloat(costo).toFixed(2);
            document.getElementById('infoTipoTramite').style.display = 'block';

            // Asignar costo automáticamente si está vacío
            if (!costoInput.value) {
                costoInput.value = parseFloat(costo).toFixed(2);
            }

            // Calcular fecha de vencimiento sugerida
            if (fechaPresentacion.value && !fechaVencimiento.value) {
                const fechaInicio = new Date(fechaPresentacion.value);
                fechaInicio.setDate(fechaInicio.getDate() + parseInt(tiempo));
                fechaVencimiento.value = fechaInicio.toISOString().split('T')[0];
            }

            // Cargar documentos requeridos
            cargarDocumentosRequeridos(this.value);

            // Actualizar resumen
            document.getElementById('resumenTiempo').textContent = tiempo + ' días';
        } else {
            document.getElementById('infoTipoTramite').style.display = 'none';
            document.getElementById('listaDocumentosRequeridos').innerHTML = `
                <p class="text-muted text-center">
                    <i class="fas fa-arrow-up fa-2x mb-2"></i><br>
                    Seleccione un tipo de trámite para ver los documentos requeridos
                </p>
            `;
            document.getElementById('checklistDocumentos').style.display = 'none';
        }
    });

    // Recalcular fecha de vencimiento al cambiar fecha de presentación
    fechaPresentacion.addEventListener('change', function() {
        const selectedOption = tipoTramiteSelect.options[tipoTramiteSelect.selectedIndex];
        if (tipoTramiteSelect.value && this.value && !fechaVencimiento.value) {
            const tiempo = selectedOption.dataset.tiempo;
            const fechaInicio = new Date(this.value);
            fechaInicio.setDate(fechaInicio.getDate() + parseInt(tiempo));
            fechaVencimiento.value = fechaInicio.toISOString().split('T')[0];
        }
    });

    // Cargar documentos requeridos vía AJAX
    function cargarDocumentosRequeridos(tipoTramite) {
        fetch(`/tramites-mtc/tipo-info/get?tipo=${tipoTramite}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const documentos = data.data.documentos_requeridos;
                    
                    // Mostrar lista de documentos
                    let htmlLista = '<ol class="small mb-0">';
                    documentos.forEach(doc => {
                        htmlLista += `<li class="mb-1">${doc}</li>`;
                    });
                    htmlLista += '</ol>';
                    document.getElementById('listaDocumentosRequeridos').innerHTML = htmlLista;

                    // Crear checkboxes
                    let htmlCheckboxes = '';
                    documentos.forEach((doc, index) => {
                        htmlCheckboxes += `
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
                    document.getElementById('documentosCheckboxes').innerHTML = htmlCheckboxes;
                    document.getElementById('checklistDocumentos').style.display = 'block';

                    // Actualizar resumen
                    document.getElementById('resumenDocumentos').textContent = documentos.length + ' documentos';

                    // Event listener para actualizar resumen
                    const checkboxes = document.querySelectorAll('input[name="documentos_presentados[]"]');
                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', actualizarResumenDocumentos);
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar documentos:', error);
            });
    }

    function actualizarResumenDocumentos() {
        const totalCheckboxes = document.querySelectorAll('input[name="documentos_presentados[]"]').length;
        const checkedCheckboxes = document.querySelectorAll('input[name="documentos_presentados[]"]:checked').length;
        document.getElementById('resumenDocumentos').textContent = 
            `${checkedCheckboxes} de ${totalCheckboxes} documentos`;
    }

    // Validación antes de enviar
    document.getElementById('formCrearTramite').addEventListener('submit', function(e) {
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
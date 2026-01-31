@extends('layouts.app')

@section('title', 'Nuevo Tramite MTC - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.index') }}">Tramites MTC</a></li>
            <li class="breadcrumb-item active">Nuevo Tramite</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                Registrar Nuevo Tramite MTC
            </h1>
            <p class="text-muted">Complete el formulario para registrar un nuevo tramite ante el MTC</p>
        </div>
    </div>

    <!-- Formulario -->
    <form action="{{ route('tramites.store') }}" method="POST" id="formCrearTramite">
        @csrf

        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">
                <!-- Seleccion de Origen -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-laptop me-2"></i>Origen del Tramite
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="btn-group w-100" role="group" aria-label="Origen del tramite">
                                    <input type="radio" class="btn-check" name="origen" id="origenTupa"
                                           value="tupa_digital" {{ $origenPreseleccionado == 'tupa_digital' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="origenTupa">
                                        <i class="fas fa-laptop me-2"></i>
                                        <strong>TUPA Digital MTC</strong>
                                        <br><small>Tramites con codigo oficial</small>
                                    </label>

                                    <input type="radio" class="btn-check" name="origen" id="origenMesa"
                                           value="mesa_partes" {{ $origenPreseleccionado == 'mesa_partes' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-info" for="origenMesa">
                                        <i class="fas fa-inbox me-2"></i>
                                        <strong>Mesa de Partes Virtual</strong>
                                        <br><small>Escritos y comunicaciones</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informacion Basica -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Informacion Basica del Tramite
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="numero_expediente" class="form-label">
                                    Numero de Expediente <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('numero_expediente') is-invalid @enderror"
                                       id="numero_expediente"
                                       name="numero_expediente"
                                       value="{{ old('numero_expediente') }}"
                                       placeholder="Ej: T-123456-2026"
                                       required>
                                @error('numero_expediente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_oficio_mtc" class="form-label">
                                    Numero de Oficio MTC
                                </label>
                                <input type="text"
                                       class="form-control @error('numero_oficio_mtc') is-invalid @enderror"
                                       id="numero_oficio_mtc"
                                       name="numero_oficio_mtc"
                                       value="{{ old('numero_oficio_mtc') }}"
                                       placeholder="Ej: OFICIO-001-2026-MTC/28">
                                @error('numero_oficio_mtc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="tipo_tramite_id" class="form-label">
                                    Tipo de Tramite <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('tipo_tramite_id') is-invalid @enderror"
                                        id="tipo_tramite_id"
                                        name="tipo_tramite_id"
                                        required>
                                    <option value="">Seleccione un tipo de tramite</option>
                                    <optgroup label="TUPA Digital" id="optgroupTupa">
                                        @foreach($tiposTramite['tupa_digital'] ?? [] as $tipo)
                                            <option value="{{ $tipo['value'] }}"
                                                    data-plazo="{{ $tipo['plazo_dias'] }}"
                                                    data-costo="{{ $tipo['costo'] }}"
                                                    data-evaluacion="{{ $tipo['tipo_evaluacion'] }}"
                                                    data-requiere-estacion="{{ $tipo['requiere_estacion'] ? '1' : '0' }}"
                                                    data-permite-padre="{{ $tipo['permite_tramite_padre'] ? '1' : '0' }}"
                                                    {{ old('tipo_tramite_id') == $tipo['value'] ? 'selected' : '' }}>
                                                {{ $tipo['label'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Mesa de Partes" id="optgroupMesa">
                                        @foreach($tiposTramite['mesa_partes'] ?? [] as $tipo)
                                            <option value="{{ $tipo['value'] }}"
                                                    data-plazo="{{ $tipo['plazo_dias'] }}"
                                                    data-costo="{{ $tipo['costo'] }}"
                                                    data-evaluacion="{{ $tipo['tipo_evaluacion'] }}"
                                                    data-requiere-estacion="{{ $tipo['requiere_estacion'] ? '1' : '0' }}"
                                                    data-permite-padre="{{ $tipo['permite_tramite_padre'] ? '1' : '0' }}"
                                                    {{ old('tipo_tramite_id') == $tipo['value'] ? 'selected' : '' }}>
                                                {{ $tipo['label'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('tipo_tramite_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Panel de Informacion del Tipo -->
                        <div id="panelInfoTipo" class="alert alert-info" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="infoTipoNombre"></span>
                                    </h6>
                                    <p class="small mb-0" id="infoTipoDescripcion"></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Plazo:</small><br>
                                            <strong id="infoTipoPlazo">-</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Costo:</small><br>
                                            <strong id="infoTipoCosto">-</strong>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Evaluacion:</small><br>
                                        <span id="infoTipoEvaluacion" class="badge"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="divEstacion">
                            <div class="col-md-12 mb-3">
                                <label for="estacion_id" class="form-label">
                                    Estacion <span class="text-danger" id="estacionRequired">*</span>
                                </label>
                                <select class="form-control @error('estacion_id') is-invalid @enderror"
                                        id="estacion_id"
                                        name="estacion_id">
                                    <option value="">Seleccione una estacion</option>
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

                        <!-- Vinculacion a Tramite Padre -->
                        <div class="row" id="divTramitePadre" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label for="tramite_padre_id" class="form-label">
                                    <i class="fas fa-link me-1"></i>Vincular a Tramite Existente
                                </label>
                                <select class="form-control @error('tramite_padre_id') is-invalid @enderror"
                                        id="tramite_padre_id"
                                        name="tramite_padre_id">
                                    <option value="">Sin vinculacion (tramite independiente)</option>
                                    @foreach($tramitesParaVincular as $tramite)
                                        <option value="{{ $tramite->id }}" {{ old('tramite_padre_id') == $tramite->id ? 'selected' : '' }}>
                                            {{ $tramite->numero_expediente }}
                                            @if($tramite->tipoTramite)
                                                - {{ $tramite->tipoTramite->nombre_completo }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('tramite_padre_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Util para respuestas a oficios o recursos</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_presentacion" class="form-label">
                                    Fecha de Presentacion
                                </label>
                                <input type="date"
                                       class="form-control @error('fecha_presentacion') is-invalid @enderror"
                                       id="fecha_presentacion"
                                       name="fecha_presentacion"
                                       value="{{ old('fecha_presentacion') }}">
                                @error('fecha_presentacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Dejar vacio si aun no se presenta</small>
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
                                <small class="form-text text-muted">Se calcula automaticamente</small>
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
                                            {{ $responsable->name }}
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
                                    Costo del Tramite (S/.)
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
                                       placeholder='Ej: L.O. 79 47 33.29 L.S. 05 02 41.45'>
                                @error('coordenadas_utm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="direccion_completa" class="form-label">
                                    Direccion Completa
                                </label>
                                <textarea class="form-control @error('direccion_completa') is-invalid @enderror"
                                          id="direccion_completa"
                                          name="direccion_completa"
                                          rows="2"
                                          placeholder="Direccion de la estacion o ubicacion del tramite">{{ old('direccion_completa') }}</textarea>
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
                                          placeholder="Observaciones adicionales sobre el tramite">{{ old('observaciones') }}</textarea>
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
                <!-- Requisitos del Tipo de Tramite -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-list me-2"></i>Requisitos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="panelRequisitos">
                            <p class="text-muted text-center">
                                <i class="fas fa-arrow-left fa-2x mb-2 d-block"></i>
                                Seleccione un tipo de tramite para ver los requisitos
                            </p>
                        </div>
                        <div id="listaRequisitos" style="display: none;">
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     id="barraProgreso" style="width: 0%;">0%</div>
                            </div>
                            <div id="checklistRequisitos"></div>
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
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <strong>Estado Inicial:</strong>
                                @if($estadoInicial)
                                    <span class="badge bg-{{ $estadoInicial->color }}">
                                        <i class="{{ $estadoInicial->icono }} me-1"></i>
                                        {{ $estadoInicial->nombre }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Recopilacion</span>
                                @endif
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <strong>Origen:</strong> <span id="resumenOrigen">TUPA Digital</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <strong>Tipo:</strong> <span id="resumenTipo">Por seleccionar</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <strong>Plazo:</strong> <span id="resumenPlazo">-</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-circle text-secondary" style="font-size: 0.5rem;"></i>
                                <strong>Costo:</strong> <span id="resumenCosto">-</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Accion -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body text-end">
                        <a href="{{ route('tramites.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar Tramite
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .btn-check:checked + .btn-outline-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        color: white;
    }
    .btn-check:checked + .btn-outline-info {
        background-color: #36b9cc;
        border-color: #36b9cc;
        color: white;
    }
    .form-check-input:checked {
        background-color: #1cc88a;
        border-color: #1cc88a;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const origenRadios = document.querySelectorAll('input[name="origen"]');
    const tipoSelect = document.getElementById('tipo_tramite_id');
    const optgroupTupa = document.getElementById('optgroupTupa');
    const optgroupMesa = document.getElementById('optgroupMesa');
    const fechaPresentacion = document.getElementById('fecha_presentacion');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const costoInput = document.getElementById('costo_tramite');
    const divEstacion = document.getElementById('divEstacion');
    const estacionSelect = document.getElementById('estacion_id');
    const estacionRequired = document.getElementById('estacionRequired');
    const divTramitePadre = document.getElementById('divTramitePadre');

    // Filtrar tipos segun origen
    function filtrarTiposPorOrigen(origen) {
        if (origen === 'tupa_digital') {
            optgroupTupa.style.display = '';
            optgroupMesa.style.display = 'none';
            document.getElementById('resumenOrigen').textContent = 'TUPA Digital';
        } else {
            optgroupTupa.style.display = 'none';
            optgroupMesa.style.display = '';
            document.getElementById('resumenOrigen').textContent = 'Mesa de Partes';
        }
        // Reset selection
        tipoSelect.value = '';
        actualizarInfoTipo();
    }

    // Event listeners para origen
    origenRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            filtrarTiposPorOrigen(this.value);
        });
    });

    // Inicializar segun origen preseleccionado
    const origenInicial = document.querySelector('input[name="origen"]:checked');
    if (origenInicial) {
        filtrarTiposPorOrigen(origenInicial.value);
    }

    // Cuando cambia el tipo de tramite
    tipoSelect.addEventListener('change', function() {
        actualizarInfoTipo();
    });

    function actualizarInfoTipo() {
        const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
        const panelInfo = document.getElementById('panelInfoTipo');
        const panelRequisitos = document.getElementById('panelRequisitos');
        const listaRequisitos = document.getElementById('listaRequisitos');

        if (tipoSelect.value) {
            const plazo = selectedOption.dataset.plazo;
            const costo = parseFloat(selectedOption.dataset.costo || 0);
            const evaluacion = selectedOption.dataset.evaluacion;
            const requiereEstacion = selectedOption.dataset.requiereEstacion === '1';
            const permitePadre = selectedOption.dataset.permitePadre === '1';

            // Mostrar panel de info
            panelInfo.style.display = 'block';

            // Actualizar resumen
            document.getElementById('resumenTipo').textContent = selectedOption.text.substring(0, 30) + '...';
            document.getElementById('resumenPlazo').textContent = plazo ? plazo + ' dias habiles' : 'No especificado';
            document.getElementById('resumenCosto').textContent = costo > 0 ? 'S/ ' + costo.toFixed(2) : 'Gratuito';
            document.getElementById('infoTipoPlazo').textContent = plazo ? plazo + ' dias habiles' : 'No especificado';
            document.getElementById('infoTipoCosto').textContent = costo > 0 ? 'S/ ' + costo.toFixed(2) : 'Gratuito';

            // Badge de evaluacion
            const badgeEval = document.getElementById('infoTipoEvaluacion');
            if (evaluacion === 'positiva') {
                badgeEval.className = 'badge bg-success';
                badgeEval.textContent = 'Silencio Administrativo Positivo';
            } else if (evaluacion === 'negativa') {
                badgeEval.className = 'badge bg-danger';
                badgeEval.textContent = 'Silencio Administrativo Negativo';
            } else {
                badgeEval.className = 'badge bg-secondary';
                badgeEval.textContent = 'Sin evaluacion previa';
            }

            // Autocompletar costo
            if (!costoInput.value && costo > 0) {
                costoInput.value = costo.toFixed(2);
            }

            // Calcular fecha de vencimiento
            if (fechaPresentacion.value && plazo && !fechaVencimiento.value) {
                const fechaInicio = new Date(fechaPresentacion.value);
                // Agregar dias habiles (simplificado)
                let diasAgregados = 0;
                while (diasAgregados < parseInt(plazo)) {
                    fechaInicio.setDate(fechaInicio.getDate() + 1);
                    const diaSemana = fechaInicio.getDay();
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasAgregados++;
                    }
                }
                fechaVencimiento.value = fechaInicio.toISOString().split('T')[0];
            }

            // Mostrar/ocultar estacion segun requiere_estacion
            if (requiereEstacion) {
                divEstacion.style.display = '';
                estacionSelect.required = true;
                estacionRequired.style.display = '';
            } else {
                divEstacion.style.display = '';
                estacionSelect.required = false;
                estacionRequired.style.display = 'none';
            }

            // Mostrar/ocultar vinculacion a tramite padre
            if (permitePadre) {
                divTramitePadre.style.display = '';
            } else {
                divTramitePadre.style.display = 'none';
            }

            // Cargar requisitos via AJAX
            cargarRequisitos(tipoSelect.value);

        } else {
            panelInfo.style.display = 'none';
            panelRequisitos.style.display = '';
            listaRequisitos.style.display = 'none';
            document.getElementById('resumenTipo').textContent = 'Por seleccionar';
            document.getElementById('resumenPlazo').textContent = '-';
            document.getElementById('resumenCosto').textContent = '-';
            divTramitePadre.style.display = 'none';
        }
    }

    function cargarRequisitos(tipoId) {
        const panelRequisitos = document.getElementById('panelRequisitos');
        const listaRequisitos = document.getElementById('listaRequisitos');
        const checklistRequisitos = document.getElementById('checklistRequisitos');

        fetch(`{{ url('/tramites-mtc/tipo-info') }}/${tipoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.requisitos && data.data.requisitos.length > 0) {
                    // Mostrar info del tipo
                    document.getElementById('infoTipoNombre').textContent = data.data.nombre_completo;
                    document.getElementById('infoTipoDescripcion').textContent = data.data.descripcion || '';

                    // Generar checklist de requisitos
                    let html = '';
                    data.data.requisitos.forEach((req, index) => {
                        html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input requisito-check" type="checkbox"
                                       id="req${req.id}" data-requisito-id="${req.id}">
                                <label class="form-check-label small" for="req${req.id}">
                                    ${req.nombre}
                                    ${req.es_obligatorio ? '<span class="text-danger">*</span>' : ''}
                                </label>
                                ${req.descripcion ? `<br><small class="text-muted ms-4">${req.descripcion}</small>` : ''}
                            </div>
                        `;
                    });

                    checklistRequisitos.innerHTML = html;
                    panelRequisitos.style.display = 'none';
                    listaRequisitos.style.display = '';

                    // Agregar listeners para actualizar progreso
                    document.querySelectorAll('.requisito-check').forEach(cb => {
                        cb.addEventListener('change', actualizarProgresoRequisitos);
                    });
                    actualizarProgresoRequisitos();

                } else {
                    checklistRequisitos.innerHTML = '<p class="text-muted small">Este tipo de tramite no tiene requisitos definidos.</p>';
                    panelRequisitos.style.display = 'none';
                    listaRequisitos.style.display = '';
                    document.getElementById('barraProgreso').style.width = '100%';
                    document.getElementById('barraProgreso').textContent = '100%';
                }
            })
            .catch(error => {
                console.error('Error al cargar requisitos:', error);
                panelRequisitos.innerHTML = '<p class="text-danger small">Error al cargar requisitos</p>';
            });
    }

    function actualizarProgresoRequisitos() {
        const total = document.querySelectorAll('.requisito-check').length;
        const marcados = document.querySelectorAll('.requisito-check:checked').length;
        const porcentaje = total > 0 ? Math.round((marcados / total) * 100) : 0;

        const barra = document.getElementById('barraProgreso');
        barra.style.width = porcentaje + '%';
        barra.textContent = porcentaje + '%';

        if (porcentaje === 100) {
            barra.classList.remove('bg-warning');
            barra.classList.add('bg-success');
        } else if (porcentaje >= 50) {
            barra.classList.remove('bg-success');
            barra.classList.add('bg-warning');
        } else {
            barra.classList.remove('bg-success', 'bg-warning');
        }
    }

    // Recalcular fecha vencimiento al cambiar fecha presentacion
    fechaPresentacion.addEventListener('change', function() {
        if (tipoSelect.value && this.value) {
            const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
            const plazo = selectedOption.dataset.plazo;
            if (plazo) {
                const fechaInicio = new Date(this.value);
                let diasAgregados = 0;
                while (diasAgregados < parseInt(plazo)) {
                    fechaInicio.setDate(fechaInicio.getDate() + 1);
                    const diaSemana = fechaInicio.getDay();
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasAgregados++;
                    }
                }
                fechaVencimiento.value = fechaInicio.toISOString().split('T')[0];
            }
        }
    });

    // Validacion antes de enviar
    document.getElementById('formCrearTramite').addEventListener('submit', function(e) {
        const numeroExpediente = document.getElementById('numero_expediente').value;
        const tipoTramite = tipoSelect.value;

        if (!numeroExpediente || !tipoTramite) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios');
            return false;
        }
    });
});
</script>
@endpush

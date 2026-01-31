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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit text-warning me-2"></i>
                        Editar Trámite: {{ $tramite->numero_expediente }}
                    </h1>
                    <p class="text-muted mb-0">
                        @if($tramite->tipoTramite)
                            <span class="badge bg-{{ $tramite->tipoTramite->origen === 'tupa_digital' ? 'primary' : 'info' }}">
                                {{ $tramite->tipoTramite->origen === 'tupa_digital' ? 'TUPA Digital' : 'Mesa de Partes' }}
                            </span>
                            @if($tramite->tipoTramite->codigo)
                                <span class="badge bg-secondary">{{ $tramite->tipoTramite->codigo }}</span>
                            @endif
                            {{ $tramite->tipoTramite->nombre }}
                        @else
                            Actualice la información del trámite
                        @endif
                    </p>
                </div>
                <div>
                    @if($tramite->estadoActual)
                        <span class="badge bg-{{ $tramite->estadoActual->color ?? 'secondary' }}" style="font-size: 0.9rem;">
                            <i class="{{ $tramite->estadoActual->icono ?? 'fas fa-circle' }} me-1"></i>
                            {{ $tramite->estadoActual->nombre }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta de Estado -->
    @if($tramite->estadoActual && !$tramite->estadoActual->esEditable())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Advertencia:</strong> Este trámite está en estado "{{ $tramite->estadoActual->nombre }}" y normalmente no debería editarse.
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
                        <!-- Origen del Trámite (solo lectura) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Origen del Trámite</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="origen" id="origenTupa"
                                               value="tupa_digital"
                                               {{ ($tramite->tipoTramite && $tramite->tipoTramite->origen === 'tupa_digital') ? 'checked' : '' }}
                                               {{ $tramite->tipoTramite ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="origenTupa">
                                            <i class="fas fa-globe text-primary me-1"></i>
                                            TUPA Digital MTC
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="origen" id="origenMesa"
                                               value="mesa_partes"
                                               {{ ($tramite->tipoTramite && $tramite->tipoTramite->origen === 'mesa_partes') ? 'checked' : '' }}
                                               {{ $tramite->tipoTramite ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="origenMesa">
                                            <i class="fas fa-building text-info me-1"></i>
                                            Mesa de Partes Virtual
                                        </label>
                                    </div>
                                </div>
                                @if($tramite->tipoTramite)
                                    <small class="text-muted">El origen no puede cambiarse una vez creado el trámite.</small>
                                @endif
                            </div>
                        </div>

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
                                       placeholder="{{ $tramite->tipoTramite && $tramite->tipoTramite->origen === 'tupa_digital' ? 'Ej: T-123456-2025' : 'Ej: MPV-001234-2025' }}"
                                       required>
                                @error('numero_expediente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_oficio_mtc" class="form-label">
                                    Número de Oficio MTC
                                </label>
                                <input type="text"
                                       class="form-control @error('numero_oficio_mtc') is-invalid @enderror"
                                       id="numero_oficio_mtc"
                                       name="numero_oficio_mtc"
                                       value="{{ old('numero_oficio_mtc', $tramite->numero_oficio_mtc) }}"
                                       placeholder="Ej: OFICIO-0123-2025-MTC/28">
                                @error('numero_oficio_mtc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_tramite_id" class="form-label">
                                    Tipo de Trámite <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('tipo_tramite_id') is-invalid @enderror"
                                        id="tipo_tramite_id"
                                        name="tipo_tramite_id"
                                        required>
                                    <option value="">Seleccione un tipo de trámite</option>
                                    @foreach($tiposTramite as $origenKey => $tipos)
                                        <optgroup label="{{ $origenKey === 'tupa_digital' ? 'TUPA Digital MTC' : 'Mesa de Partes Virtual' }}">
                                        @foreach($tipos as $tipo)
                                        <option value="{{ $tipo['value'] }}"
                                                data-plazo="{{ $tipo['plazo_dias'] ?? '' }}"
                                                data-costo="{{ $tipo['costo'] ?? 0 }}"
                                                data-evaluacion="{{ $tipo['tipo_evaluacion'] ?? '' }}"
                                                data-requiere-estacion="{{ $tipo['requiere_estacion'] ? '1' : '0' }}"
                                                {{ old('tipo_tramite_id', $tramite->tipo_tramite_id) == $tipo['value'] ? 'selected' : '' }}>
                                            {{ $tipo['label'] }}
                                        </option>
                                        @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('tipo_tramite_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3" id="estacionContainer">
                                <label for="estacion_id" class="form-label">
                                    Estación <span class="text-danger" id="estacionRequired">*</span>
                                </label>
                                <select class="form-control @error('estacion_id') is-invalid @enderror"
                                        id="estacion_id"
                                        name="estacion_id">
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

                        <!-- Trámite Padre (Vinculación) -->
                        <div class="row" id="tramitePadreContainer" style="{{ $tramite->tramite_padre_id ? '' : 'display: none;' }}">
                            <div class="col-md-12 mb-3">
                                <label for="tramite_padre_id" class="form-label">
                                    <i class="fas fa-link text-info me-1"></i>
                                    Trámite Relacionado (Respuesta a)
                                </label>
                                <select class="form-control @error('tramite_padre_id') is-invalid @enderror"
                                        id="tramite_padre_id"
                                        name="tramite_padre_id">
                                    <option value="">Sin vinculación</option>
                                    @foreach($tramitesParaVincular as $tp)
                                        <option value="{{ $tp->id }}"
                                                {{ old('tramite_padre_id', $tramite->tramite_padre_id) == $tp->id ? 'selected' : '' }}>
                                            {{ $tp->numero_expediente }} - {{ $tp->tipoTramite->nombre ?? 'Sin tipo' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tramite_padre_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Vincule este trámite como respuesta a otro trámite existente.</small>
                            </div>
                        </div>

                        <!-- Información del Tipo de Trámite -->
                        <div id="infoTipoTramite" class="mb-3" style="{{ $tramite->tipoTramite ? '' : 'display: none;' }}">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading mb-2">
                                    <i class="fas fa-info-circle me-2"></i>Información del Tipo de Trámite
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Plazo de Evaluación</small>
                                        <strong id="infoPlazo">{{ $tramite->tipoTramite->plazo_dias ?? '-' }} días hábiles</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Costo del Trámite</small>
                                        <strong id="infoCosto">S/. {{ $tramite->tipoTramite ? number_format($tramite->tipoTramite->getCostoSoles(), 2) : '0.00' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Tipo de Evaluación</small>
                                        <span id="infoEvaluacion">
                                            @if($tramite->tipoTramite)
                                                @if($tramite->tipoTramite->tipo_evaluacion === 'positiva')
                                                    <span class="badge bg-success">Silencio Positivo</span>
                                                @elseif($tramite->tipoTramite->tipo_evaluacion === 'negativa')
                                                    <span class="badge bg-warning">Silencio Negativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Sin evaluación previa</span>
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_presentacion" class="form-label">
                                    Fecha de Presentación
                                </label>
                                <input type="date"
                                       class="form-control @error('fecha_presentacion') is-invalid @enderror"
                                       id="fecha_presentacion"
                                       name="fecha_presentacion"
                                       value="{{ old('fecha_presentacion', $tramite->fecha_presentacion ? $tramite->fecha_presentacion->format('Y-m-d') : '') }}">
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
                                <small class="text-muted">Se calcula automáticamente según el plazo.</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="fecha_limite_respuesta" class="form-label">
                                    Fecha Límite Respuesta
                                </label>
                                <input type="date"
                                       class="form-control @error('fecha_limite_respuesta') is-invalid @enderror"
                                       id="fecha_limite_respuesta"
                                       name="fecha_limite_respuesta"
                                       value="{{ old('fecha_limite_respuesta', $tramite->fecha_limite_respuesta ? $tramite->fecha_limite_respuesta->format('Y-m-d') : '') }}">
                                @error('fecha_limite_respuesta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
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
                                            {{ $responsable->name }} ({{ $responsable->rol instanceof \App\Enums\RolUsuario ? $responsable->rol->getDisplayName() : ucfirst(str_replace('_', ' ', $responsable->rol)) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsable_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="coordenadas_utm" class="form-label">
                                    Coordenadas UTM
                                </label>
                                <input type="text"
                                       class="form-control @error('coordenadas_utm') is-invalid @enderror"
                                       id="coordenadas_utm"
                                       name="coordenadas_utm"
                                       value="{{ old('coordenadas_utm', $tramite->coordenadas_utm) }}"
                                       placeholder='Ej: L.O. 79° 47&apos; 33.29" L.S. 05° 02&apos; 41.45"'>
                                @error('coordenadas_utm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="resolucion" class="form-label">
                                    Resolución
                                </label>
                                <input type="text"
                                       class="form-control @error('resolucion') is-invalid @enderror"
                                       id="resolucion"
                                       name="resolucion"
                                       value="{{ old('resolucion', $tramite->resolucion) }}"
                                       placeholder="Ej: RD N° 1234-2025-MTC/28">
                                @error('resolucion')
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

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="observaciones_mtc" class="form-label">
                                    Observaciones del MTC
                                </label>
                                <textarea class="form-control @error('observaciones_mtc') is-invalid @enderror"
                                          id="observaciones_mtc"
                                          name="observaciones_mtc"
                                          rows="2"
                                          placeholder="Observaciones recibidas del MTC">{{ old('observaciones_mtc', $tramite->observaciones_mtc) }}</textarea>
                                @error('observaciones_mtc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral -->
            <div class="col-lg-4">
                <!-- Requisitos del Trámite -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-check me-2"></i>Requisitos del Trámite
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Marque los requisitos que ya ha cumplido:</p>
                        <div id="checklistRequisitos">
                            @if($tramite->tipoTramite && $tramite->tipoTramite->requisitos->count() > 0)
                                @php
                                    $requisitosCumplidos = $tramite->requisitos_cumplidos ?? [];
                                @endphp
                                @foreach($tramite->tipoTramite->requisitos()->where('activo', true)->orderBy('orden')->get() as $requisito)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input requisito-check"
                                               type="checkbox"
                                               name="requisitos_cumplidos[]"
                                               value="{{ $requisito->id }}"
                                               id="req{{ $requisito->id }}"
                                               {{ in_array($requisito->id, $requisitosCumplidos) ? 'checked' : '' }}>
                                        <label class="form-check-label small {{ $requisito->es_obligatorio ? 'fw-bold' : '' }}" for="req{{ $requisito->id }}">
                                            {{ $requisito->nombre }}
                                            @if($requisito->es_obligatorio)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        @if($requisito->descripcion)
                                            <small class="d-block text-muted ms-4">{{ $requisito->descripcion }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Seleccione un tipo de trámite para ver los requisitos.
                                </p>
                            @endif
                        </div>

                        <hr>

                        <div class="progress mb-2" style="height: 20px;">
                            @php
                                $totalRequisitos = $tramite->tipoTramite ? $tramite->tipoTramite->requisitos()->where('activo', true)->count() : 0;
                                $cumplidos = count($tramite->requisitos_cumplidos ?? []);
                                $porcentaje = $totalRequisitos > 0 ? round(($cumplidos / $totalRequisitos) * 100) : 0;
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
                            <span id="reqsCount">{{ $cumplidos }}</span> de <span id="reqsTotal">{{ $totalRequisitos }}</span> requisitos
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
                            @if($tramite->estadoActual)
                                <span class="badge bg-{{ $tramite->estadoActual->color ?? 'secondary' }}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                    <i class="{{ $tramite->estadoActual->icono ?? 'fas fa-circle' }} me-2"></i>
                                    {{ $tramite->estadoActual->nombre }}
                                </span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                    Sin estado
                                </span>
                            @endif
                        </div>

                        <ul class="list-unstyled mb-0 small">
                            @if($tramite->fecha_presentacion)
                            <li class="mb-2">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <strong>Presentado:</strong> {{ $tramite->fecha_presentacion->format('d/m/Y') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <strong>Días transcurridos:</strong> {{ $tramite->fecha_presentacion->diffInDays(now()) }}
                            </li>
                            @endif
                            @if($tramite->fecha_vencimiento)
                            <li class="mb-2">
                                @php
                                    $vencido = $tramite->fecha_vencimiento->isPast();
                                @endphp
                                <i class="fas fa-exclamation-triangle {{ $vencido ? 'text-danger' : 'text-success' }} me-2"></i>
                                <strong>Vencimiento:</strong> {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                                @if($vencido)
                                    <span class="badge bg-danger">Vencido</span>
                                @endif
                            </li>
                            @endif
                            @if($tramite->responsable)
                            <li class="mb-2">
                                <i class="fas fa-user text-info me-2"></i>
                                <strong>Responsable:</strong> {{ $tramite->responsable->name }}
                            </li>
                            @endif
                        </ul>

                        @if($tramite->aplicaSilencioPositivo())
                            <div class="alert alert-success mt-3 mb-0 py-2">
                                <i class="fas fa-check-circle me-1"></i>
                                <small>Aplica silencio administrativo positivo</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Trámite Padre -->
                @if($tramite->tramitePadre)
                <div class="card shadow mb-4 border-info">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-link me-2"></i>Trámite Relacionado
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $tramite->tramitePadre->numero_expediente }}</strong></p>
                        <p class="small text-muted mb-2">{{ $tramite->tramitePadre->tipoTramite->nombre ?? 'Sin tipo' }}</p>
                        <a href="{{ route('tramites.show', $tramite->tramitePadre) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-external-link-alt me-1"></i>Ver trámite
                        </a>
                    </div>
                </div>
                @endif

                <!-- Nota Importante -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-exclamation-circle me-2"></i>Importante
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Los cambios en el tipo de trámite actualizarán automáticamente la lista de requisitos.
                            El cambio de estado debe realizarse desde la vista de detalle del trámite.
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
    const tipoTramiteSelect = document.getElementById('tipo_tramite_id');
    const estacionSelect = document.getElementById('estacion_id');
    const estacionRequired = document.getElementById('estacionRequired');
    const costoInput = document.getElementById('costo_tramite');
    const fechaPresentacion = document.getElementById('fecha_presentacion');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const tramitePadreContainer = document.getElementById('tramitePadreContainer');

    // Actualizar información cuando cambia el tipo de trámite
    tipoTramiteSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            const plazo = selectedOption.dataset.plazo;
            const costo = selectedOption.dataset.costo;
            const evaluacion = selectedOption.dataset.evaluacion;
            const requiereEstacion = selectedOption.dataset.requiereEstacion === '1';

            // Actualizar panel informativo
            document.getElementById('infoPlazo').textContent = plazo ? plazo + ' días hábiles' : 'Sin plazo definido';
            document.getElementById('infoCosto').textContent = 'S/. ' + parseFloat(costo || 0).toFixed(2);

            let evaluacionHtml = '';
            if (evaluacion === 'positiva') {
                evaluacionHtml = '<span class="badge bg-success">Silencio Positivo</span>';
            } else if (evaluacion === 'negativa') {
                evaluacionHtml = '<span class="badge bg-warning">Silencio Negativo</span>';
            } else {
                evaluacionHtml = '<span class="badge bg-secondary">Sin evaluación previa</span>';
            }
            document.getElementById('infoEvaluacion').innerHTML = evaluacionHtml;

            document.getElementById('infoTipoTramite').style.display = 'block';

            // Actualizar costo si no fue modificado manualmente
            if (!costoInput.dataset.userModified) {
                costoInput.value = parseFloat(costo || 0).toFixed(2);
            }

            // Actualizar requerimiento de estación
            if (requiereEstacion) {
                estacionSelect.setAttribute('required', 'required');
                estacionRequired.style.display = 'inline';
            } else {
                estacionSelect.removeAttribute('required');
                estacionRequired.style.display = 'none';
            }

            // Calcular fecha de vencimiento
            if (fechaPresentacion.value && plazo) {
                calcularFechaVencimiento(fechaPresentacion.value, parseInt(plazo));
            }

            // Cargar requisitos del tipo
            cargarRequisitos(this.value);
        } else {
            document.getElementById('infoTipoTramite').style.display = 'none';
        }
    });

    // Marcar que el usuario modificó el costo manualmente
    costoInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });

    // Calcular vencimiento cuando cambia fecha de presentación
    fechaPresentacion.addEventListener('change', function() {
        const selectedOption = tipoTramiteSelect.options[tipoTramiteSelect.selectedIndex];
        const plazo = selectedOption ? selectedOption.dataset.plazo : null;

        if (this.value && plazo) {
            calcularFechaVencimiento(this.value, parseInt(plazo));
        }
    });

    function calcularFechaVencimiento(fechaInicio, diasHabiles) {
        const fecha = new Date(fechaInicio);
        let diasAgregados = 0;

        while (diasAgregados < diasHabiles) {
            fecha.setDate(fecha.getDate() + 1);
            const diaSemana = fecha.getDay();
            if (diaSemana !== 0 && diaSemana !== 6) {
                diasAgregados++;
            }
        }

        fechaVencimiento.value = fecha.toISOString().split('T')[0];
    }

    function cargarRequisitos(tipoTramiteId) {
        fetch(`{{ url('/tramites-mtc/tipo-info') }}/${tipoTramiteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.requisitos) {
                    const requisitos = data.data.requisitos;
                    let html = '';

                    if (requisitos.length === 0) {
                        html = '<p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>Este tipo de trámite no tiene requisitos configurados.</p>';
                    } else {
                        requisitos.forEach(req => {
                            html += `
                                <div class="form-check mb-2">
                                    <input class="form-check-input requisito-check" type="checkbox"
                                           name="requisitos_cumplidos[]"
                                           value="${req.id}"
                                           id="req${req.id}">
                                    <label class="form-check-label small ${req.es_obligatorio ? 'fw-bold' : ''}" for="req${req.id}">
                                        ${req.nombre}
                                        ${req.es_obligatorio ? '<span class="text-danger">*</span>' : ''}
                                    </label>
                                    ${req.descripcion ? `<small class="d-block text-muted ms-4">${req.descripcion}</small>` : ''}
                                </div>
                            `;
                        });
                    }

                    document.getElementById('checklistRequisitos').innerHTML = html;
                    document.getElementById('reqsTotal').textContent = requisitos.length;

                    // Reactivar listeners y actualizar progreso
                    document.querySelectorAll('.requisito-check').forEach(cb => {
                        cb.addEventListener('change', actualizarProgreso);
                    });
                    actualizarProgreso();
                }
            })
            .catch(error => console.error('Error cargando requisitos:', error));
    }

    function actualizarProgreso() {
        const checkboxes = document.querySelectorAll('.requisito-check');
        const total = checkboxes.length;
        const checked = document.querySelectorAll('.requisito-check:checked').length;
        const porcentaje = total > 0 ? Math.round((checked / total) * 100) : 0;

        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const reqsCount = document.getElementById('reqsCount');

        progressBar.style.width = porcentaje + '%';
        progressText.textContent = porcentaje + '%';
        reqsCount.textContent = checked;

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

    // Activar listeners de requisitos existentes
    document.querySelectorAll('.requisito-check').forEach(cb => {
        cb.addEventListener('change', actualizarProgreso);
    });

    // Validación antes de enviar
    document.getElementById('formEditarTramite').addEventListener('submit', function(e) {
        const numeroExpediente = document.getElementById('numero_expediente').value;
        const tipoTramite = document.getElementById('tipo_tramite_id').value;
        const estacion = document.getElementById('estacion_id').value;

        const selectedOption = tipoTramiteSelect.options[tipoTramiteSelect.selectedIndex];
        const requiereEstacion = selectedOption && selectedOption.dataset.requiereEstacion === '1';

        if (!numeroExpediente || !tipoTramite) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios');
            return false;
        }

        if (requiereEstacion && !estacion) {
            e.preventDefault();
            alert('Este tipo de trámite requiere seleccionar una estación');
            return false;
        }
    });
});
</script>
@endpush

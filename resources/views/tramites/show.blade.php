@extends('layouts.app')

@section('title', 'Detalle del Tramite - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.index') }}">Tramites MTC</a></li>
            <li class="breadcrumb-item active">{{ $tramite->numero_expediente }}</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-file-alt text-primary me-2"></i>
                Expediente: {{ $tramite->numero_expediente }}
                @if($tramite->codigo_tupa)
                    <span class="badge bg-primary ms-2">{{ $tramite->codigo_tupa }}</span>
                @endif
            </h1>
            <p class="text-muted mb-0">
                @if($tramite->estacion)
                    <strong>Estacion:</strong> {{ $tramite->estacion->localidad }} - {{ $tramite->estacion->razon_social }}
                @else
                    <span class="text-warning"><i class="fas fa-info-circle me-1"></i>Tramite sin estacion asociada</span>
                @endif
            </p>
        </div>
        <div class="col-md-4 text-end">
            @if($tramite->puedeSerEditado())
                <a href="{{ route('tramites.edit', $tramite) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            @endif
            <a href="{{ route('tramites.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if($alertaSilencioPositivo)
        <div class="alert alert-success">
            <i class="fas fa-thumbs-up me-2"></i>
            <strong>SILENCIO ADMINISTRATIVO POSITIVO</strong> - El plazo de evaluacion ha vencido sin respuesta.
            Segun la normativa, la solicitud se considera APROBADA automaticamente.
        </div>
    @endif

    @if($tramite->estaVencido() && !$alertaSilencioPositivo)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>TRAMITE VENCIDO</strong> - Este tramite vencio el {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
        </div>
    @endif

    @if($tramite->requiereDocumentosAdicionales())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Requisitos Pendientes:</strong> Faltan {{ 100 - $tramite->porcentaje_requisitos_cumplidos }}% de requisitos por completar
        </div>
    @endif

    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Informacion General -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Informacion General
                    </h6>
                    @if($tramite->estadoActual)
                        <span class="badge bg-{{ $tramite->estadoActual->color }}">
                            <i class="{{ $tramite->estadoActual->icono }} me-1"></i>
                            {{ $tramite->estadoActual->nombre }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tipo de Tramite:</strong></p>
                            @if($tipoInfo)
                                <span class="badge bg-{{ $tipoInfo['color'] }}">
                                    <i class="{{ $tipoInfo['icono'] }} me-1"></i>
                                    {{ $tipoInfo['nombre'] }}
                                </span>
                                <br><small class="badge bg-{{ $tipoInfo['origen'] == 'tupa_digital' ? 'primary' : 'info' }} mt-1">
                                    {{ $tipoInfo['origen_label'] }}
                                </small>
                            @else
                                <span class="badge bg-secondary">{{ $tramite->tipo_tramite ?? 'No especificado' }}</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Estado:</strong></p>
                            @if($tramite->estadoActual)
                                <span class="badge bg-{{ $tramite->estadoActual->color }}">
                                    <i class="{{ $tramite->estadoActual->icono }} me-1"></i>
                                    {{ $tramite->estadoActual->nombre }}
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ $tramite->estado ?? 'No especificado' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($tipoInfo && $tipoInfo['tipo_evaluacion'] != 'ninguna')
                    <div class="alert alert-{{ $tipoInfo['tipo_evaluacion_color'] }} py-2 mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>{{ $tipoInfo['tipo_evaluacion_label'] }}</strong>
                            @if($tipoInfo['tipo_evaluacion'] == 'positiva')
                                - Si vence el plazo sin respuesta, la solicitud se considera APROBADA.
                            @else
                                - Si vence el plazo sin respuesta, la solicitud se considera DENEGADA.
                            @endif
                        </small>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Presentacion</p>
                            <p class="mb-0">
                                @if($tramite->fecha_presentacion)
                                    <strong>{{ $tramite->fecha_presentacion->format('d/m/Y') }}</strong>
                                @else
                                    <span class="text-muted">No presentado</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Respuesta</p>
                            <p class="mb-0">
                                @if($tramite->fecha_respuesta)
                                    <strong class="text-success">{{ $tramite->fecha_respuesta->format('d/m/Y') }}</strong>
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Vencimiento</p>
                            <p class="mb-0">
                                @if($tramite->fecha_vencimiento)
                                    <strong class="{{ $tramite->estaVencido() ? 'text-danger' : '' }}">
                                        {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                                    </strong>
                                    @if($tramite->dias_para_vencimiento !== null)
                                        @if($tramite->dias_para_vencimiento < 0)
                                            <span class="badge bg-danger ms-2">Vencido</span>
                                        @elseif($tramite->dias_para_vencimiento <= 7)
                                            <span class="badge bg-warning ms-2">{{ $tramite->dias_para_vencimiento }} dias</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Dias Transcurridos</p>
                            <p class="mb-0"><strong>{{ $tramite->dias_transcurridos }} dias</strong></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Responsable</p>
                            <p class="mb-0">
                                <i class="fas fa-user me-1"></i>
                                {{ $tramite->responsable->name ?? 'No asignado' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Costo del Tramite</p>
                            <p class="mb-0">
                                @if($tramite->costo_tramite)
                                    <strong>S/. {{ number_format($tramite->costo_tramite, 2) }}</strong>
                                @elseif($tipoInfo)
                                    <strong>{{ $tipoInfo['costo_formateado'] }}</strong>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($tramite->numero_oficio_mtc)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Numero de Oficio MTC</p>
                                <p class="mb-0"><code>{{ $tramite->numero_oficio_mtc }}</code></p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->direccion_completa)
                        <hr>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Direccion Completa</p>
                                <p class="mb-0">{{ $tramite->direccion_completa }}</p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->coordenadas_utm)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Coordenadas UTM</p>
                                <p class="mb-0"><code>{{ $tramite->coordenadas_utm }}</code></p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->observaciones)
                        <hr>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Observaciones</p>
                                <p class="mb-0">{{ $tramite->observaciones }}</p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->resolucion)
                        <hr>
                        <div class="alert alert-success mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-file-contract me-2"></i>Resolucion
                            </h6>
                            <p class="mb-0">{{ $tramite->resolucion }}</p>
                        </div>
                    @endif

                    @if($tramite->observaciones_mtc)
                        <hr>
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Observaciones del MTC
                            </h6>
                            <p class="mb-0">{{ $tramite->observaciones_mtc }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tramite Padre (si existe) -->
            @if($tramite->tramitePadre)
            <div class="card shadow mb-4 border-info">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-link me-2"></i>Tramite Vinculado (Padre)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $tramite->tramitePadre->numero_expediente }}</strong>
                            @if($tramite->tramitePadre->tipoTramite)
                                <br><small class="text-muted">{{ $tramite->tramitePadre->tipoTramite->nombre }}</small>
                            @endif
                        </div>
                        <a href="{{ route('tramites.show', $tramite->tramitePadre) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-external-link-alt me-1"></i>Ver
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tramites Hijos (respuestas) -->
            @if($tramite->tramitesHijos && $tramite->tramitesHijos->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sitemap me-2"></i>Tramites Vinculados (Respuestas)
                        <span class="badge bg-secondary ms-2">{{ $tramite->tramitesHijos->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tramite->tramitesHijos as $hijo)
                                <tr>
                                    <td>{{ $hijo->numero_expediente }}</td>
                                    <td>
                                        @if($hijo->tipoTramite)
                                            <small>{{ Str::limit($hijo->tipoTramite->nombre, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hijo->estadoActual)
                                            <span class="badge bg-{{ $hijo->estadoActual->color }}">
                                                {{ $hijo->estadoActual->nombre }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $hijo->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('tramites.show', $hijo) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Documento Principal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>Documento Principal
                    </h6>
                </div>
                <div class="card-body">
                    @if($tramite->documento_principal_nombre)
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                <div>
                                    <strong>{{ $tramite->documento_principal_nombre }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success me-1"></i>Documento presentado
                                        @if($tramite->documento_principal_size)
                                            <span class="ms-2">
                                                ({{ number_format($tramite->documento_principal_size / 1024, 0) }} KB)
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @if($tramite->documento_principal_ruta)
                                <a href="{{ Storage::url($tramite->documento_principal_ruta) }}"
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download me-1"></i>Descargar
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">No se ha subido el documento principal</p>
                            @if(in_array(auth()->user()->rol, ['administrador','gestor_radiodifusion']))
                                <form method="POST"
                                      action="{{ route('tramites.subir-documento', $tramite) }}"
                                      enctype="multipart/form-data"
                                      class="d-flex gap-2 justify-content-center">
                                    @csrf
                                    <input type="file" name="documento_principal" class="form-control" style="max-width: 300px;" required accept=".pdf,.doc,.docx">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i>Subir
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Archivos Adjuntos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-paperclip me-2"></i>Archivos Adjuntos
                        <span class="badge bg-secondary ms-2">{{ $tramite->archivos->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if(in_array(auth()->user()->rol, ['administrador','gestor_radiodifusion']))
                        <form method="POST"
                              action="{{ route('tramites.archivos.subir', $tramite) }}"
                              enctype="multipart/form-data"
                              class="d-flex gap-2 mb-3">
                            @csrf
                            <input type="file" name="archivo" class="form-control" required>
                            <button class="btn btn-primary" type="submit">Subir</button>
                        </form>
                        <small class="text-muted">PDF/JPG/PNG. Max 10MB.</small>
                        <hr>
                    @endif

                    @if($tramite->archivos->count() === 0)
                        <p class="text-muted mb-0">No hay archivos adjuntos.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Tamano</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tramite->archivos as $archivo)
                                    <tr>
                                        <td>{{ $archivo->nombre_original }}</td>
                                        <td><span class="badge bg-info">{{ strtoupper($archivo->extension) }}</span></td>
                                        <td>{{ number_format($archivo->tamano / 1024, 2) }} KB</td>
                                        <td>{{ $archivo->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('tramites.archivos.descargar', $archivo) }}"
                                               class="btn btn-sm btn-outline-primary" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(in_array(auth()->user()->rol, ['administrador','gestor_radiodifusion']))
                                                <form method="POST"
                                                      action="{{ route('tramites.archivos.eliminar', $archivo) }}"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial del Tramite -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Historial del Tramite
                        <span class="badge bg-secondary ms-2">{{ $tramite->historial->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($tramite->historial->count() > 0)
                        <ul class="timeline">
                            @foreach($tramite->historial as $evento)
                            <li class="timeline-item">
                                <span class="timeline-badge bg-{{ $evento->tipo_accion_color }}">
                                    <i class="fas {{ $evento->tipo_accion_icono }}"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $evento->tipo_accion_label }}</h6>
                                    <p class="mb-1">{{ $evento->descripcion_cambio }}</p>
                                    @if($evento->observaciones)
                                        <p class="small text-muted mb-1">{{ $evento->observaciones }}</p>
                                    @endif
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $evento->created_at->format('d/m/Y H:i') }}
                                        @if($evento->usuarioAccion)
                                            - <i class="fas fa-user me-1"></i>{{ $evento->usuarioAccion->name }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p class="mb-0">No hay eventos registrados en el historial.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Lateral -->
        <div class="col-lg-4">
            <!-- Acciones Rapidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Acciones Rapidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="abrirModalCambiarEstado()">
                            <i class="fas fa-exchange-alt me-2"></i>Cambiar Estado
                        </button>

                        @if($tramite->puedeSerEditado())
                            <a href="{{ route('tramites.edit', $tramite) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-2"></i>Editar Tramite
                            </a>
                        @endif

                        <button class="btn btn-outline-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>

                        <a href="{{ route('tramites.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>

            <!-- Requisitos del Tipo -->
            @if(count($requisitos) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-list me-2"></i>Requisitos
                        <span class="badge bg-{{ $tramite->porcentaje_requisitos_cumplidos == 100 ? 'success' : 'warning' }} ms-2">
                            {{ $tramite->porcentaje_requisitos_cumplidos }}%
                        </span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-{{ $tramite->porcentaje_requisitos_cumplidos == 100 ? 'success' : 'warning' }}"
                             role="progressbar" style="width: {{ $tramite->porcentaje_requisitos_cumplidos }}%;">
                        </div>
                    </div>
                    @foreach($requisitos as $req)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox"
                                   id="req{{ $req['id'] }}"
                                   {{ $req['cumplido'] ? 'checked' : '' }}
                                   onchange="toggleRequisito({{ $tramite->id }}, {{ $req['id'] }})">
                            <label class="form-check-label small {{ $req['cumplido'] ? 'text-success' : '' }}" for="req{{ $req['id'] }}">
                                {{ $req['nombre'] }}
                                @if($req['es_obligatorio'])
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Informacion del Tipo -->
            @if($tipoInfo)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Sobre este Tramite
                    </h6>
                </div>
                <div class="card-body">
                    @if($tipoInfo['codigo'])
                        <div class="mb-3">
                            <p class="small text-muted mb-1">Codigo TUPA</p>
                            <p class="mb-0"><strong class="text-primary">{{ $tipoInfo['codigo'] }}</strong></p>
                        </div>
                    @endif

                    @if($tipoInfo['descripcion'])
                        <p class="small text-muted mb-3">{{ $tipoInfo['descripcion'] }}</p>
                    @endif

                    <div class="mb-3">
                        <p class="small text-muted mb-1">Clasificacion</p>
                        <p class="mb-0"><strong>{{ $tipoInfo['clasificacion'] ?? 'No especificada' }}</strong></p>
                    </div>

                    <div class="mb-3">
                        <p class="small text-muted mb-1">Plazo de Evaluacion</p>
                        <p class="mb-0"><strong>{{ $tipoInfo['plazo_dias'] ? $tipoInfo['plazo_dias'] . ' dias habiles' : 'No especificado' }}</strong></p>
                    </div>

                    <div class="mb-0">
                        <p class="small text-muted mb-1">Costo</p>
                        <p class="mb-0"><strong>{{ $tipoInfo['costo_formateado'] }}</strong></p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Informacion de la Estacion -->
            @if($tramite->estacion)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-broadcast-tower me-2"></i>Estacion
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">{{ $tramite->estacion->localidad }}</h6>
                    <p class="text-muted small mb-2">{{ $tramite->estacion->razon_social }}</p>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <p class="small text-muted mb-1">Banda</p>
                            <span class="badge bg-secondary">{{ $tramite->estacion->banda->value ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <p class="small text-muted mb-1">Estado</p>
                            @if($tramite->estacion->estado)
                                <span class="badge bg-{{ $tramite->estacion->estado->value == 'al_aire' ? 'success' : 'danger' }}">
                                    {{ $tramite->estacion->estado->getLabel() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <a href="{{ route('estaciones.show', $tramite->estacion) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-external-link-alt me-1"></i>Ver Estacion
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt text-info me-2"></i>
                    Cambiar Estado del Tramite
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Expediente:</strong></label>
                    <p class="text-primary fw-bold">{{ $tramite->numero_expediente }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Estado Actual:</strong></label>
                    <div>
                        @if($tramite->estadoActual)
                            <span class="badge bg-{{ $tramite->estadoActual->color }}">
                                <i class="{{ $tramite->estadoActual->icono }} me-1"></i>
                                {{ $tramite->estadoActual->nombre }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Estados Disponibles:</strong></label>
                    <div id="estadosDisponibles" class="d-flex flex-wrap gap-2">
                        @foreach($estadosPosibles as $estado)
                            <button type="button" class="btn btn-outline-{{ $estado->color }} estado-btn"
                                    data-estado-id="{{ $estado->id }}"
                                    onclick="seleccionarEstado(this, {{ $estado->id }})">
                                <i class="{{ $estado->icono }} me-1"></i>
                                {{ $estado->nombre }}
                            </button>
                        @endforeach
                        @if($estadosPosibles->isEmpty())
                            <p class="text-muted">No hay transiciones disponibles desde el estado actual.</p>
                        @endif
                    </div>
                </div>

                <input type="hidden" id="nuevoEstadoId">

                <div id="camposAdicionales" style="display: none;">
                    <hr>
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario / Observaciones</label>
                        <textarea class="form-control" id="comentario" rows="3" placeholder="Ingrese comentarios..."></textarea>
                    </div>

                    <div class="mb-3" id="divResolucion" style="display: none;">
                        <label for="resolucion" class="form-label">Numero de Resolucion</label>
                        <input type="text" class="form-control" id="resolucion" placeholder="Ej: RD N 9827-2023-MTC/28">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEstado" onclick="guardarCambioEstado()" disabled>
                    <i class="fas fa-save me-1"></i>Guardar Cambio
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
        left: 15px;
        margin-left: -1px;
    }
    .timeline-item {
        position: relative;
        padding-left: 45px;
        padding-bottom: 20px;
    }
    .timeline-badge {
        position: absolute;
        left: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
    }
    .timeline-content h6 {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    .estado-btn {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .estado-btn:hover {
        transform: scale(1.05);
    }
    .estado-btn.selected {
        box-shadow: 0 0 0 3px rgba(0,123,255,0.5);
    }
    .form-check-input:checked {
        background-color: #1cc88a;
        border-color: #1cc88a;
    }
    @media print {
        .btn, .card-header, .breadcrumb, nav, .modal { display: none !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>
@endpush

@push('scripts')
<script>
let estadoSeleccionado = null;

function abrirModalCambiarEstado() {
    estadoSeleccionado = null;
    document.getElementById('btnGuardarEstado').disabled = true;
    document.getElementById('camposAdicionales').style.display = 'none';
    document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('selected'));

    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

function seleccionarEstado(btn, estadoId) {
    document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    estadoSeleccionado = estadoId;
    document.getElementById('nuevoEstadoId').value = estadoId;
    document.getElementById('btnGuardarEstado').disabled = false;
    document.getElementById('camposAdicionales').style.display = 'block';

    // Mostrar campo de resolucion para estados finales
    const nombreEstado = btn.textContent.trim().toLowerCase();
    if (nombreEstado.includes('finalizado') || nombreEstado.includes('denegado')) {
        document.getElementById('divResolucion').style.display = 'block';
    } else {
        document.getElementById('divResolucion').style.display = 'none';
    }
}

function guardarCambioEstado() {
    if (!estadoSeleccionado) {
        alert('Por favor seleccione un estado');
        return;
    }

    const btnGuardar = document.getElementById('btnGuardarEstado');
    const originalText = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    const datos = {
        nuevo_estado_id: estadoSeleccionado,
        comentario: document.getElementById('comentario').value,
        resolucion: document.getElementById('resolucion').value
    };

    fetch(`/tramites-mtc/{{ $tramite->id }}/cambiar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.mensaje || 'Error desconocido'));
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    });
}

function toggleRequisito(tramiteId, requisitoId) {
    fetch(`/tramites-mtc/${tramiteId}/toggle-requisito`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ requisito_id: requisitoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar barra de progreso
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush

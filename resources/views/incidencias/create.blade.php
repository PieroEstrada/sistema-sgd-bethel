@extends('layouts.app')

@section('title', 'Nueva Incidencia - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
            <li class="breadcrumb-item active">Nueva Incidencia</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus-circle text-danger me-2"></i>
                        Nueva Incidencia
                    </h1>
                    <p class="text-muted">Reportar nueva incidencia técnica</p>
                </div>
                <div>
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
                    <h6 class="m-0 font-weight-bold text-danger">Datos de la Incidencia</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('incidencias.store') }}">
                        @csrf
                        
                        <!-- Información Básica -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-danger border-bottom pb-2">
                                    <i class="fas fa-info-circle me-2"></i>Información Básica
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estacion_id" class="form-label">Estación Afectada <span class="text-danger">*</span></label>
                                <select class="form-control @error('estacion_id') is-invalid @enderror" 
                                        id="estacion_id" name="estacion_id" required>
                                    <option value="">Seleccionar estación</option>
                                    @foreach($estaciones as $estacion)
                                        <option value="{{ $estacion->id }}" {{ old('estacion_id') == $estacion->id ? 'selected' : '' }}>
                                            {{ $estacion->codigo }} - {{ $estacion->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estacion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reportado_por_user_id" class="form-label">Reportado Por <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control @error('reportado_por_user_id') is-invalid @enderror" 
                                            id="reportado_por_user_id" name="reportado_por_user_id">
                                        <option value="">Seleccionar usuario</option>
                                        @auth
                                            <option value="{{ auth()->id() }}" selected>
                                                {{ auth()->user()->name }} (Yo)
                                            </option>
                                        @endauth
                                    </select>
                                    <button type="button" class="btn btn-outline-info" id="btnBuscarUsuarios">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Por defecto se selecciona tu usuario. Cambia solo si reportas por otra persona.
                                </small>
                                @error('reportado_por_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Campo oculto para backup del nombre -->
                                <input type="hidden" id="reportado_por_backup" name="reportado_por" 
                                       value="{{ old('reportado_por', auth()->user()->name ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="descripcion_corta" class="form-label">Descripción Corta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('descripcion_corta') is-invalid @enderror" 
                                       id="descripcion_corta" name="descripcion_corta" value="{{ old('descripcion_corta') }}" 
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
                                          placeholder="Describe detalladamente el problema, síntomas, y circunstancias..." 
                                          maxlength="2000" required>{{ old('descripcion_detallada') }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_descripcion">0</span>/2000 caracteres
                                </small>
                                @error('descripcion_detallada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Clasificación -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-danger border-bottom pb-2">
                                    <i class="fas fa-tags me-2"></i>Clasificación y Prioridad
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="prioridad" class="form-label">Prioridad <span class="text-danger" id="prioridadReq">*</span></label>
                                <select class="form-control @error('prioridad') is-invalid @enderror"
                                        id="prioridad" name="prioridad">
                                    <option value="">Seleccionar prioridad</option>
                                    @foreach($prioridades as $key => $value)
                                        <option value="{{ $key }}" {{ old('prioridad') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control @error('categoria') is-invalid @enderror"
                                        id="categoria" name="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categorias as $key => $value)
                                        <option value="{{ $key }}" {{ old('categoria') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="impacto_servicio" class="form-label">Impacto <span class="text-danger">*</span></label>
                                <select class="form-control @error('impacto_servicio') is-invalid @enderror"
                                        id="impacto_servicio" name="impacto_servicio" required>
                                    <option value="">Seleccionar impacto</option>
                                    <option value="bajo" {{ old('impacto_servicio') == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                    <option value="medio" {{ old('impacto_servicio') == 'medio' ? 'selected' : '' }}>Medio</option>
                                    <option value="alto" {{ old('impacto_servicio') == 'alto' ? 'selected' : '' }}>Alto</option>
                                </select>
                                @error('impacto_servicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="area_responsable" class="form-label">Área Responsable</label>
                                <select class="form-control @error('area_responsable') is-invalid @enderror"
                                        id="area_responsable" name="area_responsable">
                                    <option value="">Sin asignar</option>
                                    <option value="ingenieria" {{ old('area_responsable') == 'ingenieria' ? 'selected' : '' }}>Ingeniería</option>
                                    <option value="laboratorio" {{ old('area_responsable') == 'laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                                    <option value="logistica" {{ old('area_responsable') == 'logistica' ? 'selected' : '' }}>Logística</option>
                                    <option value="operaciones" {{ old('area_responsable') == 'operaciones' ? 'selected' : '' }}>Operaciones</option>
                                    <option value="administracion" {{ old('area_responsable') == 'administracion' ? 'selected' : '' }}>Administración</option>
                                    <option value="contabilidad" {{ old('area_responsable') == 'contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                                    <option value="iglesia_local" {{ old('area_responsable') == 'iglesia_local' ? 'selected' : '' }}>Iglesia Local</option>
                                </select>
                                <small class="form-text text-muted">Opcional. Si no se asigna, queda como "Abierta"</small>
                                @error('area_responsable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-danger border-bottom pb-2">
                                    <i class="fas fa-clipboard me-2"></i>Información Adicional
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales, pasos ya realizados, etc..." 
                                          maxlength="1000">{{ old('observaciones') }}</textarea>
                                <small class="form-text text-muted">
                                    <span id="contador_observaciones">0</span>/1000 caracteres
                                </small>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Guía de Prioridades -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-lightbulb me-2"></i>Guía de Prioridades
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong class="text-danger">Alta:</strong>
                                            <ul class="mb-0">
                                                <li>Estación completamente fuera del aire</li>
                                                <li>Fallas de seguridad</li>
                                                <li>Problemas críticos de transmisión</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong class="text-warning">Media:</strong>
                                            <ul class="mb-0">
                                                <li>Problemas intermitentes</li>
                                                <li>Calidad de señal degradada</li>
                                                <li>Equipos de respaldo necesarios</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong class="text-info">Baja:</strong>
                                            <ul class="mb-0">
                                                <li>Mantenimiento preventivo</li>
                                                <li>Mejoras no críticas</li>
                                                <li>Problemas menores</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-save me-2"></i>Crear Incidencia
                                    </button>
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
    // Contador de caracteres para descripción detallada
    const descripcionDetallada = document.getElementById('descripcion_detallada');
    const contadorDescripcion = document.getElementById('contador_descripcion');
    
    if (descripcionDetallada && contadorDescripcion) {
        descripcionDetallada.addEventListener('input', function() {
            const longitud = this.value.length;
            contadorDescripcion.textContent = longitud;
            
            if (longitud > 1800) {
                contadorDescripcion.className = 'text-warning';
            } else if (longitud > 1900) {
                contadorDescripcion.className = 'text-danger';
            } else {
                contadorDescripcion.className = 'text-muted';
            }
        });
        
        // Trigger inicial
        descripcionDetallada.dispatchEvent(new Event('input'));
    }
    
    // Contador de caracteres para observaciones
    const observaciones = document.getElementById('observaciones');
    const contadorObservaciones = document.getElementById('contador_observaciones');
    
    if (observaciones && contadorObservaciones) {
        observaciones.addEventListener('input', function() {
            const longitud = this.value.length;
            contadorObservaciones.textContent = longitud;
            
            if (longitud > 900) {
                contadorObservaciones.className = 'text-warning';
            } else if (longitud > 950) {
                contadorObservaciones.className = 'text-danger';
            } else {
                contadorObservaciones.className = 'text-muted';
            }
        });
        
        // Trigger inicial
        observaciones.dispatchEvent(new Event('input'));
    }
    
    // ⚡ FUNCIONALIDAD DE BÚSQUEDA DE USUARIOS
    const btnBuscarUsuarios = document.getElementById('btnBuscarUsuarios');
    const selectReportadoPor = document.getElementById('reportado_por_user_id');
    const backupReportadoPor = document.getElementById('reportado_por_backup');
    
    // Cargar usuarios iniciales
    cargarUsuarios();
    
    if (btnBuscarUsuarios) {
        btnBuscarUsuarios.addEventListener('click', function() {
            mostrarModalBuscarUsuarios();
        });
    }
    
    // Actualizar backup cuando se selecciona usuario
    if (selectReportadoPor) {
        selectReportadoPor.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                backupReportadoPor.value = selectedOption.textContent.split(' (')[0]; // Solo el nombre
            }
        });
    }
    
    // Manejar categoría informativa
    const categoriaSelect = document.getElementById('categoria');
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', function() {
            const prioridadField = document.getElementById('prioridad');
            const infoAlert = document.getElementById('alertaInformativa');

            if (this.value === 'informativa') {
                // Mostrar alerta informativa
                if (!infoAlert) {
                    const alert = document.createElement('div');
                    alert.id = 'alertaInformativa';
                    alert.className = 'alert alert-info mt-2';
                    alert.innerHTML = '<i class="fas fa-info-circle me-2"></i><strong>Nota:</strong> Las incidencias informativas se crean automáticamente como <strong>Finalizadas</strong> (solo registro/documentación).';
                    this.parentNode.appendChild(alert);
                }
            } else {
                // Ocultar alerta
                const alert = document.getElementById('alertaInformativa');
                if (alert) alert.remove();
            }
        });
    }

    // Validación adicional del formulario
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const estacionId = document.getElementById('estacion_id').value;
            const descripcionCorta = document.getElementById('descripcion_corta').value;
            const descripcionDetallada = document.getElementById('descripcion_detallada').value;
            const prioridad = document.getElementById('prioridad').value;
            const categoria = document.getElementById('categoria').value;
            const impactoServicio = document.getElementById('impacto_servicio').value;
            const reportadoUserId = document.getElementById('reportado_por_user_id').value;

            // Para incidencias informativas, la prioridad no es obligatoria
            const prioridadRequerida = categoria !== 'informativa';

            if (!estacionId || !descripcionCorta || !descripcionDetallada ||
                (prioridadRequerida && !prioridad) || !categoria || !impactoServicio || !reportadoUserId) {
                e.preventDefault();
                alert('Por favor completa todos los campos obligatorios marcados con *');
                return;
            }

            if (descripcionDetallada.length < 20) {
                e.preventDefault();
                alert('La descripción detallada debe tener al menos 20 caracteres.');
                return;
            }
        });
    }
    
    // Auto-sugerir prioridad basada en palabras clave
    const descripcionCortaInput = document.getElementById('descripcion_corta');
    const prioridadSelect = document.getElementById('prioridad');
    
    if (descripcionCortaInput && prioridadSelect) {
        descripcionCortaInput.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            
            // Palabras clave para alta prioridad
            if (texto.includes('fuera del aire') || texto.includes('no transmite') || 
                texto.includes('emergencia') || texto.includes('urgente') || texto.includes('crítico')) {
                if (prioridadSelect.value === '') {
                    prioridadSelect.value = 'alta';
                    prioridadSelect.style.border = '2px solid #dc3545';
                    setTimeout(() => {
                        prioridadSelect.style.border = '';
                    }, 2000);
                }
            }
            
            // Palabras clave para media prioridad
            else if (texto.includes('intermitente') || texto.includes('calidad') || 
                     texto.includes('señal débil') || texto.includes('ruido')) {
                if (prioridadSelect.value === '') {
                    prioridadSelect.value = 'media';
                    prioridadSelect.style.border = '2px solid #ffc107';
                    setTimeout(() => {
                        prioridadSelect.style.border = '';
                    }, 2000);
                }
            }
        });
    }
});

// ⚡ FUNCIÓN PARA CARGAR USUARIOS
function cargarUsuarios(search = '') {
    const select = document.getElementById('reportado_por_user_id');
    
    fetch(`/api/users/dropdown?search=${search}`)
        .then(response => response.json())
        .then(users => {
            // Mantener usuario actual si está autenticado
            const currentUserId = '{{ auth()->id() ?? "" }}';
            const currentUserName = '{{ auth()->user()->name ?? "" }}';
            
            // Limpiar opciones excepto la primera
            select.innerHTML = '<option value="">Seleccionar usuario</option>';
            
            // Agregar usuario actual primero si está autenticado
            if (currentUserId) {
                const currentOption = document.createElement('option');
                currentOption.value = currentUserId;
                currentOption.textContent = `${currentUserName} (Yo)`;
                currentOption.selected = true;
                select.appendChild(currentOption);
            }
            
            // Agregar otros usuarios
            users.forEach(user => {
                if (user.id != currentUserId) {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.display_name;
                    option.dataset.rol = user.rol;
                    select.appendChild(option);
                }
            });
        })
        .catch(error => {
            console.error('Error cargando usuarios:', error);
        });
}

// ⚡ FUNCIÓN PARA MOSTRAR MODAL DE BÚSQUEDA
function mostrarModalBuscarUsuarios() {
    // Crear modal dinámicamente
    const modalHTML = `
        <div class="modal fade" id="modalBuscarUsuarios" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-users me-2"></i>Buscar Usuario Reportante
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="searchUserInput" 
                                       placeholder="Buscar por nombre o email...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" id="filterRol">
                                    <option value="">Todos los roles</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="sectorista">Sectorista</option>
                                    <option value="encargado_ingenieria">Enc. Ingeniería</option>
                                    <option value="encargado_laboratorio">Enc. Laboratorio</option>
                                    <option value="coordinador_operaciones">Coord. Operaciones</option>
                                    <option value="encargado_logistico">Enc. Logístico</option>
                                </select>
                            </div>
                        </div>
                        <div id="userSearchResults">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Eliminar modal anterior si existe
    const modalAnterior = document.getElementById('modalBuscarUsuarios');
    if (modalAnterior) {
        modalAnterior.remove();
    }
    
    // Agregar modal al DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalBuscarUsuarios'));
    modal.show();
    
    // Configurar búsqueda
    const searchInput = document.getElementById('searchUserInput');
    const filterRol = document.getElementById('filterRol');
    
    // Búsqueda en tiempo real
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            buscarUsuariosEnModal(this.value, filterRol.value);
        }, 300);
    });
    
    filterRol.addEventListener('change', function() {
        buscarUsuariosEnModal(searchInput.value, this.value);
    });
    
    // Carga inicial
    buscarUsuariosEnModal('', '');
    
    // Eliminar modal al cerrarse
    document.getElementById('modalBuscarUsuarios').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// ⚡ FUNCIÓN PARA BUSCAR USUARIOS EN MODAL
function buscarUsuariosEnModal(search, rol) {
    const resultsContainer = document.getElementById('userSearchResults');
    
    fetch(`/api/users/dropdown?search=${search}&rol=${rol}`)
        .then(response => response.json())
        .then(users => {
            if (users.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p>No se encontraron usuarios</p>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="list-group">';
            users.forEach(user => {
                html += `
                    <div class="list-group-item list-group-item-action" 
                         onclick="seleccionarUsuario(${user.id}, '${user.name}', '${user.rol}')"
                         style="cursor: pointer;">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${user.name}</h6>
                                <small class="text-muted">${user.email}</small>
                                ${user.telefono ? `<br><small class="text-muted"><i class="fas fa-phone me-1"></i>${user.telefono}</small>` : ''}
                            </div>
                            <span class="badge ${user.rol_badge_class}">
                                ${user.rol.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            resultsContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error buscando usuarios:', error);
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error al cargar usuarios. Inténtalo nuevamente.
                </div>
            `;
        });
}

// ⚡ FUNCIÓN PARA SELECCIONAR USUARIO
function seleccionarUsuario(userId, userName, userRol) {
    const select = document.getElementById('reportado_por_user_id');
    const backup = document.getElementById('reportado_por_backup');
    
    // Actualizar select
    select.value = userId;
    
    // Verificar si la opción existe, si no crearla
    if (!select.querySelector(`option[value="${userId}"]`)) {
        const newOption = document.createElement('option');
        newOption.value = userId;
        newOption.textContent = `${userName} (${userRol.replace('_', ' ').toUpperCase()})`;
        newOption.selected = true;
        select.appendChild(newOption);
    }
    
    // Actualizar backup
    backup.value = userName;
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuscarUsuarios'));
    modal.hide();
    
    // Notificación visual
    select.style.border = '2px solid #28a745';
    setTimeout(() => {
        select.style.border = '';
    }, 2000);
}
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

.alert-info .alert-heading {
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

.text-danger {
    color: #e74a3b !important;
}

/* Estilos para el contador de caracteres */
.form-text small {
    font-size: 0.875rem;
}

.text-warning {
    color: #f39c12 !important;
}
</style>
@endpush
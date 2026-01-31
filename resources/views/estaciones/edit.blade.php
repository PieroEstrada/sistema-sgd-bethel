@extends('layouts.app')

@section('title', 'Editar Estación - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('estaciones.index') }}">Estaciones</a></li>
            <li class="breadcrumb-item"><a href="{{ route('estaciones.show', $estacion) }}">{{ $estacion->razon_social }}</a></li>
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
                        Editar Estación
                    </h1>
                    <p class="text-muted">{{ $estacion->codigo }} - {{ $estacion->razon_social }}</p>
                </div>
                <div>
                    <a href="{{ route('estaciones.show', $estacion) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-2"></i>Ver Detalles
                    </a>
                    <a href="{{ route('estaciones.index') }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-warning">Modificar Datos de la Estación</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('estaciones.update', $estacion) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información Básica -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-info-circle me-2"></i>Información Básica
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('codigo') is-invalid @enderror" 
                                       id="codigo" name="codigo" value="{{ old('codigo', $estacion->codigo) }}" 
                                       placeholder="Ej: BET001" maxlength="20" required>
                                @error('codigo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-9">
                                <label for="razon_social" class="form-label">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('razon_social') is-invalid @enderror" 
                                       id="razon_social" name="razon_social" value="{{ old('razon_social', $estacion->razon_social) }}" 
                                       placeholder="Ej: Asociación Cultural Bethel" maxlength="255" required>
                                @error('razon_social')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>Ubicación
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="localidad" class="form-label">Localidad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('localidad') is-invalid @enderror" 
                                       id="localidad" name="localidad" value="{{ old('localidad', $estacion->localidad) }}" 
                                       placeholder="Ej: Lima" maxlength="255" required>
                                @error('localidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="provincia" class="form-label">Provincia <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('provincia') is-invalid @enderror" 
                                       id="provincia" name="provincia" value="{{ old('provincia', $estacion->provincia) }}" 
                                       placeholder="Ej: Lima" maxlength="255" required>
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="departamento" class="form-label">Departamento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('departamento') is-invalid @enderror" 
                                       id="departamento" name="departamento" value="{{ old('departamento', $estacion->departamento) }}" 
                                       placeholder="Ej: Lima" maxlength="255" required>
                                @error('departamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="sector" class="form-label">Sector <span class="text-danger">*</span></label>
                                <select class="form-control @error('sector') is-invalid @enderror" 
                                        id="sector" name="sector" required>
                                    <option value="">Seleccionar sector</option>
                                    @foreach($sectores as $key => $value)
                                        <option value="{{ $key }}" {{ old('sector', $estacion->sector->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sector')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="latitud" class="form-label">Latitud</label>
                                <input type="number" step="0.000001" class="form-control @error('latitud') is-invalid @enderror" 
                                       id="latitud" name="latitud" value="{{ old('latitud', $estacion->latitud) }}" 
                                       placeholder="Ej: -12.046374" min="-90" max="90">
                                @error('latitud')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="longitud" class="form-label">Longitud</label>
                                <input type="number" step="0.000001" class="form-control @error('longitud') is-invalid @enderror" 
                                       id="longitud" name="longitud" value="{{ old('longitud', $estacion->longitud) }}" 
                                       placeholder="Ej: -77.042793" min="-180" max="180">
                                @error('longitud')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Configuración Técnica -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-cogs me-2"></i>Configuración Técnica
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="banda" class="form-label">Banda <span class="text-danger">*</span></label>
                                <select class="form-control @error('banda') is-invalid @enderror" 
                                        id="banda" name="banda" required>
                                    <option value="">Seleccionar banda</option>
                                    @foreach($bandas as $key => $value)
                                        <option value="{{ $key }}" {{ old('banda', $estacion->banda->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('banda')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3" id="frecuencia_group">
                                <label for="frecuencia" class="form-label">Frecuencia (MHz)</label>
                                <input type="number" step="0.1" class="form-control @error('frecuencia') is-invalid @enderror" 
                                       id="frecuencia" name="frecuencia" value="{{ old('frecuencia', $estacion->frecuencia) }}" 
                                       placeholder="Ej: 104.5" min="0.1">
                                @error('frecuencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3" id="canal_group" style="display: none;">
                                <label for="canal_tv" class="form-label">Canal TV</label>
                                <input type="number" class="form-control @error('canal_tv') is-invalid @enderror" 
                                       id="canal_tv" name="canal_tv" value="{{ old('canal_tv', $estacion->canal_tv) }}" 
                                       placeholder="Ej: 4" min="2" max="69">
                                @error('canal_tv')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="potencia_watts" class="form-label">Potencia (Watts) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('potencia_watts') is-invalid @enderror" 
                                       id="potencia_watts" name="potencia_watts" value="{{ old('potencia_watts', $estacion->potencia_watts) }}" 
                                       placeholder="Ej: 1000" min="1" required>
                                @error('potencia_watts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-control @error('estado') is-invalid @enderror" 
                                        id="estado" name="estado" required>
                                    <option value="">Seleccionar estado</option>
                                    @foreach($estados as $key => $value)
                                        <option value="{{ $key }}" {{ old('estado', $estacion->estado->value) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label for="presbitero_id" class="form-label">Presbiterio/Pastor</label>
                                <select class="form-control @error('presbitero_id') is-invalid @enderror"
                                        id="presbitero_id" name="presbitero_id">
                                    <option value="">Sin asignar</option>
                                    @foreach($presbiteros ?? [] as $presbitero)
                                        <option value="{{ $presbitero->id }}"
                                            {{ old('presbitero_id', $estacion->presbitero_id) == $presbitero->id ? 'selected' : '' }}>
                                            {{ $presbitero->codigo }} - {{ $presbitero->nombre_completo }} ({{ $presbitero->sector }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('presbitero_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="celular_encargado" class="form-label">Celular del Encargado</label>
                                <input type="tel" class="form-control @error('celular_encargado') is-invalid @enderror" 
                                       id="celular_encargado" name="celular_encargado" value="{{ old('celular_encargado', $estacion->celular_encargado) }}" 
                                       placeholder="Ej: +51 999 999 999" maxlength="20">
                                @error('celular_encargado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Fechas Legales -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-calendar-alt me-2"></i>Fechas Legales
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_autorizacion" class="form-label">Fecha de Autorización</label>
                                <input type="date" class="form-control @error('fecha_autorizacion') is-invalid @enderror" 
                                       id="fecha_autorizacion" name="fecha_autorizacion" 
                                       value="{{ old('fecha_autorizacion', $estacion->fecha_autorizacion?->format('Y-m-d')) }}">
                                @error('fecha_autorizacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="fecha_vencimiento_autorizacion" class="form-label">Fecha de Vencimiento</label>
                                <input type="date" class="form-control @error('fecha_vencimiento_autorizacion') is-invalid @enderror" 
                                       id="fecha_vencimiento_autorizacion" name="fecha_vencimiento_autorizacion" 
                                       value="{{ old('fecha_vencimiento_autorizacion', $estacion->fecha_vencimiento_autorizacion?->format('Y-m-d')) }}">
                                @error('fecha_vencimiento_autorizacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Observaciones adicionales..." maxlength="1000">{{ old('observaciones', $estacion->observaciones) }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Modificación -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Última actualización:</strong> {{ $estacion->updated_at->format('d/m/Y H:i:s') }}
                                    @if($estacion->ultima_actualizacion_estado)
                                        <br><strong>Último cambio de estado:</strong> {{ $estacion->ultima_actualizacion_estado->format('d/m/Y H:i:s') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Actualizar Estación
                                    </button>
                                    <a href="{{ route('estaciones.show', $estacion) }}" class="btn btn-info">
                                        <i class="fas fa-eye me-2"></i>Ver Detalles
                                    </a>
                                    <a href="{{ route('estaciones.index') }}" class="btn btn-secondary">
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
    const bandaSelect = document.getElementById('banda');
    const frecuenciaGroup = document.getElementById('frecuencia_group');
    const canalGroup = document.getElementById('canal_group');
    const frecuenciaInput = document.getElementById('frecuencia');
    const canalInput = document.getElementById('canal_tv');

    function toggleFields() {
        const banda = bandaSelect.value;
        
        if (banda === 'FM' || banda === 'AM') {
            frecuenciaGroup.style.display = 'block';
            canalGroup.style.display = 'none';
            canalInput.value = '';
            frecuenciaInput.required = true;
            canalInput.required = false;
        } else if (banda === 'VHF' || banda === 'UHF') {
            frecuenciaGroup.style.display = 'none';
            canalGroup.style.display = 'block';
            frecuenciaInput.value = '';
            frecuenciaInput.required = false;
            canalInput.required = true;
        } else {
            frecuenciaGroup.style.display = 'block';
            canalGroup.style.display = 'none';
            frecuenciaInput.required = false;
            canalInput.required = false;
        }
    }

    bandaSelect.addEventListener('change', toggleFields);
    toggleFields(); // Ejecutar al cargar
});
</script>
@endpush
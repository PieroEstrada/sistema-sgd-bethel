@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-user-circle me-2"></i>Mi Perfil
                    </h4>
                    <p class="text-muted mb-0">Actualiza tu información personal</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>

            <!-- Información del Rol (Solo lectura) -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-id-badge me-2"></i>Información de Cuenta</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Correo Electrónico</label>
                            <p class="form-control-plaintext fw-bold">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Rol</label>
                            <p class="form-control-plaintext">
                                {{-- <span class="badge bg-primary">{{ $user->rol->label() }}</span> --}}
                                <span class="badge bg-primary">{{ $user->rol->getDisplayName() }}</span>
                            </p>
                        </div>
                        @if($user->sector_asignado)
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Sector Asignado</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">{{ $user->sector_asignado }}</span>
                            </p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Último Acceso</label>
                            <p class="form-control-plaintext">
                                {{ $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Edición -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Información</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text"
                                       class="form-control @error('telefono') is-invalid @enderror"
                                       id="telefono"
                                       name="telefono"
                                       value="{{ old('telefono', $user->telefono) }}"
                                       placeholder="+51 999 999 999">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h6>
                        <p class="text-muted small mb-3">Deja estos campos vacíos si no deseas cambiar tu contraseña.</p>

                        <div class="row">
                            <!-- Contraseña Actual -->
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Contraseña Actual</label>
                                <input type="password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       autocomplete="current-password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nueva Contraseña -->
                            <div class="col-md-4 mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="col-md-4 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       autocomplete="new-password">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

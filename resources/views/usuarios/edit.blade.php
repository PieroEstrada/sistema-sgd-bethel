@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Editar usuario</h4>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo (solo lectura)</label>
                        <input class="form-control" value="{{ $user->email }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input class="form-control @error('telefono') is-invalid @enderror" name="telefono"
                               value="{{ old('telefono', $user->telefono) }}">
                        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select class="form-select @error('rol') is-invalid @enderror" name="rol" id="rol" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}" @selected(old('rol', $user->rol->value)===$r->value)>{{ $r->getDisplayName() }}</option>
                            @endforeach
                        </select>
                        @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sector (solo Sectorista)</label>
                        <select class="form-select @error('sector_asignado') is-invalid @enderror" name="sector_asignado" id="sector_asignado">
                            <option value="">-</option>
                            @foreach($sectores as $s)
                                <option value="{{ $s->value }}" @selected(old('sector_asignado', $user->sector_asignado)===$s->value)>{{ $s->value }}</option>
                            @endforeach
                        </select>
                        @error('sector_asignado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Activo</label>
                        <select class="form-select @error('activo') is-invalid @enderror" name="activo" required>
                            <option value="1" @selected((string)old('activo', (int)$user->activo)==='1')>Sí</option>
                            <option value="0" @selected((string)old('activo', (int)$user->activo)==='0')>No</option>
                        </select>
                        @error('activo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Deja vacío para no cambiar">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const rol = document.getElementById('rol');
  const sector = document.getElementById('sector_asignado');

  function toggleSector() {
    if (rol.value === 'sectorista') {
      sector.disabled = false;
    } else {
      sector.value = '';
      sector.disabled = true;
    }
  }

  rol.addEventListener('change', toggleSector);
  toggleSector();
});
</script>
@endsection

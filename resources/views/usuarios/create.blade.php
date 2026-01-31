@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Nuevo usuario</h4>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo</label>
                        <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input class="form-control @error('telefono') is-invalid @enderror" name="telefono" value="{{ old('telefono') }}">
                        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select class="form-select @error('rol') is-invalid @enderror" name="rol" id="rol" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}" @selected(old('rol')===$r->value)>{{ $r->getDisplayName() }}</option>
                            @endforeach
                        </select>
                        @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sector (solo Sectorista)</label>
                        <select class="form-select @error('sector_asignado') is-invalid @enderror" name="sector_asignado" id="sector_asignado">
                            <option value="">-</option>
                            @foreach($sectores as $s)
                                <option value="{{ $s->value }}" @selected(old('sector_asignado')===$s->value)>{{ $s->value }}</option>
                            @endforeach
                        </select>
                        @error('sector_asignado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password inicial (opcional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Si lo dejas vacío, usa bethel2024">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">Guardar</button>
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

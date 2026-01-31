@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i>Usuarios</h4>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Nuevo
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input class="form-control" name="search" placeholder="Buscar por nombre o email"
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="rol">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->value }}" @selected(request('rol')===$r->value)>
                                {{ $r->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="activo">
                        <option value="">Activo (todos)</option>
                        <option value="1" @selected(request('activo')==='1')>Sí</option>
                        <option value="0" @selected(request('activo')==='0')>No</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="sector">
                        <option value="">Sector (todos)</option>
                        @foreach($sectores as $s)
                            <option value="{{ $s->value }}" @selected(request('sector')===$s->value)>
                                {{ $s->value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 d-grid">
                    <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Sector</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->rol->getDisplayName() }}</td>
                            <td>{{ $u->sector_asignado ?? '-' }}</td>
                            <td>
                                @if($u->activo)
                                    <span class="badge bg-success">Sí</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-warning" href="{{ route('usuarios.edit', $u) }}">Editar</a>

                                @if($u->activo)
                                    <form method="POST" action="{{ route('usuarios.destroy', $u) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Desactivar</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('usuarios.reactivar', $u) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">Reactivar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $usuarios->links() }}
        </div>
    </div>
</div>
@endsection

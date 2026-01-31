@extends('layouts.app')
@section('title','Nuevo Ticket')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Nuevo Ticket</h4>
        <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('tickets.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha ingreso</label>
                        <input type="date" class="form-control" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Equipo</label>
                        <input class="form-control" name="equipo" value="{{ old('equipo') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Servicio</label>
                        <input class="form-control" name="servicio" value="{{ old('servicio') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estación</label>
                        <select class="form-select" name="estacion_id">
                            <option value="">-</option>
                            @foreach($estaciones as $e)
                                <option value="{{ $e->id }}">{{ $e->localidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="4">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

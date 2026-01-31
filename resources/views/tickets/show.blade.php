@extends('layouts.app')
@section('title','Ticket')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Ticket #{{ $ticket->id }}</h4>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}">Volver</a>
            <a class="btn btn-outline-warning" href="{{ route('tickets.edit',$ticket) }}">Editar</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><div class="text-muted">Fecha</div>{{ $ticket->fecha_ingreso?->format('Y-m-d') }}</div>
                <div class="col-md-3"><div class="text-muted">Equipo</div>{{ $ticket->equipo }}</div>
                <div class="col-md-3"><div class="text-muted">Servicio</div>{{ $ticket->servicio }}</div>
                <div class="col-md-3"><div class="text-muted">Estación</div>{{ $ticket->estacion?->localidad ?? '-' }}</div>
                <div class="col-12 mt-2"><div class="text-muted">Descripción</div>{{ $ticket->descripcion ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Actualizar estado</div>
        <div class="card-body">
            <form method="POST" action="{{ route('tickets.estado',$ticket) }}">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" required>
                            @foreach($estados as $k => $label)
                                <option value="{{ $k }}" @selected($ticket->estado===$k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Observación logística</label>
                        <input class="form-control" name="observacion_logistica" value="{{ old('observacion_logistica', $ticket->observacion_logistica) }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </div>
                </div>
                <small class="text-muted">Cambiar el estado genera alerta a Logística.</small>
            </form>
        </div>
    </div>
</div>
@endsection

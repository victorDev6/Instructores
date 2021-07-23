@extends('adminlte::page')

@section('title', 'Agregar Permiso a un Rol')

@section('css')
    <style>
        .colorTop { 
            background-color: #541533;
        }
    </style>
@endsection

@section('content')
    
    <div class="container-fluid pt-4">
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header">Agregar Permiso a un Rol</div>
            <div class="card-body">
                
                <form action="{{route('permisos.store')}}" method="post">
                    @csrf

                    <div class="row">
                        <div class="col-2">Role</div>
                        <div class="col-4">
                            <select name="rol" id="rol" class="custom-select">
                                <option value="">SELECCIONAR</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-2">Permiso</div>
                        <div class="col-4">
                            <input class="form-control" id="permiso" name="permiso" type="text" placeholder="ej: actividades.inicio">
                        </div>

                        <div class="col">
                            <button type="submit" class="btn btn-outline-primary">Agregar</button>
                        </div>
                    </div>

                    
                </form>
            </div>
        </div>
    </div>

@endsection
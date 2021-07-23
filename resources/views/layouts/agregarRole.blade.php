@extends('adminlte::page')

@section('title', 'Agregar Rol')

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
            <div class="card-header">Agregar Rol</div>
            <div class="card-body">
                
                <form action="{{route('roles.store')}}" method="post">
                    @csrf

                    <div class="row mt-2">
                        <div class="col-2">Nombre del rol</div>
                        <div class="col-4">
                            <input class="form-control" id="rol" name="rol" type="text" placeholder="Nombre del rol">
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
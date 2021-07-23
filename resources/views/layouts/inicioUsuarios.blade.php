@extends('adminlte::page')

@section('title', 'Usuarios registrados')

@section('css')
    <style>
        .colorTop { 
            background-color: #541533;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header">USUARIOS REGISTRADOS</div>
            <div class="card-body">

                <form action="{{route('usuarios.inicio')}}" method="get">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-3">Buscar por email</div>
                        <div class="col-4">
                            <input class="form-control" type="text" name="busqueda" id="busqueda"
                            placeholder="Email">
                        </div>
                        <div class="col">
                            <button type="submit" id="btnBuscarCurso" class="btn btn-primary">Buscar</button>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <table class="table table-light table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Correo</th>
                                {{-- <th scope="col">Area</th> --}}
                                <th class="text-center" scope="col" colspan="{{count($roles)}}">Roles</th>
                                <th>Modificar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <form action="{{ route('usuarios.modificar') }}" method="post">
                                    @csrf

                                    <tr>
                                        <td>{{$user->id}}</td>
                                        <td>{{$user->name}}</td>
                                        <td>{{$user->email}}</td>
                                        {{-- <td>{{$user->descripcion}} {{$user->id_organo >= 10 ? $user->descripcion2 : ''}}</td> --}}
                                        @foreach ($roles as $rol)
                                            <td>
                                                <input class="d-none" id="id" name="id" value="{{$user->id}}" type="text">
                                                <div class="custom-control custom-checkbox d-flex justify-content-center">
                                                    <input type="checkbox" value="{{ $rol->id }}" class="custom-control-input"
                                                        @foreach ($permisos as $permiso)
                                                            @if (($permiso->model_id == $user->id) && ($permiso->role_id == $rol->id))
                                                                checked
                                                            @endif
                                                        @endforeach
                                                        name="permisos[]" id="check + {{$user->id}} + {{$rol->id}}">
                                                    <label class="custom-control-label" for="check + {{$user->id}} + {{$rol->id}}">{{$rol->name}}</label>
                                                </div>
                                            </td>
                                        @endforeach
                                        <td>
                                            <button type="submit" class="btn btn-sm btn-outline-info">Modificar</button>
                                        </td>
                                    </tr>
                                </form>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
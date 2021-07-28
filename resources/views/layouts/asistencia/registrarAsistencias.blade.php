@extends('adminlte::page')

@section('title', 'Registrar Asistencias')

@section('css')
    <style>
        .colorTop {
            background-color: #541533;
        }

    </style>
@endsection

@section('content')
    
    <div class="container-fluid pt-4">
        @if ($message)
            <div class="row px-2">
                <div class="col-md-12 alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            </div>
        @endif

        {{ Form::open(['route' => 'asistencia.inicio', 'method' => 'get', 'id'=>'frm']) }}
        {{csrf_field()}}
        <div class="card">
            <div class="card-header">Registar Asistencia</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">Clave de curso</div>
                    <div class="col-md-4">
                        {{ Form::text('clave', $clave, ['id'=>'clave', 'class' => 'form-control', 'placeholder' => 'CLAVE DEL CURSO', 'aria-label' => 'CLAVE DEL CURSO', 'required' => 'required', 'size' => 30]) }}
                    </div>
                    <div class="col">
                        {{ Form::button('Buscar', ['class' => 'btn btn-outline-primary', 'type' => 'submit']) }}
                    </div>
                </div>

                @if (isset($curso))
                    <div class="row bg-secondary mt-3" style="padding:10px">
                        <div class="form-group col-md-6">
                            CURSO: <b>{{ $curso->curso }}</b>
                        </div>
                        <div class="form-group col-md-4">
                            INSTRUCTOR: <b>{{ $curso->nombre }}</b>
                        </div>
                        <div class="form-group col-md-2">
                            DURACI&Oacute;N: <b>{{ $curso->dura }} hrs.</b>
                        </div>
                        <div class="form-group col-md-6">
                            ESPECIALIDAD: <b>{{ $curso->espe }}</b>
                        </div>
                        <div class="form-group col-md-6">
                            &Aacute;REA: <b>{{ $curso->area }}</b>
                        </div>
                        <div class="form-group col-md-6">
                            FECHAS DEL <b> {{ $curso->inicio }}</b> AL <b>{{ $curso->termino }}</b>
                        </div>
                        <div class="form-group col-md-4">
                            HORARIO: <b>{{ $curso->hini }} A {{ $curso->hfin }}</b>
                        </div>
                        <div class="form-group col-md-2">
                            CICLO: <b>{{ $curso->ciclo }}</b>
                        </div>
                    </div>

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Alumnos</th>
                                        @foreach ($dias as $dia)
                                            <th>{{$dia}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($alumnos as $alumno)
                                        <tr>
                                            <td>{{$alumno->alumno}}</td>
                                            @foreach ($dias as $dia)
                                                <td onclick="abrirModal('{{$alumno->alumno}}', '{{$alumno->matricula}}', '{{$dia}}')">
                                                    <strong id="{{$alumno->matricula}}{{$dia}}"></strong>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col d-flex justify-content-end">
                            <button id="btnGuardar" type="button" class="btn btn-primary">Guardar Asistencias</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        {!! Form::close() !!} 
    </div>

    <!-- Modal asistencia -->
    <div class="modal fade" id="modalAsistencia" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" 
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div style="background-color: #541533" class="modal-header text-white">
                    <h6 id="title" class="modal-title"></h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body py-5">
                    <div class="row text-center">
                        <div class="col"><button onclick="guardarAsistencia('asistencia')" style="width: 130px" type="button" class="btn btn-success">Asistencia</button></div>
                        <div class="col"><button onclick="guardarAsistencia('falta')" style="width: 130px" type="button" class="btn btn-danger">Falta</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        var arrayTemp = [];
        var matricula, fecha;
        function abrirModal(nombre, matricula, fecha) {
            this.matricula = matricula;
            this.fecha = fecha;
            $('#title').html('Asistencia de ' + nombre);
            $("#modalAsistencia").modal("show");
        }

        function guardarAsistencia(asis) {
            var asistencia = '', temp = {};
            if (asis == 'asistencia') {
                $('#'+ matricula + fecha).html('*');
                asistencia = 'si';
            } else {
                $('#'+ matricula + fecha).html('x');
                asistencia = 'no';
            }
            temp = {matricula, fecha, asistencia};
            arrayTemp.forEach(function myfunction(element, index, arr) {
                if (element['matricula'] == matricula && element['fecha'] == fecha) {
                    arrayTemp.splice(index, 1);
                }
            });
            arrayTemp.push(temp);
            $("#modalAsistencia").modal("hide");
        }

        $('#btnGuardar').click(function () {
            console.log(arrayTemp);
        });
    </script>
@endsection
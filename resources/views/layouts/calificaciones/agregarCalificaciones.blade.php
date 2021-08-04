@extends('adminlte::page')

@section('title', 'Registrar Calificaciones')

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

        {{ Form::open(['route' => 'calificaciones.inicio', 'method' => 'get', 'id'=>'frm']) }}
        {{csrf_field()}}

        <div class="card">
            <div class="card-header">Registrar Calificaciones</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-2">Clave de curso</div>
                    <div class="col-4">
                            {{ Form::text('clave', $clave, ['id'=>'clave', 'class' => 'form-control', 'placeholder' => 'CLAVE DEL CURSO', 'aria-label' => 'CLAVE DEL CURSO', 'required' => 'required', 'size' => 30]) }}
                    </div>
                    <div class="col">
                        {{ Form::button('Buscar', ['class' => 'btn btn-outline-primary', 'type' => 'submit']) }}
                    </div>
                </div>

                {{-- {{$curso}} --}}
                @if (isset($curso))
                    <div class="row bg-secondary mt-3" style="padding:20px">
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
                        <div class="table-responsive ">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">MATR&Iacute;CULA</th>
                                        <th scope="col">ALUMNOS</th>
                                        <th scope="col" class="text-center" width="10%">FOLIO ASIGNADO</th>
                                        <th scope="col" class="text-center" width="10%">CALIFICACI&Oacute;N</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $cambios = false; ?>
                                    @foreach ($alumnos as $key => $a)
                                        <tr>
                                            <td>{{$key + 1}}</td>
                                            <td> {{ $a->matricula }} </td>
                                            <td> {{ $a->alumno }} </td>
                                            <td class="text-center">
                                            @if ($a->folio) {{ $a->folio }} @else
                                                    {{ 'NINGUNO' }} @endif
                                            </td>
                                            <td>
                                                @if (!$a->folio or $a->folio == '0')
                                                    <?php $cambios = true; ?>
                                                    {{ Form::text('calificacion[' . $a->id . ']', $a->calificacion, ['id' => $a->id, 'class' => 'form-control numero', 'required' => 'required', 'size' => 1]) }}
                                                @else
                                                    {{ $a->calificacion }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        @if (count($alumnos) > 0 and $fecha_valida >= 0 and $cambios == true)
                                            <td colspan="5" class="text-right">
                                                {{ Form::button('GENERAR LISTA DE CALIFICACIONES', ['id' => 'reporte', 'class' => 'btn btn-outline-info']) }}
                                                {{ Form::button('GUARDAR CAMBIOS', ['id' => 'guardar', 'class' => 'btn btn-outline-success']) }}
                                            </td>
                                        @endif
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        {!! Form::close() !!} 
    </div>

@endsection

@section('js') 
    <script language="javascript">
        $(document).ready(function(){                
            $("#guardar").click(function(){
                if(confirm("¿Está seguro de ejecutar la acción?")==true){
                    $('#frm').attr('action', "{{route('calificaciones.guardar')}}");
                    $('#frm').attr('method', "post"); 
                    $('#frm').submit(); 
                }
            });             

            $('.numero').keyup(function (){                    
                this.value = (this.value + '').replace(/[^0-9NP]/g, '');
            });

            $('#reporte').click(function () {
                if(confirm("¿Está seguro de generar el reporte de calificaciones?")==true){
                    $('#frm').attr('action', "{{route('calificaciones.pdf')}}");
                    $('#frm').attr('method', "post"); 
                    $('#frm').attr('target', "_blanck"); 
                    $('#frm').submit(); 
                }
            });
        });       
    </script>  
@endsection

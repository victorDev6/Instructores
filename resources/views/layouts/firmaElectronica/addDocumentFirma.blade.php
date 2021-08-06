@extends('adminlte::page')

@section('title', 'Añadir Documento Para Firma')

@section('css')
    <style>
        .colorTop {
            background-color: #541533;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card">
            <div class="card-header">Añadir documento para firmar electronicamente</div>
            <div class="card-body d-flex justify-content-center">
                <div class="col-12 col-md-6">
                    <form action="{{route('addDocument.guardar')}}" id="form" method="post">
                        @csrf
                        <div class="col my-2">
                            <div class="custom-file">
                                <input type="file" id="doc" name="doc" class="custom-file-input" id="customFileLang" lang="es" accept="application/pdf">
                                <label class="custom-file-label" for="customFileLang">Seleccionar Archivo</label>
                            </div>
                        </div>
                        <div class="col my-5">
                            <div class="col">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" data-on="si" data-of="no" class="custom-control-input" id="firmare" name="firmare">
                                    <label class="custom-control-label" for="firmare">¿Vas a firmar este documento?</label>
                                </div>
                            </div>
                        </div>

                        <div class="col text-center pb-3"><strong>Añadir Firmantes</strong></div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <select class="custom-select" id="tipoUser" name="tipoUser">
                                    <option value = "" selected>Tipo de usuario</option>
                                    <option value="1">Instructor</option>
                                    <option value="2">Funcionario</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group flex-nowrap">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="addon-wrapping">@</span>
                                    </div>
                                    <input id="email" type="text" class="form-control" placeholder="Correo electronico" aria-label="Username" aria-describedby="addon-wrapping">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col text-center">
                                <button id="btnSearch" type="button" style="width: 200px" class="btn btn-outline-info">Buscar Firmante</button>
                            </div>
                        </div>
                        
                        <div class="col text-center pt-3"><strong>Firmantes</strong></div>
                        <div id="firmantes" class="row mt-3">
                            
                        </div>

                        <div class="row pt-3">
                            <div class="col text-center">
                                <button id="btnSolicitar" style="width: 350px" type="button" class="btn btn-primary">Solicitar Firmas</button>
                            </div>
                        </div> 
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalMessages" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #541533">
                <h5 class="modal-title text-white" id="titulo">cj</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <strong id="mensaje"></strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Aceptar</button>
                {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $('#btnSearch').click(function () {
            tipoUser = $('#tipoUser').val();
            email = $('#email').val();
            if (tipoUser != '' && email != '') {
                objFirmante = recolectarDatos('POST', tipoUser, email);
                enviarInformacion(objFirmante);
            } else {
                $('#titulo').html('Buscar Firmante');
                $('#mensaje').html('Debe seleccionar un tipo de usuario e ingresar un correo electronico antes de buscar a un firmante');
                $('#modalMessages').modal('show');
            }
        });

        function recolectarDatos(method, tipoUser, email) {
            newFirmante = {
                tipo: tipoUser,
                email: email,
                '_token': $("meta[name='csrf-token']").attr("content"),
                '_method': method
            };
            return newFirmante;
        }

        function enviarInformacion(objFirmante) {
            $.ajax({
                type: 'POST',
                url: "{{ url('/AddDocumentfirma/buscar') }}",
                data: objFirmante,
                success: function(result) {
                    if (result['id'] == null) {
                        sujeto = objFirmante['tipo'] == 1 ? 'el instructor' : 'el funcionario';
                        $('#titulo').html('Buscar Firmante');
                        $('#mensaje').html('No se encontraron coincidencias! Esto podria suceder por que el correo no esta escrito correctamente o porque ' + sujeto + ' no se encuentra activo.');
                        $('#modalMessages').modal('show');
                    } else {
                        crearInputData(objFirmante['tipo'], result);
                    }
                },
                error: function(jqXHR, textStatus) {
                    console.log(jqXHR);
                    alert("Hubo un error: " + jqXHR.status);
                }
            });
        }

        function crearInputData(tipo, result) {
            tipo = tipo == 1 ? 'Instructor' : 'Funcionario';
            firmantes = document.getElementById('firmantes');
            firmantes.innerHTML += `
                <div class="col-12 pb-1">
                    <input type="text" disabled class="form-control" name="firmas[]" value="${tipo} - ${result['id']} - ${result['nombre']} ${result['apellidoPaterno']} ${result['apellidoMaterno']}" input">
                </div>
            `;
        }

        $('#btnSolicitar').click(function () {
            if(confirm("¿Está seguro de enviar a firma este documento?") == true){
                $('#form').submit(); 
            }
        });
    </script>
@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body{
            font-family: sans-serif;
        }
        @page {
            margin: 30px 60px, 60px, 60px;
        }
        header { position: fixed;
            left: 0px;
            top: -155px;
            right: 0px;
            height: 50px;
            background-color: #ddd;
            text-align: center;
        }
        header h1{
            margin: 1px 0;
        }
        header h2{
            margin: 0 0 1px 0;
        }
        footer {
            position: fixed;
            left: 0px;
            bottom: 0px;
            right: 0px;
            height: 10px;
            text-align: center;
        }
        footer .page:after {
            content: counter(page);
        }
        footer table {
            width: 100%;
        }
        footer p {
            text-align: right;
        }
        footer .izq {
            text-align: left;
        }
        table, td {
                  border:0px solid black;
                }
        table {
            border-collapse:collapse;
            width:100%;
        }
        td {
            padding:0px;
        }
        .page-number:before {
            content: "Pagina " counter(page);
        }
    </style>
</head>
    <body>
        <footer>
            <div class="page-number"></div>
        </footer>
        <div class= "container g-pt-30" style="font-size: 12px; border:1px solid red;">
            <div id="content" style="border: 1px solid green;">
                Numero Contrato: {{$no_oficio}}<br>
                @foreach ($objeto['firmantes']['firmante'][0] as $moist)
                {{-- {{dd($moist)}} --}}
                    {{-- <p style="font-size: 5px;">Numero de Certificado: {{wordwrap($moist['_attributes']['certificado'], 64, "\n", true)}}</p><br> --}}
                    <p>Emisor: {{$moist['_attributes']['nombre_firmante']}}</p><br>
                    <p >Firma Electronica: <small>{{wordwrap($moist['_attributes']['firma_firmante'], 90, "\n", true)}}<small></p><br>
                    <p>Fecha y hora de Firma: {{$moist['_attributes']['fecha_firmado_firmante']}}</p><br>
                    <p>Puesto: {{$moist['_attributes']['puesto_firmante']}}</p><br>
                @endforeach
                <p><b>Folio:</b> {{$uuid}}</p>
                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="Código QR">
            </div>
        </div>
    </body>
</html>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>


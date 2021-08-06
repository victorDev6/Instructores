@extends('adminlte::page')

@section('title', 'Firmar Electronicamente')

@section('css')
    <style>
        .colorTop {
            background-color: #541533;
        }

    </style>
    <link rel="stylesheet" type="text/css" href="https://www.firmaelectronica.chiapas.gob.mx/tools/plugins/bootstrap-4.3.1/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="https://www.firmaelectronica.chiapas.gob.mx/tools/plugins/jasny-bootstrap4/css/jasny-bootstrap.min.css" />
    
@endsection

@section('content')

    <div class="container-fluid text-center">
        <div class="pt-5" id="vHTMLSignature"></div>
    </div>
    
@endsection

@section('js')
    {{-- js bootstrap --}}
    {{-- <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/plugins/jquery-3.4.1/jquery-3.4.1.min.js"></script> --}}
    {{-- <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/plugins/bootstrap-4.3.1/js/bootstrap.min.js"></script> --}}
    {{-- <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/plugins/jasny-bootstrap4/js/jasny-bootstrap.min.js"></script> --}}

    {{-- js para poder firmar --}}
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/sjcl.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/sha1_002.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/llave.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/jsbn.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/jsbn2.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/rsa.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/rsa2.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/base64_002.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/crypto-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/asn1hex-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/rsasign-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/x509-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/pbkdf2.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/tripledes_002.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/aes.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/rc2.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/asn1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/base64.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/hex_002.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/yahoo-min.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/hex.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/base64x-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/x64-core.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/tripledes.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/core.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/md5.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/sha1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/sha256.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/ripemd160.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/sha512.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/enc-base64.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/hmac.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/pbkdf2_002.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/cipher-core.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/asn1-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/rsapem-1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-sat/keyutil-1.js"></script>

    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/forge-0.7.1/forge-0.7.1.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-scg/mistake.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-scg/validate.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-scg/access.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-scg/dataSign.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/utilities-scg/dataTransportSign.js"></script>
    <script src="https://www.firmaelectronica.chiapas.gob.mx/tools/library/signedjs-2.1/signature-spv015_doctos.js"></script>
    
    
@endsection
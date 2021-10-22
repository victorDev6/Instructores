<?php

namespace App\Http\Controllers\firmaElectronica;


// use QrCode;
use setasign\Fpdi\Fpdi;
use App\DocumentosFirmar;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\tbl_cursos;
use App\Tokens_icti;
// use BaconQrCode\Encoder\QrCode;
use Illuminate\Support\Facades\Http;
use Vyuldashev\XmlToArray\XmlToArray;
use Illuminate\Support\Facades\Storage;
use \setasign\Fpdi\PdfParser\StreamReader;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FirmaController extends Controller {
    
    // php artisan serve --port=8001
    public function index(Request $request) {
        $email = Auth::user()->email;
        $docsFirmar1 = DocumentosFirmar::where('status','!=','CANCELADO')
            ->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                WHERE x->'_attributes'->>'email_firmante' IN ('".$email."') 
                AND x->'_attributes'->>'firma_firmante' is null)");
            // ->orderBy('id', 'desc')->get();

        $docsFirmados1 = DocumentosFirmar::where('status', 'EnFirma')
            ->where(function ($query) use ($email) {
                $query->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                    WHERE x->'_attributes'->>'email_firmante' IN ('".$email."') 
                    AND x->'_attributes'->>'firma_firmante' <> '')")
                ->orWhere(function($query1) use ($email) {
                    $query1->where('obj_documento_interno->emisor->_attributes->email', $email)
                            ->where('status', 'EnFirma');
                });
            });
            // ->orderBy('id', 'desc')->get();

        $docsValidados1 = DocumentosFirmar::where('status', 'VALIDADO')
            ->where(function ($query) use ($email) {
                $query->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                    WHERE x->'_attributes'->>'email_firmante' IN ('".$email."'))")
                ->orWhere(function($query1) use ($email) {
                    $query1->where('obj_documento_interno->emisor->_attributes->email', $email)
                            ->where('status', 'VALIDADO');
                });
            });
            // ->orderBy('id', 'desc')->get();

        $docsCancelados1 = DocumentosFirmar::where('status', 'CANCELADO')
            ->where(function ($query) use ($email) {
                $query->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                    WHERE x->'_attributes'->>'email_firmante' IN ('".$email."'))")
                ->orWhere(function($query1) use ($email) {
                    $query1->where('obj_documento_interno->emisor->_attributes->email', $email)
                            ->where('status', 'CANCELADO');
                });
            });
            // ->orderBy('id', 'desc')->get();

        $tipo_documento = $request->tipo_documento;
        // if ($tipo_documento != null) {
            session(['tipo' => $tipo_documento]);
        // }
        $tipo_documento = session('tipo');

        if($tipo_documento == null) {
            $docsFirmar = $docsFirmar1->orderBy('id', 'desc')->get();
            $docsFirmados = $docsFirmados1->orderBy('id', 'desc')->get();
            $docsValidados = $docsValidados1->orderBy('id', 'desc')->get();
            $docsCancelados = $docsCancelados1->orderBy('id', 'desc')->get();
        } else {
            $docsFirmar = $docsFirmar1->where('tipo_archivo', $tipo_documento)->orderBy('id', 'desc')->get();
            $docsFirmados = $docsFirmados1->where('tipo_archivo', $tipo_documento)->orderBy('id', 'desc')->get();
            $docsValidados = $docsValidados1->where('tipo_archivo', $tipo_documento)->orderBy('id', 'desc')->get();
            $docsCancelados = $docsCancelados1->where('tipo_archivo', $tipo_documento)->orderBy('id', 'desc')->get();
        }
        
        foreach ($docsFirmar as $value) {
            $value->base64xml = base64_encode($value->documento);
        }

        $getToken = Tokens_icti::all()->last();
        $token = $getToken->token;
        
        return view('layouts.firmaElectronica.firmaElectronica', compact('docsFirmar', 'email', 'docsFirmados', 'docsValidados', 'docsCancelados', 'tipo_documento', 'token'));
    }

    public function update(Request $request) {
        $documento = DocumentosFirmar::where('id', $request->idFile)->first();

        $obj_documento = json_decode($documento->obj_documento, true);
        $obj_documento_interno = json_decode($documento->obj_documento_interno, true);

        if (empty($obj_documento['archivo']['_attributes']['md5_archivo'])) {
            $obj_documento['archivo']['_attributes']['md5_archivo'] = $documento->md5_file;
        }

        foreach ($obj_documento['firmantes']['firmante'][0] as $key => $value) {
            if ($value['_attributes']['curp_firmante'] == $request->curp) {
                $value['_attributes']['fecha_firmado_firmante'] = $request->fechaFirmado;
                $value['_attributes']['no_serie_firmante'] = $request->serieFirmante; 
                $value['_attributes']['firma_firmante'] = $request->firma;
                $value['_attributes']['certificado'] = $request->certificado;
                $obj_documento['firmantes']['firmante'][0][$key] = $value;
            }
        }
        foreach ($obj_documento_interno['firmantes']['firmante'][0] as $key => $value) {
            if ($value['_attributes']['curp_firmante'] == $request->curp) {
                $value['_attributes']['fecha_firmado_firmante'] = $request->fechaFirmado;
                $value['_attributes']['no_serie_firmante'] = $request->serieFirmante; 
                $value['_attributes']['firma_firmante'] = $request->firma;
                $value['_attributes']['certificado'] = $request->certificado;
                $obj_documento_interno['firmantes']['firmante'][0][$key] = $value;
            }
        }

        $array = XmlToArray::convert($documento->documento);
        $array2 = XmlToArray::convert($documento->documento_interno);
        $array['DocumentoChis']['firmantes'] = $obj_documento['firmantes'];
        $array2['DocumentoChis']['firmantes'] = $obj_documento_interno['firmantes'];

        $result = ArrayToXml::convert($obj_documento, [
            'rootElementName' => 'DocumentoChis',
            '_attributes' => [
                'version' => $array['DocumentoChis']['_attributes']['version'],
                'fecha_creacion' => $array['DocumentoChis']['_attributes']['fecha_creacion'],
                'no_oficio' => $array['DocumentoChis']['_attributes']['no_oficio'],
                'dependencia_origen' => $array['DocumentoChis']['_attributes']['dependencia_origen'],
                'asunto_docto' => $array['DocumentoChis']['_attributes']['asunto_docto'],
                'tipo_docto' => $array['DocumentoChis']['_attributes']['tipo_docto'],
                'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
            ],
        ]);

        $result2 = ArrayToXml::convert($obj_documento_interno, [
            'rootElementName' => 'DocumentoChis',
            '_attributes' => [
                'version' => $array2['DocumentoChis']['_attributes']['version'],
                'fecha_creacion' => $array2['DocumentoChis']['_attributes']['fecha_creacion'],
                'no_oficio' => $array2['DocumentoChis']['_attributes']['no_oficio'],
                'dependencia_origen' => $array2['DocumentoChis']['_attributes']['dependencia_origen'],
                'asunto_docto' => $array2['DocumentoChis']['_attributes']['asunto_docto'],
                'tipo_docto' => $array2['DocumentoChis']['_attributes']['tipo_docto'],
                'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
            ],
        ]);

        DocumentosFirmar::where('id', $request->idFile)
            ->update([
                'obj_documento' => json_encode($obj_documento),
                'obj_documento_interno' => json_encode($obj_documento_interno),
                'documento' => $result,
                'documento_interno' => $result2 
            ]);

        return redirect()->route('firma.inicio')->with('warning', 'Documento firmado exitosamente!');
    }

    public function sellar(Request $request) {
        $documento = DocumentosFirmar::where('id', $request->txtIdFirmado)->first();
        $xmlBase64 = base64_encode($documento->documento);

        $getToken = Tokens_icti::all()->last();

        $response = $this->sellarFile($xmlBase64, $getToken->token);
        if ($response->json() == null) {
            $request = new Request();
            $token = $this->generarToken($request);
            $response = $this->sellarFile($xmlBase64, $token);
        }

        if ($response->json()['status'] == 1) { //exitoso
            $decode = base64_decode($response->json()['xml']);
            DocumentosFirmar::where('id', $request->txtIdFirmado)
                ->update([
                    'status' => 'VALIDADO',
                    'uuid_sellado' => $response->json()['uuid'],
                    'fecha_sellado' => $response->json()['fecha_Sellado'],
                    'documento' => $decode,
                    'cadena_sello' => $response->json()['cadenaSello']
                ]);
            return redirect()->route('firma.inicio')->with('warning', 'Documento validado exitosamente!');
        } else {
            return redirect()->route('firma.inicio')->with('danger', 'Ocurrio un error al sellar el documento, por favor intente de nuevo');
        }
    }

    public function generarPDF(Request $request) {
        $documento = DocumentosFirmar::where('id', $request->txtIdGenerar)->first();
        $objeto = json_decode($documento->obj_documento_interno,true);
        $uuid = $documento->uuid_sellado;
        $folio = $documento->nombre_archivo;
        $tipo_archivo = $documento->tipo_archivo;
        $totalFirmantes = $objeto['firmantes']['_attributes']['num_firmantes'];

        $array = [
            [
                'nombre_firmante' => 'VICTOR MANUEL ORTIZ RODRIGUEZ',
                'no_serie_firmante' => '00001000000503277602',
                'SERVICIO DE ADMINISTRACION TRIBUTARIA',
                'firma_firmante' => 'AHzFQJ0errrY6yXn+FsQjarYV1Qpg0OTRm/TRmgiGb7Y31odx5cTO1JHGgkfDr+dwEgSOvAehtMoIw+y9KiAzDk8gJOD2uf1EXMHRXq7cbLEbTG7jvL+10XORkiVfO9vQMi6Ii2YTnOEjizyWYitTOxLCfgxET+pEBULdpznIbii/lZnfFbc1uiNaPAOd9ngoF1np16V/aLe9dlFrnymSPMHT0BMAuYjXEumCBE+/fK8fNAp4x7p8mr6DP0/DPPc5us2M7/ZG+7rEP/FveykTNko5ABat8HhbNlDOZ0Px2NXepMEc8LdOcgn7siOAo0snSUCIBGsAxmo5kmWY7WR7Q==',
                'fecha_firmado_firmante' => '2021-09-01T13:30:00',
                'puesto_firmante' => 'DIRECTOR DE UNIDAD EJEMPLO'
            ],
            [
                'nombre_firmante' => 'VICTOR MANUEL ORTIZ RODRIGUEZ',
                'no_serie_firmante' => '00001000000503277602',
                'SERVICIO DE ADMINISTRACION TRIBUTARIA',
                'firma_firmante' => 'AHzFQJ0errrY6yXn+FsQjarYV1Qpg0OTRm/TRmgiGb7Y31odx5cTO1JHGgkfDr+dwEgSOvAehtMoIw+y9KiAzDk8gJOD2uf1EXMHRXq7cbLEbTG7jvL+10XORkiVfO9vQMi6Ii2YTnOEjizyWYitTOxLCfgxET+pEBULdpznIbii/lZnfFbc1uiNaPAOd9ngoF1np16V/aLe9dlFrnymSPMHT0BMAuYjXEumCBE+/fK8fNAp4x7p8mr6DP0/DPPc5us2M7/ZG+7rEP/FveykTNko5ABat8HhbNlDOZ0Px2NXepMEc8LdOcgn7siOAo0snSUCIBGsAxmo5kmWY7WR7Q==',
                'fecha_firmado_firmante' => '2021-09-01T13:30:00',
                'puesto_firmante' => 'DIRECTOR DE UNIDAD DE CAPACITACION SAN CRISTOBAL DE LAS CASAS'
            ]
        ];

        $pdf = new Fpdi();
        if ($documento->tipo_archivo == 'Contrato') {
            $result = $documento->link_pdf;
            $fileContent = file_get_contents($result, 'rb');
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($fileContent));
        } else {
            $url = $documento->link_pdf;
            $unity = explode('/', $url);
            $path = storage_path('app/public/uploadFiles/DocumentosFirmas/'.$unity[6].'/'.$documento->nombre_archivo);
            $result = str_replace('\\','/', $path);
            $pageCount =  $pdf->setSourceFile($result);
        }
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) { 
            $tplId = $pdf->importPage($pageNo);
            if ($tipo_archivo == 'Contrato') {
                $pdf->addPage('P','A4'); //
            } else {
                $pdf->addPage('L','Letter'); //
            }
            $pdf->useTemplate($tplId);
        }

        if ($tipo_archivo == 'Contrato') {
            $pdf->addPage('P','A4');
        } else {
            $pdf->addPage('L','Letter');
        }
        
        // The new content
        $fontSize = '15';
        $fontColor = `255,0,0`;
        $left = 16;
        $top = 20;
        $text = 'Documento';

        $pdf->SetFont("helvetica", "B", 10);
        // $pdf->SetTextColor($fontColor);
        $pdf->Text($left,$top,$text);
        $pdf->Text(16, 60, 'Firmas e informacion identificadora');
        
        $pdf->SetFont("helvetica", '', 7);
        // documento
        $pdf->SetTextColor(98,98,98);
        $pdf->Text(20, 25, 'Nombre del documento:');
        $pdf->Text(20, 30, 'Fecha de constancia:');
        $pdf->Text(20, 35, 'Documento creado por:');
        $pdf->Text(20, 40, 'Numero de paginas:');
        $pdf->Text(20, 45, 'Numero de firmantes:');
        
        $pdf->SetTextColor($fontColor);
        $pdf->Text(80, 25, $objeto['archivo']['_attributes']['nombre_archivo']);
        $pdf->Text(80, 30, $documento->fecha_sellado);
        $pdf->Text(80, 35, $objeto['emisor']['_attributes']['nombre_emisor']);
        $pdf->Text(80, 40, $pageCount);
        $pdf->Text(80, 45, $totalFirmantes);

        // firmas
        $pdf->Text(77, 60, '(Las firmas son unicas para este documento)');

        $x = 20; $y = 65;
        /* foreach ($array as $value) {
            $pdf->SetTextColor(98,98,98);
            $pdf->Text($x, $y, 'Nombre del Firmante:');
            if ($tipo_archivo == 'Contrato') {
                $pdf->Text($x + 100, $y, 'Numero de Certificado:');
                $pdf->Text($x, $y + 5, 'Emisor:');
                $pdf->Text($x, $y + 10, 'Firma Electronica:');
                $pdf->Text($x, $y + 27, 'Fecha y hora de Firma:');
                $pdf->Text($x + 80, $y + 27, 'Puesto: ');
            } else {
                $pdf->Text($x, $y + 5, 'Numero de Certificado:');
                $pdf->Text($x, $y + 10, 'Emisor:');
                $pdf->Text($x, $y + 15, 'Firma Electronica:');
                $pdf->Text($x, $y + 27, 'Fecha y hora de Firma:');
                $pdf->Text($x, $y + 32, 'Puesto: ');
            }
            
            $pdf->SetTextColor($fontColor);
            $pdf->Text($x + 40, $y, $value['nombre_firmante']);
            if ($tipo_archivo == 'Contrato') {
                $pdf->Text($x + 130, $y, $value['no_serie_firmante']);
                $pdf->Text($x + 40, $y + 5, 'SERVICIO DE ADMINISTRACION TRIBUTARIA');
                $pdf->setXY($x + 39, $y + 7);
                $pdf->MultiCell(0, 4, $value['firma_firmante'], 0, 'J', false);
                $pdf->Text($x + 40, $y + 27, $value['fecha_firmado_firmante']);
                $pdf->Text($x + 100, $y + 27, $value['puesto_firmante']);

                $y += 40;
            } else {
                $pdf->Text($x + 40, $y + 5, $value['no_serie_firmante']);
                $pdf->Text($x + 40, $y + 10, 'SERVICIO DE ADMINISTRACION TRIBUTARIA');
                $pdf->setXY($x + 39, $y + 12);
                $pdf->MultiCell(0, 4, $value['firma_firmante'], 0, 'J', false);
                $pdf->Text($x + 40, $y + 27, $value['fecha_firmado_firmante']);
                $pdf->Text($x + 40, $y + 32, $value['puesto_firmante']);

                $y += 55;
            }
        } */

        foreach ($objeto['firmantes']['firmante'][0] as $value) {
            $pdf->SetTextColor(98,98,98);
            $pdf->Text($x, $y, 'Nombre del Firmante:');
            if ($tipo_archivo == 'Contrato') {
                $pdf->Text($x + 100, $y, 'Numero de Certificado:');
                $pdf->Text($x, $y + 5, 'Emisor:');
                $pdf->Text($x, $y + 10, 'Firma Electronica:');
                $pdf->Text($x, $y + 27, 'Fecha y hora de Firma:');
                $pdf->Text($x + 80, $y + 27, 'Puesto:');
            } else {
                $pdf->Text($x, $y + 5, 'Numero de Certificado:');
                $pdf->Text($x, $y + 10, 'Emisor:');
                $pdf->Text($x, $y + 15, 'Firma Electronica:');
                $pdf->Text($x, $y + 27, 'Fecha y hora de Firma:');
                $pdf->Text($x, $y + 32, 'Puesto:');
            }
            
            $pdf->SetTextColor($fontColor);
            $pdf->Text($x + 40, $y, $value['_attributes']['nombre_firmante']);
            if ($tipo_archivo == 'Contrato') {
                $pdf->Text($x + 130, $y, $value['_attributes']['no_serie_firmante']);
                $pdf->Text($x + 40, $y + 5, 'SERVICIO DE ADMINISTRACION TRIBUTARIA');
                $pdf->setXY($x + 39, $y + 7);
                $pdf->MultiCell(0, 4, $value['_attributes']['firma_firmante'], 0, 'J', false);
                $pdf->Text($x + 40, $y + 27, $value['_attributes']['fecha_firmado_firmante']);
                $pdf->Text($x + 100, $y + 27, $value['_attributes']['puesto_firmante']);
            } else {
                $pdf->Text($x + 40, $y + 5, $value['_attributes']['no_serie_firmante']);
                $pdf->Text($x + 40, $y + 10, 'SERVICIO DE ADMINISTRACION TRIBUTARIA');
                $pdf->setXY($x + 39, $y + 12);
                $pdf->MultiCell(0, 4, $value['_attributes']['firma_firmante'], 0, 'J', false);
                $pdf->Text($x + 40, $y + 27, $value['_attributes']['fecha_firmado_firmante']);
                $pdf->Text($x + 40, $y + 32, $value['_attributes']['puesto_firmante']);
            }
        }

        $verificacion = "https://innovacion.chiapas.gob.mx/validacionDocumentoPrueba/consulta/Certificado3?guid=$uuid&no_folio=$folio";
        
        $parts = explode('.', $folio);
        $locat = storage_path("app/public/qrcode/$parts[0].png");
        $location = str_replace('\\','/', $locat);
        \PHPQRCode\QRcode::png($verificacion, $location, 'L', 10, 0);

        if ($tipo_archivo == 'Contrato') {
            $pdf->Image($location, 16, 270, 20, 20, "png");
            $pdf->Text(45, 275, 'Para verificar la integridad de este documento, favor de escanear el codigo QR o visitar el enlace:');
            $pdf->Text(45, 280, 'https://innovacion.chiapas.gob.mx/validaciondocumentoprueba');
            $pdf->Text(45, 285, 'Guid: ');
            $pdf->Text(55, 285, $uuid);
            $pdf->Text(45, 289, 'Folio: ');
            $pdf->Text(55, 289, $folio);
        } else {
            $pdf->Image($location, 16, 185, 20, 20, "png");
            $pdf->Text(45, 190, 'Para verificar la integridad de este documento, favor de escanear el codigo QR o visitar el enlace:');
            $pdf->Text(45, 195, 'https://innovacion.chiapas.gob.mx/validaciondocumentoprueba');
            $pdf->Text(45, 200, 'Guid: ');
            $pdf->Text(55, 200, $uuid);
            $pdf->Text(45, 204, 'Folio: ');
            $pdf->Text(55, 204, $folio);
        }
        $pdf->Output('I', 'FIRMADO_'.$objeto['archivo']['_attributes']['nombre_archivo']);
    }

    public function cancelarDocumento(Request $request) {
        $date = date('Y-m-d H:i:s');

        if ($request->motivo != null) {
            $data = [
                'usuario' => 'instructor',
                'id' => Auth::user()->id,
                'motivo' => $request->motivo,
                'fecha' => $date,
                'correo' => Auth::user()->email
            ];

            DocumentosFirmar::where('id', $request->txtIdCancel)
                ->update([
                    'status' => 'CANCELADO',
                    'cancelacion' => $data
                ]);
            tbl_cursos::where('clave', $request->txtClave)
                ->update(
                    $request->txtTipo == 'Lista de asistencia' 
                        ? ['asis_finalizado' => false] 
                        : ($request->txtTipo == 'Lista de calificaciones'
                            ?  ['calif_finalizado' => false]
                            : [])
                );
            return redirect()->route('firma.inicio')->with('warning', 'Documento cancelado exitosamente!');
        } else {
            return redirect()->route('firma.inicio')->with('danger', 'Debe ingresar el motivo de cancelación');
        }
    }

    public function generarToken(Request $request) {
        $resToken = Http::withHeaders([
            'Accept' => 'application/json'
        ])->post('https://interopera.chiapas.gob.mx/gobid/api/Auth/TokenAppAuth', [ 
            'nombre' => 'Firma Electronica', 
            'key' => '4E520F58-7103-479B-A2EC-FEE907409053' 
        ]);

        $token = $resToken->json();
        Tokens_icti::create([
            'token' => $token
        ]);

        return $token;
    }

    public function sellarFile($xml, $token) {
        $response1 = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ])->post('https://apiprueba.firma.chiapas.gob.mx/FEA/v2/NotariaXML/sellarXML', [
            'xml_Firmado' => $xml
        ]);
        return $response1;
    }

}

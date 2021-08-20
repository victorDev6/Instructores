<?php

namespace App\Http\Controllers\firmaElectronica;

use App\DocumentosFirmar;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Vyuldashev\XmlToArray\XmlToArray;

class FirmaController extends Controller {
    
    public function index() {
        $array = [
            'emisor' => [
                '_attributes' => [
                    'nombre_emisor' => 'TEST TEST',
                    'cargo_emisor' => 'INSTRUCTOR',
                    'dependencia_emisor' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA'
                ],
            ],
            /* 'receptores' => [
                'receptor' => [
                    ['_attributes' => 
                        ['nombre_receptor' => 'TEST1 TEST1', 'cargo_receptor' => 'INSTRUCTOR', 'dependencia_receptor' => 'ICATECH']
                    ],
                    ['_attributes' => 
                        ['nombre_receptor' => 'TEST2 TEST2', 'cargo_receptor' => 'DIRECTOR DE LA UNIDAD DE CAPACITACION X', 'dependencia_receptor' => 'ICATECH']
                    ],
                ], 
            ], */
            'archivo' => [
                '_attributes' => [
                    'nombre_archivo' => 'test.pdf',
                    'checksum_archivo' => 'shfshfhfeurfhufefjrefhkfhekhfkefrkehf'
                ],
                'cuerpo' => ['cuerpo archivo cuerpo archivo cuerpo archivo cuerpo archivo cuerpo archivo cuerpo archivo cuerpo archivo']
            ],
            'firmantes' => [
                '_attributes' => [
                    'num_firmantes' => '2'
                ],
                'firmante' => [
                    ['_attributes' => 
                        ['curp_firmante' => 'KFHEIFL938HKHK32', 'nombre_firmante' => 'TEST FIRMANTE 1', 'email_firmante' => 'testFirmante1@gmail.com', 'tipo_firmante' => 'FM']
                    ],
                    ['_attributes' => 
                        ['curp_firmante' => 'MCVHCVCJVCJ23GJV', 'nombre_firmante' => 'TEST FIRMANTE 2', 'email_firmante' => 'testFirmante2@gmail.com', 'tipo_firmante' => 'FM']
                    ],
                ]
            ],  

            /* 'Bad guys' => [
                'Guy' => [
                    ['name' => 'Sauron', 'weapon' => 'Evil Eye'],
                    ['name' => 'Darth Vader', 'weapon' => 'Lightsaber'],
                ],
            ], */
        ];

        // por firmar : documentos donde su correo aparezca en el nodo firmantes
        // $email = Auth::user()->email;
        $email = 'BERNARDOABARCA2@HOTMAIL.COM'; 
        $docsFirmar = DocumentosFirmar::where('status','!=','CANCELADO')
                        ->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                        WHERE x->'_attributes'->>'email_firmante' IN ('".$email."'))")->get();
        $docsFirmados = DocumentosFirmar::where('status','!=','CANCELADO')
                        ->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                        WHERE x->'_attributes'->>'email_firmante' IN ('".$email."') 
                        AND x->'_attributes'->>'firma_firmante' <> '')")->get();
        $docsValidados = DocumentosFirmar::where('status', '=', 'VALIDADO')
                        ->whereRaw("EXISTS(SELECT TRUE FROM jsonb_array_elements(obj_documento->'firmantes'->'firmante'->0) x 
                        WHERE x->'_attributes'->>'email_firmante' IN ('".$email."'))")->get();

        foreach ($docsFirmar as $value) {
            $value->base64xml = base64_encode($value->documento);
        }
        
        // dd($docsFirmar);
        return view('layouts.firmaElectronica.firmaElectronica', compact('docsFirmar', 'email', 'docsFirmados', 'docsValidados'));
    }

    public function update(Request $request) {
        $documento = DocumentosFirmar::where('id', $request->idFile)->first();

        $obj_documento = json_decode($documento->obj_documento, true);
        $obj_documento_interno = json_decode($documento->obj_documento_interno, true);
        foreach ($obj_documento['firmantes']['firmante'][0] as $key => $value) {
            if ($value['_attributes']['curp_firmante'] == $request->curp) {
                $value['_attributes']['fecha_firmado_firmante'] = $request->fechaFirmado;
                $value['_attributes']['no_serie_firmante'] = $request->serieFirmante; 
                $value['_attributes']['firma_firmante'] = $request->firma;
                $obj_documento['firmantes']['firmante'][0][$key] = $value;
            }
        }
        foreach ($obj_documento_interno['firmantes']['firmante'][0] as $key => $value) {
            if ($value['_attributes']['curp_firmante'] == $request->curp) {
                $value['_attributes']['fecha_firmado_firmante'] = $request->fechaFirmado;
                $value['_attributes']['no_serie_firmante'] = $request->serieFirmante; 
                $value['_attributes']['firma_firmante'] = $request->firma;
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

}

<?php

namespace App\Http\Controllers\firmaElectronica;

use Carbon\Carbon;
use App\tbl_cursos;
use App\Funcionarios;
use App\instructores;
use App\DocumentosFirmar;
use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Tokens_icti;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AddDocumentFirmaController extends Controller {

    public function index() {
        return view('layouts.firmaElectronica.addDocumentFirma');
    }

    public function search(Request $request) {
        $tipo = $request->tipo;
        $email = $request->email;

        if ($tipo == 1) { //tabla instructor
            $firmante = instructores::where('correo', '=', $email)
                ->where('status', '=', 'Validado')
                ->where('estado', '=', true)
                ->first();
        } else { // tabla directorio
            $firmante = Funcionarios::where('email', '=', $email)
                ->where('activo', '=', true)
                ->first();
        }

        return response()->json($firmante);
    }

    // id_organo: 34
    // id_clasificacionorgano: 3
    // nombre: Instituto de Capacitación y Vinculación Tecnológica del Estado de Chiapas
    public function save(Request $request) {
        $dataEmisor = Auth::user()->tipo_usuario == 1 
            ? instructores::where('id', '=', Auth::user()->id_sivyc)->first()
            : Funcionarios::where('id', '=', Auth::user()->id_sivyc)->first();

        // on esta activo el switch
        if ($request->hasFile('doc')) {
            $curso = tbl_cursos::where('clave', $request->no_oficio)->first();
            if ($curso){
                if ($request->firmas != null) { // si hay firmantes, se crea el arreglo para el xml
                    $nameFileOriginal = $request->file('doc')->getClientOriginalName();
                    $nameFile = trim(date('YmdHis').'_'.$nameFileOriginal);
                    $numFirmantes = count($request->firmas);
    
                    $arrayFirmantes = [];
                    $arrayFirmantes2 = [];
                    if ($request->firmare != null) {
                        $numFirmantes++;
                        $temp = ['_attributes' => 
                                    [
                                        'curp_firmante' => $dataEmisor->curp, 
                                        'nombre_firmante' => $dataEmisor->nombre.' '.$dataEmisor->apellidoPaterno.' '.$dataEmisor->apellidoMaterno, 
                                        'email_firmante' => $dataEmisor->correo, 
                                        'tipo_firmante' => 'FM'
                                    ]
                                ];
    
                        $temp2 = ['_attributes' => 
                                    [
                                        'curp_firmante' => $dataEmisor->curp, 
                                        'nombre_firmante' => $dataEmisor->nombre.' '.$dataEmisor->apellidoPaterno.' '.$dataEmisor->apellidoMaterno, 
                                        'email_firmante' => $dataEmisor->correo, 
                                        'tipo_firmante' => 'FM',
                                        'tipo_usuario' => Auth::user()->tipo_usuario,
                                        'puesto_firmante' => 'INSTRUCTOR',
                                        'fecha_firmado_firmante' => '',
                                        'no_serie_firmante' => '',
                                        'firma_firmante' => '',
                                    ]
                                ];
                        array_push($arrayFirmantes, $temp);
                        array_push($arrayFirmantes2, $temp2);
                    }
    
                    foreach ($request->firmas as $firmante) {
                        $array = explode('-', $firmante);
                        if ($array[0] == 'Instructor') {
                            $dataFirmante = instructores::where('id', '=', $array[1])->first();
                            $temp = ['_attributes' => 
                                [
                                    'curp_firmante' => $dataFirmante->curp, 
                                    'nombre_firmante' => $dataFirmante->nombre.' '.$dataFirmante->apellidoPaterno.' '.$dataFirmante->apellidoMaterno, 
                                    'email_firmante' => $dataFirmante->correo, 
                                    'tipo_firmante' => 'FM'
                                ]
                            ];
    
                            $temp2 = ['_attributes' => 
                                [
                                    'curp_firmante' => $dataFirmante->curp, 
                                    'nombre_firmante' => $dataFirmante->nombre.' '.$dataFirmante->apellidoPaterno.' '.$dataFirmante->apellidoMaterno, 
                                    'email_firmante' => $dataFirmante->correo, 
                                    'tipo_firmante' => 'FM',
                                    'tipo_usuario' => 1,
                                    'puesto_firmante' => 'INSTRUCTOR',
                                    'fecha_firmado_firmante' => '',
                                    'no_serie_firmante' => '',
                                    'firma_firmante' => ''
                                ]
                            ];
                        } else {
                            $dataFirmante = Funcionarios::where('id', '=', $array[1])->first();
                            $temp = ['_attributes' => 
                                [
                                    'curp_firmante' => $dataFirmante->curp, 
                                    'nombre_firmante' => $dataFirmante->nombre.' '.$dataFirmante->apellidoPaterno.' '.$dataFirmante->apellidoMaterno, 
                                    'email_firmante' => $dataFirmante->email, 
                                    'tipo_firmante' => 'FM'
                                ]
                            ];
    
                            $temp2 = ['_attributes' => 
                                [
                                    'curp_firmante' => $dataFirmante->curp, 
                                    'nombre_firmante' => $dataFirmante->nombre.' '.$dataFirmante->apellidoPaterno.' '.$dataFirmante->apellidoMaterno, 
                                    'email_firmante' => $dataFirmante->email, 
                                    'tipo_firmante' => 'FM',
                                    'tipo_usuario' => 2,
                                    'puesto_firmante' => $dataFirmante->puesto,
                                    'fecha_firmado_firmante' => '',
                                    'no_serie_firmante' => '',
                                    'firma_firmante' => ''
                                ]
                            ];
                        }
                        array_push($arrayFirmantes, $temp);
                        array_push($arrayFirmantes2, $temp2);
                    }

                    
                    $md5 = md5_file($request->file('doc'), false);
    
                    $text = Pdf::getText($request->file('doc'), 'c:/Program Files/Git/mingw64/bin/pdftotext');
                    $text = str_replace("\f",' ',$text);
                    
                    // otro metodo para leer el pddf
                    // $reader = new \Asika\Pdf2text;
                    // $text = $reader->decode($request->file('doc'));
                    // dd($text);
    
                    $ArrayXml = [
                        'emisor' => [
                            '_attributes' => [
                                'nombre_emisor' => $dataEmisor->nombre.' '.$dataEmisor->apellidoPaterno.' '.$dataEmisor->apellidoMaterno,
                                'cargo_emisor' => Auth::user()->tipo_usuario == 1 ? 'INSTRUCTOR' : $dataEmisor->puesto,
                                'dependencia_emisor' => 'Instituto de Capacitación y Vinculación Tecnológica del Estado de Chiapas'
                                // 'curp_emisor' => $dataEmisor->curp
                            ],
                        ],
                        'archivo' => [
                            '_attributes' => [
                                'nombre_archivo' => $nameFileOriginal
                                // 'md5_archivo' => $md5
                                // 'checksum_archivo' => utf8_encode($text)
                            ],
                            // 'cuerpo' => ['Por medio de la presente me permito solicitar el archivo '.$nameFile]
                            'cuerpo' => [utf8_encode($text)]
                        ],
                        'firmantes' => [
                            '_attributes' => [
                                'num_firmantes' => $numFirmantes
                            ],
                            'firmante' => [
                                $arrayFirmantes
                            ]
                        ], 
                    ];
    
                    $ArrayXml2 = [
                        'emisor' => [
                            '_attributes' => [
                                'nombre_emisor' => $dataEmisor->nombre.' '.$dataEmisor->apellidoPaterno.' '.$dataEmisor->apellidoMaterno,
                                'cargo_emisor' => Auth::user()->tipo_usuario == 1 ? 'INSTRUCTOR' : $dataEmisor->puesto,
                                'dependencia_emisor' => 'Instituto de Capacitación y Vinculación Tecnológica del Estado de Chiapas',
                                'curp_emisor' => $dataEmisor->curp,
                                'email' => Auth::user()->email
                            ],
                        ],
                        'archivo' => [
                            '_attributes' => [
                                'nombre_archivo' => $nameFileOriginal
                                // 'md5_archivo' => $md5
                                // 'checksum_archivo' => utf8_encode($text)
                            ],
                            // 'cuerpo' => ['Por medio de la presente me permito solicitar el archivo '.$nameFile]
                            'cuerpo' => [utf8_encode($text)]
                        ],
                        'firmantes' => [
                            '_attributes' => [
                                'num_firmantes' => $numFirmantes
                            ],
                            'firmante' => [
                                $arrayFirmantes2
                            ]
                        ], 
                    ];
    
                    $date = Carbon::now();
                    $month = $date->month < 10 ? '0'.$date->month : $date->month;
                    $day = $date->day < 10 ? '0'.$date->day : $date->day;
                    $hour = $date->hour < 10 ? '0'.$date->hour : $date->hour;
                    $minute = $date->minute < 10 ? '0'.$date->minute : $date->minute;
                    $second = $date->second < 10 ? '0'.$date->second : $date->second;
                    $dateFormat = $date->year.'-'.$month.'-'.$day.'T'.$hour.':'.$minute.':'.$second;
                    
                    $result = ArrayToXml::convert($ArrayXml, [
                        'rootElementName' => 'DocumentoChis',
                        '_attributes' => [
                            'version' => '2.0',
                            'fecha_creacion' => $dateFormat,
                            'no_oficio' => $nameFile,
                            'dependencia_origen' => 'Instituto de Capacitación y Vinculación Tecnológica del Estado de Chiapas',
                            'asunto_docto' => $request->tipo_documento,
                            'tipo_docto' => Auth::user()->tipo_usuario == 1 ? 'ACS' : 'CNT',
                            'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
                        ],
                    ]);
    
                    $result2 = ArrayToXml::convert($ArrayXml2, [
                        'rootElementName' => 'DocumentoChis',
                        '_attributes' => [
                            'version' => '2.0',
                            'fecha_creacion' => $dateFormat,
                            'no_oficio' => $nameFile,
                            'dependencia_origen' => 'Instituto de Capacitación y Vinculación Tecnológica del Estado de Chiapas',
                            'asunto_docto' => $request->tipo_documento,
                            'tipo_docto' => Auth::user()->tipo_usuario == 1 ? 'ACS' : 'CNT',
                            'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
                        ],
                    ]);

                    $xmlBase64 = base64_encode($result);
                    $getToken = Tokens_icti::all()->last();
                    if ($getToken) {
                        $response = $this->getCadenaOriginal($xmlBase64, $getToken->token);
                        if ($response->json() == null) {
                            $token = $this->generarToken();
                            $response = $this->getCadenaOriginal($xmlBase64, $token);
                        }
                    } else { // no hay registros
                        $token = $this->generarToken();
                        $response = $this->getCadenaOriginal($xmlBase64, $token);
                    }
                    
                    /* $response = Http::post(env('cadena_original', ''), [
                        'xml_OriginalBase64' => $xmlBase64,
                        'apiKey' => 'dwLChYOVylB9htqD9qIaSVHddKzWKiqXqmh7fFRHwFJk2x'
                    ]); */
    
                    if ($response->json()['cadenaOriginal'] != null) {
                        $urlFile = $this->uploadFileServer($request->file('doc'), $nameFileOriginal);
                        $datas = explode('*',$urlFile);
    
                        $dataInsert = new DocumentosFirmar();
                        $dataInsert->obj_documento = json_encode($ArrayXml);
                        $dataInsert->obj_documento_interno = json_encode($ArrayXml2);
                        $dataInsert->status = 'EnFirma';
                        $dataInsert->link_pdf = $datas[0];
                        $dataInsert->cadena_original = $response->json()['cadenaOriginal'];
                        $dataInsert->tipo_archivo = $request->tipo_documento;
                        $dataInsert->numero_o_clave = $request->no_oficio;
                        $dataInsert->nombre_archivo = $datas[1];
                        $dataInsert->documento = $result;
                        $dataInsert->documento_interno = $result2;
                        $dataInsert->md5_file = $md5;
                        $dataInsert->save();
    
                        return redirect()->route('addDocument.inicio')->with('warning', 'Se agrego el documento correctamente, puede ver el status en el que se encuentra en el apartado Firma Electronica');
                    } else {
                        return redirect()->route('addDocument.inicio')->with('danger', 'Ocurrio un error al obtener la cadena original, por favor intente de nuevo');
                    }
                } else { // no hay firmantes
                    return redirect()->route('addDocument.inicio')->with('warning', 'No se agregaron firmantes');
                }
            } else {
                return back()->with('warning', 'No se encontro el curso con la clave ingresada');
            }
        } else {
            return redirect()->route('addDocument.inicio')->with('warning', 'Debe seleccionar un archivo PDF');
        }
    }

    protected function uploadFileServer($file, $name) {
        // $extensionFile = $file->getClientOriginalExtension();
        // $path = '/'.$subPath;
        $name = trim(date('YmdHis').'_'.$name);
        $file->storeAs('/uploadFiles/DocumentosFirmas/'.Auth::user()->id, $name);
        $url = Storage::url('/uploadFiles/DocumentosFirmas/'.Auth::user()->id.'/'.$name);
        return $url.'*'.$name;
    }

    //obtener el token
    public function generarToken() {
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

    // obtener la cadena original
    public function getCadenaOriginal($xmlBase64, $token) {
        // dd(config('app.cadena'));
        // dd(Config::get('app.cadena', 'default'));
        $response1 = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('https://apiprueba.firma.chiapas.gob.mx/FEA/v2/Tools/generar_cadena_original', [
            'xml_OriginalBase64' => $xmlBase64
        ]);

        return $response1;
    }

}

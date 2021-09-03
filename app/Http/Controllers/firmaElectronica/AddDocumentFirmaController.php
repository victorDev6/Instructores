<?php

namespace App\Http\Controllers\firmaElectronica;

use Carbon\Carbon;
use App\Funcionarios;
use App\instructores;
use App\DocumentosFirmar;
use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
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

    public function save(Request $request) {
        $dataEmisor = Auth::user()->tipo_usuario == 1 
            ? instructores::where('id', '=', Auth::user()->id_sivyc)->first()
            : Funcionarios::where('id', '=', Auth::user()->id_sivyc)->first();
        // on esta activo el switch
        if ($request->hasFile('doc')) {
            if ($request->firmas != null) { // si hay firmantes, se crea el arreglo para el xml
                $nameFile = $request->file('doc')->getClientOriginalName();
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
                            'dependencia_emisor' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA'
                        ],
                    ],
                    'archivo' => [
                        '_attributes' => [
                            'nombre_archivo' => $nameFile,
                            'checksum_archivo' => utf8_encode($text)
                        ],
                        'cuerpo' => ['Por medio de la presente me permito solicitar el archivo '.$nameFile]
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
                            'dependencia_emisor' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA',
                            'email' => Auth::user()->email
                        ],
                    ],
                    'archivo' => [
                        '_attributes' => [
                            'nombre_archivo' => $nameFile,
                            'checksum_archivo' => utf8_encode($text)
                        ],
                        'cuerpo' => ['Por medio de la presente me permito solicitar el archivo '.$nameFile]
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
                        'version' => '1.0',
                        'fecha_creacion' => $dateFormat,
                        'no_oficio' => $nameFile,
                        'dependencia_origen' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA DEL ESTADO DE CHIAPAS',
                        'asunto_docto' => $request->tipo_documento,
                        'tipo_docto' => Auth::user()->tipo_usuario == 1 ? 'ACS' : 'CNT',
                        'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
                    ],
                ]);

                $result2 = ArrayToXml::convert($ArrayXml2, [
                    'rootElementName' => 'DocumentoChis',
                    '_attributes' => [
                        'version' => '1.0',
                        'fecha_creacion' => $dateFormat,
                        'no_oficio' => $nameFile,
                        'dependencia_origen' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA DEL ESTADO DE CHIAPAS',
                        'asunto_docto' => $request->tipo_documento,
                        'tipo_docto' => Auth::user()->tipo_usuario == 1 ? 'ACS' : 'CNT',
                        'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
                    ],
                ]);

                // dd($result);

                $xmlBase64 = base64_encode($result);
                // $xmlBase64 = 'dG9fZG9jdG89Ikxpc3RhIGRlIGFzaXN0ZW5jaWEiIHRpcG9fZG9jdG89IkFDUyI+PGVtaXNvciBub21icmVfZW1pc29yPSJWSUNUT1IgTUFOVUVMIE9SVElaIFJPRFJJR1VFWiIgY2FyZ29fZW1pc29yPSJJTlNUUlVDVE9SIiBkZXBlbmRlbmNpYV9lbWlzb3I9IklOU1RJVFVUTyBERSBDQVBBQ0lUQUNJT04gWSBWSU5DVUxBQ0lPTiBURUNOT0xPR0lDQSIvPjxhcmNoaXZvIG5vbWJyZV9hcmNoaXZvPSJMSVNUQV9BU0lTVEVOQ0lBXzFDLTIxLUFTRUotRVhULTAwMTQuUERGIiBjaGVja3N1bV9hcmNoaXZvPSJTVUJTRUNSRVRBUiYjeENEO0EgREUgRURVQ0FDSSYjeEQzO04gRSBJTlZFU1RJR0FDSSYjeEQzO04gVEVDTk9MJiN4RDM7R0lDQVMgRElSRUNDSSYjeEQzO04gR0VORVJBTCBERSBDRU5UUk9TIERFIEZPUk1BQ0kmI3hEMztOIFBBUkEgRUwgVFJBQkFKTyYjMTM7JiMxMDtMSVNUQSBERSBBU0lTVEVOQ0lBIChMQUQtMDQpJiMxMzsmIzEwOyYjMTM7JiMxMDtVTklEQUQgREUgQ0FQQUNJVEFDSSYjeEQzO046IEFSRUE6IEFETUlOSVNUUkFDSSYjeEQzO04gRkVDSEEgSU5JQ0lPOiAwMy8wOC8yMDIxJiMxMzsmIzEwOyYjMTM7JiMxMDsxNzkgVEFQQUNIVUxBJiMxMzsmIzEwOyYjMTM7JiMxMDtDTEFWRSBDQ1Q6JiMxMzsmIzEwOyYjMTM7JiMxMDswN0VJQzAwMDFDJiMxMzsmIzEwOyYjMTM7JiMxMDtDSUNMTyBFU0NPTEFSOiYjMTM7JiMxMDsmIzEzOyYjMTA7MjAyMS0yMDIyJiMxMzsmIzEwOyYjMTM7JiMxMDtHUlVQTzomIzEzOyYjMTA7JiMxMzsmIzEwOzAwMTQmIzEzOyYjMTA7JiMxMzsmIzEwO01FUzomIzEzOyYjMTA7JiMxMzsmIzEwO0VTUEVDSUFMSURBRDogQVNJU1RFTkNJQSBFSkVDVVRJVkEmIzEzOyYjMTA7JiMxMzsmIzEwO0NVUlNPOiBPUlRPR1JBRklBIFkgUkVEQUNDSU9OIERFIERPQ1VNRU5UT1MgT0ZJQ0lBTEVTJiMxMzsmIzEwOyYjMTM7JiMxMDtDTEFWRTogMUMtMjEtQVNFSi1FWFQtMDAxNCYjMTM7JiMxMDsmIzEzOyYjMTA7RkVDSEEgVEVSTUlOTzogMzAvMDgvMjAyMSYjMTM7JiMxMDsmIzEzOyYjMTA7SE9SQVJJTzogTFVORVMgQSBWSUVSTkVTIERFIDEwOjAwIGEubS4gQSAxMjowMCBwLm0uJiMxMzsmIzEwOyYjMTM7JiMxMDtDVVJQOiBHT0ROODYwMzIyTUNTUk1MMDgmIzEzOyYjMTA7JiMxMzsmIzEwO0FHT1NUTyYjMTM7JiMxMDsmIzEzOyYjMTA7QSYjeEQxO086IDIwMjEmIzEzOyYjMTA7JiMxMzsmIzEwO04gVSBNJiMxMzsmIzEwOyYjMTM7JiMxMDtOJiN4REE7TUVSTyBERSBDT05UUk9MJiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwNzY4JiMxMzsmIzEwOyYjMTM7JiMxMDsyJiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODg1JiMxMzsmIzEwOyYjMTM7JiMxMDszJiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwNzcxJiMxMzsmIzEwOyYjMTM7JiMxMDs0JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAyNVYwMTgzJiMxMzsmIzEwOyYjMTM7JiMxMDs1JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODg2JiMxMzsmIzEwOyYjMTM7JiMxMDs2JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODg4JiMxMzsmIzEwOyYjMTM7JiMxMDs3JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODg5JiMxMzsmIzEwOyYjMTM7JiMxMDs4JiMxMzsmIzEwOyYjMTM7JiMxMDsyMDA3MDAyMVowMDIyJiMxMzsmIzEwOyYjMTM7JiMxMDs5JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwNzcyJiMxMzsmIzEwOyYjMTM7JiMxMDsxMCYjMTM7JiMxMDsmIzEzOyYjMTA7MjEwNzAwMDFDMDg5MSYjMTM7JiMxMDsmIzEzOyYjMTA7MTEmIzEzOyYjMTA7JiMxMzsmIzEwOzIxMDcwMDAxQzA3NzQmIzEzOyYjMTA7JiMxMzsmIzEwOzEyJiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODkyJiMxMzsmIzEwOyYjMTM7JiMxMDsxMyYjMTM7JiMxMDsmIzEzOyYjMTA7MjEwNzAwMDFDMDg5MyYjMTM7JiMxMDsmIzEzOyYjMTA7MTQmIzEzOyYjMTA7JiMxMzsmIzEwOzExMDcwMDAxQzE5NTkmIzEzOyYjMTA7JiMxMzsmIzEwOzE1JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwNzYyJiMxMzsmIzEwOyYjMTM7JiMxMDsxNiYjMTM7JiMxMDsmIzEzOyYjMTA7MjEwNzAwMDFDMDg5NCYjMTM7JiMxMDsmIzEzOyYjMTA7MTcmIzEzOyYjMTA7JiMxMzsmIzEwOzIxMDcwMDAxQzA4OTUmIzEzOyYjMTA7JiMxMzsmIzEwOzE4JiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODk2JiMxMzsmIzEwOyYjMTM7JiMxMDsxOSYjMTM7JiMxMDsmIzEzOyYjMTA7MjEwNzAwMDFDMDg5NyYjMTM7JiMxMDsmIzEzOyYjMTA7MjAmIzEzOyYjMTA7JiMxMzsmIzEwOzIxMDcwMDAxQzA4OTgmIzEzOyYjMTA7JiMxMzsmIzEwOzIxJiMxMzsmIzEwOyYjMTM7JiMxMDsyMTA3MDAwMUMwODk5JiMxMzsmIzEwOyYjMTM7JiMxMDsyMiYjMTM7JiMxMDsmIzEzOyYjMTA7MjAwNzAwMTBLMDg3OCYjMTM7JiMxMDsmIzEzOyYjMTA7MjMmIzEzOyYjMTA7JiMxMzsmIzEwOzIxMDcwMDAxQzA3ODAmIzEzOyYjMTA7JiMxMzsmIzEwOzI0JiMxMzsmIzEwOyYjMTM7JiMxMDsyMDA3MDAxMEswODcxJiMxMzsmIzEwOyYjMTM7JiMxMDtOT01CUkUgREVMIEFMVU1OTyYjMTM7JiMxMDtQUklNRVIgQVBFTExJRE8vU0VHVU5ETyBBUEVMTElETy9OT01CUkUoUykgQkFSUk9OIExPUEVaIERJQU5BIENJVExBTEkgQ0FNQVMgVklEQUwgTUFSSVNPTCBERSBDT1NTIENSVVogQ0VTQVIgQU1JTiYjMTM7JiMxMDtERSBMQSBDUlVaIFJFWUVTIE1BUklBIFRFUkVTQSBFTlJJUVVFWiBQT0NFUk9TIEVMSVpBQkVUSCBBTElDSUEmIzEzOyYjMTA7RVNUUkFEQSBMT1BFWiBNT05JQ0EgQkVSRU5JQ0UgRkFSUkVSQSBTVUFSRVogQU5FVEggQkVSRU5JQ0UgRklHVUVST0EgRVdBTkNIWU5BIEFMRVhBTkRSQSBNQUdOT0xJQSYjMTM7JiMxMDtGSUdVRVJPQSBHT0lUSUEgUEVEUk8gRlVFTlRFUyBBQ0VJVFVOTyBDQU5EWSBFTklESUEgR0FSQ0lBIENBU1RJTExPIFZMQURJTUlSIEFMRUpBTkRSTyYjMTM7JiMxMDtHT01FWiBQRU5BR09TIEpVQU4gUEFCTE8gR1VUSUVSUkVaIE1BTkRVSkFOTyBMVUlTIFJPQkVSVE8gTE9QRVogRVNDT0JBUiBHVUFEQUxVUEUgREUgTE9TIEFOR0VMRVMmIzEzOyYjMTA7TUFSUk9RVUlOIERFIFBBWiBKT1NFIEVMRU9CRUQgTUFSUk9RVUlOIE9aVU5BIEFSTEVUVEUgQU1BSVJBTlkgTU9SQUxFUyBCQUxMSU5BUyBWRVJPTklDQSBST1hBTkEmIzEzOyYjMTA7TVVSSUxMTyBMT1BFWiBTRVJHSU8gSVZBTiBOSUdFTkRBIFRPUklKQSBCRVJMQU4mIzEzOyYjMTA7UEFMQUNJT1MgUEVSRVogSlVMSUVUQSBFTElaQUJFVEggUEVSRVogQkFSUklPUyBBTk5BIExVQ0lBJiMxMzsmIzEwO1BFUkVaIEdBTERBTUVaIFBBVFJJQ0lBIFJPU0FMSUEgUkVZRVMgTUFaQSBNQVJJQSBERUwgQ0FSTUVOIFJPRFJJR1VFWiBDQU1BQ0hPIEFMRUpBTkRSQSYjMTM7JiMxMDsmIzEzOyYjMTA7MSAyIDMgNCA1IDYgNyA4IDkgMTAgMTEgMTIgMTMgMTQgMTUgMTYgMTcgMTggMTkgMjAgMjEgMjIgMjMgMjQgMjUgMjYgMjcgMjggMjkgMzAgMzEmIzEzOyYjMTA7Knh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHggKnh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eCAqeHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4ICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHgmIzEzOyYjMTA7JiMxMzsmIzEwO1RPVEFMJiMxMzsmIzEwOyYjMTM7JiMxMDtBJiMxMzsmIzEwOyYjMTM7JiMxMDtJJiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7MSYjMTM7JiMxMDsmIzEzOyYjMTA7MjcmIzEzOyYjMTA7JiMxMzsmIzEwOzEmIzEzOyYjMTA7JiMxMzsmIzEwOzI3JiMxMzsmIzEwOyYjMTM7JiMxMDtHT1JESUxMTyBET01JTkdVRVogTkFMTEVMWSBCRVJFTklDRSBOT01CUkUgWSBGSVJNQSBERUwgSU5TVFJVQ1RPUiYjMTM7JiMxMDsmIzEzOyYjMTA7U0VMTE8mIzEzOyYjMTA7JiMxMzsmIzEwO1xmU1VCU0VDUkVUQVImI3hDRDtBIERFIEVEVUNBQ0kmI3hEMztOIEUgSU5WRVNUSUdBQ0kmI3hEMztOIFRFQ05PTCYjeEQzO0dJQ0FTIERJUkVDQ0kmI3hEMztOIEdFTkVSQUwgREUgQ0VOVFJPUyBERSBGT1JNQUNJJiN4RDM7TiBQQVJBIEVMIFRSQUJBSk8mIzEzOyYjMTA7TElTVEEgREUgQVNJU1RFTkNJQSAoTEFELTA0KSYjMTM7JiMxMDsmIzEzOyYjMTA7VU5JREFEIERFIENBUEFDSVRBQ0kmI3hEMztOOiBBUkVBOiBBRE1JTklTVFJBQ0kmI3hEMztOIEZFQ0hBIElOSUNJTzogMDMvMDgvMjAyMSYjMTM7JiMxMDsmIzEzOyYjMTA7MTc5IFRBUEFDSFVMQSYjMTM7JiMxMDsmIzEzOyYjMTA7Q0xBVkUgQ0NUOiYjMTM7JiMxMDsmIzEzOyYjMTA7MDdFSUMwMDAxQyYjMTM7JiMxMDsmIzEzOyYjMTA7Q0lDTE8gRVNDT0xBUjomIzEzOyYjMTA7JiMxMzsmIzEwOzIwMjEtMjAyMiYjMTM7JiMxMDsmIzEzOyYjMTA7R1JVUE86JiMxMzsmIzEwOyYjMTM7JiMxMDswMDE0JiMxMzsmIzEwOyYjMTM7JiMxMDtNRVM6JiMxMzsmIzEwOyYjMTM7JiMxMDtFU1BFQ0lBTElEQUQ6IEFTSVNURU5DSUEgRUpFQ1VUSVZBJiMxMzsmIzEwOyYjMTM7JiMxMDtDVVJTTzogT1JUT0dSQUZJQSBZIFJFREFDQ0lPTiBERSBET0NVTUVOVE9TIE9GSUNJQUxFUyYjMTM7JiMxMDsmIzEzOyYjMTA7Q0xBVkU6IDFDLTIxLUFTRUotRVhULTAwMTQmIzEzOyYjMTA7JiMxMzsmIzEwO0ZFQ0hBIFRFUk1JTk86IDMwLzA4LzIwMjEmIzEzOyYjMTA7JiMxMzsmIzEwO0hPUkFSSU86IExVTkVTIEEgVklFUk5FUyBERSAxMDowMCBhLm0uIEEgMTI6MDAgcC5tLiYjMTM7JiMxMDsmIzEzOyYjMTA7Q1VSUDogR09ETjg2MDMyMk1DU1JNTDA4JiMxMzsmIzEwOyYjMTM7JiMxMDtBR09TVE8mIzEzOyYjMTA7JiMxMzsmIzEwO0EmI3hEMTtPOiAyMDIxJiMxMzsmIzEwOyYjMTM7JiMxMDtOIFUgTSYjMTM7JiMxMDsmIzEzOyYjMTA7TiYjeERBO01FUk8gREUgQ09OVFJPTCYjMTM7JiMxMDsmIzEzOyYjMTA7MjUmIzEzOyYjMTA7JiMxMzsmIzEwOzIxMDcwMDAxQzA5MDAmIzEzOyYjMTA7JiMxMzsmIzEwO05PTUJSRSBERUwgQUxVTU5PIFBSSU1FUiBBUEVMTElETy9TRUdVTkRPIEFQRUxMSURPL05PTUJSRShTKSYjMTM7JiMxMDtSVUJJTiBOSUVUTyBESUFOQSBBVURSRVkmIzEzOyYjMTA7JiMxMzsmIzEwOzEgMiAzIDQgNSA2IDcgOCA5IDEwIDExIDEyIDEzIDE0IDE1IDE2IDE3IDE4IDE5IDIwIDIxIDIyIDIzIDI0IDI1IDI2IDI3IDI4IDI5IDMwIDMxICp4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHgmIzEzOyYjMTA7JiMxMzsmIzEwO1RPVEFMJiMxMzsmIzEwOyYjMTM7JiMxMDtBJiMxMzsmIzEwOyYjMTM7JiMxMDtJJiMxMzsmIzEwOyYjMTM7JiMxMDsxJiMxMzsmIzEwOyYjMTM7JiMxMDsyNyYjMTM7JiMxMDsmIzEzOyYjMTA7R09SRElMTE8gRE9NSU5HVUVaIE5BTExFTFkgQkVSRU5JQ0UgTk9NQlJFIFkgRklSTUEgREVMIElOU1RSVUNUT1ImIzEzOyYjMTA7JiMxMzsmIzEwO1NFTExPIj48Y3VlcnBvPlBvciBtZWRpbyBkZSBsYSBwcmVzZW50ZSBtZSBwZXJtaXRvIHNvbGljaXRhciBlbCBhcmNoaXZvIExJU1RBX0FTSVNURU5DSUFfMUMtMjEtQVNFSi1FWFQtMDAxNC5QREY8L2N1ZXJwbz48L2FyY2hpdm8';
                $response = Http::post('https://interopera.chiapas.gob.mx/FirmadoElectronicoDocumentos/api/v1/DocumentoXml/CadenaOriginalBase64', [
                    'xml_OriginalBase64' => $xmlBase64,
                    'apiKey' => 'dwLChYOVylB9htqD9qIaSVHddKzWKiqXqmh7fFRHwFJk2x'
                ]);

                // dd($response->json());

                if ($response->json()['cadenaOriginal'] != null) {
                    $urlFile = $this->uploadFileServer($request->file('doc'), $nameFile);
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
                    $dataInsert->save();

                    return redirect()->route('addDocument.inicio')->with('warning', 'Se agrego el documento correctamente, puede ver el status en el que se encuentra en el apartado Firma Electronica');
                } else {
                    return redirect()->route('addDocument.inicio')->with('danger', 'Ocurrio un error al obtener la cadena original, por favor intente de nuevo');
                }
            } else { // no hay firmantes
                return redirect()->route('addDocument.inicio')->with('warning', 'No se agregaron firmantes');
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

}

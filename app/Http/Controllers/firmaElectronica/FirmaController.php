<?php

namespace App\Http\Controllers\firmaElectronica;

use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use App\Http\Controllers\Controller;

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

        // $result = ArrayToXml::convert($array);
        $result = ArrayToXml::convert($array, [
            'rootElementName' => 'DocumentoChis',
            '_attributes' => [
                'version' => '1.0',
                'fecha_creacion' => '2021-08-05',
                'no_oficio' => 'ICATECH/0001/2021',
                'dependencia_origen' => 'INSTITUTO DE CAPACITACION Y VINCULACION TECNOLOGICA DEL ESTADO DE CHIAPAS',
                'asunto_docto' => 'LISTA DE ASISTENCIA',
                'tipo_docto' => 'ACS',
                'xmlns' => 'http://firmaelectronica.chiapas.gob.mx/GCD/DoctoGCD',
            ],
        ]);
        // true, 'UTF-8');
        // dd($result);
        
        return view('layouts.firmaElectronica.firmaElectronica');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}

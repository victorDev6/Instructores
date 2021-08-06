<?php

namespace App\Http\Controllers\firmaElectronica;

use App\Funcionarios;
use App\Http\Controllers\Controller;
use App\instructores;
use Illuminate\Http\Request;

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
        dd($request);
    }

}

<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistroController extends Controller {

    public function index() {
        // $organos = DB::table('organos')->where('id', '!=', 5) ->get();
        return view('layouts.registro');
    }

    public function store(Request $request) {
        // dd($request);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 'organo' => ['required'],
            'telefono' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // $organo = DB::table('organos')->where('id', '=', $request->organo)->get();
        /*$id_parent = $organo[0]->id_parent;
        if ($id_parent == 0) {
            $id_parent = null;
        }*/

        User::create([
            'name' => $request->name,
            // 'id_organo' => $id_parent,
            // 'id_area' => $request->organo,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'tipo_usuario' => 0,
            'curp' => ' ',
            'id_sivyc' => '10',
            'password' => Hash::make($request->password),
        ])->assignRole($request->unidades);

        return redirect()->route('registro.inicio')->with('success', 'Usuario Agregado Exitosamente');
    }

}

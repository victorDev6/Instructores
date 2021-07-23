<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermisosController extends Controller {
    
    public function index() {
        $roles = DB::table('roles')->get();

        return view('layouts.agregarPermiso', compact('roles'));
    }

    public function store(Request $request) {
        Permission::create(['name' => $request->permiso])->assignRole($request->rol);

        return redirect()->route('permisos.inicio')->with('success', 'PERMISO AGREGADO EXITOSAMENTE');
    }
}
